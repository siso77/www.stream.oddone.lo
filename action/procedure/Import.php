<?php
ini_set('display_errors',false);
ini_set('max_execution_time','3600');

$siteUrl = "http://shop-oddone.biz";

// IMPORTO I CONTENUTI
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $siteUrl."/action/ImportDataNew.php?user=admin&pwd=f7b44cfafd5c52223d5498196c8a2e7b");
curl_setopt($ch, CURLOPT_HTTPHEADER,array('Stream-Demo-Integration: f7b44cfafd5c52223d5498196c8a2e7b'));
curl_exec($ch);
curl_close($ch);
// IMPORTO I CONTENUTI

sleep(15);

// IMPORTO I CONTENUTI
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $siteUrl."/action/ImportDataNew.php?user=admin&pwd=f7b44cfafd5c52223d5498196c8a2e7b");
curl_setopt($ch, CURLOPT_HTTPHEADER,array('Stream-Demo-Integration: f7b44cfafd5c52223d5498196c8a2e7b'));
curl_exec($ch);
curl_close($ch);
// IMPORTO I CONTENUTI

sleep(15);

// IMPORTO I CONTENUTI
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $siteUrl."/action/ImportDataNew.php?user=admin&pwd=f7b44cfafd5c52223d5498196c8a2e7b");
curl_setopt($ch, CURLOPT_HTTPHEADER,array('Stream-Demo-Integration: f7b44cfafd5c52223d5498196c8a2e7b'));
curl_exec($ch);
curl_close($ch);
// IMPORTO I CONTENUTI
sleep(15);

// IMPORTO I CONTENUTI
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $siteUrl."/action/ImportDataNew.php?user=admin&pwd=f7b44cfafd5c52223d5498196c8a2e7b");
curl_setopt($ch, CURLOPT_HTTPHEADER,array('Stream-Demo-Integration: f7b44cfafd5c52223d5498196c8a2e7b'));
curl_exec($ch);
curl_close($ch);
// IMPORTO I CONTENUTI

?>