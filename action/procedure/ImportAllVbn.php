<?php
define( 'APP_ROOT', str_replace('/action/procedure', '', str_replace('\\', '/', getcwd()) ));
define( 'APPLICATION_CONFIG_FILENAME', 'config.xml' );

if(!isset($_SERVER['APPLICATION_ENV']))
	$_SERVER['APPLICATION_ENV'] = 'pro';

error_reporting(E_ERROR);
ini_set('display_errors', false);
ini_set("max_execution_time", "36000000");

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
	$email_customer_logo;
	
	$conn = MyDB::connect();

	$email_customer_name = PREFIX_META_TITLE;
	$email_customer_logo = WWW_ROOT.'themes/uploads/2013/03/logo1.png';
	
	$debugTime = new debugTime();
	Start();
	$debugTime->OutPutDebugTime('Esecuzione avvenuta in sec: ');
	
	
	function Start()
	{
		global $conn;

// 		if(true)
 		if($_REQUEST['user'] == 'admin' && $_REQUEST['pwd'] == 'f7b44cfafd5c52223d5498196c8a2e7b' && $_SERVER['HTTP_STREAM_DEMO_INTEGRATION'] == 'f7b44cfafd5c52223d5498196c8a2e7b') //pwd = md5('stream')
		{
			include_once(APP_ROOT.'/beans/content.php');
			include_once(APP_ROOT.'/libs/VbnCurl.php');
			
			$BeanContent = new content();
			$data = $BeanContent->dbSearch($conn, ' AND is_vbn_updated = 0 LIMIT 0,10000');
			
			require_once(APP_ROOT."/libs/ext/nusoap-0.9.5/lib/nusoap.php");
			$client = new nusoap_client('http://www.sisoweb.it/soap-server/ServerSoapVbn.php?wsdl', true);
			foreach($data as $value)
			{
				try
				{
					$err = $client->getError();
					if ($err) 
						echo '<h2>Constructor error</h2><pre>'.$err.'</pre>';
					$return = $client->call("dispach",array('user' => 'soap_user','pwd' => '#s040p4ssw0rd!', 'className' => 'VbnCurl', 'methothName' => 'process', 'args' =>  $value['vbn']));
					$vbn = unserialize($return);
				}
				catch (Exception $e){
					echo $e->getMessage();
				}

				if(!empty($vbn['nome']))
				{
					$BeanContent = new content($conn, $value['id']);
// 					$BeanContent->setNome_en(strtoupper(str_replace("'", "", $vbn['nome'])));
					$BeanContent->setNome_it(strtoupper(str_replace("'", "", $vbn['nome'])));
// 					$BeanContent->setDescrizione_en(strtoupper(str_replace("'", "", $vbn['nome'])));
					$BeanContent->setDescrizione_it(strtoupper(str_replace("'", "", $vbn['nome'])));
					$BeanContent->setIs_vbn_updated(1);
					$BeanContent->dbStore($conn);
				}

			}
			sendEmailConfirmation();
		}
	}

	function getDiff($date1, $date2)
	{
		$diff = abs(strtotime($date2) - strtotime($date1));
		
		$years = floor($diff / (365*60*60*24));
		$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
		$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
		
		// 		printf("%d years, %d months, %d days\n", $years, $months, $days);
		
		return $days;
	}
	
	function sendEmailConfirmation()
	{
		global $email_customer_name;
		$headers .= 'From: Stream <".EMAIL_ADMIN_FROM.">' . "\r\n";
		$to = "siso77@gmail.com";
		mail($to, "Aggiornamento Codici Vbn ".$email_customer_name,"OPERAZIONE AVVENUTA CON SUCCESSO", $headers);
	}
	
	function storeMonitor()
	{
		global $conn;
		$query = "UPDATE  `monitor` SET `last_execute` = '".date('Y-m-d H:i:s')."'";
		mysql_query($query, $conn->connection);
	}

?>