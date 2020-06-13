<?php
header('Content-type: application/json');
$data_base = 'gamn_gps_otra_plataforma';
$dsn = 'mysql:host=localhost;dbname=' . $data_base;
//$dsn = 'mysql:host=192.168.1.79;dbname=' . $data_base;
//$user = 'firulais99';
//$pass = 'vatoloko';
$user = 'gamn';
$pass = 'rXj8nBpwFu2tDwe2.';
$pdo = new PDO($dsn, $user, $pass);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $params = file_get_contents('php://input');
    $params = json_decode($params);
    $respuesta = array('validado' => true, 'mensaje' => '', 'data' => array());
    
    $query = 'select cve as id, usuario as user, password as pass, admin, estatus, dispositivos from usuariogpsmovil where usuario = :user and password = :pass';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user', $params->user, PDO::PARAM_STR);
    $stmt->bindParam(':pass', $params->pass, PDO::PARAM_STR);
    $stmt->execute();
    
    if($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $respuesta['data']['usuario'] = $result;
        $dispositivos = $result['dispositivos'];
        $query = "select cve as id, nombre as economico, telefono, paro, arranque as arrancar from dispositivos where cve in ($dispositivos)";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $respuesta['data']['economicos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $respuesta['validado'] = false;
        $respuesta['mensaje'] = 'El usuario no existe o los datos son incorrectos';
    }

    echo(json_encode($respuesta));
    exit();
}
?>