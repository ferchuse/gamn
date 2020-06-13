<?php



mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
mysql_select_db('gamn_gps_otra_plataforma') or die(mysql_error());
$resB = mysql_query("SELECT * FROM cat_bases ORDER BY cve");
while($rowB = mysql_fetch_array($resB)){
	$conexion = mysql_connect($rowB['ip'],$rowB['usuario'],$rowB['contra']) or die(mysql_error());
	$base=$rowB['cve'];
	mysql_select_db($rowB['base'],$conexion);
	$fechaborrar = date( "Y-m-d" , strtotime ( "+2 day" , strtotime(date('Y-m-d')) ) );
	$fecha = date( "Y-m-d H:i:s" , strtotime ( "-30 minute" , strtotime(date('Y-m-d H:i:s')) ) );
	$posiciones = mysql_query("SELECT a.servertime as fechahora, a.servertime, a.devicetime, a.fixtime, a.deviceid, a.id, a.latitude, a.longitude, a.altitude, a.attributes, a.speed  FROM positions a WHERE servertime >= '".$fecha."'");

	mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');
	mysql_select_db('gamn_gps_otra_plataforma') or die(mysql_error());
	while($row = mysql_fetch_assoc($posiciones)){
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
					adc3='".$atributos['adc3']."', batery='".$atributos['battery']."', power = '".$atributos['power']."', speed='".$row['speed']."'");
			}
	}

}

?>