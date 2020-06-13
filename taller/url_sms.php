<?php
/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/

$data_base = 'gamn_gps_skymedia';
$dsn = 'mysql:host=localhost;dbname=' . $data_base;
$user = 'gamn';
$pass = 'rXj8nBpwFu2tDwe2';
$pdo = new PDO($dsn, $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));

$respuesta = array();

$query = 'select * from url_sms order by id asc';
$stmt = $pdo->prepare($query);
$stmt->execute();
$respuesta['urls'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo(json_encode($respuesta));
?>
