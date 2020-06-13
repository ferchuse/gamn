<?php
include ("main.php");


mysql_select_db('gamn_gps_skymedia');
$select= " SELECT * FROM geocercas_gps WHERE ruta > 0 AND orden > 0 ORDER BY orden";
$array_puntos = array();
$res = mysql_query($select);
while($row = mysql_fetch_array($res)){
	$array_puntos[$row['ruta']][$row['cve']] = $row['nombre'];
}
mysql_select_db('gamn_gps');


if($_POST['ajax']==1){

	$base='gamn_gps_skymedia';
	mysql_select_db($base);
	$res1 = mysql_query("SELECT * FROM gps_objects WHERE imei = '".$_POST['imei']."'");
	while($row1 = mysql_fetch_assoc($res1)){
		echo '<h1>';
		echo 'IMEI:'. $row1['imei'].'<br>Dispositivo:'.$row1['dispositivo'].'<br>Placa:'.$row1['placa'];
		echo '</h1>';
		$cvepuntos = "";
		foreach($array_puntos[$row1['ruta']] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
		$cvepuntos = substr($cvepuntos, 1);


		$array_resultadopuntos = array();

		$res = mysql_query("SELECT * FROM trackingps 
			WHERE fecham BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND imei = '".$row1['imei']."' AND id_geocerca IN ($cvepuntos)
			ORDER BY fecham, horam") or die(mysql_error());
		$primera = true;
		$mindist = 0;
		$horapunto = $_POST['fecha_ini'].' 00:00:00';
		$punto = '';
		$nvuelta = 0;
		$empieza = false;
		while($row = mysql_fetch_array($res)){
			$diferencia = diferenciapunto($horapunto, $row['fecham'].' '.$row['horam']);
			if($diferencia > '00:01:00' && $punto != $row['geocerca']){
				$array_resultadopuntos[] = $row;
				$horapunto = $row['fecham'].' '.$row['horam'];
			}
			$punto=$row['id_geoceca'];
		}

		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			
		echo'<tr bgcolor="#E9F2F8">';
		echo '<th>Id</th><th>Usuario</th><th>Tipo</th><th>Descripcion</th><th>Geocerca</th><th>Fecha Mexico</th><th>Fecha Dispositivo</th>
		<th>Latitud</th><th>Longitud</th><th>Altitud</th><th>Angulo</th><th>Velocidad</th><th>Estatus</th>
		<th>Fecha Creacion</th>';
		if($_POST['imei']!="") echo '<th>Kms</th>';
		echo'</tr>';

		foreach($array_resultadopuntos as $row){
			rowb();
			echo '<td align="center">'.$row['id'].'</td>';
			echo '<td align="center">'.$row['username'].'</td>';
			echo '<td align="center">'.$row['tipo'].'</td>';
			echo '<td align="center">'.$row['descripcion'].'</td>';
			echo '<td align="center">'.$row['geocerca'].'</td>';
			echo '<td align="center">'.$row['fecham'].' '.$row['horam'].'</td>';
			echo '<td align="center">'.$row['fecha'].' '.$row['hora'].'</td>';
			echo '<td align="center">'.$row['latitud'].'</td>';
			echo '<td align="center">'.$row['longitud'].'</td>';
			echo '<td align="center">'.$row['altitud'].'</td>';
			echo '<td align="center">'.$row['angulo'].'</td>';
			echo '<td align="center">'.$row['velocidad'].'</td>';
			echo '<td align="center">'.$row['estatus'].'</td>';
			echo '<td align="center">'.$row['fecha_creacion'].' '.$row['hora_creacion'].'</td>';
			echo '</tr>';
		}
		echo'<tr bgcolor="#E9F2F8"><td colspan="14" align="left">'.count($array_resultadopuntos).' Registro(s)</td>';
		 echo'</tr></table>';
	}
	exit();
}

 top($_SESSION);

 	if ($_POST['cmd']<1) {
		$base='gamn_gps_skymedia';
		if($usuarioempresa!='')
			$select="SELECT * FROM gps_objects where usuario='$usuarioempresa' order by imei";
		else
			$select="SELECT * FROM gps_objects where 1  order by imei";
		$rsMotivo=mysql_db_query($base,$select);
		while($Motivo=mysql_fetch_array($rsMotivo)){
			$array_imei[$Motivo['imei']]=$Motivo['dispositivo'];
		}
		$res = mysql_db_query($base,"SELECT minutos FROM minutos_atrasados ORDER BY cve DESC LIMIT 1");
		$row = mysql_fetch_array($res);
		$minutos = $row[0];
		//Busqueda
		echo '<table><input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	//	echo''.$select.'';
		echo '<tr>
				<td><a href="#" onclick="if(document.forma.imei.value==\'\') alert(\'Necesita seleccionar el imei\'); else buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>

			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr ><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Eco</td><td><select name="imei" id="imei"><option value="">Seleccione</option>';
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
			objeto.open("POST","auto_tracking2.php",true);
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
 ?>
<?
bottom();
?>