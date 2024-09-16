<?php
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/adodb/adodb-php/adodb.inc.php';

$host = 'localhost';
$db = 'todo_list';
$user = 'root';
$password = '';

$dsn = "mysqli://$user:$password@$host/$db";
$adodb = ADONewConnection($dsn);

if (!$adodb) {
    die('Error de conexión: ' . $adodb->ErrorMsg());
}

$conn = $adodb;
?>
