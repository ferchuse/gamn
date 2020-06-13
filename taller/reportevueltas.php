<?php
include('main.php');

$res = mysql_query("SELECT kmsodo FROM usuarios WHERE cve = '1' ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$kmsodo = $row[0];


mysql_select_db('gamn_gps_otra_plataforma');
$select= " SELECT * FROM geocercas WHERE orden > 0 ORDER BY orden";
$array_puntos = array();
$res = mysql_query($select);
while($row = mysql_fetch_array($res)){
	$array_puntos[$row['base']][$row['ruta']][$row['direccion']][$row['cvebase']] = $row['codigo'];
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


if($_POST['ajax']==1){

$base='gamn_gps_otra_plataforma';
mysql_select_db($base);

	function CalcularOdometro_viejito($lat1, $lon1, $lat2, $lon2)
	{
		$theta = $lon1 - $lon2; 
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$km = $dist * 60 * 1.1515 * 1.609344;
		
		return sprintf("%01.6f", $km);
	}

	function CalcularOdometro2($lat1, $lat2, $lon1, $lon2){
	  $PI = 3.141592653589793;
	  $theta = $lon1 - $lon2; 
	  $dist = sin($PI*($lat1)) * sin($PI*($lat2)) +  cos($PI*($lat1)) * cos($PI*($lat2)) * cos($PI*($theta)); 
	  $dist = acos($dist); 
	  $recorrido = round(($dist * 60),2);
	  return $recorrido/1000;
	}

	function calcular_kms_dia($base, $dispositivo, $fecha_ini, $fecha_fin)
	{
		global $kmsodo;
		$res = mysql_query("SELECT * FROM posiciones WHERE base = '$base' AND dispositivo = '".$dispositivo."' AND fecha BETWEEN '$fecha_ini' AND '$fecha_fin' ORDER BY fecha,hora");
		$primera = true;
		$kms = 0;
		while($row = mysql_fetch_assoc($res))
		{
			if(!$primera){
				$km=0;
				if($anterior['latitud']!=0 && $anterior['longitud']!=0 && $row['latitud']!=0 && $row['longitud']!=0 ){
					$km = CalcularOdometro2($anterior['latitud'], $anterior['longitud'], $row['latitud'], $row['longitud']);
				}
				if($km<$kmsodo){
					$kms+=round($km,2);
					$anterior = $row;
				}
			}
			else{
				$anterior = $row;
				$primera = false;
			}
		}
		return $kms;
	}


	if($_POST['dispositivo'] != ''){
		mysql_select_db('gamn_gps_otra_plataforma');
		$select="SELECT a.* FROM dispositivos a  where a.cve='".$_POST['dispositivo']."'";
		$res1 = mysql_query($select);
		$row1 = mysql_fetch_array($res1);
		echo '<h3>'.$row1['nombre'].' Ruta: '.$array_rutas[$row1['base']][$row1['ruta']].'</h3>';
		$array_vueltas_recaudacion = array();
		mysql_select_db('gamn');
		$res = mysql_query("SELECT a.fecha_cuenta, SUM(cuenta-condonacion) as vueltas, GROUP_CONCAT(tarjeta), SUM(condonacion), GROUP_CONCAT(c.obs) FROM parque_abono a INNER JOIN parque b ON b.cve = a.unidad AND b.imei = '".$row1['uniqueid']."' 
			LEFT JOIN tarjeta_condonacion c ON a.tarjeta = c.cvetar AND c.estatus!='C'
			WHERE a.fecha_cuenta BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C' GROUP BY a.fecha");
		while($row = mysql_fetch_array($res))
			$array_vueltas_recaudacion[$row['fecha_cuenta']] = array($row[1], $row[2], $row[3], $row[4]);
		
		mysql_select_db('gamn_gps_otra_plataforma');
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
		      <tr bgcolor="#E9F2F8"><th>Fecha</th><th>Vueltas</th><th>Importe Recaudado</th><th>Tarjetas</th><th>Importe Condonado</th><th>Observaciones Condonacion</th><th>Kms</th></tr>';
		
		$primeros_puntos = array();
		$tvueltas=0;
		$tvueltasr=0;
		$tcondonacion=0;
		$tkms=0;
		$fecha = $_POST['fecha_ini'];
		while($fecha<=$_POST['fecha_fin']){
			rowb();
			echo'<td align="center">'.$fecha.'</td>';
			
			
			if($primeros_puntos[$row1['base']][$row1['ruta']][0] == ''){
				$primeros_puntos[$row1['base']][$row1['ruta']][0] = key($array_puntos[$row1['base']][$row1['ruta']][0]);
			}
			if($primeros_puntos[$row1['base']][$row1['ruta']][1] == ''){
				$primeros_puntos[$row1['base']][$row1['ruta']][1] = key($array_puntos[$row1['base']][$row1['ruta']][1]);
			}
			$primerpunto = $primeros_puntos[$row1['base']][$row1['ruta']][0];
			$primerpunto2 = $primeros_puntos[$row1['base']][$row1['ruta']][1];
			$cvepuntos = "";
			foreach($array_puntos[$row1['base']][$row1['ruta']][0] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
			$cvepuntos = substr($cvepuntos, 1);

			$array_resultadopuntos = array();
			$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM eventos 
				WHERE fecha = '".$fecha."' AND base='".$row1['base']."' AND dispositivo = '".$row1['cvebase']."' AND geocerca IN ($cvepuntos) AND tipo = 'geofenceEnter'
				ORDER BY fecha,hora") or die(mysql_error());
			$primera = true;
			$mindist = 0;
			$horapunto = $_POST['fecha_ini'].' 00:00:00';
			$nvuelta = 0;
			$empieza = false;
			while($row = mysql_fetch_array($res)){
				$diferencia = diferenciapunto($horapunto, $row['servertime']);
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
			}

			$vueltas=0;

			foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
				$i=0;
				$puntos = count($resultadovueltas);
				$puntos_encontrados = 0;
				$hora='';
				$esvuelta = 0;
				foreach($array_puntos[$row1['base']][$row1['ruta']][0] as $cve => $punto){
					$puntoinicio = $i;
					$encontrado = false;
					while($i<$puntos){
						if($resultadovueltas[$i]['idpunto'] == $cve){
							if($cve==18) $esvuelta=1;
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
					}
				}

				$cvepuntos = "";
				foreach($array_puntos[$row1['base']][$row1['ruta']][1] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
				$cvepuntos = substr($cvepuntos, 1);
				$hora2 = $array_resultadopuntos[$vuelta+1][0]['horapunto'];
				if($hora2 == '') $hora2 = $fecha.' '.'23:59:59';

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
				$puntoregresoencontrado = 0;
				foreach($array_puntos[$row1['base']][$row1['ruta']][1] as $cve => $punto){
					$puntoinicio = $j;
					$encontrado = false;
					while($j<$puntos2){
						if($resultadovueltas2[$j]['idpunto'] == $cve){
							if($cve==19) $esvuelta=1;
							$j++;
							$puntos_encontrados++;
							$puntoregresoencontrado=1;
							$encontrado = true;
							break;
						}
						$j++;
					}
					if(!$encontrado){
						$j=$puntoinicio;
					}
				}



				if($esvuelta >= 1) $vueltas++;;
			}



			echo '<td align="center">'.$vueltas.'</td>';
			$kms = calcular_kms_dia($row1['base'], $row1['cvebase'], $fecha, $fecha);
			echo '<td align="right">'.number_format($array_vueltas_recaudacion[$fecha][0],2).'</td>';
			echo '<td align="center">'.$array_vueltas_recaudacion[$fecha][1].'</td>';
			echo '<td align="right">'.number_format($array_vueltas_recaudacion[$fecha][2],2).'</td>';
			echo '<td align="left">'.$array_vueltas_recaudacion[$fecha][3].'</td>';
			echo '<td align="center">'.number_format($kms,2).'</td>';
			echo'</tr>';
			$tvueltas += $vueltas;
			$tvueltasr += $array_vueltas_recaudacion[$fecha][0];
			$tcondonacion += $array_vueltas_recaudacion[$fecha][2];
			$tkms += $kms;
			$fecha = date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
		}
		echo '<tr bgcolor="#E9F2F8"><th>Total Vueltas</th><th>'.number_format($tvueltas,0).'</th><th align="right">'.number_format($tvueltasr,2).'</th><th>&nbsp;</th><th align="right">'.number_format($tcondonacion,2).'</th><th>&nbsp;</th><th>'.number_format($tkms,2).'</th></tr>';
		echo '</table>';
	}
	else{

		$array_vueltas_recaudacion = array();
		mysql_select_db('gamn');
		$res = mysql_query("SELECT b.imei, SUM(cuenta-condonacion) as vueltas, GROUP_CONCAT(tarjeta), SUM(condonacion) FROM parque_abono a INNER JOIN parque b ON b.cve = a.unidad AND b.imei != '' WHERE a.fecha_cuenta BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C' GROUP BY a.unidad");
		while($row = mysql_fetch_array($res))
			$array_vueltas_recaudacion[$row['imei']] = array($row[1], $row[2], $row[3]);
		
		mysql_select_db('gamn_gps_otra_plataforma');
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
		      <tr bgcolor="#E9F2F8"><th>ID</th>
			  <th>Nombre</th><th>Ruta</th><th>Vueltas</th><th>Importe Recaudado</th><th>Tarjetas</th><th>Importe Condonado</th><th>Kms</th></tr>';
		$filtro = '';
		if($_POST['ruta']!=''){
			$datosruta = explode(',', $_POST['ruta']);
			$filtro .= " AND a.base = '".$datosruta[0]."' AND a.ruta = '".$datosruta[1]."'";
		}
		if($_POST['plazausuario']==1)
			$select="SELECT a.* FROM dispositivos a  where a.ruta>0 {$filtro} order by a.nombre";
		else
			$select="SELECT a.* FROM dispositivos a inner join rutas b on a.ruta = b.cvebase AND a.base = b.base where b.plaza IN (0,".$_POST['plazausuario'].") {$filtro} order by a.nombre";
		$res1 = mysql_query($select);
		$primeros_puntos = array();
		$tvueltas=0;
		$tvueltasr=0;
		$tcondonacion=0;
		$tkms=0;
		while($row1 = mysql_fetch_assoc($res1)){
			rowb();
			echo'<td align="center">'.$row1['cvebase'].'</td>';
			echo'<td align="center">'.$row1['nombre'].'</td>';
			//echo'<td align="center">'.$row1['uniqueid'].'</td>';
			echo'<td align="center">'.$array_rutas[$row1['base']][$row1['ruta']].'</td>';
			
			if($primeros_puntos[$row1['base']][$row1['ruta']][0] == ''){
				$primeros_puntos[$row1['base']][$row1['ruta']][0] = key($array_puntos[$row1['base']][$row1['ruta']][0]);
			}
			if($primeros_puntos[$row1['base']][$row1['ruta']][1] == ''){
				$primeros_puntos[$row1['base']][$row1['ruta']][1] = key($array_puntos[$row1['base']][$row1['ruta']][1]);
			}
			$primerpunto = $primeros_puntos[$row1['base']][$row1['ruta']][0];
			$primerpunto2 = $primeros_puntos[$row1['base']][$row1['ruta']][1];
			$cvepuntos = "";
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
				$diferencia = diferenciapunto($horapunto, $row['servertime']);
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
			}

			$vueltas=0;

			foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
				$i=0;
				$puntos = count($resultadovueltas);
				$puntos_encontrados = 0;
				$hora='';
				$esvuelta = 0;
				foreach($array_puntos[$row1['base']][$row1['ruta']][0] as $cve => $punto){
					$puntoinicio = $i;
					$encontrado = false;
					while($i<$puntos){
						if($resultadovueltas[$i]['idpunto'] == $cve){
							if($cve==18) $esvuelta=1;
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
				$puntoregresoencontrado = 0;
				foreach($array_puntos[$row1['base']][$row1['ruta']][1] as $cve => $punto){
					$puntoinicio = $j;
					$encontrado = false;
					while($j<$puntos2){
						if($resultadovueltas2[$j]['idpunto'] == $cve){
							if($cve==19) $esvuelta=1;
							$j++;
							$puntos_encontrados++;
							$puntoregresoencontrado=1;
							$encontrado = true;
							break;
						}
						$j++;
					}
					if(!$encontrado){
						$j=$puntoinicio;
					}
				}



				if($esvuelta >= 1) $vueltas++;;
			}



			echo '<td align="center">'.$vueltas.'</td>';
			$kms = calcular_kms_dia($row1['base'], $row1['cvebase'], $_POST['fecha_ini'], $_POST['fecha_fin']);
			echo '<td align="right">'.number_format($array_vueltas_recaudacion[$row1['uniqueid']][0],2).'</td>';
			echo '<td align="center">'.$array_vueltas_recaudacion[$row1['uniqueid']][1].'</td>';
			echo '<td align="right">'.number_format($array_vueltas_recaudacion[$row1['uniqueid']][2],2).'</td>';
			echo '<td align="center">'.number_format($kms,2).'</td>';
			echo'</tr>';
			$tvueltas += $vueltas;
			$tvueltasr += $array_vueltas_recaudacion[$row1['uniqueid']][0];
			$tcondonacion += $array_vueltas_recaudacion[$row1['uniqueid']][2];
			$tkms += $kms;
		}
		echo '<tr bgcolor="#E9F2F8"><th colspan="3">Total Vueltas</th><th>'.number_format($tvueltas,0).'</th><th align="right">'.number_format($tvueltasr,2).'</th><th>&nbsp;</th><th align="right">'.number_format($tcondonacion,2).'</th><th>'.number_format($tkms,2).'</th></tr>';
		echo '</table>';
	}
exit();
mysql_select_db("gamn_gps");
}


 top($_SESSION);

 	if ($_POST['cmd']<1) {
 		$base='gamn_gps_otra_plataforma';
		if($_POST['plazausuario']==1)
			$select="SELECT a.* FROM dispositivos a  where ruta>0 order by a.nombre";
		else
			$select="SELECT a.* FROM dispositivos a inner join rutas b on a.ruta = b.cvebase AND a.base = b.base where b.plaza IN (0,".$_POST['plazausuario'].")  order by a.nombre";
		
		$rsMotivo=mysql_db_query($base,$select);
		while($Motivo=mysql_fetch_array($rsMotivo)){
			$array_imei[$Motivo['cve']]=$Motivo['nombre'].'('.$array_base[$Motivo['base']].')';
		}
		$base='gamn_gps_otra_plataforma';
		//Busqueda
		echo '<table><input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	//	echo''.$select.'';
		echo '<tr>
				<td><a href="#" onclick="if(document.forma.ruta.value==\'\' && document.forma.dispositivo.value==\'\') alert(\'Necesita seleccionar una ruta o un dispositivo\'); else buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>

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
			objeto.open("POST","reportevueltas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&dispositivo="+document.getElementById("dispositivo").value+"&ruta="+document.getElementById("ruta").value+"&plaza="+document.getElementById("plaza").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
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