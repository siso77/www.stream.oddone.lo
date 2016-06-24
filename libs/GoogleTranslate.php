<?php
require_once APP_ROOT.'/libs/ext/google-api-php-client/src/Google_Client.php';
require_once APP_ROOT.'/libs/ext/google-api-php-client/src/contrib/Google_TranslateService.php';
$client = new Google_Client();
$client->setApplicationName('Google Translate PHP Starter Application');
$client->setDeveloperKey('AIzaSyC8mNkVy-S1Ptg0S9_UbPR0RJxcZ5UrOcI');
$service = new Google_TranslateService($client);
// $langs = $service->languages->listLanguages();
// print "<h1>Languages</h1><pre>" . print_r($langs, true) . "</pre>";
$translations = $service->translations->listTranslations('Hello', 'hi');
print "<h1>Translations</h1><pre>" . print_r($translations, true) . "</pre>";
exit();
?>