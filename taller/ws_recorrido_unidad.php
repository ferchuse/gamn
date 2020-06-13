<?php
require_once('subs/cnx_db.php');

function trae_unidades($datos){
	mysql_select_db('gamn_gps_otra_plataforma');
	if($datos['ruta'] > 0)
		$res1 = mysql_query("SELECT cve,nombre FROM dispositivos WHERE ruta = '".$datos['ruta']."' AND uniqueid != '' ORDER BY nombre");
	else
		$res1 = mysql_query("SELECT cve,nombre FROM dispositivos WHERE uniqueid != '' ORDER BY nombre");
	$dispositivos = array();
	while($row1 = mysql_fetch_array($res1)){
		$dispositivos[] = array('cve' => $row1['cve'], 'nombre' => $row1['nombre']) ;
	}

	return json_encode($dispositivos);
}

function diferenciapunto($anterior, $nuevo){
		$res2 = mysql_query("SELECT TIMEDIFF('$nuevo','$anterior')");
		$row2 = mysql_fetch_array($res2);
		return $row2[0];
	}

function recorrido_unidad($datos){

	mysql_select_db('gamn_gps_skymedia');
	$select= " SELECT * FROM geocercas_gps WHERE ruta > 0 AND orden > 0 ORDER BY orden";
	$array_puntos = array();
	$res = mysql_query($select);
	while($row = mysql_fetch_array($res)){
		$array_puntos[$row['ruta']][$row['cve']] = $row['nombre'];
	}

	$html = '';
	$res1 = mysql_query("SELECT * FROM gps_objects WHERE imei = '".$datos['imei']."' AND imei != ''");
	while($row1 = mysql_fetch_assoc($res1)){
		$primerpunto = key($array_puntos[$row1['ruta']]);
		$cvepuntos = "";
		foreach($array_puntos[$row1['ruta']] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
		$cvepuntos = substr($cvepuntos, 1);$array_resultadopuntos = array();
		$res = mysql_query("SELECT * FROM trackingps 
			WHERE fecham BETWEEN '".$datos['fecha_ini']."' AND '".$datos['fecha_fin']."' AND imei = '".$row1['imei']."' AND id_geocerca IN ($cvepuntos)
			ORDER BY fecham, horam") or die(mysql_error());
		$primera = true;
		$mindist = 0;
		$horapunto = $_POST['fecha_ini'].' 00:00:00';
		$nvuelta = 0;
		$empieza = false;
		while($row = mysql_fetch_array($res)){
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
		}


		$html .= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
				
		$html .='<tr bgcolor="#E9F2F8">';
		foreach($array_puntos[$row1['ruta']] as $cve => $punto)
			$html .='<th>'.$punto.'</th>';
		$html .='</tr>';
		$vueltas = 0;
		foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
			$i=0;
			$puntos_vuelta = 0;
			$puntos = count($resultadovueltas);
				$html2 = '<tr>';
				foreach($array_puntos[$row1['ruta']] as $cve => $punto){
					$puntoinicio = $i;
					$encontrado = false;
					while($i<$puntos){
						if($resultadovueltas[$i]['idpunto'] == $cve){
							$html2 .= '<td align="center">'.$resultadovueltas[$i]['horapunto'].'</td>';
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
				$html2 .= '</tr>';
				if($puntos_vuelta > 1){
					$html .= $html2;
					$vueltas++;
				}

		}
		$html .= '</table>';

	}

	$html = '<b>Plataforma Anterior<br>Vueltas: '.$vueltas.'</b><br>'.$html;

	mysql_select_db('gamn_gps_otra_plataforma');
	$select= " SELECT * FROM geocercas WHERE orden > 0 ORDER BY orden";
	$array_puntos = array();
	$res = mysql_query($select);
	while($row = mysql_fetch_array($res)){
		$array_puntos[$row['ruta']][$row['cve']] = $row['codigo'];
	}
	$html2 = '';

	$res1 = mysql_query("SELECT * FROM dispositivos WHERE uniqueid = '".$datos['imei']."' AND uniqueid != ''");
	while($row1 = mysql_fetch_assoc($res1)){

		
		$primerpunto = key($array_puntos[$row1['ruta']]);
		$cvepuntos = "";
		foreach($array_puntos[$row1['ruta']] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
		$cvepuntos = substr($cvepuntos, 1);

		
		$array_resultadopuntos = array();
		$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM eventos 
			WHERE fecha BETWEEN '".$datos['fecha_ini']."' AND '".$datos['fecha_fin']."' AND dispositivo = '".$row1['cve']."' AND geocerca IN ($cvepuntos)
			ORDER BY fecha,hora") or die(mysql_error());
		$primera = true;
		$mindist = 0;
		$horapunto = $datos['fecha_ini'].' 00:00:00';
		$nvuelta = 0;
		$empieza = false;
		while($row = mysql_fetch_array($res)){
				$diferencia = diferenciapunto($horapunto, $row['servertime']);
				if($diferencia > '00:01:00'){
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
		$html2 .=  '<table width="100%" cellpadding="4" border="1" cellspacing="1" class="" id="tabla1">';
		$html2 .= '<tr bgcolor="#E9F2F8">';
		foreach($array_puntos[$row1['ruta']] as $cve => $punto)
			$html2 .= '<th>'.$punto.'</th>';
		$html2 .= '</tr>';
		$vueltas=0;
		foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
			$i=0;
			$puntos = count($resultadovueltas);
			$puntos_encontrados = 0;
				$html3 = '<tr>';
				foreach($array_puntos[$row1['ruta']] as $cve => $punto){
					$puntoinicio = $i;
					$encontrado = false;
					while($i<$puntos){
						if($resultadovueltas[$i]['idpunto'] == $cve){
							$html3 .= '<td align="center">'.$resultadovueltas[$i]['horapunto'].'</td>';
							$i++;
							$puntos_encontrados++;
							$encontrado = true;
							break;
						}
						$i++;
					}
					if(!$encontrado){
						$i=$puntoinicio;
						$html3 .= '<td>&nbsp;</td>';
					}
				}
				$html3 .= '</tr>';
				if($puntos_encontrados > 1){
					$html2 .=  $html3;
					$vueltas++;
				}
		}
		$html2 .= '</table>';
	}

	$html .= '<br><b>Plataforma Nueva<br>Vueltas: '.$vueltas.'</b><br>'.$html2;
	
	return $html;
}

function recorrido_unidad2($datos){

	

	mysql_select_db('gamn_gps_otra_plataforma');
	$select= " SELECT * FROM geocercas WHERE orden > 0 ORDER BY orden";
	$array_puntos = array();
	$res = mysql_query($select);
	while($row = mysql_fetch_array($res)){
		$array_puntos[$row['ruta']][$row['cve']] = $row['codigo'];
	}
	$html2 = '';

	$res1 = mysql_query("SELECT * FROM dispositivos WHERE cve = '".$datos['unidad']."' AND uniqueid != ''");
	while($row1 = mysql_fetch_assoc($res1)){

		
		$primerpunto = key($array_puntos[$row1['ruta']]);
		$cvepuntos = "";
		foreach($array_puntos[$row1['ruta']] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
		$cvepuntos = substr($cvepuntos, 1);

		
		$array_resultadopuntos = array();
		$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM eventos 
			WHERE fecha BETWEEN '".$datos['fecha_ini']."' AND '".$datos['fecha_fin']."' AND dispositivo = '".$row1['cve']."' AND geocerca IN ($cvepuntos)
			ORDER BY fecha,hora") or die(mysql_error());
		$primera = true;
		$mindist = 0;
		$horapunto = $datos['fecha_ini'].' 00:00:00';
		$nvuelta = 0;
		$empieza = false;
		while($row = mysql_fetch_array($res)){
				$diferencia = diferenciapunto($horapunto, $row['servertime']);
				if($diferencia > '00:01:00'){
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
		$html2 .=  '<table width="100%" cellpadding="4" border="1" cellspacing="1" class="" id="tabla1">';
		$html2 .= '<tr bgcolor="#E9F2F8">';
		foreach($array_puntos[$row1['ruta']] as $cve => $punto)
			$html2 .= '<th>'.$punto.'</th>';
		$html2 .= '</tr>';
		$vueltas=0;
		foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
			$i=0;
			$puntos = count($resultadovueltas);
			$puntos_encontrados = 0;
				$html3 = '<tr>';
				foreach($array_puntos[$row1['ruta']] as $cve => $punto){
					$puntoinicio = $i;
					$encontrado = false;
					while($i<$puntos){
						if($resultadovueltas[$i]['idpunto'] == $cve){
							$html3 .= '<td align="center">'.$resultadovueltas[$i]['horapunto'].'</td>';
							$i++;
							$puntos_encontrados++;
							$encontrado = true;
							break;
						}
						$i++;
					}
					if(!$encontrado){
						$i=$puntoinicio;
						$html3 .= '<td>&nbsp;</td>';
					}
				}
				$html3 .= '</tr>';
				if($puntos_encontrados > 1){
					$html2 .=  $html3;
					$vueltas++;
				}
		}
		$html2 .= '</table>';
	}

	$html = '<b>Plataforma Nueva<br>Vueltas: '.$vueltas.'</b><br>'.$html2;
	
	return $html;
}

function marcar_vuelta($datos){
	mysql_query("INSERT vueltas_recaudadas SET fecha='{$datos['fecha']}', dispositivo='{$datos['dispositivo']}',usuario='{$datos['usuario']}',fechacaptura=NOW()");
}

function recorrido_unidad_marcar_vueltas($datos){

	

	mysql_select_db('gamn_gps_otra_plataforma');
	$select= " SELECT * FROM geocercas WHERE orden > 0 ORDER BY orden";
	$array_puntos = array();
	$res = mysql_query($select);
	while($row = mysql_fetch_array($res)){
		$array_puntos[$row['ruta']][$row['cve']] = $row['codigo'];
	}
	$html2 = '';

	$res1 = mysql_query("SELECT * FROM dispositivos WHERE cve = '".$datos['unidad']."' AND uniqueid != ''");
	while($row1 = mysql_fetch_assoc($res1)){

		$res2 = mysql_query("SELECT MAX(fecha) FROM vueltas_recaudadas WHERE dispositivo = '".$row1['unidad']."'");
		$row2 = mysql_fetch_array($res2);
		$ultimavueltarecaudada = $row2[0];

		
		$primerpunto = key($array_puntos[$row1['ruta']]);
		$cvepuntos = "";
		foreach($array_puntos[$row1['ruta']] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
		$cvepuntos = substr($cvepuntos, 1);


		$html2 .= '<h3>Checadas del dia '.$datos['fecha_ini'].'</h3>';

		
		$array_resultadopuntos = array();
		$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM eventos 
			WHERE fecha BETWEEN '".$datos['fecha_ini']."' AND '".$datos['fecha_ini']."' AND dispositivo = '".$row1['cve']."' AND geocerca IN ($cvepuntos)
			ORDER BY fecha,hora") or die(mysql_error());
		$primera = true;
		$mindist = 0;
		$horapunto = $datos['fecha_ini'].' 00:00:00';
		$nvuelta = 0;
		$empieza = false;
		while($row = mysql_fetch_array($res)){
				$diferencia = diferenciapunto($horapunto, $row['servertime']);
				if($diferencia > '00:01:00'){
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
		$html2 .=  '<table width="100%" cellpadding="4" border="1" cellspacing="1" class="" id="tabla1">';
		$html2 .= '<tr bgcolor="#E9F2F8"><td>&nbsp;</td>';
		foreach($array_puntos[$row1['ruta']] as $cve => $punto)
			$html2 .= '<th>'.$punto.'</th>';
		$html2 .= '</tr>';
		$vueltas=0;
		foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
			$i=0;
			$puntos = count($resultadovueltas);
			$puntos_encontrados = 0;
				$html3 = '';
				$horavuelta='';
				foreach($array_puntos[$row1['ruta']] as $cve => $punto){
					$puntoinicio = $i;
					$encontrado = false;
					while($i<$puntos){
						if($resultadovueltas[$i]['idpunto'] == $cve){
							$html3 .= '<td align="center">'.$resultadovueltas[$i]['horapunto'].'</td>';
							if($horavuelta=='') $horavuelta = $resultadovueltas[$i]['horapunto'];
							$i++;
							$puntos_encontrados++;
							$encontrado = true;
							break;
						}
						$i++;
					}
					if(!$encontrado){
						$i=$puntoinicio;
						$html3 .= '<td>&nbsp;</td>';
					}
				}
				$html3 .= '</tr>';
				if($puntos_encontrados > 1){
					if($horavuelta<=$ultimavueltarecaudada){
						$html2 .= '<tr bgcolor="#00FF00"><td>&nbsp;</td>';
					}
					else{
						$html2 .= '<tr><td><input type="button" value="Marcar Vuelta" onClick="marcar_vuelta(\''.$horavuelta.'\','.$row1['cve'].')"></td>';	
					}
					$html2 .=  $html3;
					$vueltas++;
				}
		}
		$html2 .= '</table>';

		$html2 .= '<br>Vueltas: '.$vueltas;

		$html2 .= '<h3>Checadas del dia '.$datos['fecha_fin'].'</h3>';

		
		$array_resultadopuntos = array();
		$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM eventos 
			WHERE fecha BETWEEN '".$datos['fecha_fin']."' AND '".$datos['fecha_fin']."' AND dispositivo = '".$row1['cve']."' AND geocerca IN ($cvepuntos)
			ORDER BY fecha,hora") or die(mysql_error());
		$primera = true;
		$mindist = 0;
		$horapunto = $datos['fecha_fin'].' 00:00:00';
		$nvuelta = 0;
		$empieza = false;
		while($row = mysql_fetch_array($res)){
				$diferencia = diferenciapunto($horapunto, $row['servertime']);
				if($diferencia > '00:01:00'){
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
		$html2 .=  '<table width="100%" cellpadding="4" border="1" cellspacing="1" class="" id="tabla1">';
		$html2 .= '<tr bgcolor="#E9F2F8"><td>&nbsp;</td>';
		foreach($array_puntos[$row1['ruta']] as $cve => $punto)
			$html2 .= '<th>'.$punto.'</th>';
		$html2 .= '</tr>';
		$vueltas=0;
		foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
			$i=0;
			$puntos = count($resultadovueltas);
			$puntos_encontrados = 0;
				$html3 = '';
				$horavuelta='';
				foreach($array_puntos[$row1['ruta']] as $cve => $punto){
					$puntoinicio = $i;
					$encontrado = false;
					while($i<$puntos){
						if($resultadovueltas[$i]['idpunto'] == $cve){
							$html3 .= '<td align="center">'.$resultadovueltas[$i]['horapunto'].'</td>';
							if($horavuelta=='') $horavuelta = $resultadovueltas[$i]['horapunto'];
							$i++;
							$puntos_encontrados++;
							$encontrado = true;
							break;
						}
						$i++;
					}
					if(!$encontrado){
						$i=$puntoinicio;
						$html3 .= '<td>&nbsp;</td>';
					}
				}
				$html3 .= '</tr>';
				if($puntos_encontrados > 1){
					if($horavuelta<=$ultimavueltarecaudada){
						$html2 .= '<tr bgcolor="#00FF00"><td>&nbsp;</td>';
					}
					else{
						$html2 .= '<tr><td><input type="button" value="Marcar Vuelta" onClick="marcar_vuelta(\''.$horavuelta.'\','.$row1['cve'].')"></td>';	
					}
					$html2 .=  $html3;
					$vueltas++;
				}
		}
		$html2 .= '</table>';

		$html2 .= '<br>Vueltas: '.$vueltas;
	}

	
	return $html2;
}

function recorrido_unidades($datos){

	

	mysql_select_db('gamn_gps_otra_plataforma');
	$select= " SELECT * FROM geocercas WHERE orden > 0 AND ruta = '".$datos['ruta']."' ORDER BY orden";
	$array_puntos = array();
	$res = mysql_query($select);
	while($row = mysql_fetch_array($res)){
		$array_puntos[$row['ruta']][$row['cve']] = $row['codigo'];
	}

	$cvepuntos = "";
	foreach($array_puntos[$datos['ruta']] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
	$cvepuntos = substr($cvepuntos, 1);
	$html = '';

	$html .=  '<table width="100%" cellpadding="4" border="1" cellspacing="1" class="" id="tabla1">';
	$html .= '<tr bgcolor="#E9F2F8">';
	foreach($array_puntos[$row1['ruta']] as $cve => $punto)
		$html .= '<th>'.$punto.'</th>';
	$html .= '</tr>';

	$res1 = mysql_query("SELECT b.* FROM eventos a INNER JOIN dispositivos b ON b.cve = a.dispositivo WHERE b.ruta = '".$datos['ruta']."' AND fecha BETWEEN '".$datos['fecha_ini']."' AND '".$datos['fecha_fin']."' AND geocerca IN ($cvepuntos) 
		GROUP BY a.dispositivo ORDER BY b.nombre");

	while($row1 = mysql_fetch_array($res1)){


		
		$primerpunto = key($array_puntos[$row1['ruta']]);
		

		
		$array_resultadopuntos = array();
		$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM eventos 
			WHERE fecha BETWEEN '".$datos['fecha_ini']."' AND '".$datos['fecha_fin']."' AND dispositivo = '".$row1['cve']."' AND geocerca IN ($cvepuntos)
			ORDER BY fecha,hora") or die(mysql_error());
		$primera = true;
		$mindist = 0;
		$horapunto = $datos['fecha_ini'].' 00:00:00';
		$nvuelta = 0;
		$empieza = false;
		while($row = mysql_fetch_array($res)){
				$diferencia = diferenciapunto($horapunto, $row['servertime']);
				if($diferencia > '00:01:00'){
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
		
		$html2 = '';
		$vueltas=0;
		foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
			$i=0;
			$puntos = count($resultadovueltas);
			$puntos_encontrados = 0;
			if($vueltas>0)
				$html3 = '<tr>';
			else
				$html3 = '';
			foreach($array_puntos[$row1['ruta']] as $cve => $punto){
				$puntoinicio = $i;
				$encontrado = false;
				while($i<$puntos){
					if($resultadovueltas[$i]['idpunto'] == $cve){
						$html3 .= '<td align="center">'.$resultadovueltas[$i]['horapunto'].'</td>';
						$i++;
						$puntos_encontrados++;
						$encontrado = true;
						break;
					}
					$i++;
				}
				if(!$encontrado){
					$i=$puntoinicio;
					$html3 .= '<td>&nbsp;</td>';
				}
			}
			$html3 .= '</tr>';
			if($puntos_encontrados > 1){
				$html2 .=  $html3;
				$vueltas++;
			}
		}
		if($vueltas>0){
			$html .= '<tr><td rowspan="'.$vueltas.'">'.$row1['nombre'].'</td>'.$html2;
		}
		
	}

	$html .= '</table>';

	
	return $html;
}



$function = $_POST['function'];

echo $function($_POST['datos']);

?>