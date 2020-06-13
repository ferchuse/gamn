<?
include ("main.php"); 
$base2="enero_aaz";
/*** ARREGLOS ***********************************************************/

$rsUsuario=mysql_db_query($base,"SELECT * FROM ".$pre."usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}


$rsconductor=mysql_db_query($base,"SELECT * FROM ".$pre."parque");
while($Conductor=mysql_fetch_array($rsconductor)){
	$array_unidad[$Conductor['cve']]=$Conductor['no_eco'];
}

$res=mysql_db_query($base,"SELECT * FROM ".$pre."propietarios ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_propietario[$row['cve']]=$row['nombre'];
}

if($_POST['ajax'] == 110){
	$res = mysql_query("SELECT kmsodo FROM gps_otra_plataforma.usuarios WHERE cve = '1' ORDER BY cve DESC LIMIT 1");
	$row = mysql_fetch_array($res);
	$kmsodo = $row[0];
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
		$res = mysql_query("SELECT * FROM gps_otra_plataforma.posiciones WHERE base = '$base' AND dispositivo = '".$dispositivo."' AND fecha BETWEEN '$fecha_ini' AND '$fecha_fin' ORDER BY fecha,hora");
		$primera = true;
		$kms = 0;
		while($row = mysql_fetch_assoc($res))
		{
			if(!$primera){
				$km=0;
				if($anterior['latitud']!=0 && $anterior['longitud']!=0 && $row['latitud']!=0 && $row['longitud']!=0 ){
					$km = CalcularOdometro2($anterior['latitud'], $anterior['longitud'], $row['latitud'], $row['longitud']);
				}
				//if($km<$kmsodo){
					$kms+=round($km,2);
					$anterior = $row;
				//}
			}
			else{
				$anterior = $row;
				$primera = false;
			}
		}
		return $kms;
	}
	$select= " SELECT * FROM gps_otra_plataforma.geocercas WHERE orden > 0 ORDER BY orden";
	$array_puntos = array();
	$res = mysql_query($select);
	while($row = mysql_fetch_array($res)){
		$array_puntos[$row['base']][$row['ruta']][$row['direccion']][$row['cvebase']] = $row['codigo'];
	}

	$rsMotivo=mysql_query("SELECT * FROM gps_otra_plataforma.rutas WHERE 1 ORDER BY nombre");
	while($Motivo=mysql_fetch_array($rsMotivo)){
		$array_rutas[$Motivo['base']][$Motivo['cvebase']]=$Motivo['nombre'];
	}

	if($_POST['dispositivo'] != ''){
		$select="SELECT a.* FROM gps_otra_plataforma.dispositivos a  where a.cve='".$_POST['dispositivo']."'";
		$res1 = mysql_query($select);
		$row1 = mysql_fetch_array($res1);
		echo '<h3>'.$row1['nombre'].' Ruta: '.$array_rutas[$row1['base']][$row1['ruta']].'</h3>';
		$array_vueltas_recaudacion = array();
		$res = mysql_query("SELECT a.fecha_cuenta, SUM(cuenta-condonacion) as vueltas, GROUP_CONCAT(tarjeta), SUM(condonacion), GROUP_CONCAT(c.obs) FROM gamn.parque_abono a INNER JOIN gamn.parque b ON b.cve = a.unidad AND b.imei = '".$row1['uniqueid']."' 
			LEFT JOIN gamn.tarjeta_condonacion c ON a.tarjeta = c.cvetar AND c.estatus!='C'
			WHERE a.fecha_cuenta BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C' GROUP BY a.fecha");
		while($row = mysql_fetch_array($res))
			$array_vueltas_recaudacion[$row['fecha_cuenta']] = array($row[1], $row[2], $row[3], $row[4]);
		
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
			$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM gps_otra_plataforma.eventos 
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
				$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM gps_otra_plataforma.eventos 
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
		$res = mysql_query("SELECT b.imei, SUM(cuenta-condonacion) as vueltas, GROUP_CONCAT(tarjeta), SUM(condonacion) FROM gamn.parque_abono a INNER JOIN gamn.parque b ON b.cve = a.unidad AND b.imei != '' WHERE a.fecha_cuenta BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C' GROUP BY a.unidad");
		while($row = mysql_fetch_array($res))
			$array_vueltas_recaudacion[$row['imei']] = array($row[1], $row[2], $row[3]);
		
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
		      <tr bgcolor="#E9F2F8"><th>ID</th>
			  <th>Nombre</th><th>Ruta</th><th>Vueltas</th><th>Importe Recaudado</th><th>Tarjetas</th><th>Importe Condonado</th><th>Kms</th></tr>';
		$select="SELECT b.* FROM gamn.parque a INNER JOIN gps_otra_plataforma.dispositivos b ON a.imei = b.uniqueid where a.propietario='".$_POST['cveusuario']."' order by b.nombre";
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
			$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM gps_otra_plataforma.eventos 
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
				$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM gps_otra_plataforma.eventos 
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
}

if($_POST['ajax'] == 100){

	function CalcularOdometro($lat1, $lon1, $lat2, $lon2)
	{
		$theta = $lon1 - $lon2; 
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$km = $dist * 60 * 1.1515 * 1.609344;
		
		return sprintf("%01.6f", $km);
	}

	$select= " SELECT * FROM gps_otra_plataforma.geocercas WHERE orden > 0 ORDER BY orden";
	$array_puntos = array();
	$res = mysql_query($select);
	while($row = mysql_fetch_array($res)){
		$array_puntos[$row['base']][$row['ruta']][$row['direccion']][$row['cvebase']] = $row['codigo'];
	}

	$rsMotivo=mysql_query("SELECT * FROM gps_otra_plataforma.rutas WHERE 1 ORDER BY nombre");
	while($Motivo=mysql_fetch_array($rsMotivo)){
		$array_rutas[$Motivo['base']][$Motivo['cvebase']]=$Motivo['nombre'];
	}

	$filtro = '';
	if($_POST['dispositivo'] != '') $filtro .= " AND b.cve = '".$_POST['dispositivo']."'";
	$select="SELECT b.* FROM gamn.parque a INNER JOIN gps_otra_plataforma.dispositivos b ON a.imei = b.uniqueid where a.propietario='".$_POST['cveusuario']."' $filtro order by b.nombre";
	$res1 = mysql_query($select) or die(mysql_error());
	$primeros_puntos = array();
	while($row1 = mysql_fetch_assoc($res1)){

		
		echo '<h1>';
		echo 'Dispositivo:'.$row1['nombre'].'</div>';
		echo '</h1>';
		echo '<table width="100%" cellpadding="4" border="1" cellspacing="1" class="" id="tabla1">';
		
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
		$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM gps_otra_plataforma.eventos 
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
		foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
			$i=0;
			$puntos = count($resultadovueltas);
			$puntos_encontrados = 0;
			$hora='';
				$html = '<tr>';
				foreach($array_puntos[$row1['base']][$row1['ruta']][0] as $cve => $punto){
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
				$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM gps_otra_plataforma.eventos 
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
		}
		echo'</table><br>';
	}
	
	exit();
}

if($_POST['ajax']==1){
	$select= " SELECT * FROM parque WHERE propietario='".$_POST['cveusuario']."' ";
	if ($_POST['unidad']!="") { $select.=" AND no_eco='".$_POST['unidad']."'"; }
	if ($_POST['estatus']!="all") { $select.=" AND estatus='".$_POST['estatus']."'"; }
	$select.=" ORDER BY no_eco";
	$res=mysql_db_query($base,$select);
	if(mysql_num_rows($rsconductor)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="10">'.mysql_num_rows($rsconductor).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8">';
		echo '<th>No Economico</th><th>Estatus</th><th>Fecha Estatus</th><th>Saldo Anterior</th><th>Cargos<th>Abonos</th><th>Saldo</th>';
		echo '</tr>';
		$i=0;
		$x=0;
		$totales=array();
		while($row=mysql_fetch_array($res)){
			rowb();
			echo '<td align="center">'.$row['no_eco'].'</td>';
			echo '<td align="center">'.$array_estatus_parque[$row['estatus']].'</td>';
			echo '<td align="center">'.$row['fecha_sta'].'</td>';
			$saldo_anterior = saldo_unidad($row['cve'],1,0,$_POST['fecha_ini'],"");
			$cargo = saldo_unidad($row['cve'],2,1,$_POST['fecha_ini'],$_POST['fecha_fin']);
			$abono = saldo_unidad($row['cve'],2,2,$_POST['fecha_ini'],$_POST['fecha_fin']);
			echo '<td align="right">'.number_format($saldo_anterior,2).'</td>';
			echo '<td align="right">'.number_format($cargo,2).'</td>';
			echo '<td align="right">'.number_format($abono,2).'</td>';
			echo '<td align="right"><a href="#" onClick="atcr(\'edo_cuenta_parque_propietario.php\',\'\',10,'.$row['cve'].')">'.number_format($saldo_anterior+$abono-$cargo,2).'</a></td>';
			echo '</tr>';
			$totales[0]+=$saldo_anterior;
			$totales[1]+=$cargo;
			$totales[2]+=$abono;
			$totales[3]+=$saldo_anterior+$abono-$cargo;
			$i++;
		}
		echo '	
			<tr>
			<td colspan="2" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
			<td bgcolor="#E9F2F8" align="right">Totales</td>';
		foreach($totales as $v)
			echo '<td bgcolor="#E9F2F8" align="right">'.number_format($v,2).'</td>';
		echo '
			</tr>
		</table>';
	} else {
		echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
	}
	exit();	
}

topsocio($_SESSION);

if($_POST['cmd'] == 110){
	echo '<h3>Reporte de Vueltas</h3><table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
			<td><a href="#" onclick="atcr(\'edo_cuenta_parque_propietario.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	$select="SELECT b.* FROM gamn.parque a INNER JOIN gps_otra_plataforma.dispositivos b ON a.imei = b.uniqueid where a.propietario='".$_POST['cveusuario']."' order by b.nombre";
	$rsMotivo=mysql_query($select);
	while($Motivo=mysql_fetch_array($rsMotivo)){
		$array_imei[$Motivo['cve']]=$Motivo['nombre'];
	}
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>No Eco</td><td><select name="dispositivo" id="dispositivo"><option value="">Seleccione</option>';
	foreach($array_imei as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';
	echo '
	<script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","edo_cuenta_parque_propietario.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=110&dispositivo="+document.getElementById("dispositivo").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	
	</script>';


}

if($_POST['cmd'] == 100){

	echo '<h3>Reporte de Recorrido</h3><table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
			<td><a href="#" onclick="atcr(\'edo_cuenta_parque_propietario.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	$select="SELECT b.* FROM gamn.parque a INNER JOIN gps_otra_plataforma.dispositivos b ON a.imei = b.uniqueid where a.propietario='".$_POST['cveusuario']."' order by b.nombre";
	$rsMotivo=mysql_query($select);
	while($Motivo=mysql_fetch_array($rsMotivo)){
		$array_imei[$Motivo['cve']]=$Motivo['nombre'];
	}
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>No Eco</td><td><select name="dispositivo" id="dispositivo"><option value="">Seleccione</option>';
	foreach($array_imei as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';
	echo '
	<script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","edo_cuenta_parque_propietario.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=100&dispositivo="+document.getElementById("dispositivo").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	
	</script>';


}

if($_POST['cmd']==10){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscar_cargos(\''.$_POST['reg'].'\');"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar Cargos</a></td>
			<td><a href="#" onclick="atcr(\'edo_cuenta_parque_propietario.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>
		</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.$_POST['fecha_ini'].'" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.$_POST['fecha_fin'].'" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<input type="hidden" name="sel[]" value="'.$_POST['reg'].'">';
	//Listado
	echo '<div id="idCargos">';
	echo '</div>';
	echo '
	<script>
	function buscar_cargos(unidad)
	{
		if(document.forma.fecha_ini.value<"2009-09-01") document.forma.fecha_ini.value="2009-09-01";
		if(document.forma.fecha_fin.value<"2009-09-01") document.forma.fecha_fin.value="2009-09-01";
		document.getElementById("idCargos").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","edo_cuenta_parque.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&unidad="+unidad+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("idCargos").innerHTML = objeto.responseText;}
			}
		}
	}
	window.onload = function () {
			buscar_cargos(\''.$_POST['reg'].'\'); //Realizar consulta de todos los registros al iniciar la forma.
	}
	</script>';
}

if($_POST['cmd']==2){
	$update = " UPDATE ".$pre."propietarios 
					SET pass='".$_POST['pass2']."' WHERE cve='".$_POST['cveusuario']."' " ;
	$ejecutar = mysql_db_query($base,$update);
	$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	$select=" SELECT * FROM ".$pre."propietarios WHERE cve='".$_POST['cveusuario']."' ";
	$res=mysql_db_query($base,$select);
	$row=mysql_fetch_array($res);
	
	//Menu
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();validarpass();"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'edo_cuenta_parque_propietario.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Edicion de password</td></tr>';
	echo '</table>';
	$bloqueado='';
	$class='textField';
	echo '<table>';
	echo '<tr><th>Password Actual</th><td><input autocomplete="off" type="password" name="pass1" id="pass1" value="" class="textField"></td></tr>';
	echo '<tr><th>Nuevo Password</th><td><input autocomplete="off" type="password" name="pass2" id="pass2" value="" class="textField"></td></tr>';
	echo '<tr><th>Confirmacion Password</th><td><input autocomplete="off" type="password" name="pass3" id="pass3" value="" class="textField"></td></tr>';
	echo '</table>';
	echo '<script>
			function validarpass(){
				if(document.forma.pass1.value==""){
					$(\'#panel\').hide();
					alert("Necesita ingresar el password actual");
				}
				else if(document.forma.pass2.value==""){
					$(\'#panel\').hide();
					alert("Necesita ingresar el nuevo password");
				}
				else if(document.forma.pass3.value==""){
					$(\'#panel\').hide();
					alert("Necesita ingresar la confirmacion password");
				}
				else if(document.forma.pass1.value!="'.$row['pass'].'"){
					$(\'#panel\').hide();
					alert("Error en password actual");
				}
				else if(document.forma.pass2.value!=document.forma.pass3.value){
					$(\'#panel\').hide();
					alert("El nuevo password y confirmacion password deben de ser iguales");
				}
				else{
					atcr(\'edo_cuenta_parque_propietario.php\',\'\',2,0);
				}
			}
		</script>';
}


if($_POST['cmd']<1){
	/*** PAGINA PRINCIPAL **************************************************/

		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'edo_cuenta_parque_propietario.php\',\'\',1,0);"><img src="images/modificar.gif" border="0"></a>&nbsp;&nbsp;Cambiar Password</td>
				<td><a href="#" onclick="atcr(\'edo_cuenta_parque_propietario.php\',\'\',100,\'0\');"><img src="images/finalizar.gif" border="0">&nbsp;&nbsp;Reporte de  Recorridos</a></td>
				<td><a href="#" onclick="atcr(\'edo_cuenta_parque_propietario.php\',\'\',110,\'0\');"><img src="images/finalizar.gif" border="0">&nbsp;&nbsp;Reporte de  Veultas</a></td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.substr(fechaLocal(),0,8).'01'.'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus" class="textField"><option value="all">---Todos---</option>';
		foreach($array_estatus_parque as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==1) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>No Eco</td><td><input type="text" class="textField" name="unidad" id="unidad" size="10"></td></tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';


echo '
<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","edo_cuenta_parque_propietario.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&unidad="+document.getElementById("unidad").value+"&estatus="+document.getElementById("estatus").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}

	echo '
	
	</Script>
';

}
bottom();
?>