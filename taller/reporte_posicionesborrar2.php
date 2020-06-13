<?php
require_once('subs/cnx_db.php');




function CalcularOdometro($lat1, $lon1, $lat2, $lon2)
{
	$theta = $lon1 - $lon2; 
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
	$dist = acos($dist); 
	$dist = rad2deg($dist); 
	$km = $dist * 60 * 1.1515 * 1.609344;
	
	return sprintf("%01.6f", $km);
}




	$base='gamn_gps_otra_plataforma';
	mysql_select_db($base);
	
$res = mysql_query("SELECT * FROM dispositivos WHERE base = 1");
while($row = mysql_fetch_array($res)){

	$filtro = "";

	$fecha = date( "Y-m-d H:i:s" , strtotime ( "-70 minute" , strtotime(date('Y-m-d H:i:s')) ) );
	$consulta = mysql_query("select * from posiciones  where base='".$row['base']."' AND dispositivo= '".$row['cvebase']."' and concat(fecha, ' ', hora)<='2018-02-01 00:00:00' and en_marcha=1 order by fecha,hora") or die (mysql_error());
	$primera=true;
	$fecha_anterior = '';
	while ($dato = mysql_fetch_assoc($consulta)) {
		if($primera){
			$tiempo = '&nbsp;';
			$odometro=0;
			$primera = false;
		}
		else{
			$res1 = mysql_query("SELECT TIMEDIFF('".$dato['fecha']." ".$dato['hora']."','$anterior')");
			$row1 = mysql_fetch_array($res1);
			$tiempo = $row1[0];
			$odometro = CalcularOdometro($lat1, $lon1, $dato['latitud'], $dato['longitud']);
		}
		if($odometro > 1){
			mysql_query("DELETE FROM posiciones WHERE cve='".$dato['cve']."'");
		}
		else{
			$lat1 = $dato['latitud'];
			$lon1 = $dato['longitud'];
			$anterior = $dato['fecha']." ".$dato['hora'];
		}
		
		
		$i++;

	}
	
	
}


?>