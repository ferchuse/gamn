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
	$array_puntos[$row['base']][$row['ruta']][$row['cvebase']] = $row['codigo'];
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
	  $recorrido = ($dist * 60).toFixed(2);
	  return $recorrido;
	}

	function calcular_kms_dia($base, $dispositivo, $fecha)
	{
		global $kmsodo;
		$res = mysql_query("SELECT * FROM posiciones WHERE base = '$base' AND dispositivo = '".$dispositivo."' AND en_marcha=1 AND fecha = '$fecha' ORDER BY fecha,hora");
		$primera = true;
		$kms = 0;
		while($row = mysql_fetch_assoc($res))
		{
			if(!$primera){
				$km=0;
				if($anterior['latitud']!=0 && $anterior['longitud']!=0 && $row['latitud']!=0 && $row['longitud']!=0 && $row['en_marcha']==1){
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

	
	
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8"><th><input type="checkbox" onClick="seleccionartodos(this.checked)"></th><th>ID</th>
		  <th>Nombre</th><th>Ruta</th><th>Estatus</th><th>Vueltas</th><th>Kms</th></tr>';
	$filtro = '';
	if($_POST['ruta']!=''){
		$datosruta = explode(',', $_POST['ruta']);
		$filtro .= " AND a.base = '".$datosruta[0]."' AND a.ruta = '".$datosruta[1]."'";
	}
	if($_POST['plazausuario']==1)
		$select="SELECT a.*,c.vueltas,c.kms,c.cve as cvekms FROM dispositivos a left join uni_kms_dias c ON a.cve = c.dispositivo AND c.fecha='".$_POST['fecha']."'  where 1 {$filtro} order by a.nombre";
	else
		$select="SELECT a.*,c.vueltas,c.kms FROM dispositivos a inner join rutas b on a.ruta = b.cvebase AND a.base = b.base left join uni_kms_dias c ON a.cve = c.dispositivo AND c.fecha='".$_POST['fecha']."' where b.plaza IN (0,".$_POST['plazausuario'].") {$filtro} order by a.nombre";
	$res1 = mysql_query($select);
	$primeros_puntos = array();
	$tvueltas=0;
	$tkms=0;
	while($row1 = mysql_fetch_assoc($res1)){
		if($row1['cvekms'] > 0){
		
			if($primeros_puntos[$row1['base']][$row1['ruta']] == ''){
				$primetos_puntos[$row1['base']][$row1['ruta']] = key($array_puntos[$row1['base']][$row1['ruta']]);
			}
			$primerpunto = $primeros_puntos[$row1['base']][$row1['ruta']];
			$cvepuntos = "";
			foreach($array_puntos[$row1['base']][$row1['ruta']] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
			$cvepuntos = substr($cvepuntos, 1);

			$array_resultadopuntos = array();
			$res = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as servertime FROM eventos 
				WHERE fecha = '".$_POST['fecha']."' AND base='".$row1['base']."' AND dispositivo = '".$row1['cvebase']."' AND geocerca IN ($cvepuntos)
				ORDER BY fecha,hora") or die(mysql_error());
			$primera = true;
			$mindist = 0;
			$horapunto = $_POST['fecha_ini'].' 00:00:00';
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
			$vueltas=0;
			foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
				$i=0;
				$puntos = count($resultadovueltas);
				$puntos_encontrados = 0;
				$html = '<tr>';
				foreach($array_puntos[$row1['base']][$row1['ruta']] as $cve => $punto){
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
				if($puntos_encontrados > 2){
					//echo $html;
					$vueltas++;
				}
			}
			$kms = calcular_kms_dia($row1['base'], $row1['cvebase'], $_POST['fecha']);
		}
		else{
			$vueltas = $row1['vueltas'];
			$kms = $row1['kms'];
		}
		rowb();
		echo '<td align="center"><input type="checkbox" name="check['.$row1['cve'].']" value="'.$_POST['fecha'].'|'.$vueltas.'|'.$kms.'"></td>';
		echo'<td align="center">'.$row1['cvebase'].'</td>';
		echo'<td align="center">'.$row1['nombre'].'</td>';
		//echo'<td align="center">'.$row1['uniqueid'].'</td>';
		echo'<td align="center">'.$array_rutas[$row1['base']][$row1['ruta']].'</td>';
		echo'<td align="center">'.$array_estatus[$row1['estatus']].'</td>';
		echo '<td align="center">'.$vueltas.'</td>';
		
		echo '<td align="center">'.number_format($kms,2).'</td>';
		echo'</tr>';
		$tvueltas += $vueltas;
		$tkms += $kms;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="4">Total Vueltas</th><th>'.number_format($tvueltas,0).'</th><th>'.number_format($tkms,2).'</th></tr>';
	echo '</table>';
exit();
mysql_select_db("gamn_gps");
}


 top($_SESSION);

 if($_POST['cmd']==3){
 	$base='gamn_gps_otra_plataforma';
	mysql_select_db($base);
	foreach($_POST['check'] as $dispositivo => $cadenadatos){
		$datos = explode('|', $cadenadatos);
		mysql_query("DELETE FROM uni_kms_dias WHERE dispositivo='$dispositivo' AND fecha='".$datos[0]."'");
	}
 }

 	if($_POST['cmd']==2){
 		$base='gamn_gps_otra_plataforma';
 		mysql_select_db($base);
 		foreach($_POST['check'] as $dispositivo => $cadenadatos){
 			$datos = explode('|', $cadenadatos);
 			$res = mysql_query("SELECT cve FROM uni_kms_dias WHERE dispositivo='$dispositivo' AND fecha='".$datos[0]."'");
 			if(!$row = mysql_fetch_array($res)){
 				mysql_query("INSERT uni_kms_dias SET dispositivo='$dispositivo', fecha='".$datos[0]."', vueltas='".$datos[1]."', kms='".$datos[2]."'");
 			}
 		}
 	}

 	if ($_POST['cmd']<1) {
		$base='gamn_gps_otra_plataforma';
		//Busqueda
		echo '<table><input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	//	echo''.$select.'';
		echo '<tr>
				<td><a href="#" onclick="if(document.forma.ruta.value==\'\') alert(\'Necesita seleccionar una ruta\'); else buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'reportevueltasroot.php\',\'\',\'2\',\'\');">'.$imgguardar.'</a>&nbsp;&nbsp;Guardar&nbsp;&nbsp;</td>
				<td><a href="#" onclick="if(confirm(\'Esta seguro de eliminar el registro guardado\')) atcr(\'reportevueltasroot.php\',\'\',\'3\',\'\');">'.$imgborrar.'</a>&nbsp;&nbsp;Borrar&nbsp;&nbsp;</td>

			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr ><td>Fecha </td><td><input type="text" name="fecha" id="fecha" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Ruta</td><td><select name="ruta" id="ruta" class="textField"><option value="">Todas</option>';
		foreach ($array_rutas as $base=>$rutas) { 
			foreach ($rutas as $k=>$v) { 
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
			objeto.open("POST","reportevueltasroot.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha="+document.getElementById("fecha").value+"&ruta="+document.getElementById("ruta").value+"&plaza="+document.getElementById("plaza").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
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

	}

	function seleccionartodos(seleccionado){
		if(seleccionado) 
			$(".chks").attr("checked", "checked");
		else
			$(".chks").removeAttr("checked");
	}

	';
	
	echo '
	</Script>';
	}

bottom();
?>