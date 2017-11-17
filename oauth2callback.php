<?php
require_once __DIR__.'/vendor/autoload.php';

session_start();
$_SESSION['access_token'] = ""; // Reset old access tokens.

$client = new Google_Client();
$client->setAuthConfigFile('client_secrets.json');
$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/ND_OIT_Calendar_Web_App/oauth2callback.php');
$client->addScope(Google_Service_Calendar::CALENDAR);

if (! isset($_GET['code'])) {  //  Redirects to authentification url
  $auth_url = $client->createAuthUrl();
  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {  //  Successful authentification where access token is inside 'code'
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/ND_OIT_Calendar_Web_App/print.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
