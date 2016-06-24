<?php
define( 'APP_ROOT', str_replace('/action', '', str_replace('\\', '/', getcwd()) ));
define( 'APPLICATION_CONFIG_FILENAME', 'config.xml' );

if(!isset($_SERVER['APPLICATION_ENV']))
	$_SERVER['APPLICATION_ENV'] = 'pro';

error_reporting(E_ALL);
// ini_set('display_errors', true);
ini_set("max_execution_time", "360000");

include_once(APP_ROOT.'/libs/Dump.php');
include_once(APP_ROOT.'/libs/INI.php');
include_once(APP_ROOT.'/libs/configureSystem.php');
include_once(APP_ROOT.'/libs/BeanBase.php');
include_once(APP_ROOT.'/libs/xml_parser.php');
include_once(APP_ROOT.'/libs/debugTime.php');
/**
 * Inclusione dello ZendCache
 */
define( 'CACHE_CONFIG_INI_PATH', APP_ROOT.'/ZendCache/' );
ini_set('include_path', APP_ROOT.'/ZendCache/');
require_once(APP_ROOT.'/ZendCache/Zend/Cache.php');
require_once(APP_ROOT.'/ZendCache/Cache.php');
/**
 * Inclusione dello ZendCache
*/
new configureSystem();

/***
 * Inizio Logica di Caricamento
 */
	$conn;
	$operator;
	$email_customer_name;	
	$separator = ';';
	$riepilogo_email;
	$fileCustomer;
	$fileContent;
	$fileArticle;
	$fileFamily;
	$customer_name;
	$email_customer_logo;

	$flagFile;
	
	$conn = MyDB::connect();
	$operator = 'StreamImportProcedure';
	
	$flagFile = APP_ROOT."/FlorSysIntegration/In/finito.txt";
	
	if(is_file(APP_ROOT."/FlorSysIntegration/In/CLIENTI.CSV"))
		$fileCustomer = APP_ROOT."/FlorSysIntegration/In/CLIENTI.CSV";
	if(is_file(APP_ROOT."/FlorSysIntegration/In/GIACENZA.CSV"))
		$fileContent = APP_ROOT."/FlorSysIntegration/In/GIACENZA.CSV";
	if(is_file(APP_ROOT."/FlorSysIntegration/In/FAMIGLIE.CSV"))
		$fileFamily = APP_ROOT."/FlorSysIntegration/In/FAMIGLIE.CSV";
	if(is_file(APP_ROOT."/FlorSysIntegration/In/ARTICOLI.CSV"))
		$fileArticle = APP_ROOT."/FlorSysIntegration/In/ARTICOLI.CSV";

	$email_customer_name = PREFIX_META_TITLE;
	$email_customer_logo = WWW_ROOT.'themes/uploads/2013/03/logo1.png';
	
	$inizio = microtime(true);
	Start($flagFile, $fileCustomer, $fileContent, $fileFamily, $fileArticle);
	
	function Start($flagFile = null, $fileCustomer = null, $fileContent = null, $fileFamily = null, $fileArticle = null)
	{
		global $tempo;
		
// 		if(true)
		if($_REQUEST['user'] == 'admin' && $_REQUEST['pwd'] == 'f7b44cfafd5c52223d5498196c8a2e7b' && $_SERVER['HTTP_STREAM_DEMO_INTEGRATION'] == 'f7b44cfafd5c52223d5498196c8a2e7b') //pwd = md5('stream')
		{
			
			if(is_file($flagFile))
			{
				if(date('H') > 2)
					$fileArticle = null;
				
				destroyData($fileCustomer, $fileContent, $fileFamily, $fileArticle);
				$isImport = false;

				if(!empty($fileArticle))
				{
					chmod($fileArticle, 0777);
					importArticoli($fileArticle);
					copy($fileArticle, APP_ROOT."/FlorSysIntegration/In/bck/ARTICOLI_".date('d_m_Y__H_i_s').".CSV");
					unlink($fileArticle);
					$isImport = true;
				}

				if(!empty($fileContent))
				{
					chmod($fileContent, 0777);
					importContent($fileContent);
					copy($fileContent,  APP_ROOT."/FlorSysIntegration/In/bck/GIACENZA_".date('d_m_Y__H_i_s').".CSV");
					unlink($fileContent);
					$isImport = true;
				}
				
				if(!empty($fileCustomer))
				{
					chmod($fileCustomer, 0777);
					importCustomer($fileCustomer);
					copy($fileCustomer, APP_ROOT."/FlorSysIntegration/In/bck/CLIENTI_".date('d_m_Y__H_i_s').".CSV");
					unlink($fileCustomer);
					$isImport = true;
				}
				
				if(!empty($fileFamily))
				{
					chmod($fileFamily, 0777);
					importFamily($fileFamily);
					copy($fileFamily, APP_ROOT."/FlorSysIntegration/In/bck/FAMIGLIE_".date('d_m_Y__H_i_s').".CSV");
					unlink($fileFamily);
					$isImport = true;
				}
				// CANCELLO IL FILE SEMAFORO
				unlink($flagFile);

				if($isImport)
				{
					$fine = microtime(true);
					$tempo = $fine - $inizio;
					sendEmailConfirmation();
				}
			}
		}
	}
	
	function sendEmailConfirmation()
	{
		global $email_customer_name;
		global $riepilogo_email;
		global $riepilogo_email_articoli;
		global $tempo;
	
		// 		$hdrs = array("From" 	=> EMAIL_ADMIN_FROM,
		// 					  "To" 			=> "siso77@gmail.com",
		// 					  "Cc" 			=> "",
		// 					  "Bcc" 		=> "",
		// 					  "Subject" 	=> "Importazione Contenuti da FlorSystem per ".$this->email_customer_name,
		// 					  "Date"		=> date("r")
		// 					  );
		// 		$this->setHeaders($hdrs);
	
		$to = "siso77@gmail.com";
		mail($to, "Importazione Contenuti da FlorSystem per ".$email_customer_name,$riepilogo_email."
				".$riepilogo_email_articoli."
				Tempo di esecuzione".$tempo." sec.");
	}
	
	function destroyData($fileCustomer = null, $fileContent = null, $fileFamily = null, $fileArticle = null)
	{
		global $conn;
		global $operator;

		if(!empty($fileCustomer))
			mysql_query("TRUNCATE TABLE customer", $conn->connection);
	}
		
	function importCustomer($File)
	{
		global $conn;
		global $operator;
		global $separator;

		$fh = fopen($File, 'rb'); 
		$key = 0;

		while ( ($data = fgetcsv($fh, 1000, $separator)) !== false)
		{
			$query = "INSERT INTO customer (
				`customer_code` ,
				`nome` ,
				`cognome` ,
				`codice_fiscale` ,
				`ragione_sociale` ,
				`p_iva` ,
				`indirizzo` ,
				`provincia` ,
				`stato` ,
				`citta` ,
				`cap` ,
				`cellulare` ,
				`fisso` ,
				`fax` ,
				`email` ,
				`text_spedizione` ,
				`indirizzo_spedizione` ,
				`cap_spedizione` ,
				`citta_spedizione` ,
				`provincia_spedizione` ,
				`stato_spedizione` ,
				`percentuale_sconto` ,
				`listino` ,
				`registred_from` ,
				`data_inserimento_riga` ,
				`data_modifica_riga` ,
				`is_active` ,
				`operatore`
				) VALUES (
				'".$data[0]."',
				'',
				'',
				'',
				'".mysql_real_escape_string($data[1])."',
				'".mysql_real_escape_string($data[11])."',
				'".mysql_real_escape_string($data[2])."',
				'".mysql_real_escape_string($data[5])."',
				'',
				'".mysql_real_escape_string($data[4])."',
				'".mysql_real_escape_string($data[3])."',
				'".mysql_real_escape_string($data[8])."',
						
						
				'".mysql_real_escape_string($data[9])."',
				'".mysql_real_escape_string($data[7])."',
				'".mysql_real_escape_string($data[10])."',
						
				'".mysql_real_escape_string($data[1])."',
				'".mysql_real_escape_string($data[2])."',
				'".mysql_real_escape_string($data[3])."',
				'".mysql_real_escape_string($data[4])."',
				'".mysql_real_escape_string($data[5])."',
				'',
				'',
				'".mysql_real_escape_string($data[12])."',
				'".$operator."',
				'".date('Y-m-d H:i:s')."',
				'".date('Y-m-d H:i:s')."',
				1,
				'".$operator."')";
				
			mysql_query($query, $conn->connection);
			$key++;
		}
	}
	
	function importContent($File)
	{
		global $conn;
		global $operator;
		global $separator;
		global $riepilogo_email;

		$fh = fopen($File, 'rb'); 
		$key_new_prod = 0;
		$key_upd_prod = 0;
		$key_new_giac = 0;
		$key_upd_giac = 0;
		while ( ($data = fgetcsv($fh, 1000, $separator)) !== false)
		{
			$result = mysql_query("SELECT * FROM `gruppi_merceologici` WHERE gruppo = '".$data[8]."'", $conn->connection);
			if(!$row=mysql_fetch_assoc($result))
				mysql_query("INSERT INTO `gruppi_merceologici` (gruppo) VALUES ('" . $data[8]. "')", $conn->connection);
			
			$result = mysql_query("SELECT * FROM `famiglie` WHERE famiglia = '".$data[8]."'", $conn->connection);
			if(!$row=mysql_fetch_assoc($result))
				mysql_query("INSERT INTO `famiglie` (codice_famiglia, famiglia) VALUES ('".substr($data[8], 0, 3)."', '".$data[8]."')", $conn->connection);
			
			
			$result = mysql_query("SELECT * FROM `gruppi_merceologici` WHERE gruppo = '".$data[8]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
				$id_gm = $row['id'];
				
			$result = mysql_query("SELECT * FROM `famiglie` WHERE famiglia = '".$data[8]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
				$id_famiglia = $row['id'];

			$result = mysql_query("SELECT * FROM `content` WHERE vbn = '".$data[1]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
			{
				if($row['prezzo_0'] != str_replace(',', '.', $data[12]))
					$to_uodate = true;
				if($row['prezzo_1'] != str_replace(',', '.', $data[14]))
					$to_uodate = true;
				if($row['prezzo_2'] != str_replace(',', '.', $data[15]))
					$to_uodate = true;
				if($row['prezzo_3'] != str_replace(',', '.', $data[16]))
					$to_uodate = true;
				if($row['prezzo_4'] != str_replace(',', '.', $data[17]))
					$to_uodate = true;
				if($row['prezzo_5'] != str_replace(',', '.', $data[18]))
					$to_uodate = true;
				if($row['prezzo_6'] != str_replace(',', '.', $data[19]))
					$to_uodate = true;
				if($row['prezzo_7'] != str_replace(',', '.', $data[20]))
					$to_uodate = true;
				if($row['prezzo_8'] != str_replace(',', '.', $data[21]))
					$to_uodate = true;
				if($row['prezzo_9'] != str_replace(',', '.', $data[22]))
					$to_uodate = true;
				
				if($to_uodate)
				{
					$query = "UPDATE content SET prezzo_0 = '".str_replace(',', '.', $data[12])."', 
												 prezzo_1 = '".str_replace(',', '.', $data[14])."',
												 prezzo_2 = '".str_replace(',', '.', $data[15])."',
												 prezzo_3 = '".str_replace(',', '.', $data[16])."',
												 prezzo_4 = '".str_replace(',', '.', $data[17])."',
												 prezzo_5 = '".str_replace(',', '.', $data[18])."',
												 prezzo_6 = '".str_replace(',', '.', $data[19])."',
												 prezzo_7 = '".str_replace(',', '.', $data[20])."',
												 prezzo_8 = '".str_replace(',', '.', $data[21])."',
												 prezzo_9 = '".str_replace(',', '.', $data[22])."',
												 WHERE id = ".$row['id'];
					mysql_query($query, $conn->connection);
					$query = null;
					$key_upd_prod++;
				}
				$id_content = $row['id'];
			}
			else
			{
				$data[2] = str_replace("'", "", $data[2]);
				$data[3] = str_replace("'", "", $data[3]);
				$data[4] = str_replace("'", "", $data[4]);
				$data[5] = str_replace("'", "", $data[5]);

				$exp = explode(' ', $data[5]);
				$tipoColore = $exp[1];
				$exp2 = explode(' ', $data[7]);				
				$query = "INSERT INTO content (vbn,
						nome_it,
						descrizione_it,
						nome_en,
						descrizione_en,
						id_gm,
						id_famiglia,
						C1,
						C2,
						C3,
						C4,
						C5,
						tipo_colore,
						prezzo_0,
						prezzo_1,
						prezzo_2,
						prezzo_3,
						prezzo_4,
						prezzo_5,
						prezzo_6,
						prezzo_7,
						prezzo_8,
						prezzo_9,
						cod_iva,
						is_active,
						data_inserimento_riga,
						data_modifica_riga,
						operatore) VALUES
						('".$data[1]."',
						'".$data[2]."',
						'".$data[2]."',
						'".$data[2]."',
						'".$data[2]."',
						".$id_gm.",
						".$id_famiglia.",
						'".$data[3]."',
						'".$data[4]."',
						'".$exp[0]."',
						'".$data[6]."',
						'".$exp2[0]."',
						'".$tipoColore."',
						'".str_replace(',', '.', $data[12])."',
						'".str_replace(',', '.', $data[14])."',
						'".str_replace(',', '.', $data[15])."',
						'".str_replace(',', '.', $data[16])."',
						'".str_replace(',', '.', $data[17])."',
						'".str_replace(',', '.', $data[18])."',
						'".str_replace(',', '.', $data[19])."',
						'".str_replace(',', '.', $data[20])."',
						'".str_replace(',', '.', $data[21])."',
						'".str_replace(',', '.', $data[22])."',								
						".$data[13].",
						1,
						'".date('Y-m-d H:i:s')."',
						'".date('Y-m-d H:i:s')."',
						'".$operator."')";
				
				mysql_query($query, $conn->connection);
				
				$result = mysql_query("SELECT * FROM content WHERE id = ( SELECT MAX( id ) AS id_content FROM content )", $conn->connection);
				if($row=mysql_fetch_assoc($result))
					$id_content = $row['id'];
				
				$query = null;
				$key_new_prod++;
			}
			if(!empty($data[11]))
			{			
				$result = mysql_query("SELECT * FROM giacenze WHERE bar_code = '".$data[0]."' ORDER BY bar_code DESC", $conn->connection);
				if($row=mysql_fetch_assoc($result))
				{
					if($row['quantita'] != $data[11])
						$to_uodate = true;
					if($row['disponibilita'] != $data[10])
						$to_uodate = true;
					if($to_uodate)
					{
						$query = "UPDATE giacenze SET quantita = ".$data[11].", disponibilita = ".$data[10]." WHERE id = ".$row['id'];
						mysql_query($query, $conn->connection);
						$query = null;
						$key_upd_giac++;
					}
				}
				else
				{			
					$query = "INSERT INTO giacenze (
							bar_code,
							id_content,
							id_fornitore,
							quantita,
							disponibilita,
							data_inserimento_riga,
							data_modifica_riga,
							is_active,
							operatore) VALUES (
							'".$data[0]."',
							".$id_content.",
							0,
							'".$data[11]."',
							'".$data[10]."',
							'".date('Y-m-d H:i:s')."',
							'".date('Y-m-d H:i:s')."',
							1,
							'".$operator."')";	
		
					mysql_query($query, $conn->connection);
					$query = null;

					$key_new_giac++;
				}
			}
			$id_gm = null;
			$id_content = null;
			$id_famiglia = null;			

		}
		fclose($fh);
		
		$riepilogo_email = "Riepilogo Importazione Giacenze:
				Nuovi Contenuti:".$key_new_prod."
				Contenuti Modificati:".$key_upd_prod."
				Nuove Giacenze:".$key_new_giac."
				Giacenze Modificate:".$key_upd_giac."";

		return true;
	}
	
	function importArticoli($File)
	{
		global $conn;
		global $operator;
		global $separator;
		global $riepilogo_email_articoli;

		$fh = fopen($File, 'rb');
		$key_new_prod = 0;
		$key_upd_prod = 0;
		
		while ( ($data = fgetcsv($fh, 1000, $separator)) !== false)
		{
			$result = mysql_query("SELECT * FROM `gruppi_merceologici` WHERE gruppo = '".$data[5]."'", $conn->connection);
			if(!$row=mysql_fetch_assoc($result))
				mysql_query("INSERT INTO `gruppi_merceologici` (gruppo) VALUES ('" . $data[5]. "')", $conn->connection);

			$result = mysql_query("SELECT * FROM `famiglie` WHERE famiglia = '".$data[5]."'", $conn->connection);
			if(!$row=mysql_fetch_assoc($result))
				mysql_query("INSERT INTO `famiglie` (codice_famiglia, famiglia) VALUES ('".substr($data[5], 0, 3)."', '".$data[5]."')", $conn->connection);


			$result = mysql_query("SELECT * FROM `gruppi_merceologici` WHERE gruppo = '".$data[5]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
				$id_gm = $row['id'];
			
			$result = mysql_query("SELECT * FROM `famiglie` WHERE famiglia = '".$data[5]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
				$id_famiglia = $row['id'];
		
			$result = mysql_query("SELECT * FROM `content` WHERE vbn = '".$data[1]."'", $conn->connection);
			if(!$row=mysql_fetch_assoc($result))
			{		
				$data[2] = str_replace("'", "", $data[2]);
				$data[3] = str_replace("'", "", $data[3]);
				$data[4] = str_replace("'", "", $data[4]);
				
				$exp = explode(' ', $data[4]);
				$tipoColore = $exp[1];

				$query = "INSERT INTO content (vbn,
						nome_it,
						descrizione_it,
						nome_en,
						descrizione_en,
						id_gm,
						id_famiglia,
						C1,
						C2,
						C3,
						C4,
						C5,
						tipo_colore,
						prezzo_0,
						prezzo_1,
						prezzo_2,
						prezzo_3,
						prezzo_4,
						prezzo_5,
						prezzo_6,
						prezzo_7,
						prezzo_8,
						prezzo_9,
						cod_iva,
						is_active,
						data_inserimento_riga,
						data_modifica_riga,
						operatore) VALUES
						('".$data[1]."',
						'".$data[2]."',
						'".$data[2]."',
						'".$data[2]."',
						'".$data[2]."',
						".$id_gm.",
						".$id_famiglia.",
						'".$data[3]."',
						'".$data[4]."',
						'".$exp[0]."',
						'',
						'',
						'".$tipoColore."',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						1,
						'".date('Y-m-d H:i:s')."',
						'".date('Y-m-d H:i:s')."',
						'".$operator."_articoli')";
				mysql_query($query, $conn->connection);
				$key_new_prod++;
			}
			$id_gm = null;
			$id_famiglia = null;
		}
		$riepilogo_email_articoli = "Riepilogo Importazione Articoli:
				Nuovi Contenuti:".$key_new_prod."
				Contenuti Modificati:".$key_upd_prod."";

		return true;
	}
?>