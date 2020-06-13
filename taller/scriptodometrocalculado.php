<?php
mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2');


mysql_select_db('gamn_gps_otra_plataforma') or die(mysql_error());
$fecha = date( "Y-m-d" , strtotime ( "-1 day" , strtotime(date('Y-m-d')) ) );
mysql_query("INSERT INTO odometro_unidad (fecha, base, dispositivo, odometro) SELECT '$fecha', base, dispositivo, MAX(odometro) FROM posiciones WHERE fecha='$fecha' GROUP BY base, dispositivo");

mysql_select_db('gamn_gps_skymedia');


$select= " SELECT imei FROM gps_objects";

function CalcularOdometro2($lat1, $lon1, $lat2, $lon2)
{
	$theta = $lon1 - $lon2; 
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
	$dist = acos($dist); 
	$dist = rad2deg($dist); 
	$km = $dist * 60 * 1.1515 * 1.609344;
	
	return sprintf("%01.6f", $km);
}

function calcular_kms_dia($imei, $fecha)
{
	$res = mysql_query("SELECT * FROM gps_objects_history WHERE imei = '".$imei."' AND fecha = '".$fecha."' ORDER BY hora");
	$primera = true;
	$kms = 0;
	while($row = mysql_fetch_assoc($res))
	{
		if(!$primera){
			if($anterior['latitud']!=0 && $anterior['longitud']!=0 && $anterior['velocidad']>3 && $row['latitud']!=0 && $row['longitud']!=0 && $row['velocidad']>3){
				$km = CalcularOdometro2($anterior['latitud'], $anterior['longitud'], $row['latitud'], $row['longitud']);
			}
			$kms+=$km;
		}
		$anterior = $row;
		$primera = false;
	}
	return $kms;
}

$fecha = date( "Y-m-d" , strtotime ( "-1 day" , strtotime(date('Y-m-d')) ) );
$res = mysql_query($select);
while($row = mysql_fetch_assoc($res))
{
	$km = calcular_kms_dia($row['imei'], $fecha);
	mysql_query("INSERT gps_objects_odo_calculado SET imei='".$row['imei']."',fecha='".$fecha."',odo='".$km."'");
}



?>