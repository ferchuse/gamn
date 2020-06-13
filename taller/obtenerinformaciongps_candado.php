<?php
require('vendor/autoload.php');
use Mailgun\Mailgun;

function harvestine($lat1, $long1, $lat2, $long2){
	$degtorad = 0.01745329;
	$radtodeg = 57.29577951;

	$dlong = ($long1 - $long2);
	$dvalue = (sin($lat1 * $degtorad) * sin($lat2 * $degtorad))
	+ (cos($lat1 * $degtorad) * cos($lat2 * $degtorad)
	* cos($dlong * $degtorad));

	$dd = acos($dvalue) * $radtodeg;

	$miles = ($dd * 69.16);
	$km = ($dd * 111.302);

	return $km;
}

mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
mysql_select_db('gamn_gps_skymedia');
$res = mysql_query("SELECT MAX(iddata) FROM data");
$row = mysql_fetch_array($res);
$maxdata=$row[0];

$res = mysql_query("SELECT MAX(iddata) FROM data_v2");
$row = mysql_fetch_array($res);
$maxdatav2=$row[0];

$res = mysql_query("SELECT MAX(idplaca) FROM placas");
$row = mysql_fetch_array($res);
$maxplacas=$row[0];

$res = mysql_query("SELECT MAX(idempresa) FROM sk_empresas");
$row = mysql_fetch_array($res);
$maxsk_empresas=$row[0];


$array_data = array();
$array_data_v2 = array();
$array_placas = array();

mysql_connect('23.239.1.151', 'remoto', 'bAllenA#66#');
mysql_select_db('skymedia');
$res = mysql_query("SELECT * FROM data WHERE iddata>'".$maxdata."' AND idtracker NOT IN (998,999) ORDER BY iddata");
while($row = mysql_fetch_assoc($res))
	$array_data[] = $row;

$res = mysql_query("SELECT * FROM data_v2 WHERE iddata>'".$maxdatav2."' AND idtracker NOT IN (998,999) ORDER BY iddata");
while($row = mysql_fetch_assoc($res))
	$array_data_v2[] = $row;

$res = mysql_query("SELECT * FROM placas WHERE idplaca>'".$maxplacas."' ORDER BY idplaca");
while($row = mysql_fetch_assoc($res))
	$array_placas[] = $row;

$res = mysql_query("SELECT * FROM sk_empresas WHERE idempresa>'".$maxsk_empresas."' ORDER BY idempresa");
while($row = mysql_fetch_assoc($res))
	$array_sk_empresas[] = $row;

mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
mysql_select_db('gamn_gps_skymedia') or die(mysql_error());
if(count($array_data)>0){

	$campos = "";
	foreach($array_data[0] as $k=>$v){
		$campos .= ",`".$k."`";
	}
	//$campos = substr($campos, 1);
	$insert = "INSERT INTO data (fecha_creacion, hora_creacion{$campos}) VALUES ";
	$contador = 0;
	$datos = "";
	foreach($array_data as $valores)
	{
		$datos .= ",(CURDATE(), CURTIME()";
		$primero = false;
		foreach($valores as $campo => $valor)
		{
			if(!$primero) $datos .= ",";
			if($campo!='date')
				$datos .= "'".addslashes($valor)."'";
			else
				$datos .= "STR_TO_DATE('".$valor."', '%d/%m/%Y')";
			$primero = false;
		}
		$datos .= ")";
		$contador++;
		if($contador == 500)
		{
			$datos = substr($datos,1);
			mysql_query($insert.$datos.";") or die(mysql_error());
			$datos = "";
			$contador = 0;
		}
	}
	if($contador > 0)
	{
		$datos = substr($datos,1);
		mysql_query($insert.$datos.";");
	}
}

if(count($array_data_v2)>0){

	$campos = "";
	foreach($array_data_v2[0] as $k=>$v){
		$campos .= ",`".$k."`";
	}
	//$campos = substr($campos, 1);
	$insert = "INSERT INTO data_v2 (fecha_creacion, hora_creacion{$campos}) VALUES ";

	$contador = 0;
	$datos = "";
	foreach($array_data_v2 as $valores)
	{
		$datos .= ",(CURDATE(), CURTIME()";
		$primero = false;

		// variable donde se almacenaran el idtracker latx y longt
		$reg = array();

		foreach($valores as $campo => $valor)
		{
			if(!$primero) $datos .= ",";
			$datos .= "'".addslashes($valor)."'";
			$primero = false;

			// se pregunta si el campo es idtracker o latx o longt y si si es alguno se agregar al arreglo
			if($campo == 'idtracker' || $campo == 'latx' || $campo == 'longt')
				$reg[$campo] = $valor;
		}

		$datos .= ")";

		//obtenemos el candado de la placa asociado al idtracker
		$query = "select candado, idempresa from placas where idtracker = '{$reg['idtracker']}'";
		$result = mysql_query($query);
		$result = mysql_fetch_assoc($result);

		// verificamos si el candado esta activado o no
		if($result['candado'] == 1){
			$id_empresa = $result['idempresa'];
			$candado = $result['candado'];
			// obtenemos el ultimo envio de coordenadas registrado en la base de datos
			$query = "select latx, longt from data_v2 where idtracker = '{$reg['idtracker']}' order by fecha_creacion desc, hora_creacion desc";
			$result = mysql_query($query);
			$result = mysql_fetch_assoc($result);
			// obtenemos la distancia en KM que hay entre la coordenada del registro en el momento de la iteracion con el ultimo registro obtenido de la base de datos y lo multiplicamos por 1000 para obtener la distancia en metros
			$distancia = harvestine($result['latx'], $result['longt'], $reg['latx'], $reg['longt']) * 1000;

			/*$distancia2 = round($distancia);
			$query = "insert into sms set mensaje = 'id tracker: {$reg['idtracker']}, candado: $candado, distancia: $distancia, distancaia_redondeada: $distancia2, latitud: {$reg['latx']}, longitud: {$reg['longt']}, latitud: {$result['latx']}, longitud: {$result['longt']}'";
			mysql_query($query);*/

			// verificamos que el redondeo de la distancia sea igual a 4 metros
			if(round($distancia) >= 5){
				// si es igual a 4 metros gardamos el sms
			/*	$distancia2 = round($distancia);
				$query = "insert into sms set mensaje = 'id tracker: {$reg['idtracker']}, candado: {$result['candado']}, distancia: $distancia, distancaia_redondeada: $distancia2, latitud: {$reg['latx']}, longitud: {$reg['longt']}, latitud: {$result['latx']}, longitud: {$result['longt']}'";
				mysql_query($query);*/

				$query = "select correo, telefono from sk_empresas where idempresa = '$id_empresa'";
				$result = mysql_query($query);
				$empresa = mysql_fetch_assoc($result);

				$html = "El vehiculo {$reg['idtracker']} a sido movido ilegalmente $distancia metros";

				$query = "insert into sms set mensaje = '$html', telefono = '{$empresa['telefono']}', enviado = 0";
				mysql_query($query);

				// se envia tmb el correo electronico
				require_once("phpmailer/class.phpmailer.php");
	            /*$mgClient = new Mailgun('key-21c746b361efffee28fa9f560769805f', new \Http\Adapter\Guzzle6\Client());
	            $domain = "gcompufax.com";

	            $aemails = explode(',', 'el.cholito99@gmail.com,hgaribay@gmail.com');
	            $emails = '';

	            foreach($aemails as $correo){
	                $correo = trim($correo);

	                if($emails != '')
	                    $emails .= ',';

	                $emails .= "Prueba <$correo>";
	            }

	            $result = $mgClient->sendMessage($domain, array(
	                'from'    => 'GCOMPUFAX <gcompufax@gcompufax.com>',
	                'to'      => $emails,
	                'subject' => 'Movimientos furtivos',
	                'html'    => $html,
	            ));*/
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
                $mail->Subject = 'Movimientos furtivos';
                $mail->IsHTML(true);
                $mail->Body = $html;
				$mail->Send();
			}
		}

		$contador++;
		if($contador == 500)
		{
			$datos = substr($datos,1);
			mysql_query($insert.$datos.";");
			$datos = "";
			$contador = 0;
		}
	}
	if($contador > 0)
	{
		$datos = substr($datos,1);
		mysql_query($insert.$datos.";");
	}
}

if(count($array_placas)>0){

	$campos = "";
	foreach($array_placas[0] as $k=>$v){
		$campos .= ",`".$k."`";
	}
	$campos = substr($campos, 1);
	$insert = "INSERT INTO placas ({$campos}) VALUES ";

	$contador = 0;
	$datos = "";
	foreach($array_placas as $valores)
	{
		$datos .= ",(";
		$primero = true;
		foreach($valores as $campo => $valor)
		{
			if(!$primero) $datos .= ",";
			if($valor == 'NULL')
				$datos .= "".addslashes($valor)."";
			else
				$datos .= "'".addslashes($valor)."'";
			$primero = false;
		}
		$datos .= ")";
		$contador++;
		if($contador == 500)
		{
			$datos = substr($datos,1);
			mysql_query($insert.$datos.";") or die(mysql_error());
			$datos = "";
			$contador = 0;
		}
	}
	if($contador > 0)
	{
		$datos = substr($datos,1);
		mysql_query($insert.$datos.";") or die(mysql_error());
	}
}

if(count($array_sk_empresas)>0){

	$campos = "";
	foreach($array_sk_empresas[0] as $k=>$v){
		$campos .= ",`".$k."`";
	}
	$campos = substr($campos, 1);
	$insert = "INSERT INTO sk_empresas ({$campos}) VALUES ";

	$contador = 0;
	$datos = "";
	foreach($array_sk_empresas as $valores)
	{
		$datos .= ",(";
		$primero = true;
		foreach($valores as $campo => $valor)
		{
			if(!$primero) $datos .= ",";
			if($valor == 'NULL')
				$datos .= "".addslashes($valor)."";
			else
				$datos .= "'".addslashes($valor)."'";
			$primero = false;
		}
		$datos .= ")";
		$contador++;
		if($contador == 500)
		{
			$datos = substr($datos,1);
			mysql_query($insert.$datos.";") or die(mysql_error());
			$datos = "";
			$contador = 0;
		}
	}
	if($contador > 0)
	{
		$datos = substr($datos,1);
		mysql_query($insert.$datos.";") or die(mysql_error());
	}
}


$conexion = mysql_connect("192.99.81.148","sadecv","Sadecv*DB");
mysql_select_db("trackingps",$conexion);

$consulta = mysql_query("SELECT s.id, s.type, s.description, s.imei, s.date, s.lat, s.lng, s.altitud, s.orientation, s.speed, s.status
	from gs_sadecv s, gs_objects g
	where s.imei=g.imei and g.manager_id=59 and s.status='0'");

mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
mysql_select_db('gamn_gps_skymedia') or die(mysql_error());
$resultado = array();
while($row = mysql_fetch_assoc($consulta)){
	$fecha = explode(' ', $row['date']);
	if($res1 = mysql_query("INSERT trackingps SET id = '{$row['id']}',tipo='{$row['type']}',descripcion='{$row['description']}',
		imei='{$row['imei']}',fecha='{$fecha[0]}',hora='{$fecha[1]}',latitud='{$row['lat']}',
		longitud='{$row['lng']}',altitud='{$row['altitud']}',angulo='{$row['orientation']}',
		velocidad='{$row['speed']}',estatus='{$row['status']}',fecha_creacion = CURDATE(), hora_creacion=CURTIME()")){
		$resultado[] = $row['id'];
	}
}

if(count($resultado) > 0)
{
	$conexion = mysql_connect("192.99.81.148","sadecv","Sadecv*DB");
	mysql_select_db("trackingps",$conexion);
	$ids = implode(',', $resultado);
	mysql_query("UPDATE gs_sadecv SET status=1 WHERE id IN ({$ids})");
}
?>
