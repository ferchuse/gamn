<?php

//mysql_connect('localhost', 'gamn', 'gamn');
echo 'entra1';
$MySQL=@mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
mysql_select_db('gamn_gps_otra_plataforma') or die(mysql_error());
$resB = mysql_query("SELECT * FROM cat_bases ORDER BY cve");
while($rowB = mysql_fetch_array($resB)){
	echo 'entra';
	//$conexion = mysql_connect("45.33.35.94","gps","bAllenA6##6") or die(mysql_error());
	//$base=1;
	//mysql_select_db("gps",$conexion);
	$conexion = mysql_connect($rowB['ip'],$rowB['usuario'],$rowB['contra']) or die(mysql_error());
	$base=$rowB['cve'];
	mysql_select_db($rowB['base'],$conexion);
	$res = mysql_query("SELECT * FROM devices") or die(mysql_error());
	$array_devices=array();
	while($row = mysql_fetch_assoc($res)){
		$array_devices[] = $row;
	}

	$res = mysql_query("SELECT * FROM groups") or die(mysql_error());
	$array_rutas=array();
	while($row = mysql_fetch_assoc($res)){
		$array_rutas[] = $row;
	}
	$fechaborrar = date( "Y-m-d" , strtotime ( "+2 day" , strtotime(date('Y-m-d')) ) );
	//mysql_query("DELETE FROM positions WHERE devicetime > '".$fechaborrar." 00:00:00'");

	$res = mysql_query("SELECT a.id, a.name, a.description, IFNULL(b.groupid,0) as groupid FROM geofences a LEFT JOIN group_geofence b ON a.id = b.geofenceid") or die(mysql_error());
	$array_geocercas=array();
	while($row = mysql_fetch_assoc($res)){
		$array_geocercas[] = $row;
	}

	$fecha = date( "Y-m-d H:i:s" , strtotime ( "-30 minute" , strtotime(date('Y-m-d H:i:s')) ) );

	$eventos = mysql_query("SELECT a.type, a.servertime as fechahora, a.id, a.servertime, a.deviceid, a.geofenceid, c.latitude, c.longitude, c.altitude, a.attributes as attributsevent, c.attributes  from events a inner join positions c on c.id = a.positionid 
			where a.servertime>='".$fecha."'") or die(mysql_error());//ifnull(a.geofenceid,0) > 0 AND 

	mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
	mysql_select_db('gamn_gps_otra_plataforma') or die(mysql_error());
	foreach($array_devices as $row){
		echo 'entra5';
		$res = mysql_query("SELECT cve FROM dispositivos WHERE base='$base' AND cvebase = '".$row['id']."'");
		if($row1 = mysql_fetch_array($res)){
			mysql_query("UPDATE dispositivos SET nombre = '".$row['name']."', uniqueid='".$row['uniqueid']."',ruta='".$row['groupid']."',modelo='".$row['model']."',contacto='".$row['contact']."',categoria='".$row['category']."', telefono='".$row['phone']."' WHERE cve = '".$row1['cve']."'");
		}
		else{
			mysql_query("INSERT dispositivos SET base='$base', cvebase = '".$row['id']."', nombre = '".$row['name']."', uniqueid='".$row['uniqueid']."',ruta='".$row['groupid']."',modelo='".$row['model']."',contacto='".$row['contact']."',categoria='".$row['category']."', telefono='".$row['phone']."'");
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
		$atributosevento = json_decode($row['attributsevent'], true);
		$atributos = json_decode($row['attributes'], true);
		$en_marcha = ($atributos['motion'] == 'true') ? 1 : 0;
		mysql_query("INSERT eventos SET fecha = '$fecha', hora='$hora', fechaserver='".$row['servertime']."', geocerca='".$row['geofenceid']."', dispositivo='".$row['deviceid']."', idserver='".$row['id']."', latitud='".$row['latitude']."', longitud='".$row['longitude']."', altitud='".$row['altitude']."', tipo='".$row['type']."',odometro='".$atributos['odometer']."', base='$base',motor_encendido='".$atributos['power']."',en_marcha='".$en_marcha."',velocidad='".$atributosevento['speed']."', limite_velocidad='".$atributosevento['speedLimit']."'") or die(mysql_error());
	}
}
?>