<?php
include ("main.php");
$res = mysql_query("SELECT usuario FROM plazas WHERE cve = '".$_POST['plazausuario']."' ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$usuarioempresa = $row[0];


mysql_select_db('gamn_gps_otra_plataforma');
$select= " SELECT * FROM geocercas WHERE orden > 0 ORDER BY orden";
$array_puntos = array();
$res = mysql_query($select);
while($row = mysql_fetch_array($res)){
	$array_puntos[$row['base']][$row['ruta']][$row['direccion']][$row['cvebase']] = $row['codigo'];
}

$rsMotivo=mysql_query("SELECT * FROM rutas WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_rutas[$Motivo['base']][$Motivo['cvebase']]=$Motivo['nombre'];
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

$base='gamn_gps_otra_plataforma';
mysql_select_db($base);



	
	



	//$res1 = mysql_query("SELECT * FROM dispositivos WHERE cve = '".$_POST['dispositivo']."'");
	$filtro = '';
	if($_POST['ruta']!=''){
		$datosruta = explode(',', $_POST['ruta']);
		$filtro .= " AND a.base = '".$datosruta[0]."' AND a.ruta = '".$datosruta[1]."'";
	}
	if($_POST['dispositivo'] != '') $filtro .= " AND a.cve = '".$_POST['dispositivo']."'";
	if($_POST['plazausuario']==1)
		$select="SELECT a.* FROM dispositivos a  where a.ruta>0 {$filtro} order by a.nombre";
	else
		$select="SELECT a.* FROM dispositivos a inner join rutas b on a.ruta = b.cvebase AND a.base = b.base where b.plaza IN (0,".$_POST['plazausuario'].") {$filtro} order by a.nombre";
	$res1 = mysql_query($select) or die(mysql_error());
	$primeros_puntos = array();
	while($row1 = mysql_fetch_assoc($res1)){

		
		echo '<h1>';
		echo 'Dispositivo:'.$row1['nombre'].'</div>';
		echo '</h1>';
		echo '<table width="100%" cellpadding="4" border="1" cellspacing="1" class="" id="tabla1">';
		
		//$primerpunto = key($array_puntos[$row1['base']][$row1['ruta']]);
		if($primeros_puntos[$row1['base']][$row1['ruta']][0] == ''){
			$primeros_puntos[$row1['base']][$row1['ruta']][0] = key($array_puntos[$row1['base']][$row1['ruta']][0]);
		}
		if($primeros_puntos[$row1['base']][$row1['ruta']][1] == ''){
			$primeros_puntos[$row1['base']][$row1['ruta']][1] = key($array_puntos[$row1['base']][$row1['ruta']][1]);
		}
		$primerpunto = $primeros_puntos[$row1['base']][$row1['ruta']][0];
		$primerpunto2 = $primeros_puntos[$row1['base']][$row1['ruta']][1];
		$cvepuntos = "";

		echo'<tr bgcolor="#E9F2F8">';
		foreach($array_puntos[$row1['base']][$row1['ruta']][0] as $cve => $punto)
			echo'<th>'.$cve.' '.$punto.'</th>';
		foreach($array_puntos[$row1['base']][$row1['ruta']][1] as $cve => $punto)
			echo'<th>'.$cve.' '.$punto.'</th>';
		echo'</tr>';

		foreach($array_puntos[$row1['base']][$row1['ruta']][0] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
		$cvepuntos = substr($cvepuntos, 1);

		$array_resultadopuntos = array();
		$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM eventos 
			WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND base='".$row1['base']."' AND dispositivo = '".$row1['cvebase']."' AND geocerca IN ($cvepuntos) AND tipo = 'geofenceEnter'
			ORDER BY fecha,hora") or die(mysql_error());
		$primera = true;
		$mindist = 0;
		$horapunto = $_POST['fecha_ini'].' 00:00:00';
		$nvuelta = 0;
		$empieza = false;
		while($row = mysql_fetch_array($res)){
			//if($empieza){
				$diferencia = diferenciapunto($horapunto, $row['servertime']);
				//print_r($row);
				//echo '<br>';
				//echo $diferencia;
				//echo '<br>';
				if($diferencia > '00:00:01'){
					if($row['geofenceid'] == $primerpunto){
						$nvuelta++;
					}
					$array_resultadopuntos[$nvuelta][] = array(
						'idpunto' => $row['geofenceid'],
						'punto' => $row['geocerca'],
						'horapunto' => $row['servertime']
					);
					$horapunto = $row['servertime'];
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
		
		foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
			$i=0;
			$puntos = count($resultadovueltas);
			$puntos_encontrados = 0;
			$hora='';
			//while($i<$puntos){
				$html = '<tr>';
				foreach($array_puntos[$row1['base']][$row1['ruta']][0] as $cve => $punto){
					//$punto = $array_puntos[$row1['ruta']][$j];
					$puntoinicio = $i;
					$encontrado = false;
					while($i<$puntos){
						if($resultadovueltas[$i]['idpunto'] == $cve){
							$html .= '<td align="center">'.substr($resultadovueltas[$i]['horapunto'],-8).'</td>';
							$hora = $resultadovueltas[$i]['horapunto'];
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

				$cvepuntos = "";
				foreach($array_puntos[$row1['base']][$row1['ruta']][1] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
				$cvepuntos = substr($cvepuntos, 1);
				$hora2 = $array_resultadopuntos[$vuelta+1][0]['horapunto'];
				if($hora2 == '') $hora2 = $_POST['fecha_fin'].' '.'23:59:59';

				$array_resultadopuntos2 = array();
				$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM eventos 
					WHERE CONCAT(fecha,' ',hora) BETWEEN '".$hora."' AND '".$hora2."' AND base='".$row1['base']."' AND dispositivo = '".$row1['cvebase']."' AND geocerca IN ($cvepuntos) AND tipo = 'geofenceEnter'
					ORDER BY fecha,hora") or die(mysql_error());
				$primera = true;
				$mindist = 0;
				$horapunto = $hora;
				$nvuelta = 0;
				$empieza = false;
				while($row = mysql_fetch_array($res)){
						$diferencia = diferenciapunto($horapunto, $row['servertime']);
						if($diferencia > '00:00:01'){
							if($row['geofenceid'] == $primerpunto){
								$nvuelta++;
							}
							$array_resultadopuntos2[$nvuelta][] = array(
								'idpunto' => $row['geofenceid'],
								'punto' => $row['geocerca'],
								'horapunto' => $row['servertime']
							);
							$horapunto = $row['servertime'];
						}
				}

				$j=0;
				$resultadovueltas2 = $array_resultadopuntos2[1];
				$puntos2 = count($resultadovueltas2);
				if($puntos2 == 0){
					$resultadovueltas2 = $array_resultadopuntos2[0];
					$puntos2 = count($resultadovueltas2);
				}
				foreach($array_puntos[$row1['base']][$row1['ruta']][1] as $cve => $punto){
					//$punto = $array_puntos[$row1['ruta']][$j];
					$puntoinicio = $j;
					$encontrado = false;
					while($j<$puntos2){
						if($resultadovueltas2[$j]['idpunto'] == $cve){
							$html .= '<td align="center">'.substr($resultadovueltas2[$j]['horapunto'],-8).'</td>';
							$j++;
							$puntos_encontrados++;
							$encontrado = true;
							break;
						}
						$j++;
					}
					if(!$encontrado){
						$j=$puntoinicio;
						$html .= '<td>&nbsp;</td>';
					}
				}



				$html .= '</tr>';
				if($puntos_encontrados > 1) echo $html;
			//}
		}
		echo'</table><br>';
		/*echo '<table>';
		foreach($array_resultadopuntos as $dato){
			rowb();
			echo '<td align="center">'.$dato['punto'].'</td>';
			echo '<td align="center">'.$dato['horapunto'].'</td>';
			echo '</tr>';
			$i++;
		}
		echo '</table>';*/
	}
	
	 //echo '</div>';
exit();
mysql_select_db("gps");
}


 top($_SESSION);

 	if ($_POST['cmd']<1) {
		$base='gps_otra_plataforma';
		if($_POST['plazausuario']==1)
			$select="SELECT a.* FROM dispositivos a  where ruta>0 order by a.nombre";
		else
			$select="SELECT a.* FROM dispositivos a inner join rutas b on a.ruta = b.cvebase AND a.base = b.base where b.plaza IN (0,".$_POST['plazausuario'].")  order by a.nombre";
		
		$rsMotivo=mysql_db_query($base,$select);
		while($Motivo=mysql_fetch_array($rsMotivo)){
			$array_imei[$Motivo['cve']]=$Motivo['nombre'].'('.$array_base[$Motivo['base']].')';
		}
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
		echo '<tr><td>Ruta</td><td><select name="ruta" id="ruta" class="textField"><option value="">Todas</option>';
		foreach ($array_rutas as $base=>$rutas) { 
			foreach ($rutas as $k=>$v) { 
	    		echo '<option value="'.$base.','.$k.'">'.$v.'</option>';
	    	}
		}
		echo '</select></td></tr>';
		echo '<tr><td>Eco</td><td><select name="dispositivo" id="dispositivo"><option value="">Seleccione</option>';
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
			objeto.open("POST","reporte_recorrido2.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&dispositivo="+document.getElementById("dispositivo").value+"&ruta="+document.getElementById("ruta").value+"&plaza="+document.getElementById("plaza").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
