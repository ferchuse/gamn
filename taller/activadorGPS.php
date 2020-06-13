<?php
$url = 'localhost';
$user = 'gps';
$pass = 'ballena';
$db = 'gamn_gps_skymedia';
mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');

define('ACT_DES_VEHICULO', 1);
define('GET_ULTIMO_ESTADO', 2);

$opc = $_POST['opc'];

if($opc == ACT_DES_VEHICULO){
    $respuesta = array();
    $imei_cel = $_POST['imei_cel'];
    $imei_gps = $_POST['imei_gps'];
    $accion = $_POST['accion'];

    try{
        $query = "select * from gps_objects where imei = '$imei_gps'";
        $result = mysql_db_query($db, $query);
        
        if(!$result = mysql_fetch_assoc($result))
            throw new Exception('El dispositivo con el imei ' . $imei . ' no esta registrado');

        $query = "update gps_objects set candado = '$accion' where imei = '$imei_gps'";
        
        if(mysql_db_query($db, $query)){
            $estado = $accion == 1 ? 'activado' : 'desactivado';
            $fecha = date('Y-m-d H:i:s');
            $mensaje = "El dispositivo con el imei $imei_gps ha sido $estado $fecha";

            $query = "insert into sms set mensaje = '$mensaje', telefono = '{$dispositivo['telefono']}', enviado = 0";
            mysql_db_query($db, $query);

            $respuesta['validado'] = true;
            $respuesta['mensaje'] = "El candado esta $estado";
        }else
            throw new Exception('Ocurrio un problema al momento de activar/desactivar el candado.\nFavor de volver a intentarlo.');
    }catch(Exception $e){
        $respuesta['validado'] = false;
        $respuesta['mensaje'] = $e->getMessage();
    }

    echo(json_encode($respuesta));
}

if($opc == GET_ULTIMO_ESTADO){
    $respuesta = array();
    $imei_gps = $_POST['imei_gps'];

    $query = "select candado from gps_objects where imei = '$imei_gps'";
    $result = mysql_db_query($db2, $query);

    if($result = mysql_fetch_assoc($result)){
        $respuesta['validado'] = true;
        $respuesta['candado'] = $result['candado'];
    }else{
        $respuesta['validado'] = false;
        $respuesta['mensaje'] = 'No hay vehiculo asignado al gps solicitado';
    }

    echo(json_encode($respuesta));
}
?>
