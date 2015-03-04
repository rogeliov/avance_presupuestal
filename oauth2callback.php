<?php

$include_path = '/opt/lampp/htdocs/AvancePresupuesto/';
//$include_path = "";
require_once 'google-api-php-client/src/Google/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfigFile('client_secret_166812253810-gg40f3a5e94blpb3k1263knjb7jvcnrp.apps.googleusercontent.com.json');
$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/AvancePresupuesto/oauth2callback.php');
$client->addScope(Google_Service_Drive::DRIVE);
$client->addScope(Google_Service_Drive::DRIVE_FILE);
$client->addScope(Google_Service_Drive::DRIVE_READONLY);
$client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
$client->addScope(Google_Service_Drive::DRIVE_APPDATA);
$client->addScope(Google_Service_Drive::DRIVE_APPS_READONLY);

if (! isset($_GET['code'])) {
  $auth_url = $client->createAuthUrl();
  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/DrivePHP/';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}