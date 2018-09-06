<?php

preg_match('/^\/home\/([^\/]*)/', getcwd(), $usermatch);

$USER = $usermatch[1];
$BASEPATH = realpath(__DIR__.'/../..');

unset($usermatch);

header('Pragma: no-cache');

$DB = new mysqli('localhost', 'checker', 'niggerchecker', 'dxalert');
if ($DB->connect_errno) {
	die("Failed to connect to MySQL: ".$DB->connect_error);
}

$required = array('id','callback','address');

foreach($required as $key) {
	if(!array_key_exists($key, $_REQUEST)) {
		http_response_code(400);
		die;
	}
	$$key = $_REQUEST[$key];
}

$url = 'https://blockchain.info/q/getreceivedbyaddress/'.$address.'?confirmations=1';

$sql = 'INSERT IGNORE INTO alerts (id,callback,url) VALUES (?,?,?)';

try {
	$statement = $DB->prepare($sql);
	$statement->bind_param('sss', $id, $callback, $url);
	$statement->execute();
	$statement->close();
} catch(Exception $e) {
	echo $e->getMessage();
	exit;
}

echo 'ok';
