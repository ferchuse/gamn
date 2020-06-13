<?php
$data_base = 'gps';
$dsn = 'mysql:host=localhost;dbname=' . $data_base;
$user = 'gps';
$pass = 'ballena';
$pdo = new PDO($dsn, $user, $pass);

define('GUARDAR_ASISTENCIA', 1);

$opc = $_POST['opc'];

if($opc == GUARDAR_ASISTENCIA){
    $respuesta = array();
    $query = 'select id from movil_dispositivos where imei = :imei';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':imei', $_POST['imei'], PDO::PARAM_STR);
    $stmt->execute();

    if($dispositivo = $stmt->fetch(PDO::FETCH_ASSOC)){
        $query = 'insert into movil_asistencias(id_dispositivo, observaciones, imei, latitud, longitud, fecha, estatus) values(:id_dispositivo, :observaciones, :imei, :latitud, :longitud, :fecha, :estatus)';
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_dispositivo', $dispositivo['id'], PDO::PARAM_INT);
        $stmt->bindParam(':observaciones', $_POST['observaciones'], PDO::PARAM_STR);
        $stmt->bindParam(':imei', $_POST['imei'], PDO::PARAM_STR);
        $stmt->bindParam(':latitud', $_POST['latitud'], PDO::PARAM_STR);
        $stmt->bindParam(':longitud', $_POST['longitud'], PDO::PARAM_STR);
        $stmt->bindParam(':fecha', $_POST['fecha'], PDO::PARAM_STR);
        $stmt->bindParam(':estatus', $_POST['asistencia'], PDO::PARAM_STR);

        if($stmt->execute())
            $respuesta['validado'] = true;
        else{
            $respuesta['validado'] = false;
            $respuesta['mensaje'] = 'Problemas al guardar el registro';
        }
    }else{
        $respuesta['validado'] = false;
        $respuesta['mensaje'] = 'El dispositivo no esta registrado';
    }

    echo(json_encode($respuesta));
}
?>
