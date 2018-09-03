<?php

preg_match('/^\/home\/([^\/]*)/', getcwd(), $usermatch);

$USER = $usermatch[1];
$BASEPATH = realpath(__DIR__.'/../..');

unset($usermatch);

$DB = new mysqli('localhost', 'checker', 'niggerchecker', 'dxalert');
if ($DB->connect_errno) {
	die("Failed to connect to MySQL: ".$DB->connect_error);
}

$sql = 'SELECT * FROM alerts';

$results = $DB->query($sql);

$fields = $results->fetch_fields();
$columns = array_map(function($f) { return $f->name; }, $fields);

$rows = array();

while(($row = $results->fetch_array(MYSQLI_NUM)))
	$rows[] = $row;

$data = array('columns' => $columns, 'rows' => $rows);

header('Content-Type: application/json');

echo json_encode($data,JSON_PRETTY_PRINT);
