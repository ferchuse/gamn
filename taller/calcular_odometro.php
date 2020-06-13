<?php
mysql_connect('localhost', 'gps', 'ballena');
mysql_select_db('gamn_gps_skymedia');
function CalcularOdometro($lat1, $lon1, $lat2, $lon2)
{
	$theta = $lon1 - $lon2; 
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
	$dist = acos($dist); 
	$dist = rad2deg($dist); 
	$km = $dist * 60 * 1.1515 * 1.609344;
	
	return sprintf("%01.6f", $km);
}
$res = mysql_query("SELECT imei,date(dt_server) FROM gps_objects_history 
			WHERE latitud != 0 AND longitud != 0 AND velocidad > 3 GROUP BY imei,date(dt_server)");
while($row = mysql_fetch_array($res)){
	$primera = true;
	$res1 = mysql_query("SELECT * FROM gps_objects_history 
				WHERE imei = '".$row['imei']."' and date(dt_server) = '".$row[1]."' AND latitud != 0 AND longitud != 0 AND velocidad > 3 ORDER BY cve") or die(mysql_error());
	$primera = true;
	$kms = 0;
	while($row1 = mysql_fetch_array($res1)){
		if(!$primera)
		{
			$kms += CalcularOdometro($anterior['latitud'], $anterior['longitud'], $row1['latitud'], $row1['longitud']);
		}
		$anterior = $row1;
		$primera = false;
	}
	mysql_query("INSERT gps_objects_km SET imei='{$row['imei']}',fecha='{$row[1]}',km='{$kms}'");
}