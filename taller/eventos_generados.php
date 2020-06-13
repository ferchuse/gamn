<?php
include ("main.php");
mysql_select_db("gamn_gps_skymedia");
$res = mysql_query("SELECT minutos FROM minutos_atrasados ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$minutos = $row[0];

if($_POST['ajax']==1){


	
	echo '
			<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			
	echo'<tr bgcolor="#E9F2F8">';
	echo '<th>ID</th>
		<th>Usuario</th>
		<th>Tipo</th>
		<th>Descripción</th>
		<th>IMEI</th>
		<th>Fecha Hora</th>
		<th>Fecha Hora Mexico</th>
		<th>Latitud</th>
		<th>Longitud</th>
		<th>Altitud</th>
		<th>Angulo</th>
		<th>Velocidad</th>
		<th>Status</th></tr>';
	$conexion = mysql_connect("192.99.81.148","sadecv","sadecv++") or die ( mysql_error());
	mysql_select_db("trackingps",$conexion) or die(mysql_error());

	$filtro = "";
	if($_POST['fecha_ini']!='') $filtro .= " and DATE(s.date)>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!='') $filtro .= " and DATE(s.date)<='".$_POST['fecha_fin']."'";
	if($_POST['imei']!='') $filtro .= " and s.imei='".$_POST['imei']."'";
			   
	$consulta = mysql_query("SELECT s.id, u.username, s.type, s.description, s.imei, s.date, s.lat, s.lng, s.altitud, s.orientation, s.speed, s.status from gs_sadecv s, gs_objects g, gs_users u 
		where s.imei=g.imei and g.manager_id=59 and s.user_id=u.id $filtro");
	while ($dato = mysql_fetch_assoc($consulta)) {
		echo "<tr><td> ".$dato['id'].'</td>';
		echo "<td> ".$dato['username'].'</td>';
		echo "<td> ".$dato['type'].'</td>';
		echo "<td> ".$dato['description'].'</td>';
		echo "<td> ".$dato['imei'].'</td>';
		echo "<td> ".$dato['date'].'</td>';
		$fechamex = date( "Y-m-d H:i:s" , strtotime ( "-".$minutos." minute" , strtotime($dato['date']) ) );
		echo "<td> ".$fechamex.'</td>';
		echo "<td> ".$dato['lat'].'</td>';
		echo "<td> ".$dato['lng'].'</td>';
		echo "<td> ".$dato['altitud'].'</td>';
		echo "<td> ".$dato['orientation'].'</td>';
		echo "<td> ".$dato['speed'].'</td>';
		echo "<td> ".$dato['status'].'</td></tr>';

	}
	echo '</table>';
	exit();
}

mysql_select_db("gamn_gps");
 top($_SESSION);


 	if ($_POST['cmd']<1) {
		$base='gamn_gps_skymedia';
		$select="SELECT * FROM gps_objects where 1  order by imei";
		$rsMotivo=mysql_db_query($base,$select);
		while($Motivo=mysql_fetch_array($rsMotivo)){
			$array_imei[$Motivo['imei']]=$Motivo['imei'].', Placa: '.$Motivo['placa'].', Nombre: '.$Motivo['dispositivo'];
		}
		$res = mysql_db_query($base,"SELECT minutos FROM minutos_atrasados ORDER BY cve DESC LIMIT 1");
		$row = mysql_fetch_array($res);
		$minutos = $row[0];
		//Busqueda
		echo '<table><input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	//	echo''.$select.'';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>

			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr ><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Imei</td><td><select name="imei" id="imei"><option value="">Seleccione</option>';
		foreach($array_imei as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
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
			objeto.open("POST","eventos_generados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&imei="+document.getElementById("imei").value+"&plaza="+document.getElementById("plaza").value);
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
