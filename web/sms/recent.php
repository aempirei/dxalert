<?php

preg_match('/^\/home\/([^\/]*)/', getcwd(), $usermatch);

$USER = $usermatch[1];
$BASEPATH = realpath(__DIR__.'/../../..');

unset($usermatch);
require 'config.php';

header('Pragma: no-cache');
header('Content-Type: application/json');

$DB = new mysqli('localhost', 'checker', 'niggerchecker', 'dxalert');
if ($DB->connect_errno) {
	die("Failed to connect to MySQL: ".$DB->connect_error);
}

function sqlgo($sql) {

        global $DB;

        $results = $DB->query($sql);

        $fields = $results->fetch_fields();
        $columns = array_map(function($f) { return $f->name; }, $fields);

        $rows = array();

        while(($row = $results->fetch_array(MYSQLI_NUM)))
                $rows[] = $row;

        return array($columns,$rows);
}

echo json_encode(sqlgo('SELECT * FROM sms WHERE 4*id > 3*MAX(id)'));
