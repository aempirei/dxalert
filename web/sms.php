<?php

preg_match('/^\/home\/([^\/]*)/', getcwd(), $usermatch);

$USER = $usermatch[1];
$BASEPATH = realpath(__DIR__.'/../..');

unset($usermatch);
require 'config.php';

header('Pragma: no-cache');

$DB = new mysqli('localhost', 'checker', 'niggerchecker', 'dxalert');
if ($DB->connect_errno) {
	die("Failed to connect to MySQL: ".$DB->connect_error);
}

function send_sms($from, $to, $message) {
	global $API_USERNAME;
	global $API_PASSWORD;
	$baseurl="https://voip.ms/api/v1/rest.php";
	$method = "sendSMS";
	$username = urlencode($API_USERNAME);
	$password = urlencode($API_PASSWORD);
	$message = urlencode($message);
	$url = "$baseurl?api_username=$username&api_password=$password&method=$method&did=$from&dst=$to&message=$message";
	$resp = file_get_contents($url);
	return json_decode($resp);
}

$required = array('id','from','to','message','timestamp');

foreach($required as $key) {
	if(!array_key_exists($key, $_REQUEST)) {
		http_response_code(400);
		die;
	}
	$$key = $_REQUEST[$key];
}

$options = explode(' ', $message);
$method = strtolower(array_shift($options));

switch($method) {
case 'bitcoin':
case 'btc':
	$address = array_shift($options);
	$url = 'https://blockchain.info/q/getreceivedbyaddress/'.$address.'?confirmations=1';
	$id = strtoupper(substr(hash('md5',$url),0,3));
	break;
default:
	$resp = send_sms($to, $from, "Sorry, I don't understand \"$method\".");
	echo 'ok';
	exit;

}

$resp = send_sms($to, $from, 'thank you for your request; your id is '.$id.'.');
error_log(json_encode($resp));

$sql = 'INSERT IGNORE INTO alerts (id,callback,url) VALUES (?,?,?)';

try {
	$statement = $DB->prepare($sql);
	$statement->bind_param('sss', $id, $from, $url);
	$statement->execute();
	$statement->close();
} catch(Exception $e) {
	echo $e->getMessage();
	http_response_code(500);
	exit;
}

echo 'ok';
