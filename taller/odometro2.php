<?php
include ("main.php");


$res = mysql_query("SELECT kmsodo FROM usuarios WHERE cve = '1' ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$kmsodo = $row[0];


$base='gamn_gps_otra_plataforma';
mysql_select_db($base);
if($_POST['plazausuario'] == 1)
	$rsMotivo=mysql_query("SELECT * FROM rutas WHERE 1 ORDER BY nombre");
else
	$rsMotivo=mysql_query("SELECT * FROM rutas WHERE plaza IN (0, ".$_POST['plazausuario'].") ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_rutasgps[$Motivo['base']][$Motivo['cvebase']]=$Motivo['nombre'];
}
//$rutas='';
//foreach($array_rutasgps as $k=>$v) $rutas.=",'".$k."'";

if($_POST['ajax']==2){
	$base='gamn_gps_otra_plataforma';
	mysql_select_db($base);
	mysql_query("UPDATE dispositivos SET fechainicial='".$_POST['fechainicial']."', kmsiniciales='".$_POST['kmsiniciales']."' WHERE cve='".$_POST['cvedispositivo']."'");
	echo "UPDATE dispositivos SET fechainicial='".$_POST['fechainicial']."', kmsiniciales='".$_POST['kmsiniciales']."' WHERE cve='".$_POST['cvedispositivo']."'";
	exit();
}

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
	  $recorrido = round($dist * 60,2);
	  return $recorrido;
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


	//echo '<div style="height: 350px; overflow: auto;">';
	
	echo '
			<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			
	echo'<tr bgcolor="#E9F2F8">';
	echo'<th>Dispositivo</th><th>Kms Recorridos</th>';
	if($_POST['cveusuario']==1){
		echo '<th>Fecha Inicial</th><th>Kms Iniciales</th><th>&nbsp;</th>';
	}
	echo'</tr>';

	$filtroruta='(';
	foreach($array_rutasgps as $base=>$rutas){
		if($filtroruta!='(') $filtroruta.=" OR ";
		$filtroruta .= "(a.base = '$base' AND a.ruta IN (";
		$primero = true;
		foreach($rutas as $k=>$v){
			if(!$primero) $filtroruta.=",";
			$filtroruta.="'".$k."'";
			$primero = false;
		}
		$filtroruta.="))";
	}
	$filtroruta.=")";
	$select= " SELECT a.*, b.odo_actual, c.odo_anterior FROM dispositivos a
	left join (select MAX(odometro) as odo_actual, base, dispositivo from odometro_unidad where fecha between '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."'";
	if($_POST['dispositivo']!="") $select .= " AND dispositivo='".$_POST['dispositivo']."'";
	$select.=" group by base, dispositivo) b on a.base = b.base AND a.cvebase = b.dispositivo
	left join (select MAX(odometro) as odo_anterior, base, dispositivo from odometro_unidad where fecha < '".$_POST['fecha_ini']."'";
	if($_POST['dispositivo']!="") $select .= " AND dispositivo='".$_POST['imei']."'";
	$select.=" group by base, dispositivo) c on a.base = c.base AND a.cvebase = c.dispositivo
	WHERE $filtroruta ";
	if($_POST['ruta']!=""){
		$ruta = explode(',',$_POST['ruta']);
		$select .= " AND a.base='".$ruta[0]."' AND a.ruta= '".$ruta[1]."'";
	}
	$select.=" ORDER BY a.nombre";
	$res=mysql_query($select) or die(mysql_error());

	$kms = 0;
	$tkms=0;
	while($row=mysql_fetch_array($res)){
		if($_POST['fecha_fin']>=date('Y-m-d')){
			$res1 = mysql_query("SELECT MAX(odometro) FROM posiciones WHERE dispositivo='".$row['cve']."' AND fecha = CURDATE()");
			$row1 = mysql_fetch_array($res1);
			$row['odo_actual'] = $row1[0];
		}
		rowb();
		if($row['odo_actual']=='' || $row['odo_actual'] <= 0) $row['odo_actual']=$row['odo_anterior'];
		echo '<td align="center">'.$row['nombre'].'</td>';
		//echo '<td align="center">'.$row['uniqueid'].'</td>';
		$kms = calcular_kms_dia($row['base'], $row['cvebase'], $_POST['fecha_ini'], $_POST['fecha_fin']);
		echo '<td align="center">'.number_format($kms,2).'</td>';
		if($_POST['cveusuario'] == 1){
			if($row['fechainicial'] == '0000-00-00') $row['fechainicial'] = '';
			echo '<td align="center"><input type="text" id="fechaini_'.$row['cve'].'" value="'.$row['fechainicial'].'" class="readOnly" size="12" readOnly>&nbsp;<span style="cursor:pointer;" onClick="displayCalendar(document.getElementById(\'fechaini_'.$row['cve'].'\'),\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></span></td>';
			echo '<td align="center"><input type="text" id="kmsini_'.$row['cve'].'" value="'.$row['kmsiniciales'].'" class="textField" size="12"></td>';
			echo '<td align="center"><input type="button" id="btn_'.$row['cve'].'" value="Guardar" class="textField" onClick="guardarKms('.$row['cve'].')"></td>';
		}
		//echo '<td align="center">'.number_format($row['odo_actual'],2).'</td>';
		//echo '<td align="center">'.number_format($row['odo_actual']-$row['odo_anterior'],2).'</td>';
		echo '</tr>';	
		$tkms+=$kms;	 

	}
	echo'<tr bgcolor="#E9F2F8"><td colspan="1" align="left">'.mysql_num_rows($res).' Registro(s)</td><td align="center">'.number_format($tkms,2).'</td>';
	if($_POST['cveusuario'] == 1) echo '<td colspan="3">&nbsp;</td>';
	 echo'</tr></table>';
	 //echo '</div>';
exit();
mysql_select_db("gamn_gps");
}
$base='gamn_gps';
mysql_select_db($base);

 top($_SESSION);
 
 	if ($_POST['cmd']<1) {
		$base='gamn_gps_otra_plataforma';
		$select="SELECT * FROM gps_objects where ruta in (".substr($rutas,1).")  order by nombre";
		$rsMotivo=mysql_db_query($base,$select);
		while($Motivo=mysql_fetch_array($rsMotivo)){
			$array_imei[$Motivo['cve']]=$Motivo['nombre'];
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
		foreach ($array_rutasgps as $base=>$rutas) { 
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
			objeto.open("POST","odometro2.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&ruta="+document.getElementById("ruta").value+"&plaza="+document.getElementById("plaza").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
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

	function guardarKms(cve){
		bjeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else{
			objeto.open("POST","odometro2.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&fechainicial="+document.getElementById("fechaini_"+cve).value+"&kmsiniciales="+document.getElementById("kmsini_"+cve).value+"&cvedispositivo="+cve+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
			objeto.onreadystatechange = function(){
				if (objeto.readyState==4)
				{}
			}
		}
	}
	
	
	</Script>';
	}
 ?>
<?
bottom();
?>
