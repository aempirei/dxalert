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
<div><h1>(dx)Alert</h1><p>Here are your alerts.</p>
<table>
<?php

function format($column, $data) {
	if($column == 'url') {
		return '<a href="'.$data.'">'.htmlspecialchars($data).'</a>';
	} else {
		return htmlspecialchars($data);
	}
}

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
</body></html>
