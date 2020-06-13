<?php
include ("main.php");
mysql_select_db("gamn_gps_skymedia");
$res = mysql_query("SELECT minutos FROM minutos_atrasados ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$minutos = $row[0];

mysql_select_db('gamn_gps_otra_plataforma');
if($_POST['plazausuario'] == 1)
	$rsMotivo=mysql_query("SELECT * FROM rutas WHERE 1 ORDER BY nombre");
else
	$rsMotivo=mysql_query("SELECT * FROM rutas WHERE plaza IN (0, ".$_POST['plazausuario'].") ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_rutasgps[$Motivo['base']][$Motivo['cvebase']]=$Motivo['nombre'];
}



mysql_select_db('gamn_gps');

if($_POST['ajax']==1){
	$reporte = '
			<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			
	$reporte .='<tr bgcolor="#E9F2F8">';
	$reporte .= '<th>Dispositivo</th>
		<th>Fecha y Hora de la Maxima</th><th>Maxima</th><th>Fecha y Hora de la Minima</th><th>Minima</th><th>Coordenadas de la Maxima</th>
		<th>Eventos</th></tr>';

	$base='gamn_gps_otra_plataforma';
	mysql_select_db($base);
	$filtro = "";
	if($_POST['fecha_ini']!='') $filtro .= " and a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!='') $filtro .= " and a.fecha<='".$_POST['fecha_fin']."'";
	//if($_POST['tipo']!='') $filtro .= " and a.tipo='".$_POST['tipo']."'";
	//if($_POST['geocerca']!='') $filtro .= " and b.cve='".$_POST['geocerca']."'";
	if($_POST['device']!=''){
		$device = explode(',', $_POST['device']);
		$filtro .= " and a.base='".$device[0]."' and a.dispositivo='".$device[1]."'";
	}
	if($_POST['ruta']!=''){
		$device = explode(',', $_POST['ruta']);
		$filtro .= " and d.base='".$device[0]."' and d.ruta='".$device[1]."'";
	}
	$filtro .= " and a.tipo='deviceOverspeed'";
		$sel="select a.cve, d.cve as unidad, d.nombre as device ,count(a.cve) as eventos,concat(a.fecha, ' ', a.hora) as servertime, max(velocidad) as maxima, min(velocidad) as minima, a.dispositivo, a.base, a.latitud, a.longitud
	from eventos a 
	inner join dispositivos d on d.cvebase = a.dispositivo  AND d.base = a.base
	left join geocercas b on b.cvebase = a.geocerca AND b.base = a.base and b.ruta = d.ruta
	
		where 1 $filtro group by d.nombre order by fecha,hora";	
		$reporte .='<input type="hidden" value="'.$_POST['fecha_ini'].'" name="fecha_ini" id="fecha_ini">';
		$reporte .='<input type="hidden" value="'.$_POST['fecha_fin'].'" name="fecha_fin" id="fecha_fin">';
		mysql_select_db($base);
	$consulta = mysql_query($sel) or die(mysql_error());//ifnull(a.geocerca,0) > 0
	$primera=true;
	$i=0;

	$data=array();
	while ($dato = mysql_fetch_assoc($consulta)){
		$data[]=array($dato['device'],$dato['eventos'],$dato['maxima'],$dato['minima']);
		$reporte .= "<tr><td align='center'> ".$dato['device'].'</td>';
		$res1 = mysql_query("SELECT * FROM eventos WHERE base='".$dato['base']."' AND dispositivo='".$dato['dispositivo']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND velocidad = '".$dato['maxima']."' AND tipo='deviceOverspeed'") or die(mysql_error());
		$row1 = mysql_fetch_array($res1);
		$reporte .= "<td align='center'> ".$row1['fecha'].' '.$row1['hora'].'</td>';
		$reporte .= "<td align='center'> ".number_format($dato['maxima']*1.85,3).'</td>';
		$res3 = mysql_query("SELECT * FROM eventos WHERE base='".$dato['base']."' AND dispositivo='".$dato['dispositivo']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND velocidad = '".$dato['minima']."' AND tipo='deviceOverspeed'") or die(mysql_error());
		$row3 = mysql_fetch_array($res3);
		$reporte .= "<td align='center'> ".$row3['fecha'].' '.$row3['hora'].'</td>';
		$reporte .= "<td align='center'> ".number_format($dato['minima']*1.85,3).'</td>';
		$reporte .= '<td align="center"><a href="#" onClick="atcr(\'eventos_generados2.php\',\'_blank\',\'100\',\''.$row1['cve'].'\')">'.$row1['latitud'].', '.$row1['longitud'].'</a></td>';
		$reporte .='<td align="center"><a href="#" onClick="atcr(\'eventos_x_unidad.php\',\'\',\'1\','.$dato['unidad'].')">'.$dato['eventos'].'</a></td>';

		$reporte .= '</tr>';
		$i++;

	}
	$reporte .= '<tr bgcolor="#E9F2F8"><td colspan="7">'.$i.' Registro(s)</td></tr>';
	$reporte .= '</table>';
	require_once("../taller/phplot/phplot.php");
	$plot = new PHPlot(800,400);
	$plot->SetFileFormat("jpg");
	$plot->SetFailureImage(False);
	//$plot->SetPrintImage(False);
	$plot->SetTitle("Excesos de Velocidad");
	$plot->SetLegend(array("Eventos", "Maxima Velocidad", "Minima Velocidad"));
	$plot->SetIsInline(True);
	$plot->SetOutputFile("fotos_graf/grafica1.jpg");
	$plot->SetImageBorderType('plain');
	$plot->SetDataType('text-data');
	//$plot->SetXDataLabelPos('plotin');
	$plot->SetDataValues($data);
	$plot->SetPlotType('bars');
	//foreach ($data2 as $row) $plot->SetLegend($row[0]); // Copy labels to legend
	//$plot->SetLegend($data3);
	//$plot->SetYDataLabelPos('plotin');
	$plot->SetXTickLabelPos('none');
	$plot->SetXTickPos('none');
	$plot->DrawGraph();
	$reporte .= '<br><img src="fotos_graf/grafica1.jpg?'.date("Y-m-d H:i:s").'">';
	echo $reporte;
	exit();
}

mysql_select_db("gamn_gps");
 top($_SESSION);
if($_POST['cmd']==1){


	
	echo '
	<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	';
	echo'<tr bgcolor="#E9F2F8">';
	echo '<h2>Eventos del Dispositivo </h2></tr>
		  <tr><td><a href="#" onClick="atcr(\'eventos_x_unidad.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver </a></td></tr></table></br>
			<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			
	echo'<tr bgcolor="#E9F2F8">';
	echo '<!--<th>Tipo</th>-->
		<th>Fecha y Hora</th>
		<th>Diferencia</th>
		<th>Geocera</th>
		<th>Dispositivo</th></tr>';

	$base='gamn_gps_otra_plataforma';
	mysql_select_db($base);
	$filtro = "";
	if($_POST['fecha_ini']!='') $filtro .= " and a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!='') $filtro .= " and a.fecha<='".$_POST['fecha_fin']."'";
//	if($_POST['tipo']!='') $filtro .= " and a.tipo='".$_POST['tipo']."'";
//	if($_POST['geocerca']!='') $filtro .= " and b.cve='".$_POST['geocerca']."'";
//	if($_POST['device']!=''){
//		$device = explode(',', $_POST['device']);
//		$filtro .= " and a.base='".$device[0]."' and a.dispositivo='".$device[1]."'";
//	}
	
$filtro .= " and a.dispositivo='".$_POST['reg']."'";
$filtro .= " and a.tipo='deviceOverspeed'";
		$sel="select a.tipo, concat(a.fecha, ' ', a.hora) as servertime, concat(b.cvebase,' ',b.codigo) as geocerca, d.nombre as device  
	from eventos a 
	inner join dispositivos d on d.cvebase = a.dispositivo  AND d.base = a.base
	left join geocercas b on b.cvebase = a.geocerca AND b.base = a.base and b.ruta = d.ruta
	
		where 1 $filtro order by fecha,hora,a.cve";	
	$consulta = mysql_query($sel);//ifnull(a.geocerca,0) > 0
	$primera=true;
	$i=0;

	while ($dato = mysql_fetch_assoc($consulta)){
		//echo "<tr><td align='center'> ".$dato['tipo'].'</td>';
		echo "<td align='center'> ".$dato['servertime'].'</td>';
		if($primera){
			$tiempo = '&nbsp;';
			$primera = false;
		}
		else{
			$res1 = mysql_query("SELECT TIMEDIFF('".$dato['servertime']."','$anterior')");
			$row1 = mysql_fetch_array($res1);
			$tiempo = $row1[0];
		}
		$anterior = $dato['servertime'];
		echo '<td align="center">'.$tiempo.'</td>';
		echo "<td align='center'> ".$dato['geocerca'].'</td>';
		echo "<td align='center'> ".$dato['device'].'</td>';
		echo '</tr>';
		$i++;

	}
	echo '<tr bgcolor="#E9F2F8"><td colspan="5">'.$i.' Registro(s)</td></tr>';
	echo '</table>';
	
}


 	if ($_POST['cmd']<1) {
		$base='gamn_gps_otra_plataforma';
		if($_POST['plazausuario']==1)
			$select="SELECT a.* FROM dispositivos a  where borrado!=1 order by a.nombre";
		else
			$select="SELECT a.* FROM dispositivos a inner join rutas b on a.ruta = b.cvebase AND a.base = b.base where a.borrado!=1 order by a.nombre";//$select="SELECT a.* FROM dispositivos a inner join rutas b on a.ruta = b.cvebase AND a.base = b.base where a.borrado!=1 and b.plaza IN (0,".$_POST['plazausuario'].")  order by a.nombre";
		
		$rsMotivo=mysql_db_query($base,$select);
		while($Motivo=mysql_fetch_array($rsMotivo)){
			$array_imei[$Motivo['base'].','.$Motivo['cvebase']]=$Motivo['nombre'].'('.$array_base[$Motivo['base']].')';
		}
		
		$sel="SELECT a.cve, concat(a.codigo, ' RUTA: ',b.nombre) as codigo, a.cvebase FROM geocercas a inner join rutas b on a.base = b.base and a.ruta = b.cvebase where b.plaza='".$_POST['plazausuario']."' order by a.cve";
		$rsMotiv=mysql_db_query($base,$sel);
		while($Motiv=mysql_fetch_array($rsMotiv)){
			$array_geocercas[$Motiv['cve']]=$Motiv['cvebase'].'  '.$Motiv['codigo'];
		}
		
		$selec="SELECT * FROM eventos  group by tipo order by tipo";
		$rsMotiv=mysql_db_query($base,$selec);
		$i=1;
		while($Motiv=mysql_fetch_array($rsMotiv)){
			$array_tipo[$Motiv[$i]]=$Motiv['tipo'];
			$i++;
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
		echo '<tr><td>Unidad</td><td><select name="device" id="device"><option value="">Seleccione</option>';
		foreach($array_imei as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><td>Tipo</td><td><select name="tipo" id="tipo"><option value="">Seleccione</option>';
		foreach($array_tipo as $k=>$v){
			echo '<option value="'.$v.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><td>Geocera</td><td><select name="geocerca" id="geocerca"><option value="">Seleccione</option>';
		foreach($array_geocercas as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
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
			objeto.open("POST","eventos_x_unidad.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&device="+document.getElementById("device").value+"&plaza="+document.getElementById("plaza").value+"&tipo="+document.getElementById("tipo").value+"&geocerca="+document.getElementById("geocerca").value+"&ruta="+document.getElementById("ruta").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
