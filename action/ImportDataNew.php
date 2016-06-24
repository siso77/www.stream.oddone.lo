<?php
define( 'APP_ROOT', str_replace('/action', '', str_replace('\\', '/', getcwd()) ));
define( 'APPLICATION_CONFIG_FILENAME', 'config.xml' );

if(!isset($_SERVER['APPLICATION_ENV']))
	$_SERVER['APPLICATION_ENV'] = 'pro';

error_reporting(E_ERROR);
ini_set('display_errors', false);
ini_set("max_execution_time", "36000000000");
ini_set('memory_limit','2048M');

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
	$num_customers_inserted;
	$num_products_inserted;
	$num_family_inserted;
	$num_content_inserted;
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
	$flagIntFile = APP_ROOT."/FlorSysIntegration/In/start.txt";
	
	if(is_file(APP_ROOT."/FlorSysIntegration/In/CLIENTI.CSV"))
		$fileCustomer = APP_ROOT."/FlorSysIntegration/In/CLIENTI.CSV";
	if(is_file(APP_ROOT."/FlorSysIntegration/In/GIACENZA.CSV"))
		$fileContent = APP_ROOT."/FlorSysIntegration/In/GIACENZA.CSV";
	if(is_file(APP_ROOT."/FlorSysIntegration/In/FAMIGLIE.CSV"))
		$fileFamily = APP_ROOT."/FlorSysIntegration/In/FAMIGLIE.CSV";
	if(is_file(APP_ROOT."/FlorSysIntegration/In/ARTICOLI.CSV"))
		$fileArticle = APP_ROOT."/FlorSysIntegration/In/ARTICOLI.CSV";
	if(is_file(APP_ROOT."/FlorSysIntegration/In/FORNITORI.CSV"))
		$fileFornitori = APP_ROOT."/FlorSysIntegration/In/FORNITORI.CSV";
	if(is_file(APP_ROOT."/FlorSysIntegration/In/DESTINAZIONI.CSV"))
		$fileDestinazioni = APP_ROOT."/FlorSysIntegration/In/DESTINAZIONI.CSV";
	
	$email_customer_name = PREFIX_META_TITLE;
	$email_customer_logo = WWW_ROOT.'themes/uploads/2013/03/logo1.png';

	$debugTime = new debugTime();
	Start($flagFile, $fileCustomer, $fileFornitori, $fileContent, $fileFamily, $fileArticle, $fileDestinazioni, $flagIntFile);
	$debugTime->OutPutDebugTime('Esecuzione avvenuta in sec: ');
	
	
	function Start($flagFile = null, $fileCustomer = null, $fileFornitori = null, $fileContent = null, $fileFamily = null, $fileArticle = null, $fileDestinazioni = null, $flagInizioFile = null)
	{
		if(is_file($flagInizioFile))
		{
			date_default_timezone_set('Europe/Dublin');
			$start_time = date('Y-m-d H:i:s', filemtime($flagInizioFile)); // fill this in with actual time in this format
			$end_time = date('Y-m-d H:i:s'); // fill this in with actual time in this format
			$start = new DateTime($start_time);
			$interval = $start->diff(new DateTime($end_time));
			if($interval->h > 0 || $interval->d > 0)
			{
				unlink($flagInizioFile);
				$flagInizioFile = null;
			}
		}
		
		if(true)
		{
			if(is_file($flagIntFile))
				return false;

			if(is_file($flagFile))
			{
				$fp = fopen($flagIntFile,"w");
				fwrite($fp, '');
				fclose($fp);
				
// 				if(date('H') > 2)
// 					$fileArticle = null;
				
				storeMonitor();
				$isImport = false;
				if(!empty($fileArticle))
				{
					chmod($fileArticle, 0777);
					importArticoli($fileArticle);
					unlink($fileArticle);
					$isImport = true;
				}
				
				if(!empty($fileCustomer))
				{
					chmod($fileCustomer, 0777);
					importCustomer($fileCustomer);
					unlink($fileCustomer);
					$isImport = true;
				}
				
				if(!empty($fileFornitori))
				{
					chmod($fileFornitori, 0777);
					importFornitori($fileFornitori);
					unlink($fileFornitori);
					$isImport = true;
				}
				if(!empty($fileDestinazioni))
				{
					chmod($fileDestinazioni, 0777);
					importDestinazioni($fileDestinazioni);
					unlink($fileDestinazioni);
					$isImport = true;
				}
				
// 				checkEternalIds();
				Base_CacheCore::getInstance()->clean();

				// CANCELLO IL FILE SEMAFORO
				unlink($flagFile);
				unlink($flagIntFile);
			}
		}
	}

	function sendEmailConfirmation()
	{
		global $email_customer_name;

		$headers .= 'From: Stream <".EMAIL_ADMIN_FROM.">' . "\r\n";
		$to = "siso77@gmail.com";
		mail($to, "Importazione Contenuti da FlorSystem per ".$email_customer_name,"IMPORTAZIONE AVVENUTA CON SUCCESSO", $headers);
	}
	
	function storeMonitor()
	{
		global $conn;
		$query = "UPDATE  `monitor` SET `last_execute` = '".date('Y-m-d H:i:s')."'";
		mysql_query($query, $conn->connection);
	}
	
	function destroyData($fileCustomer = null, $fileContent = null, $fileFamily = null, $fileArticle = null)
	{
		global $conn;
		global $operator;

		if(!empty($fileFamily))
			mysql_query("TRUNCATE TABLE famiglie", $conn->connection);
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
			$result = mysql_query("SELECT * FROM `customer` WHERE customer_code = '".$data[0]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
			{
				$customer_exists = $row['id'];
				$result = mysql_query("UPDATE `customer`
						SET
						ragione_sociale = '".mysql_real_escape_string($data[1])."',
						p_iva = '".mysql_real_escape_string($data[11])."',
						indirizzo = '".mysql_real_escape_string($data[2])."',
						provincia = '".mysql_real_escape_string($data[5])."',
						stato = '".mysql_real_escape_string($data[6])."',
						citta = '".mysql_real_escape_string($data[4])."',
						cap = '".mysql_real_escape_string($data[3])."',
						cellulare = '".mysql_real_escape_string($data[8])."',
						fisso = '".mysql_real_escape_string($data[7])."',
						fax = '".mysql_real_escape_string($data[9])."',
						email = '".mysql_real_escape_string($data[10])."',
						listino = '".$data[12]."',
						scorporo_iva = '".$data[13]."',
						data_modifica_riga = '".date('Y-m-d')."',
						operatore = '".$operator."'
					WHERE id = '".$row['id']."'", $conn->connection);
			}
			else
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
					`scorporo_iva`,
					`data_inserimento_riga` ,
					`data_modifica_riga` ,
					`is_active` ,
					`operatore`
					) VALUES (
					'".mysql_real_escape_string($data[0])."',
					'',
					'',
					'',
					'".mysql_real_escape_string($data[1])."',
					'".mysql_real_escape_string($data[11])."',
					'".mysql_real_escape_string($data[2])."',
					'".mysql_real_escape_string($data[5])."',
					'".mysql_real_escape_string($data[6])."',
					'".mysql_real_escape_string($data[4])."',
					'".mysql_real_escape_string($data[3])."',
					'".mysql_real_escape_string($data[8])."',
					'".mysql_real_escape_string($data[7])."',
					'".mysql_real_escape_string($data[9])."',
					'".mysql_real_escape_string($data[10])."',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'".$data[12]."',
					'',
					'".$data[13]."',
					'".date('Y-m-d')."',
					'".date('Y-m-d')."',
					1,
					'".$operator."')";
				mysql_query($query, $conn->connection);
				$query = null;
			}
			$key++;
		}
	}	

	function importDestinazioni($File)
	{
		global $conn;
		global $operator;
		global $separator;

		$fh = fopen($File, 'rb'); 
		$key = 0;
		while ( ($data = fgetcsv($fh, 1000, $separator)) !== false)
		{
			$listino = $data[12];
			$p_iva = mysql_real_escape_string($data[11]);
			$email = $data[10];
			
			$codice_etiflor = $data[count($data)-2];
			if(!is_numeric($p_iva))
			{
				$email .= ";".$data[11];
				$p_iva = mysql_real_escape_string($data[12]);
// 				$codice_etiflor = $data[13];
			}
			if(!is_numeric($p_iva))
			{
				$email .= ";".$data[12];
				$p_iva = mysql_real_escape_string($data[13]);
// 				$codice_etiflor = $data[14];
			}
			if(!is_numeric($p_iva))
			{
				$email .=";". $data[13];
				$p_iva = mysql_real_escape_string($data[14]);
// 				$codice_etiflor = $data[15];
			}
			if(!is_numeric($p_iva))
			{
				$email .= ";".$data[15];
				$p_iva = mysql_real_escape_string($data[16]);
// 				$codice_etiflor = $data[17];
			}
			if(!is_numeric($p_iva))
			{
				$email .= ";".$data[16];
				$p_iva = mysql_real_escape_string($data[17]);
// 				$codice_etiflor = $data[18];
			}
			
			$result = mysql_query("SELECT * FROM `destinazioni` WHERE customer_code = '".$data[0]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
			{
				$customer_exists = $row['id'];
				$result = mysql_query("UPDATE `destinazioni`
						SET 
						codice_etiflor = '".$codice_etiflor."',
						ragione_sociale = '".mysql_real_escape_string($data[1])."',
						p_iva = '".$p_iva."',
						indirizzo = '".mysql_real_escape_string($data[2])."',
						provincia = '".mysql_real_escape_string($data[5])."',
						stato = '".mysql_real_escape_string($data[6])."',
						citta = '".mysql_real_escape_string($data[4])."',
						cap = '".mysql_real_escape_string($data[3])."',
						cellulare = '".mysql_real_escape_string($data[8])."',
						fisso = '".mysql_real_escape_string($data[7])."',
						fax = '".mysql_real_escape_string($data[9])."',
						email = '".$email."',
						listino = '".$data[12]."',
						data_modifica_riga = '".date('Y-m-d')."',
						operatore = '".$operator."'
					WHERE id = '".$row['id']."'", $conn->connection);
			}
			else
			{
				$query = "INSERT INTO destinazioni (
					`codice_etiflor`,
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
					`listino` ,
					`data_inserimento_riga` ,
					`data_modifica_riga` ,
					`is_active` ,
					`operatore`
					) VALUES (
					'".$codice_etiflor."',
					'".mysql_real_escape_string($data[0])."',
					'',
					'',
					'',
					'".mysql_real_escape_string($data[1])."',
					'".$p_iva."',
					'".mysql_real_escape_string($data[2])."',
					'".mysql_real_escape_string($data[5])."',
					'".mysql_real_escape_string($data[6])."',
					'".mysql_real_escape_string($data[4])."',
					'".mysql_real_escape_string($data[3])."',
					'".mysql_real_escape_string($data[8])."',
					'".mysql_real_escape_string($data[7])."',
					'".mysql_real_escape_string($data[9])."',
					'".$email."',
					'".$listino."',
					'".date('Y-m-d')."',
					'".date('Y-m-d')."',
					1,
					'".$operator."')";
				mysql_query($query, $conn->connection);
				$query = null;
			}
			$key++;
		}
	}
	
	function importFornitori($File)
	{
		global $conn;
		global $operator;
		global $separator;
		require_once(APP_ROOT.'/beans/fornitore.php');
		
		$fh = fopen($File, 'rb');
		$key = 0;
		while ( ($data = fgetcsv($fh, 1000, $separator)) !== false)
		{
			if($data[0] == '?')
				continue;
				
			$BeanFornitori = new fornitore();
			$exists = $BeanFornitori->dbGetOneByName($conn, $data[1]);
			if(empty($exists))
			{
				$exists = null;
				$exists = $BeanFornitori->dbGetOneByCode($conn, $data[0]);
				if(empty($exists))
				{
					$BeanFornitori->setNome($data[1]);
					$BeanFornitori->setCodice_fornitore($data[0]);
					$BeanFornitori->setIndirizzo($data[4].' - '.$data[2]);
					$BeanFornitori->setCap($data[3]);
					$BeanFornitori->setProvincia($data[5]);
					$BeanFornitori->setOperatore('ImportProcedure');
					$BeanFornitori->dbStore($conn);
				}
			}
			else 
			{
				$BeanFornitori = new fornitore($conn, $exists['id']);
				$BeanFornitori->setNome($data[1]);
				$BeanFornitori->setCodice_fornitore($data[0]);
				$BeanFornitori->setIndirizzo($data[4].' - '.$data[2]);
				$BeanFornitori->setCap($data[3]);
				$BeanFornitori->setProvincia($data[5]);
				$BeanFornitori->setOperatore('ImportProcedure');
				$BeanFornitori->dbStore($conn);
			}
		}
	}

	function importArticoli($File)
	{
		global $conn;
		global $operator;
		global $separator;
	
		$fh = fopen($File, 'rb');
		$key = 0;

// 		if(date('H') < 2)
// 			mysql_query("DELETE FROM content_adhoc WHERE operatore = 'StreamImportProcedure_articoli'",$conn->connection);
		
		while ( ($data = fgetcsv($fh, 1000, $separator)) !== false)
		{
			if(empty($data[0]) || $data[0] == '(' || $data[0] == ') ON [PRIMARY]' || $data[0] == '.' || $data[0] == '---->')
				continue;

			$result = mysql_query("SELECT * FROM giacenze WHERE bar_code = '".$data[0]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
			{
				$res = mysql_query("SELECT * FROM content WHERE id = '".$row['id_content']."'", $conn->connection);
				if($r=mysql_fetch_assoc($res))
				{
					$query = "UPDATE  content SET
						vbn =  '".$data[5]."',
						operatore =  '".$operator."_articoli'
						WHERE  content.id =".$r['id'].";";
					mysql_query($query, $conn->connection);
				}
			}

			$result = mysql_query("SELECT * FROM content_adhoc WHERE vbn = '".$data[0]."'", $conn->connection);
			if($row=mysql_fetch_assoc($result))
			{				
				$query = "UPDATE  content_adhoc SET
						id_gm =  '".$id_gm."',
						id_famiglia =  '".$id_famiglia."',
						id_settore =  '".$id_settore."',
						id_reparto =  '".$id_reparto."',
						nome_it =  '".$data[1]."',
						descrizione_it =  '".$data[1]."',
						nome_en =  '".$data[1]."',
						descrizione_en =  '".$data[1]."',
						vbn =  '".$data[0]."',
						prezzo_0 =  '".str_replace(',', '.', $data[12])."',
						prezzo_1 =  '".str_replace(',', '.', $data[14])."',
						prezzo_2 =  '".str_replace(',', '.', $data[15])."',
						prezzo_3 =  '".str_replace(',', '.', $data[16])."',
						prezzo_4 =  '".str_replace(',', '.', $data[17])."',
						prezzo_5 =  '".str_replace(',', '.', $data[18])."',
						prezzo_6 =  '".str_replace(',', '.', $data[19])."',
						prezzo_7 =  '".str_replace(',', '.', $data[20])."',
						prezzo_8 =  '".str_replace(',', '.', $data[21])."',
						prezzo_9 =  '".str_replace(',', '.', $data[22])."',
						operatore =  '".$operator."_articoli'
						WHERE  content.id =".$row['id'].";";
				mysql_query($query, $conn->connection);
			}
			else
			{
				$id_gm = 1;
				$id_famiglia = 1;
				$id_settore = 1;
				$id_reparto = 1;
				$query = "INSERT INTO content_adhoc (
					vbn,
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
					have_image,
					is_active,
					data_inserimento_riga,
					data_modifica_riga,
					operatore) VALUES
					('".$data[0]."',
					'".mysql_real_escape_string($data[1])."',
					'".mysql_real_escape_string($data[1])."',
					'".mysql_real_escape_string($data[1])."',
					'".mysql_real_escape_string($data[1])."',
					".$id_gm.",
					".$id_famiglia.",
					'',
					'',
					'',
					'',
					'',
					'',
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
					'',
					".((int)$have_image).",
					1,
					'".date('Y-m-d H:i:s')."',
					'".date('Y-m-d H:i:s')."',
					'".$operator."_articoli')";
				mysql_query($query, $conn->connection);
			}
			$id_gm = null;
			$id_famiglia = null;
	
			$key++;
		}

		return true;
	}	
?>