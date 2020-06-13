<?php

$conexion = mysql_connect("212.227.200.116","hgaribay","bAllenA6##6");


mysql_select_db("gps3",$conexion);

if($_GET['tabla']!=''){
	mysql_query("TRUNCATE TABLE ".$_GET['tabla']);
}





echo '<h3><a href="eliminartablas.php?tabla=positions">Eliminar posiciones</a></h3>';
echo '<h3><a href="eliminartablas.php?tabla=events">Eliminar eventos</a></h3>';
echo '<h3><a href="eliminartablas.php?tabla=devices">Eliminar dispositivos</a></h3>';

?>