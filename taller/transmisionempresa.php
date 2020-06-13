<?php
include ("main.php");
mysql_select_db("gamn_gps_skymedia");
$res = mysql_query("SELECT minutos FROM minutos_atrasados ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$minutos = $row[0];

mysql_select_db("gps");
 top($_SESSION);


		
//$conexion = mysql_connect("192.99.81.148","sadecv","sadecv++");
$conexion = mysql_connect("94.23.33.14","root","inteligps+");
mysql_select_db("trackingps",$conexion);
//Listado
echo '<div id="Resultados">';
echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			
echo'<tr bgcolor="#E9F2F8">';
echo '<th>Usuario</th>
		<th>Dispositivo</th>
		<th>Placa</th>
		<th>IMEI</th>
		<th>Fecha Hora</th>
		<th>Fecha Hora Mexico</th>
		<th>Latitud</th>
		<th>Longitud</th>
		<th>Altitud</th>
		<th>Angulo</th>
		<th>Velocidad</th>
		<th>Odometro (KM)</th></tr>';
$consulta = mysql_query("SELECT * FROM `gs_view_sadecv`");
while ($dato = mysql_fetch_assoc($consulta)) {
	echo "<tr><td> ".$dato['username'].'</td>';
	echo "<td> ".$dato['name'].'</td>';
	echo "<td> ".$dato['plate_number'].'</td>';
	echo "<td> ".$dato['imei'].'</td>';
	echo "<td> ".$dato['dt_server'].'</td>';
	$fechamex = date( "Y-m-d H:i:s" , strtotime ( "-".$minutos." minute" , strtotime($dato['dt_server']) ) );
	echo "<td> ".$fechamex.'</td>';
	echo "<td> ".$dato['lat'].'</td>';
	echo "<td> ".$dato['lng'].'</td>';
	echo "<td> ".$dato['altitude'].'</td>';
	echo "<td> ".$dato['angle'].'</td>';
	echo "<td> ".$dato['speed'].'</td>';
	echo "<td> ".round($dato['odometer'],2).' km</td></tr>';

}


echo "</table>";
echo '</div>';



bottom();

?>