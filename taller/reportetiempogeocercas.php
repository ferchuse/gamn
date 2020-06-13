<?php
include('main.php');

$res = mysql_query("SELECT kmsodo FROM usuarios WHERE cve = '1' ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$kmsodo = $row[0];


mysql_select_db('gamn_gps_otra_plataforma');
$select= " SELECT * FROM geocercas WHERE 1 ORDER BY codigo, nombre";
$array_puntos = array();
$res = mysql_query($select);
while($row = mysql_fetch_array($res)){
	$array_puntos[$row['base']][$row['cvebase']] = $row['codigo'];
}

if($_POST['plazausuario'] == 1)
	$rsMotivo=mysql_query("SELECT * FROM rutas WHERE 1 ORDER BY nombre");
else
	$rsMotivo=mysql_query("SELECT * FROM rutas WHERE plaza IN (0,'".$_POST['plazausuario']."') ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_rutas[$Motivo['base']][$Motivo['cvebase']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM estatus WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_estatus[$Motivo['cve']]=$Motivo['nombre'];
}
mysql_select_db('gamn_gps');

function sumar_tiempo($tiempo1, $tiempo2){
	$datos1 = explode(':', $tiempo1);
	$datos2 = explode(':', $tiempo2);

	$segundos1 = ($datos1[0] * 3600) + ($datos1[1] * 60) + $datos1[2];
	$segundos2 = ($datos2[0] * 3600) + ($datos2[1] * 60) + $datos2[2];

	$segundos = $segundos1 + $segundos2;

	$hora = intval($segundos/3600);
	$segundos -= ($hora*3600);
	$minutos = intval($segundos/60);
	$segundos -= ($minutos*60);
	if($hora < 10) $hora='0'.$hora;
	if($minutos < 10) $minutos='0'.$minutos;
	if($segundos < 10) $segundos='0'.$segundos;
	$resultado = $hora.':'.$minutos.':'.$segundos;
	return $resultado;
}

function calcular_tiempo_dia($baseg, $dispositivo, $geocerca, $fecha_ini, $fecha_fin){
	$base='gamn_gps_otra_plataforma';
	mysql_select_db($base);
	$datos = explode(',', $geocerca);
	$geocerca = $datos[1];

	$tiempo = '00:00:00';
	$res = mysql_query("SELECT fecha, hora, tipo FROM eventos WHERE base = '$baseg' AND dispositivo = '$dispositivo' AND geocerca = '$geocerca' AND fecha BETWEEN '$fecha_ini' AND '$fecha_fin' AND tipo IN ('geofenceEnter', 'geofenceExit') ORDER BY fecha, hora");
	$fecha1 = '';
	while($row = mysql_fetch_array($res)){
		if($row['tipo'] == 'geofenceEnter'){
			$fecha1 = $row['fecha'].' '.$row['hora'];
		}
		elseif($row['tipo'] == 'geofenceExit' && $fecha1 != ''){
			$res1 = mysql_query("SELECT TIMEDIFF('".$row['fecha']." ".$row['hora']."', '$fecha1')");
			$row1 = mysql_fetch_array($res1);
			$tiempo = sumar_tiempo($tiempo, $row1[0]);
			$fecha1 = '';
		}
	}
	return $tiempo;
}

if($_POST['ajax']==1){

$base='gamn_gps_otra_plataforma';
mysql_select_db($base);

	
	
	
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8"><th>ID</th>
		  <th>Nombre</th><th>Ruta</th><th>Tiempo</th></tr>';
	$filtro = '';
	if($_POST['ruta']!=''){
		$datosruta = explode(',', $_POST['ruta']);
		$filtro .= " AND a.base = '".$datosruta[0]."' AND a.ruta = '".$datosruta[1]."'";
	}
	if($_POST['plazausuario']==1)
		$select="SELECT a.* FROM dispositivos a  where 1 {$filtro} order by a.nombre";
	else
		$select="SELECT a.* FROM dispositivos a inner join rutas b on a.ruta = b.cvebase AND a.base = b.base where b.plaza IN (0,".$_POST['plazausuario'].") {$filtro} order by a.nombre";
	$res1 = mysql_query($select);
	$primeros_puntos = array();
	$tvueltas=0;
	$tkms=0;
	while($row1 = mysql_fetch_assoc($res1)){
		rowb();
		echo'<td align="center">'.$row1['cvebase'].'</td>';
		echo'<td align="center">'.$row1['nombre'].'</td>';
		echo'<td align="center">'.$array_rutas[$row1['base']][$row1['ruta']].'</td>';
		$tiempo = calcular_tiempo_dia($row1['base'], $row1['cvebase'], $_POST['geocerca'], $_POST['fecha_ini'], $_POST['fecha_fin']);
		echo '<td align="center">'.$tiempo.'</td>';
		echo'</tr>';
		$ttiempo = sumar_tiempo($ttiempo, $$tiempo);
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="3">Total Tiempo</th><th>'.number_format($ttiempo,2).'</th></tr>';
	echo '</table>';
exit();
mysql_select_db("gamn_gps");
}


 top($_SESSION);

 	if ($_POST['cmd']<1) {
		$base='gamn_gps_otra_plataforma';
		//Busqueda
		echo '<table><input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	//	echo''.$select.'';
		echo '<tr>
				<td><a href="#" onclick="if(document.forma.ruta.value==\'\') alert(\'Necesita seleccionar una ruta\'); else buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>

			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr ><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Ruta</td><td><select name="ruta" id="ruta" class="textField"><option value="">Todas</option>';
		foreach ($array_rutas as $base=>$rutas) { 
			foreach ($rutas as $k=>$v) { 
	    		echo '<option value="'.$base.','.$k.'">'.$v.'</option>';
	    	}
		}
		echo '</select></td></tr>';
		echo '<tr><td>Geocercas</td><td><select name="geocerca" id="geocerca" class="textField"><option value="">Todas</option>';
		foreach ($array_puntos as $base=>$puntos) { 
			foreach ($puntos as $k=>$v) { 
	    		echo '<option value="'.$base.','.$k.'">'.$v.'</option>';
	    	}
		}
		echo '</select></td></tr>';
		echo '</table>
		';
		echo '<br><hr/>';

		//Listado
		echo '<div id="Resultados">';

		echo '</div>';



/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">
	function buscarRegistros(){
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else{
			objeto.open("POST","reportetiempogeocercas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&ruta="+document.getElementById("ruta").value+"&geocerca="+document.getElementById("geocerca").value+"&plaza="+document.getElementById("plaza").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
			objeto.onreadystatechange = function(){
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;

	}';
	
	echo '
	</Script>';
	}

bottom();
?>