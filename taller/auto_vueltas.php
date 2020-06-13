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

$rsMotivo=mysql_query("SELECT * FROM cat_rutas WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_rutas[$Motivo['cve']]=$Motivo['nombre'];
}

$res = mysql_query("SELECT usuario FROM plazas WHERE cve = '".$_POST['plazausuario']."' ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$usuarioempresa = $row[0];

mysql_select_db('gamn_gps_skymedia');
$select= " SELECT * FROM geocercas_gps WHERE ruta > 0 AND orden > 0 ORDER BY orden";
$array_puntos = array();
$res = mysql_query($select);
while($row = mysql_fetch_array($res)){
	$array_puntos[$row['ruta']][$row['cve']] = $row['nombre'];
}
mysql_select_db('gamn_gps');

function CalcularOdometro($lat1, $lon1, $lat2, $lon2)
{
	$theta = $lon1 - $lon2; 
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
	$dist = acos($dist); 
	$dist = rad2deg($dist); 
	$km = $dist * 60 * 1.1515 * 1.609344;
	
	return sprintf("%01.6f", $km);
}

if($_POST['ajax']==1){

$base='gamn_gps_skymedia';
mysql_select_db($base);
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


	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
			<tr bgcolor="#E9F2F8"><th>Dispositivo</th><th>Empresa</th><th>Placa</th><th>Ruta</th><th>Vueltas</th></tr>';

	$filtro="";

	if($_POST['imei']!='') $filtro .= " AND imei='".$_POST['imei']."'";
	if($_POST['ruta']!='') $filtro .= " AND ruta='".$_POST['ruta']."'";
	if($_POST['usuario']!='') $filtro .= " AND usuario='".$_POST['usuario']."'";
	if($usuarioempresa!='') $filtro.= " AND usuario='".$usuarioempresa."'";
	$res1 = mysql_query("SELECT * FROM gps_objects WHERE 1 $filtro ORDER BY dispositivo");
	while($row1 = mysql_fetch_assoc($res1)){
		$primerpunto = key($array_puntos[$row1['ruta']]);
		$cvepuntos = "";
		foreach($array_puntos[$row1['ruta']] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
		$cvepuntos = substr($cvepuntos, 1);


		$array_resultadopuntos = array();
		$res = mysql_query("SELECT * FROM trackingps 
			WHERE fecham BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND imei = '".$row1['imei']."' AND id_geocerca IN ($cvepuntos)
			ORDER BY fecham, horam") or die(mysql_error());
		/*$primera = true;
		$mindist = 0;
		$horapunto = 0;
		$nvuelta = 0;
		$empieza = false;
		while($row = mysql_fetch_array($res)){
			if($empieza){
				$diferencia = diferenciapunto($horapunto, $row['fecham'].' '.$row['horam']);
				if($diferencia > '00:10:00'){
					if($row['geocerca'] == $array_puntos[$row1['ruta']][0]){
						$nvuelta++;
					}
					$array_resultadopuntos[$nvuelta][] = array(
						'punto' => $row['geocerca'],
						'horapunto' => $row['fecham'].' '.$row['horam']
					);
					$horapunto = $row['fecham'].' '.$row['horam'];
				}
			}
			else{
				if($row['geocerca'] == $array_puntos[$row1['ruta']][0]){
					$array_resultadopuntos[$nvuelta][] = array(
						'punto' => $row['geocerca'],
						'horapunto' => $row['fecham'].' '.$row['horam']
					);
					$horapunto = $row['fecham'].' '.$row['horam'];
					$empieza = true;
				}
			}
		}*/

		$primera = true;
		$mindist = 0;
		$horapunto = $_POST['fecha_ini'].' 00:00:00';
		$nvuelta = 0;
		$empieza = false;
		while($row = mysql_fetch_array($res)){
			//if($empieza){
				$diferencia = diferenciapunto($horapunto, $row['fecham'].' '.$row['horam']);
				if($diferencia > '00:01:00'){
					if($row['id_geocerca'] == $primerpunto){
						$nvuelta++;
					}
					$array_resultadopuntos[$nvuelta][] = array(
						'idpunto' => $row['id_geocerca'],
						'punto' => $row['geocerca'],
						'horapunto' => $row['fecham'].' '.$row['horam']
					);
					$horapunto = $row['fecham'].' '.$row['horam'];
				}
			/*}
			else{
				if($row['geocerca'] == $array_puntos[$row1['ruta']][0]){
					$array_resultadopuntos[$nvuelta][] = array(
						'punto' => $row['geocerca'],
						'horapunto' => $row['fecham'].' '.$row['horam']
					);
					$horapunto = $row['fecham'].' '.$row['horam'];
					$empieza = true;
				}
				else{

				}
			}*/
		}

		$vueltas = 0;

		foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
			$i=0;
			$puntos_vuelta = 0;
			$puntos = count($resultadovueltas);
			//while($i<$puntos){
			//echo '<tr>';
			foreach($array_puntos[$row1['ruta']] as $cve => $punto){
				//$punto = $array_puntos[$row1['ruta']][$j];
				$puntoinicio = $i;
				$encontrado = false;
				while($i<$puntos){
					if($resultadovueltas[$i]['idpunto'] == $cve){
						$puntos_vuelta++;
						$i++;
						$encontrado = true;
						break;
					}
					$i++;
				}
				if(!$encontrado)
					$i=$puntoinicio;
			}

			if($puntos_vuelta > 1) $vueltas++;

			//}
		}
		rowb();
		echo '<td>'.$row1['dispositivo'].'</td>';
		echo '<td>'.$row1['usuario'].'</td>';
		echo '<td>'.$row1['placa'].'</td>';
		echo '<td>'.$array_rutas[$row1['ruta']].'</td>';
		echo '<td><a href="#" onClick="atcr(\'\',\'\',1,\''.$row1['imei'].'\');">'.$vueltas.'</a></td>';
	}
	echo'</table>';

	/*echo '<table>';
	foreach($array_resultadopuntos as $dato){
		rowb();
		echo '<td align="center">'.$array_puntos[$dato['punto']]['clave'].'</td>';
		echo '<td align="center">'.$dato['horapunto'].'</td>';
		echo '</tr>';
		$i++;
	}
	echo '</table>';*/
	 //echo '</div>';
exit();
mysql_select_db("gamn_gps");
}


 top($_SESSION);

if($_POST['cmd']==1){
	$base='gamn_gps_skymedia';
	mysql_select_db($base);
	echo '<table>';
	//	echo''.$select.'';
		echo '<tr>
				<td><a href="#" onclick="atcr(\'\',\'\',0,0);">'.$imgvolver.'&nbsp;&nbsp;Volver&nbsp;&nbsp;</a></td>

			 </tr>';
		echo '</table>';
	$res1 = mysql_query("SELECT * FROM gps_objects WHERE imei = '".$_POST['reg']."'");
	while($row1 = mysql_fetch_assoc($res1)){
		echo '<h1>';
		echo 'IMEI:'. $row1['imei'].'<br>Dispositivo:'.$row1['dispositivo'].'<br>Placa:'.$row1['placa'];
		echo '</h1>';
		$primerpunto = key($array_puntos[$row1['ruta']]);
		$cvepuntos = "";
		foreach($array_puntos[$row1['ruta']] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
		$cvepuntos = substr($cvepuntos, 1);


		$array_resultadopuntos = array();
		$res = mysql_query("SELECT * FROM trackingps 
			WHERE fecham BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND imei = '".$row1['imei']."' AND id_geocerca IN ($cvepuntos)
			ORDER BY fecham, horam") or die(mysql_error());
		/*$primera = true;
		$mindist = 0;
		$horapunto = 0;
		$nvuelta = 0;
		$empieza = false;
		while($row = mysql_fetch_array($res)){
			if($empieza){
				$diferencia = diferenciapunto($horapunto, $row['fecham'].' '.$row['horam']);
				if($diferencia > '00:10:00'){
					if($row['geocerca'] == $array_puntos[$row1['ruta']][0]){
						$nvuelta++;
					}
					$array_resultadopuntos[$nvuelta][] = array(
						'punto' => $row['geocerca'],
						'horapunto' => $row['fecham'].' '.$row['horam']
					);
					$horapunto = $row['fecham'].' '.$row['horam'];
				}
			}
			else{
				if($row['geocerca'] == $array_puntos[$row1['ruta']][0]){
					$array_resultadopuntos[$nvuelta][] = array(
						'punto' => $row['geocerca'],
						'horapunto' => $row['fecham'].' '.$row['horam']
					);
					$horapunto = $row['fecham'].' '.$row['horam'];
					$empieza = true;
				}
			}
		}*/
		$primera = true;
		$mindist = 0;
		$horapunto = $_POST['fecha_ini'].' 00:00:00';
		$nvuelta = 0;
		$empieza = false;
		while($row = mysql_fetch_array($res)){
			//if($empieza){
				$diferencia = diferenciapunto($horapunto, $row['fecham'].' '.$row['horam']);
				if($diferencia > '00:01:00'){
					if($row['id_geocerca'] == $primerpunto){
						$nvuelta++;
					}
					$array_resultadopuntos[$nvuelta][] = array(
						'idpunto' => $row['id_geocerca'],
						'punto' => $row['geocerca'],
						'horapunto' => $row['fecham'].' '.$row['horam']
					);
					$horapunto = $row['fecham'].' '.$row['horam'];
				}
			/*}
			else{
				if($row['geocerca'] == $array_puntos[$row1['ruta']][0]){
					$array_resultadopuntos[$nvuelta][] = array(
						'punto' => $row['geocerca'],
						'horapunto' => $row['fecham'].' '.$row['horam']
					);
					$horapunto = $row['fecham'].' '.$row['horam'];
					$empieza = true;
				}
				else{

				}
			}*/
		}
		/*echo '<pre>';
		print_r($array_resultadopuntos);
		echo '</pre>';*/
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
				
		echo'<tr bgcolor="#E9F2F8">';
		foreach($array_puntos[$row1['ruta']] as $cve => $punto)
			echo'<th>'.$punto.'</th>';
		echo'</tr>';
		foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
			$i=0;
			$puntos = count($resultadovueltas);
			$puntos_encontrados = 0;
			//while($i<$puntos){
				$html = '<tr>';
				foreach($array_puntos[$row1['ruta']] as $cve => $punto){
					//$punto = $array_puntos[$row1['ruta']][$j];
					$puntoinicio = $i;
					$encontrado = false;
					while($i<$puntos){
						if($resultadovueltas[$i]['idpunto'] == $cve){
							$html .= '<td align="center">'.$resultadovueltas[$i]['horapunto'].'</td>';
							$i++;
							$puntos_encontrados++;
							$encontrado = true;
							break;
						}
						$i++;
					}
					if(!$encontrado){
						$i=$puntoinicio;
						$html .= '<td>&nbsp;</td>';
					}
				}
				$html .= '</tr>';
				if($puntos_encontrados > 1) echo $html;
			//}
		}
		echo'</table>';
	}
	mysql_select_db("gamn_gps");
}


 	if ($_POST['cmd']<1) {
		$base='gamn_gps_skymedia';
		if($usuarioempresa!='')
			$select="SELECT * FROM gps_objects where usuario='$usuarioempresa'  order by imei";
		else
			$select="SELECT * FROM gps_objects where 1  order by imei";
		$rsMotivo=mysql_db_query($base,$select);
		while($Motivo=mysql_fetch_array($rsMotivo)){
			$array_imei[$Motivo['imei']]=$Motivo['dispositivo'];
		}
		$select="SELECT * FROM gps_objects group by usuario  order by usuario";
		$rsMotivo=mysql_db_query($base,$select);
		while($Motivo=mysql_fetch_array($rsMotivo)){
			$array_usuario[$Motivo['usuario']]=$Motivo['usuario'];
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
		echo '<tr><td>Dispositivo</td><td><select name="imei" id="imei"><option value="">Todos</option>';
		foreach($array_imei as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
		if($usuarioempresa!='') echo ' style="display:none;"';
		echo '><td>Empresa</td><td><select name="usuario" id="usuario"><option value="">Todas</option>';
		foreach($array_usuario as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Ruta</td><td><select name="ruta" id="ruta"><option value="">Todas</option>';
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
			objeto.open("POST","auto_vueltas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&ruta="+document.getElementById("ruta").value+"&usuario="+document.getElementById("usuario").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&imei="+document.getElementById("imei").value+"&plaza="+document.getElementById("plaza").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
