<?php


$PI = 3.141592653589793;
$lat = 114;

echo deg2rad($lat);
echo '<br>';

echo $PI*$lat;

exit();

$conexion = mysql_connect("45.33.35.94","gps","bAllenA6##6") or die(mysql_error());
mysql_select_db("gps",$conexion);
$res = mysql_query("SELECT * FROM devices");
$array_devices=array();
while($row = mysql_fetch_assoc($res)){
	$array_devices[] = $row;
}

$res = mysql_query("SELECT * FROM groups");
$array_rutas=array();
while($row = mysql_fetch_assoc($res)){
	$array_rutas[] = $row;
}
$fechaborrar = date( "Y-m-d" , strtotime ( "+2 day" , strtotime(date('Y-m-d')) ) );
mysql_query("DELETE FROM positions WHERE devicetime > '".$fechaborrar." 00:00:00'");

$res = mysql_query("SELECT a.id, a.name, a.description, IFNULL(b.groupid,0) as groupid FROM geofences a LEFT JOIN group_geofence b ON a.id = b.geofenceid");
$array_geocercas=array();
while($row = mysql_fetch_assoc($res)){
	$array_geocercas[] = $row;
}

$fecha = date( "Y-m-d H:i:s" , strtotime ( "-1 day" , strtotime(date('Y-m-d H:i:s')) ) );
$fecha2 = date( "Y-m-d H:i:s" , strtotime ( "+4 hour" , strtotime(date('Y-m-d H:i:s')) ) );
$posiciones = mysql_query("SELECT DATE_ADD(a.servertime, interval -0 minute) as fechahora, a.servertime, a.devicetime, a.fixtime, a.deviceid, a.id, a.latitude, a.longitude, a.altitude, a.attributes  FROM positions a WHERE servertime >= '2017-12-25 12:00:00' AND servertime<='2017-12-26 12:00:00' ORDER BY servertime");

$eventos = mysql_query("SELECT a.type, date_add(a.servertime, interval -0 minute) as fechahora, a.id, a.servertime, a.deviceid, a.geofenceid, c.latitude, c.longitude, c.altitude, c.attributes  from events a inner join positions c on c.id = a.positionid 
		where ifnull(a.geofenceid,0) > 0 AND a.servertime>='2017-12-25 12:00:00' AND a.servertime<='2017-12-26 12:00:00' order by a.servertime");

mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
mysql_select_db('gamn_gps_otra_plataforma') or die(mysql_error());
foreach($array_devices as $row){
	$res = mysql_query("SELECT cve FROM dispositivos WHERE cve = '".$row['id']."'");
	if($row1 = mysql_fetch_array($res)){
		mysql_query("UPDATE dispositivos SET nombre = '".$row['name']."', uniqueid='".$row['uniqueid']."',ruta='".$row['groupid']."',telefono='".$row['phone']."',modelo='".$row['modelo']."',contacto='".$row['contact']."',categoria='".$row['category']."' WHERE cve = '".$row['id']."'");
	}
	else{
		mysql_query("INSERT dispositivos SET cve = '".$row['id']."', nombre = '".$row['name']."', uniqueid='".$row['uniqueid']."',ruta='".$row['groupid']."',telefono='".$row['phone']."',modelo='".$row['modelo']."',contacto='".$row['contact']."',categoria='".$row['category']."'");
	}
}
foreach($array_rutas as $row){
	$res = mysql_query("SELECT cve FROM rutas WHERE cve = '".$row['id']."'");
	if($row1 = mysql_fetch_array($res)){
		mysql_query("UPDATE rutas SET nombre = '".$row['name']."' WHERE cve = '".$row['id']."'");
	}
	else{
		mysql_query("INSERT rutas SET cve = '".$row['id']."', nombre = '".$row['name']."'");
	}
}

foreach($array_geocercas as $row){
	$res = mysql_query("SELECT cve FROM geocercas WHERE cve = '".$row['id']."'");
	if($row1 = mysql_fetch_array($res)){
		mysql_query("UPDATE geocercas SET codigo = '".$row['name']."',nombre='".$row['description']."',ruta='".$row['groupid']."' WHERE cve = '".$row['id']."'");
	}
	else{
		mysql_query("INSERT geocercas SET cve = '".$row['id']."', codigo = '".$row['name']."',nombre='".$row['description']."',ruta='".$row['groupid']."'");
	}
}

while($row = mysql_fetch_assoc($posiciones)){
	if(substr($row['devicetime'],0,10) < $fechaborrar){
		$datos = explode(' ', $row['fechahora']);
		$fecha = $datos[0];
		$hora = $datos[1];
		$atributos = json_decode($row['attributes'], true);
		mysql_query("INSERT posiciones SET fecha = '$fecha', hora='$hora', fechaserver='".$row['servertime']."', fechadispositivo='".$row['devicetime']."', fechafix='".$row['fixtime']."', dispositivo='".$row['deviceid']."', idserver='".$row['id']."', latitud='".$row['latitude']."', longitud='".$row['longitude']."', altitud='".$row['altitude']."', distancia='".$atributos['distance']."', totaldistancia='".$atributos['totalDistance']."',odometro='".$atributos['odometer']."'");
	}
}

while($row = mysql_fetch_assoc($eventos)){
	$datos = explode(' ', $row['fechahora']);
	$fecha = $datos[0];
	$hora = $datos[1];
	$atributos = json_decode($row['attributes'], true);
	mysql_query("INSERT eventos SET fecha = '$fecha', hora='$hora', fechaserver='".$row['servertime']."', geocerca='".$row['geofenceid']."', dispositivo='".$row['deviceid']."', idserver='".$row['id']."', latitud='".$row['latitud']."', longitud='".$row['longitude']."', altitud='".$row['altitude']."', tipo='".$row['type']."',odometro='".$atributos['odometer']."'");
}

exit();

$conexion = mysql_connect("45.33.35.94","gps","bAllenA6##6") or die(mysql_error());
$res = mysql_query("SHOW databases");
while($row = mysql_fetch_array($res)) echo $row[0];

exit();

//$conexion = mysql_connect("192.99.81.148","sadecv","sadecv++");
echo 'entra1';
$conexion = mysql_connect("94.23.33.14","sadecv","inteligps+") or die(mysql_error());
mysql_select_db("trackingps",$conexion);
echo 'enntra2';
$consulta = mysql_query("SELECT * from gs_objects where manager_id=59") or die(mysql_error());
while($row = mysql_fetch_assoc($consulta)){
	print_r($row);
	echo '<br>';
}
/*$conexion = mysql_connect("192.99.81.148","sadecv","Sadecv*DB");
mysql_select_db("trackingps",$conexion);
mysq_query("UPDATE gs_sadecv s INNER JOIN gs_objects g ON s.imei=g.imei AND g.manager_id = 59 SET s.status=0 WHERE s.status = 1")
$consulta = mysql_query("SELECT s.id, s.type, s.description, s.imei, s.date, s.lat, s.lng, s.altitud, s.orientation, s.speed, s.status 
	from gs_sadecv s, gs_objects g 
	where s.imei=g.imei and g.manager_id=59 and s.status='0'");

mysql_connect('localhost', 'gps', 'ballena');
mysql_select_db('gps_skymedia') or die(mysql_error());
$resultado = array();
while($row = mysql_fetch_assoc($consulta)){
	print_r($row);
	$fecha = explode(' ', $row['date']);
	if($res1 = mysql_query("INSERT trackingps SET id = '{$row['id']}',tipo='{$row['type']}',descripcion='{$row['description']}',
		imei='{$row['imei']}',fecha='{$fecha[0]}',hora='{$fecha[1]}',latitud='{$row['lat']}',
		longitud='{$row['lng']}',altitud='{$row['altitud']}',angulo='{$row['orientation']}',
		velocidad='{$row['speed']}',estatus='{$row['status']}'")){
		$resultado[] = $row['id'];
	}	
}

if(count($resultado) > 0)
{
	$conexion = mysql_connect("192.99.81.148","sadecv","Sadecv*DB");
	mysql_select_db("trackingps",$conexion);
	$ids = implode(',', $resultado);
	mysql_query("UPDATE gs_sadecv SET status=1 WHERE id IN ({$ids})");
}*/
/*
$conexion = mysql_connect("192.99.81.148","sadecv","Sadecv*DB");
mysql_select_db("trackingps",$conexion);

echo '<pre>';
$conexion = mysql_connect("192.99.81.148","sadecv","Sadecv*DB");
mysql_select_db("trackingps",$conexion);

$consulta = mysql_query("SELECT name, imei, dt_server, lat, lng, altitude, angle, speed from gs_objects where manager_id=59");
mysql_connect('localhost', 'gps', 'ballena');
mysql_select_db('gps_skymedia') or die(mysql_error());
while ($row = mysql_fetch_assoc($consulta)) {
	mysql_query("INSERT gps_objects SET dispositivo='{$row['name']}',
			imei='{$row['imei']}',fecha='{$fecha[0]}',hora='{$fecha[1]}',latitud='{$row['lat']}',
			longitud='{$row['lng']}',altitud='{$row['altitude']}',angulo='{$row['angle']}',
			velocidad='{$row['speed']}',fecha_creacion = CURDATE(), hora_creacion=CURTIME()") or die(mysql_error());

}*/
/*$consulta = mysql_query("SELECT * FROM gs_object_data_863835029987760 LIMIT 10");
while ($dato = mysql_fetch_assoc($consulta)) {
	print_r($dato);

}
echo '</pre>';
*/
/*$conexion = mysql_connect("192.99.81.148","sadecv","sadecv++");
mysql_select_db("trackingps",$conexion);


echo "<center><table border='0' width='90%'>";
echo "<tr bgcolor='c3c3c3'>
		<td>ID</td>
		<td>Usuario</td>
		<td>Tipo</td>
		<td>Descripción</td>
		<td>IMEI</td>
		<td>Fecha Hora</td>
		<td>Latitud</td>
		<td>Longitud</td>
		<td>Altitud</td>
		<td>Angulo</td>
		<td>Velocidad</td>
		<td>Status</td></tr>";
$consulta = mysql_query("SELECT s.id, u.username, s.type, s.description, s.imei, s.date, s.lat, s.lng, s.altitud, s.orientation, s.speed, s.status 
	from gs_sadecv s, gs_objects g, gs_users u where s.imei=g.imei and g.manager_id=59 and s.user_id=u.id ") or die(mysql_error());
while ($dato = mysql_fetch_assoc($consulta)) {
	echo "<tr><td> ".$dato['id'].'</td>';
	echo "<td> ".$dato['username'].'</td>';
	echo "<td> ".$dato['type'].'</td>';
	echo "<td> ".$dato['description'].'</td>';
	echo "<td> ".$dato['imei'].'</td>';
	echo "<td> ".$dato['date'].'</td>';
	echo "<td> ".$dato['lat'].'</td>';
	echo "<td> ".$dato['lng'].'</td>';
	echo "<td> ".$dato['altitud'].'</td>';
	echo "<td> ".$dato['orientation'].'</td>';
	echo "<td> ".$dato['speed'].'</td>';
	echo "<td> ".$dato['status'].'</td></tr>';

}

echo "</table>";*/
/*require_once('subs/cnx_db.php');

function CalcularOdometro($lat1, $lon1, $lat2, $lon2)
{
	$theta = $lon1 - $lon2; 
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
	$dist = acos($dist); 
	$dist = rad2deg($dist); 
	$km = $dist * 60 * 1.1515 * 1.609344;
	
	return sprintf("%01.6f", $km);
}

$select= " SELECT * FROM puntos";
$array_puntos = array();
$res = mysql_query($select);
while($row = mysql_fetch_array($res)){
	$datos = explode(',', $row['coordenada']);
	$array_puntos[$row['cve']] = array('lat' => $datos[0], 'lon' => $datos[1]);
}

mysql_select_db('gps_skymedia');
$res = mysql_query("SELECT * FROM gps_objects_history WHERE punto > 0");
while($row = mysql_fetch_array($res)){
	
	$distancia = CalcularOdometro($coordenadas[$row['punto']]['lat'], $coordenadas[$row['punto']]['lon'], $row['latitud'], $row['longitud']);
	
	mysql_query("UPDATE gps_objects_history SET distancia = '$distancia' WHERE cve='".$row['cve']."'");
}*/
?>