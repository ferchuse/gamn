<?php
$url = 'localhost';
$user = 'gps';
$pass = 'ballena';
$db1 = 'gamn_gps';
$db2 = 'gamn_gps_skymedia';
mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');

define('ACT_DES_VEHICULO', 1);
define('GET_ULTIMO_ESTADO', 2);

$opc = $_POST['opc'];

if($opc == ACT_DES_VEHICULO){
    $respuesta = array();
    $imei_cel = $_POST['imei_cel'];
    $imei_gps = $_POST['imei_gps'];
    $accion = $_POST['accion'];

    $query = "select estatus, telefono, email from movil_dispositivos where imei = '$imei_cel'";
    $result = mysql_db_query($db1, $query);

    if($dispositivo = mysql_fetch_assoc($result)){
        if($dispositivo['estatus'] == 'A'){
            $query = "select candado from placas where idtracker = '$imei_gps'";
            $result = mysql_db_query($db2, $query);

            if($result = mysql_fetch_assoc($result)){
                $query = "update placas set candado = '$accion' where idtracker = '$imei_gps'";

                if(mysql_db_query($db2, $query)){
                    $estado = $accion == 1 ? 'activado' : 'desactivado';
                    $fecha = date('Y-m-d H:i:s');
                    $mensaje = "El dispositivo con el imei $imei_gps ha sido $estado $fecha";

                    $query = "insert into sms set mensaje = '$mensaje', telefono = '{$dispositivo['telefono']}', enviado = 0";
                    mysql_db_query($db2, $query);

                    /*include_once("phpmailer/class.phpmailer.php");
                    $mail = new PHPMailer();
                    $mail->Host = "ssl://smtp.mailgun.org";
                    $mail->IsSMTP();
                    $mail->Port = 465;
                    $mail->SMTPAuth = true;
                    $mail->Username ='postmaster@sandbox41deea0ea6eb42be851dd31543f3a267.mailgun.org';
                    $mail->Password = 'c33cf079c2869674f31afd9594f3ebb9';
                    $mail->From = 'hgaribay@gmail.com'; //$emailfrom; 'hgaribay@gmail.com'
                    $mail->FromName = 'GCOMPUFAX';
                    $mail->AddAddress('hgaribay@gmail.com');
                    $mail->Subject = 'ejemplo de activacion';
                    $mail->IsHTML(true);
                    $mail->Body = $html;
    				$mail->Send();*/

                    $respuesta['validado'] = true;
                    $respuesta['mensaje'] = "El candado esta $estado";
                }else{
                    $respuesta['validado'] = false;
                    $respuesta['mensaje'] = 'Ocurrio un problema al momento de activar/desactivar el candado.\nFavor de volver a intentarlo.';
                }
            }else{
                $respuesta['validado'] = false;
                $respuesta['mensaje'] = 'No hay vehiculo asignado al gps solicitado';
            }
        }else{
            $respuesta['validado'] = false;
            $respuesta['mensaje'] = 'El IMEI del Celular esta dado de baja';
        }
    }else{
        $respuesta['validado'] = false;
        $respuesta['mensaje'] = 'El IMEI del Celular no esta registrado';
    }

    echo(json_encode($respuesta));
}

if($opc == GET_ULTIMO_ESTADO){
    $respuesta = array();
    $imei_gps = $_POST['imei_gps'];

    $query = "select candado from placas where idtracker = '$imei_gps'";
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
