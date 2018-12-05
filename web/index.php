<?php

preg_match('/^\/home\/([^\/]*)/', getcwd(), $usermatch);

$USER = $usermatch[1];
$BASEPATH = realpath(__DIR__.'/../..');

unset($usermatch);

$DB = new mysqli('localhost', 'checker', 'niggerchecker', 'dxalert');
if ($DB->connect_errno) {
	die("Failed to connect to MySQL: ".$DB->connect_error);
}

function sqlgo($sql) {

	$results = $DB->query($sql);

	$fields = $results->fetch_fields();
	$columns = array_map(function($f) { return $f->name; }, $fields);

	$rows = array();

	while(($row = $results->fetch_array(MYSQLI_NUM)))
		$rows[] = $row;

	return array($columns,$rows);
}

$alertrs = sqlgo('SELECT * FROM alerts');
$smsrs = sqlgo('SELECT * FROM sms');

function format($column, $data) {
	if($column == 'url') {
		return '<a href="'.$data.'">'.htmlspecialchars($data).'</a>';
	} else {
		return htmlspecialchars($data);
	}
}

?>
<html><head>
<title>(dx)Alert</title>
<style>
table {
border: 0;
border-collapse: collapse;
}
th {
/*border-bottom: 1px solid black;*/
background-color:lightgray;
}
td {
padding: 4px;
padding-left: 5px;
padding-right: 5px;
}
td:nth-child(odd) {
background-color: whitesmoke;
}
td:nth-child(even) {
background-color: white;
}
h1 {
padding-top: 0;
margin-top: 0;
}
div {
padding: 15px;
}
</style>
</head>
<body>

<div><h1>(dx)Alert</h1>

<!-- A L E R T S --!>
<p>Here are your alerts.</p>

<table>
<?php

$columns = $alertrs[0];
$rows = $alertrs[1];

echo '<tr>';
for($x = 0; $x < sizeof($columns); $x++)
	echo '<th>'.htmlspecialchars($columns[$x]).'</th>';
echo "</tr>\n";

foreach($rows as $row) {
	echo '<tr>';
	for($x = 0; $x < sizeof($columns); $x++)
		echo '<td>'.format($columns[$x],$row[$x]).'</td>';
	echo "</tr>\n";
}

?>
</table>

<!-- S M S --!>
<p>Here are the text messages.</p>

<table>
<?php

$columns = $smsrs[0];
$rows = $smsrs[1];

echo '<tr>';
for($x = 0; $x < sizeof($columns); $x++)
	echo '<th>'.htmlspecialchars($columns[$x]).'</th>';
echo "</tr>\n";

foreach($rows as $row) {
	echo '<tr>';
	for($x = 0; $x < sizeof($columns); $x++)
		echo '<td>'.format($columns[$x],$row[$x]).'</td>';
	echo "</tr>\n";
}

?>
</table>

</div>

</body></html>
