<?php

mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
mysql_select_db('gamn_gps_skymedia');

$res = mysql_query("SELECT minutos FROM minutos_atrasados ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$minutos = $row[0];

$conexion = mysql_connect("192.99.81.148","sadecv","sadecv++");
//$conexion=mysql_connect("94.23.33.14","sadecv","sadecv++");
//$conexion = mysql_connect("94.23.33.14","root","inteligps+");
mysql_select_db("trackingps",$conexion);

$consulta = mysql_query("SELECT s.id, u.username, s.type, s.description, s.imei, s.date, s.lat, s.lng, s.altitud, s.orientation, s.speed, s.status 
	from gs_sadecv s, gs_objects g, gs_users u 
	where s.imei=g.imei and g.manager_id=59 and s.user_id=u.id and s.status='0'");


echo mysql_num_rows($consulta);
exit();

mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
mysql_select_db('gamn_gps_skymedia') or die(mysql_error());
$resultado = array();
$tipos = array();
$usuario = array();
$array_geocercas = array();
while($row = mysql_fetch_assoc($consulta)){
	$fecha = explode(' ', $row['date']);
	$fecha_actual = date( "Y-m-d H:i:s" , strtotime ( "-".$minutos." minute" , strtotime($row['date']) ) );
	$fecham = explode(' ',$fecha_actual);
	$parentesis = strpos($row['description'], '(');
	if($parentesis === false){

	}
	else{
		$row['geocerca'] = substr($row['description'], $parentesis+1, -1);
		$row['description'] = substr($row['description'], 0, $parentesis);
	}
	if($res1 = mysql_query("INSERT trackingps SET id = '{$row['id']}',tipo='{$row['type']}',descripcion='{$row['description']}',
		geocerca='{$row['geocerca']}',imei='{$row['imei']}',fecha='{$fecha[0]}',hora='{$fecha[1]}',latitud='{$row['lat']}',
		longitud='{$row['lng']}',altitud='{$row['altitud']}',angulo='{$row['orientation']}',
		velocidad='{$row['speed']}',estatus='{$row['status']}',fecha_creacion = CURDATE(), hora_creacion=CURTIME(),
		fecham='{$fecham[0]}',horam='{$fecham[1]}',username='{$row['username']}'")){
		$resultado[] = $row['id'];
		$tipos[$row['type']] = $row['type'];
		$array_usuario[$row['imei']] = $row['username'];
		$array_geocercas[$row['username']][$row['geocerca']] = $row['geocerca'];
	}	
}

if(count($resultado) > 0)
{
	foreach($array_geocercas as $usuario => $geocercas){
		foreach($geocercas as $geocerca){
			$res = mysql_query("SELECT cve FROM geocercas WHERE nombre = '{$geocerca}' AND usuario = '{$usuario}'");
			if(!$row = mysql_fetch_array($res)){
				mysql_query("INSERT geocercas SET nombre = '{$geocerca}', usuario = '{$usuario}'");
			}
		}
	}
	
	foreach($tipos as $tipo){
		mysql_query("INSERT tipotrakin SET nombre = '{$tipo}'");
	}
	$conexion = mysql_connect("192.99.81.148","sadecv","sadecv++");//Sadecv*DB
	//$conexion=mysql_connect("94.23.33.14","sadecv","sadecv++");
	//$conexion = mysql_connect("94.23.33.14","root","inteligps+");
	mysql_select_db("trackingps",$conexion);
	$ids = implode(',', $resultado);
	mysql_query("UPDATE gs_sadecv SET status=1 WHERE id IN ({$ids})");
	
}

$conexion = mysql_connect("192.99.81.148","sadecv","sadecv++");
//$conexion=mysql_connect("94.23.33.14","sadecv","sadecv++");
//$conexion = mysql_connect("94.23.33.14","root","inteligps+");
mysql_select_db("trackingps",$conexion);

$consulta = mysql_query("SELECT name, plate_number, imei, dt_server, lat, lng, altitude, angle, speed, odometer from gs_objects where manager_id=59");

mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
mysql_select_db('gamn_gps_skymedia') or die(mysql_error());

$array_imes = array();
$res = mysql_query("SELECT imei, MAX(dt_server) FROM gps_objects_history GROUP BY imei");
while($row = mysql_fetch_array($res)){
	$array_imes[$row['imei']] = $row[1];
}

$resultado = array();
while($row = mysql_fetch_assoc($consulta)){
	$fecha = explode(' ', $row['dt_server']);
	$res1 = mysql_query("SELECT id, usuario FROM gps_objects WHERE imei = '{$row['imei']}'");
	$updateusuario='';
	if($array_usuario[$row['imei']] != '') $updateusuario = ", usuario = '".$array_usuario[$row['imei']]."'";
	if($row1 = mysql_fetch_assoc($res1)){
		if($res1 = mysql_query("UPDATE gps_objects SET dispositivo='{$row['name']}',placa='{$row['plate_number']}',
			fecha='{$fecha[0]}',hora='{$fecha[1]}',latitud='{$row['lat']}',
			longitud='{$row['lng']}',altitud='{$row['altitude']}',angulo='{$row['angle']}',
			velocidad='{$row['speed']}',fecha_creacion = CURDATE(), hora_creacion=CURTIME(),
			odometer='{$row['odometer']}' $updateusuario
			WHERE id = '{$row1['id']}'")){
			$resultado[] = "'".$row['imei']."'";
		}	
	}
	else{
		if($res1 = mysql_query("INSERT gps_objects SET dispositivo='{$row['name']}',placa='{$row['plate_number']}',
			imei='{$row['imei']}',fecha='{$fecha[0]}',hora='{$fecha[1]}',latitud='{$row['lat']}',
			longitud='{$row['lng']}',altitud='{$row['altitude']}',angulo='{$row['angle']}',
			velocidad='{$row['speed']}',fecha_creacion = CURDATE(), hora_creacion=CURTIME(),
			odometer='{$row['odometer']}' $updateusuario")){
			$resultado[] = "'".$row['imei']."'";
		}	
	}
	$fecha_actual = date( "Y-m-d H:i:s" , strtotime ( "-".$minutos." minute" , strtotime($row['dt_server']) ) );
	$fecha = explode(' ', $fecha_actual);
	mysql_query("INSERT gps_objects_odo SET imei = '{$row['imei']}',fecha='{$fecha[0]}',hora='{$fecha[1]}',
		dt_server='{$row['dt_server']}',odo='{$row['odometer']}'");
}

function calcular_distancia_marcador($lat1,$lng1,$lat2,$lng2)
{
	$R = 6371; // Radius of the earth in km
	$Lat = deg2rad($lat2-$lat1);  // deg2rad below
	$dLon = deg2rad($lng2-$lng1); 
	$a = sin($dLat/2) * sin($dLat/2) +
		cos(toRad($lat1)) * cos(toRad($lat2)) * 
		sin($dLon/2) * sin($dLon/2)
		; 
	$c = 2 * atan2(sqrt($a), sqrt(1-$a)); 
	$d = $R * $c; // Distance in km
	
	return $d;
}

function CalcularOdometro($lat1, $lon1, $lat2, $lon2)
{
	$theta = $lon1 - $lon2; 
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
	$dist = acos($dist); 
	$dist = rad2deg($dist); 
	$km = $dist * 60 * 1.1515 * 1.609344;
	
	return sprintf("%01.6f", $km);
}

$array_datos_historial = array();
if(count($resultado) > 0)
{
	$conexion = mysql_connect("192.99.81.148","sadecv","sadecv++");
	//$conexion=mysql_connect("94.23.33.14","sadecv","sadecv++");
	//$conexion = mysql_connect("94.23.33.14","root","inteligps+");
	mysql_select_db("trackingps",$conexion);
	$ids = implode(',', $resultado);
	//mysql_query("UPDATE gs_objects SET status=1 WHERE imei IN ({$ids})");

	foreach($resultado as $imei){
		$res = mysql_query("SELECT latitud as lat,longitud as lng,velocidad as speed, dt_server from gps_objects_history WHERE imei='{$imei}' AND dt_server='{$array_imes[$imei]}'");
		$anterior = mysql_fetch_array($res);
		$kms=0;
		$imei = substr($imei, 1, -1);
		$res = mysql_query("SELECT '{$imei}' as imei, dt_server, dt_tracker, lat, lng, altitude, angle, speed 
			FROM gs_object_data_{$imei} WHERE dt_server > '{$array_imes[$imei]}'") or die(mysql_error());
		while($row = mysql_fetch_assoc($res)){
			$fecha_actual = date( "Y-m-d H:i:s" , strtotime ( "-".$minutos." minute" , strtotime($datos['dt_server']) ) );
			$row['fecha_actual'] = $fecha_actual;
			$array_datos_historial[] = $row;
			if(substr($anterior['fecha_actual'],0,10)==substr($row['fecha_actual'], 0, 10)){
				if($anterior['lat']!=0 && $anterior['lng']!=0 && $anterior['speed']>3 && $row['lat']!=0 && $row['lng']!=0 && $row['speed']>3)
					$kms += CalcularOdometro($anterior['lat'], $anterior['lng'], $row['lat'], $row['lng']);
			}
			else{
				$res1 = mysql_query("SELECT cve FROM gps_objects_km WHERE imei='{$imei}' AND fecha='".substr($anterior['fecha_actual'],0,10)."'");
				if($row1 = mysql_fetch_array($res1))
					mysql_query("UPDATE gps_objects_km SET km = km + {$kms} WHERE cve = '{$row1['cve']}'");
				else
					mysql_query("INSERT gps_objects_km SET imei='{$imei}' AND fecha='".substr($anterior['fecha_actual'],0,10)."',km = '{$kms}'");
				$kms=0;
			}
			$anterior = $row;
		}
		$res1 = mysql_query("SELECT cve FROM gps_objects_km WHERE imei='{$imei}' AND fecha='".substr($anterior['fecha_actual'],0,10)."'");
		if($row1 = mysql_fetch_array($res1))			
			mysql_query("UPDATE gps_objects_km SET kms = kms + {$kms} WHERE cve = '{$row1['cve']}'");
		else
			mysql_query("INSERT gps_objects_km SET imei='{$imei}' AND fecha='".substr($anterior['fecha_actual'],0,10)."',kms = '{$kms}'");
	}
}

if(count($array_datos_historial) > 0)
{
	mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
	mysql_select_db('gamn_gps') or die(mysql_error());
	$select= " SELECT * FROM puntos";
	$array_puntos = array();
	$res = mysql_query($select);
	while($row = mysql_fetch_array($res)){
		$datos = explode(',', $row['coordenada']);
		$array_puntos[$row['ruta']][$row['cve']] = array('lat' => $datos[0], 'lon' => $datos[1]);
	}
	mysql_select_db('gamn_gps_skymedia') or die(mysql_error());
	

	$array_marcadores = array();
	$select= " SELECT * FROM marcadores where ruta > 0";
	$res = mysql_query($select);
	while($row = mysql_fetch_array($res)){
		$array_marcadores[$row['ruta']][$row['cve']] = array('lat' => $row['latitud'], 'lon' => $row['longitud']);
	}
	
	$array_dispositivos = array();
	$select = "SELECT imei, ruta, ruta_gps FROM gps_objects";
	$res = mysql_query($select);
	while($row = mysql_fetch_array($res)){
		$array_dispositivos[$row['imei']][0]=$row['ruta'];
		$array_dispositivos[$row['imei']][1]=$row['ruta_gps'];
	}
	foreach($array_datos_historial as $datos)
	{
		$punto = 0;
		$minimo = 0;
		foreach($array_puntos[$array_dispositivos[$datos['imei'][0]]] as $cvepunto => $coordenadas){
			$distancia = CalcularOdometro($coordenadas['lat'], $coordenadas['lon'], $datos['lat'], $datos['lng']);
			if($punto == 0){
				$punto = $cvepunto;
				$minimo = $distancia;
			}
			elseif($minimo>$distancia){
				$punto = $cvepunto;
				$minimo = $distancia;	
			}
		}
		$marcador = 0;
		$minimo2 = 0;
		foreach($array_marcadores[$array_dispositivos[$datos['imei'][1]]] as $cvepunto => $coordenadas){
			$distancia = calcular_distancia_marcador($coordenadas['lat'], $coordenadas['lon'], $datos['lat'], $datos['lng']);
			if($marcador == 0){
				$punto = $cvepunto;
				$minimo2 = $distancia;
			}
			elseif($minimo>$distancia){
				$marcador = $cvepunto;
				$minimo2 = $distancia;	
			}
		}

		// mio
		$query = "select latitud, longitud from gps_objects_history where imei = '{$datos['imei']}' order by cve desc limit 1";
		$result = mysql_query($query);
		$result = mysql_fetch_assoc($result);
		$latitud = $result['latitud'];
		$longitud = $result['longitud'];
		// termina mio

		$fecha_actual = date( "Y-m-d H:i:s" , strtotime ( "-".$minutos." minute" , strtotime($datos['dt_server']) ) );
		$fecha = explode(' ',$fecha_actual);
		mysql_query("INSERT gps_objects_history SET imei='{$datos['imei']}',
			dt_server='{$datos['dt_server']}',dt_tracker='{$datos['dt_tracker']}',
			latitud='{$datos['lat']}',longitud='{$datos['lng']}',altitud='{$datos['altitude']}',
			angulo='{$datos['angle']}',velocidad='{$datos['speed']}',fecha_creacion=NOW(),
			fecha='{$fecha[0]}',hora='{$fecha[1]}',punto='{$punto}',distancia = '{$minimo}',
			marcador='$marcador',distancia_marcador='$minimo2'") or die(mysql_error());

		// mio
		$query = "select candado * from gps_objects where imei = '{$datos['imei']}'";
		$result = mysql_query($query);
		$result = mysql_fetch_assoc($result);

		if($result['candado'] == 1){
			$distancia = harvestine($result['latx'], $result['longt'], $reg['latx'], $reg['longt']) * 1000;
			// verificamos que el redondeo de la distancia sea igual a 4 metros
			if(round($distancia) >= 5){
				$html = "El vehiculo {$datos['imei']} a sido movido ilegalmente $distancia metros";
				$query = "insert into sms set mensaje = '$html', telefono = '{$empresa['telefono']}', enviado = 0";
				mysql_query($query);

				require_once("phpmailer/class.phpmailer.php");
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
		// termina mio
	}
}

$conexion = mysql_connect("192.99.81.148","sadecv","sadecv++");
//$conexion=mysql_connect("94.23.33.14","sadecv","sadecv++");
//$conexion = mysql_connect("94.23.33.14","root","inteligps+");
mysql_select_db("trackingps",$conexion);
$consulta = mysql_query("SELECT s.marker_id, u.username, s.marker_name, s.marker_lat, s.marker_lng  from gs_user_markers s, gs_users u where u.manager_id=59 and s.user_id=u.id order by s.marker_id desc");
mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
mysql_select_db('gamn_gps_skymedia') or die(mysql_error());
while ($dato = mysql_fetch_assoc($consulta)) {
	$res = mysql_query("SELECT cve FROM marcadores WHERE cve = '".$dato['marker_id']."'");
	if($row = mysql_fetch_assoc($res)){
		mysql_query("UPDATE marcadores SET usuario='".$dato['username']."',
			nombre='".$dato['marker_name']."',latitud='".$dato['marker_lat']."',
			longitud='".$dato['marker_lng']."' 
			WHERE cve='".$dato['marker_id']."'");
	}
	else{
		mysql_query("INSERT marcadores SET cve='".$dato['marker_id']."',usuario='".$dato['username']."',
			nombre='".$dato['marker_name']."',latitud='".$dato['marker_lat']."',
			longitud='".$dato['marker_lng']."'");
	}
}

$conexion = mysql_connect("192.99.81.148","sadecv","sadecv++");
//$conexion=mysql_connect("94.23.33.14","sadecv","sadecv++");
//$conexion = mysql_connect("94.23.33.14","root","inteligps+");
mysql_select_db("trackingps",$conexion);
$consulta = mysql_query("SELECT s.route_id, u.username, s.route_name, s.route_points  from gs_user_routes s, gs_users u where u.manager_id=59 and s.user_id=u.id order by s.route_id desc");mysql_connect('localhost', 'gps', 'ballena');
mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
mysql_select_db('gamn_gps_skymedia') or die(mysql_error());
while ($dato = mysql_fetch_assoc($consulta)) {
	$res = mysql_query("SELECT cve FROM rutas_gps WHERE cve = '".$dato['route_id']."'");
	if($row = mysql_fetch_assoc($res)){
		mysql_query("UPDATE rutas_gps SET usuario='".$dato['username']."',
			nombre='".$dato['route_name']."',puntos='".$dato['route_points']."' 
			WHERE cve='".$dato['route_id']."'");
	}
	else{
		mysql_query("INSERT rutas_gps SET cve='".$dato['route_id']."',usuario='".$dato['username']."',
			nombre='".$dato['route_name']."',puntos='".$dato['route_points']."'");
	}
}

$conexion = mysql_connect("192.99.81.148","sadecv","sadecv++");
//$conexion=mysql_connect("94.23.33.14","sadecv","sadecv++");
//$conexion = mysql_connect("94.23.33.14","root","inteligps+");
mysql_select_db("trackingps",$conexion);
$consulta = mysql_query("SELECT s.zone_id, u.username, s.zone_name, s.zone_vertices  from gs_user_zones s, gs_users u where u.manager_id=59 and s.user_id=u.id order by s.zone_id desc");
mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
mysql_select_db('gamn_gps_skymedia') or die(mysql_error());
while ($dato = mysql_fetch_assoc($consulta)) {
	$res = mysql_query("SELECT cve FROM geocercas_gps WHERE cve = '".$dato['zone_id']."'");
	if($row = mysql_fetch_assoc($res)){
		mysql_query("UPDATE geocercas_gps SET usuario='".$dato['username']."',
			nombre='".$dato['zone_name']."',vertices='".$dato['zone_vertices']."' 
			WHERE cve='".$dato['zone_id']."'");
	}
	else{
		mysql_query("INSERT geocercas_gps SET cve='".$dato['zone_id']."',usuario='".$dato['username']."',
			nombre='".$dato['zone_name']."',vertices='".$dato['zone_vertices']."'");
	}
	mysql_query("UPDATE trackingps SET id_geocerca = '".$dato['zone_id']."' WHERE geocerca = '".$dato['zone_name']."' AND username='".$dato['username']."' AND id_geocerca = 0");
}

?>