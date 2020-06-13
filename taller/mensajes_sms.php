<?php
/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/

$data_base = 'gamn_gps_skymedia';
$dsn = 'mysql:host=localhost;dbname=' . $data_base;
$user = 'gamn';
$pass = 'rXj8nBpwFu2tDwe2';
$pdo = new PDO($dsn, $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));

define('GET_MENSAJES', 1);
define('MARCAR_MENSAJE', 2);

$opc = $_POST['opc'];

if($opc == GET_MENSAJES){
	// devuelve un objeto json con un atibuto mensajes que es un arreglo de objetos json con la siguiente estructura
	// {'mensajes': [{'id': 1, 'mensaje': 'asdfasdf', 'telefono': 'xxxxxxxxxx'}, ...]}
	$respuesta = array();
	$query = 'select * from sms where enviado = 0';
	$stmt = $pdo->prepare($query);
	$stmt->execute();
	$respuesta['mensajes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo(json_encode($respuesta));
}

if($opc == MARCAR_MENSAJE){
	// Recibe los mensajes en $_POST['id_mensajes'] de la siguiente manera
	// 1|2|3|4|5 donde cada numero corresponde al id del mensaje que ha sido enviado
	$respuesta = array('validado' => true);
	$id_mensajes = $_POST['id_mensajes'];
	$id_mensajes = explode('|', $id_mensajes);
	$query = 'update sms set enviado = 1 where id = :id';
	$stmt = $pdo->prepare($query);

	foreach($id_mensajes as $id_mensaje){
		$stmt->bindParam(':id', $id_mensaje, PDO::PARAM_INT);
		$stmt->execute();
	}

	echo(json_encode($respuesta));
}
?>
