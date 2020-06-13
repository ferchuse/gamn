<?php
include ("main.php");

$rsMotivo=mysql_query("SELECT * FROM usuarios ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_personal[$Motivo['cve']]=$Motivo['nombre'];
}

$rsMotivo=mysql_query("SELECT * FROM usuarios where 1 ORDER by usuario asc");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_usuarios_movil[$Motivo['idpersonal']]=$Motivo['login'];
	$array_usuarios_movi[$Motivo['id']]=$Motivo['idpersonal'];
}

$base='gamn_gps_skymedia';
mysql_select_db($base);

$select= " SELECT * FROM marcadores ORDER BY orden";
$array_puntos = array();
$res = mysql_query($select);
while($row = mysql_fetch_array($res)){
	$array_puntos[$row['ruta']][$row['cve']] = array('clave' => $row['clave'], 'des' => $row['des']);
}

function CalcularOdometro($lat1, $lon1, $lat2, $lon2)
{
	$theta = $lon1 - $lon2; 
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
	$dist = acos($dist); 
	$dist = rad2deg($dist); 
	$km = $dist * 60 * 1.1515 * 1.609344;
	
	return sprintf("%01.6f", $km);
}

$rsMotivo=mysql_query("SELECT * FROM rutas_gps WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_rutas[$Motivo['cve']]=$Motivo['nombre'];
}

if($_POST['ajax']==1){


	//echo '<div style="height: 350px; overflow: auto;">';
	/*if($_POST['imei']!=""){
		$res = mysql_query("SELECT * FROM gps_objects_history 
			WHERE imei = '".$_POST['imei']."' and fecha >= '".$_POST['fecha_ini']."' AND fecha <= '".$_POST['fecha_fin']."' ORDER BY cve");
		$primera = true;
		$kms = 0;
		while($row = mysql_fetch_array($res)){
			if(!$primera)
			{
				if($anterior['latitud']!=0 && $anterior['longitud']!=0 && $anterior['velocidad']>3 && $row['latitud']!=0 && $row['longitud']!=0 && $row['velocidad']>3){
					$kms += CalcularOdometro($anterior['latitud'], $anterior['longitud'], $row['latitud'], $row['longitud']);
				}
			}
			$anterior = $row;
			$primera = false;
		}
	
		echo '<h3>Kms Totales: '.$kms.'</h3>';



	}*/


	



	echo '<h1>';
	$res1 = mysql_query("SELECT * FROM gps_objects WHERE imei = '".$_POST['imei']."'");
	while($row1 = mysql_fetch_assoc($res1)){

		$cvepuntos = "";
		foreach($array_puntos[$row1['ruta']] as $cve => $punto) $cvepuntos .= ",".$cve;
		$cvepuntos = substr($cvepuntos, 1);


		$array_resultadopuntos = array();
		$res = mysql_query("SELECT * FROM gps_objects_history 
			WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND imei = '".$row1['imei']."' AND marcador IN ($cvepuntos)
			AND marcador > 0 AND distancia <= 4
			ORDER BY fecha, hora") or die(mysql_error());
		$punto = 0;
		$mindist = 0;
		$horapunto = 0;
		while($row = mysql_fetch_array($res)){
			if($punto == 0){
				$punto = $row['punto'];
				$mindist = $row['distancia'];
				$horapunto = $row['fecha'].' '.$row['hora'];
			}
			if($punto != $row['punto']){
				$array_resultadopuntos[] = array(
					'punto' => $punto,
					'horapunto' => $horapunto
				);
				$punto = $row['punto'];
				$mindist = $row['distancia'];
				$horapunto = $row['fecha'].' '.$row['hora'];
			}
			if($mindist > $row['distancia']){
				$punto = $row['punto'];
				$mindist = $row['distancia'];
				$horapunto = $row['fecha'].' '.$row['hora'];
			}
		}
		$array_resultadopuntos[] = array(
			'punto' => $punto,
			'horapunto' => $horapunto
		);

		echo 'IMEI:'. $row1['imei'].'<br>Dispositivo:'.$row1['dispositivo'].'<br>Placa:'.$row1['placa'];
		echo '</h1>
				<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
				
		echo'<tr bgcolor="#E9F2F8">';
		foreach($array_puntos[$row1['ruta']] as $cve => $datos)
			echo'<th>'.$datos['clave'].'<br>'.$datos['des'].'</th>';
		echo'</tr>';
		$i=0;
		$puntos = count($array_resultadopuntos);
		while($i<$puntos){
			echo '<tr>';
			if($i==0){
				foreach($array_puntos[$row1['ruta']] as $cve => $datos){
					if($i==0){
						if($array_resultadopuntos[$i]['punto'] == $cve){
							echo '<td align="center">'.$array_resultadopuntos[$i]['horapunto'].'</td>';
							$i++;
						}
						else{
							echo '<td>&nbsp;</td>';
						}
					}
					else{
						while($i<$puntos){
							if($array_resultadopuntos[$i]['punto'] == $cve){
								echo '<td align="center">'.$array_resultadopuntos[$i]['horapunto'].'</td>';
								$i++;
								break;
							}
							$i++;
						}
					}
				}
			}
			else{
				foreach($array_puntos[$row1['ruta']] as $cve => $datos){
					while($i<$puntos){
						if($array_resultadopuntos[$i]['punto'] == $cve){
							echo '<td align="center">'.$array_resultadopuntos[$i]['horapunto'].'</td>';
							$i++;
							break;
						}
						$i++;
					}
				}
			}
			echo '</tr>';

		}

		echo'</table>';
		echo '<table>';
		foreach($array_resultadopuntos as $dato){
			rowb();
			echo '<td align="center">'.$array_puntos[$row1['ruta']][$dato['punto']]['clave'].'</td>';
			echo '<td align="center">'.$dato['horapunto'].'</td>';
			echo '</tr>';
			$i++;
		}
		echo '</table>';
	}
	
	 //echo '</div>';
exit();
mysql_select_db("gamn_gps");
}


 top($_SESSION);

 	if ($_POST['cmd']<1) {
		$base='gamn_gps_skymedia';
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
		echo '<tr><td>Ruta</td><td><select name="ruta" id="ruta"><option value="">Seleccione</option>';
		foreach($array_rutas as $k=>$v){
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
			objeto.open("POST","auto_history_marcadores.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&ruta="+document.getElementById("ruta").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&imei="+document.getElementById("imei").value+"&plaza="+document.getElementById("plaza").value);
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
