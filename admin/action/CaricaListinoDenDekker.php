<?php 
class CaricaListinoDenDekker extends DBSmartyAction
{
	function __construct()
	{
		parent::DBSmartyAction();
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['import_from_mail']))
		{
			if(!empty($_FILES['listino']['name']))
			{
				$ext = substr($_FILES['listino']['name'], -3);
				$filename = substr($_FILES['listino']['name'], 0, -4);
				$destination = APP_ROOT."/upload_fornitori/dendekker/".$filename."_".date('Ymd_His').".".$ext;
				move_uploaded_file($_FILES['listino']['tmp_name'], $destination);
			}
			else
			{
				$directory = APP_ROOT."/upload_fornitori/dendekker/";
				$d = dir($directory);
				while (false !== ($entry = $d->read())) 
				{
					if($entry != '.' && $entry != '..' && $entry != '.DS_Store')
						$destination = APP_ROOT."/upload_fornitori/dendekker/".$entry;
				}
				$d->close();
			}

			include_once(APP_ROOT."/libs/ext/Excel/reader.php");
			$data = new Spreadsheet_Excel_Reader();
			$data->setOutputEncoding('CP1251'); // Set output Encoding.
			$data->read($destination);
			
// 			$query_trucate = "TRUNCATE TABLE giacenze_fornitori WHERE operatore = 'ImportFornitoriDenDekker'";
			$query_trucate = "DELETE FROM giacenze_fornitori WHERE operatore = 'ImportFornitoriDenDekker'";
			mysql_query($query_trucate, $this->conn->connection);
			
			$query_trucate = "ALTER TABLE giacenze_fornitori AUTO_INCREMENT = 1";
			mysql_query($query_trucate, $this->conn->connection);
				
			$j = 0;
			for ($i = 11; $i <= $data->sheets[0]['numRows']; $i++)
			{
				$values = $data->sheets[0]['cells'][$i];
				if(!empty($values[1]))
				{
					$exp = explode(' x ', $values[3]);
					if($exp[1] <= 1)
					{
						$exp[1] = $exp[0];
						$exp[0] = 1;
					}
					$qty_scatola = $exp[1];
					$qty_scatole = $exp[0];

					$raggio = str_replace(',', '.', $values[5]) / 2;
 					$raggio = $raggio + 1.5;
					//$raggio = str_replace(',', '.', $values[5]);
					
					$altezza = str_replace(',', '.', $values[6]);
					$area_base = 3.14 * $raggio * $raggio;
	
					$volume_singolo = $area_base * $altezza;
					$volume_sc = $volume_singolo * $qty_scatola;
					//$volume_sc = ($volume_singolo * $qty_scatola)*$qty_scatole;
						
					$query = "INSERT INTO `giacenze_fornitori` (
							`codice`, 
							`bar_code`, 
							`descrizione`, 
							`qta_scatola`, 
							`qta_pianale`, 
							`diametro_vaso`, 
							`altezza_pianta`,
							
							`volume_singolo`,
							`volume_sc`,
							
							`prezzo_sc`, 
							`prezzo_pi`, 
							`carrello`, 
							`stato`, 
							`fornitore`, 
							`data_inserimento_riga`, 
							`data_modifica_riga`, 
							`is_active`, 
							`operatore`) VALUES (
							'".$values[1]."', 
							'".$values[12]."', 
							'".mysql_real_escape_string($values[2])."', 
							'1 x ".$exp[1]."', 
							'".$values[4]."', 
							'".str_replace(',', '.', $values[5])."', 
							'".str_replace(',', '.', $values[6])."', 
									
							'".$volume_singolo."',
							'".$volume_sc."',
							
							'".$values[7]."', 
							'".$values[8]."', 
							'".$values[9]."', 
							'".mysql_real_escape_string($values[14])."', 
							'dendekker', 
							'".date('Y-m-d')."', 
							'".date('Y-m-d')."', 
							'1', 
							'ImportFornitoriDenDekker');";
					mysql_query($query, $this->conn->connection);
					
					$j++;
				}
			}
			unlink($destination);
			$this->tEngine->assign('msg', "Importazione contenuti avventuta con successo, sono stati importati ".$j." prodotti.");
		}
		$this->tEngine->assign('action_class_name', get_class($this));
		$this->tEngine->assign('tpl_action', get_class($this));
		$this->tEngine->display('Index');
	}
}
?>