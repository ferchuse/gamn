<?php

mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
mysql_select_db('gamn_gps_otra_plataforma') or die(mysql_error());
$resB = mysql_query("SELECT * FROM cat_bases ORDER BY cve");
while($rowB = mysql_fetch_array($resB)){
	//$conexion = mysql_connect("45.33.35.94","gps","bAllenA6##6") or die(mysql_error());
	//$base=1;
	//mysql_select_db("gps",$conexion);
	$conexion = mysql_connect($rowB['ip'],$rowB['usuario'],$rowB['contra']) or die(mysql_error());
	$base=$rowB['cve'];
	mysql_select_db($rowB['base'],$conexion);
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
	//mysql_query("DELETE FROM positions WHERE devicetime > '".$fechaborrar." 00:00:00'");

	$res = mysql_query("SELECT a.id, a.name, a.description, IFNULL(b.groupid,0) as groupid FROM geofences a LEFT JOIN group_geofence b ON a.id = b.geofenceid");
	$array_geocercas=array();
	while($row = mysql_fetch_assoc($res)){
		$array_geocercas[] = $row;
	}

	$fecha = '2018-03-12 09:52:00';

	$eventos = mysql_query("SELECT a.type, a.servertime as fechahora, a.id, a.servertime, a.deviceid, a.geofenceid, c.latitude, c.longitude, c.altitude, c.attributes  from events a inner join positions c on c.id = a.positionid 
			where a.servertime>='".$fecha."'");//ifnull(a.geofenceid,0) > 0 AND 

	$posiciones = mysql_query("SELECT a.servertime as fechahora, a.servertime, a.devicetime, a.fixtime, a.deviceid, a.id, a.latitude, a.longitude, a.altitude, a.attributes  FROM positions a WHERE servertime >= '".$fecha."'");

	mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
	mysql_select_db('gamn_gps_otra_plataforma') or die(mysql_error());
	foreach($array_devices as $row){
		$res = mysql_query("SELECT cve FROM dispositivos WHERE base='$base' AND cvebase = '".$row['id']."'");
		if($row1 = mysql_fetch_array($res)){
			mysql_query("UPDATE dispositivos SET nombre = '".$row['name']."', uniqueid='".$row['uniqueid']."',ruta='".$row['groupid']."',modelo='".$row['model']."',contacto='".$row['contact']."',categoria='".$row['category']."' WHERE cve = '".$row1['cve']."'");
		}
		else{
			mysql_query("INSERT dispositivos SET base='$base', cvebase = '".$row['id']."', nombre = '".$row['name']."', uniqueid='".$row['uniqueid']."',ruta='".$row['groupid']."',modelo='".$row['model']."',contacto='".$row['contact']."',categoria='".$row['category']."'");
		}
	}
	foreach($array_rutas as $row){
		$res = mysql_query("SELECT cve FROM rutas WHERE base='$base' AND cvebase = '".$row['id']."'");
		if($row1 = mysql_fetch_array($res)){
			mysql_query("UPDATE rutas SET nombre = '".$row['name']."' WHERE cve = '".$row1['cve']."'");
		}
		else{
			mysql_query("INSERT rutas SET base='$base', cvebase = '".$row['id']."', nombre = '".$row['name']."'");
		}
	}

	foreach($array_geocercas as $row){
		$res = mysql_query("SELECT cve FROM geocercas WHERE base = '$base' AND cvebase = '".$row['id']."' AND ruta = '".$row['groupid']."'");
		if($row1 = mysql_fetch_array($res)){
			mysql_query("UPDATE geocercas SET codigo = '".$row['name']."',nombre='".$row['description']."',ruta='".$row['groupid']."' WHERE cve = '".$row1['cve']."'");
		}
		else{
			mysql_query("INSERT geocercas SET base='$base', cvebase = '".$row['id']."', codigo = '".$row['name']."',nombre='".$row['description']."',ruta='".$row['groupid']."'");
		}
	}

	while($row = mysql_fetch_assoc($eventos)){
		$datos = explode(' ', $row['fechahora']);
		$fecha = $datos[0];
		$hora = $datos[1];
		$atributos = json_decode($row['attributes'], true);
		$en_marcha = ($atributos['motion'] == 'true') ? 1 : 0;
		mysql_query("INSERT eventos SET fecha = '$fecha', hora='$hora', fechaserver='".$row['servertime']."', geocerca='".$row['geofenceid']."', dispositivo='".$row['deviceid']."', idserver='".$row['id']."', latitud='".$row['latitud']."', longitud='".$row['longitude']."', altitud='".$row['altitude']."', tipo='".$row['type']."',odometro='".$atributos['odometer']."', base='$base',motor_encendido='".$atributos['power']."',en_marcha='".$en_marcha."'");
	}

	while($row = mysql_fetch_assoc($posiciones)){
		/*if(($row['longitude'] >= -118 && $row['longitude'] <= -84) || ($row['latitude'] >= 15 && $row['latitude'] <= 33)){
		}
		else{*/
			if(substr($row['devicetime'],0,10) < $fechaborrar){
				$datos = explode(' ', $row['fechahora']);
				$fecha = $datos[0];
				$hora = $datos[1];
				$atributos = json_decode($row['attributes'], true);
				$en_marcha = ($atributos['motion'] == 'true') ? 1 : 0;
				mysql_query("INSERT posiciones SET fecha = '$fecha', hora='$hora', fechaserver='".$row['servertime']."', fechadispositivo='".$row['devicetime']."', fechafix='".$row['fixtime']."', dispositivo='".$row['deviceid']."', idserver='".$row['id']."', latitud='".$row['latitude']."', longitud='".$row['longitude']."', altitud='".$row['altitude']."', distancia='".$atributos['distance']."', totaldistancia='".$atributos['totalDistance']."',odometro='".$atributos['odometer']."', base='$base',
					motor_encendido='".$atributos['power']."',en_marcha='".$en_marcha."', event='".$atributos['event']."',
					sat='".$atributos['sat']."', hdop='".$atributos['hdop']."', runtime = '".$atributos['runtime']."', 
					status='".$atributos['status']."', adc1='".$atributos['adc1']."', adc2='".$atributos['adc2']."',
					adc3='".$atributos['adc3']."', batery='".$atributos['battery']."', power = '".$atributos['power']."'");
			}
		//}
	}
}
?>