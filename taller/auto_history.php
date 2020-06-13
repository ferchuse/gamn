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

$res = mysql_query("SELECT usuario FROM plazas WHERE cve = '".$_POST['plazausuario']."' ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$usuarioempresa = $row[0];

$select= " SELECT * FROM puntos";
$array_puntos = array();
$res = mysql_query($select);
while($row = mysql_fetch_array($res)){
	$array_puntos[$row['cve']] = array('clave' => $row['clave'], 'des' => $row['des']);
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
	echo '
			<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			
	echo'<tr bgcolor="#E9F2F8">';
			
	echo'<th>Dispositivo</th><th>Placa</th><th>IMEI</th><th>Empresa</th><th>Fecha Mexico</th>';
	if($_POST['imei']!='') echo '<th>Tiempo Punto Anterior</th>';
	echo '<th>Fecha Servidor</th>
	<th>Fecha Tracker</th><th>Latitud</th><th>Longitud</th><th>Velocidad</th><!--<th>Punto</th>-->';
				
	//if($_POST['imei']!="") echo '<th>Odometro</th>';
	echo'</tr>';
			   
	$select= " SELECT a.*, b.dispositivo, b.placa, b.usuario FROM gps_objects_history a
			inner join gps_objects b on a.imei = b.imei
	            WHERE 1";
	if($usuarioempresa!='') $select .= " AND b.usuario='$usuarioempresa' ";
	if($_POST['imei']!="") {$select .= " AND a.imei='".$_POST['imei']."'";}
	if($_POST['usuario']!="") {$select .= " AND b.usuario='".$_POST['usuario']."'";}
	$select .= " and a.fecha >= '".$_POST['fecha_ini']."' AND a.fecha <= '".$_POST['fecha_fin']."'  ORDER BY a.cve, hora";
	$res=mysql_db_query($base,$select) or die(mysql_error());
//	echo''.$select.'';
	$primera = true;
	$resultado = array();
	$kms = 0;
	$anterior = '';
	while($row=mysql_fetch_array($res)){
		$resultado[$row[0]]='<td align="center">'.$row['dispositivo'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['placa'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row[1].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['usuario'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row[4].' '.$row[5].'</td>';
		if($_POST['imei']!=''){
			if($primera){
				$tiempo = '&nbsp;';
				$primera = false;
			}
			else{
				$res1 = mysql_query("SELECT TIMEDIFF('".$row[4]." ".$row[5]."','$anterior')");
				$row1 = mysql_fetch_array($res1);
				$tiempo = $row1[0];
				if($tiempo > '00:05:00') $tiempo = '<font color="RED">'.$tiempo.'</font>';
			}
			$resultado[$row[0]].='<td align="center">'.$tiempo.'</td>';
			$anterior = $row[4]." ".$row[5];
		}
		$resultado[$row[0]].='<td align="center">'.$row[2].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row[3].'</td>';
		$resultado[$row[0]].='<td align="center"><span style="cursor:pointer;color:blue;" onclick="atcr(\'auto_history.php\',\'_blank\',1,'.$row['cve'].');">'.$row[6].'</span></td>';
		$resultado[$row[0]].='<td align="center"><span style="cursor:pointer;color:blue;" onclick="atcr(\'auto_history.php\',\'_blank\',1,'.$row['cve'].');">'.$row[7].'</span></td>';
		$resultado[$row[0]].='<td align="center">'.$row[10].'</td>';
		//$resultado[$row[0]].='<td align="center">'.$array_puntos[$row['punto']]['clave'].'<br>'.$array_puntos[$row['punto']]['des'].'</td>';

		
		/*if($_POST['imei']!=""){
			if(!$primera){
				if($anterior['latitud']!=0 && $anterior['longitud']!=0 && $anterior['velocidad']>3 && $row['latitud']!=0 && $row['longitud']!=0 && $row['velocidad']>3){
					$km = CalcularOdometro($anterior['latitud'], $anterior['longitud'], $row['latitud'], $row['longitud']);
				}
				else{
					$km=0;
				}
				$resultado[$row[0]].= '<td align="center">'.$km.'</td>';
				$kms+=$km;
			}
			else
				$resultado[$row[0]].='<td>&nbsp;</td>';
			$anterior = $row;
			$primera = false;
		}*/
		$resultado[$row[0]].='</tr>';		 

	}
	krsort($resultado);
	foreach($resultado as $html){
		rowb();
		echo $html;
	}
	echo'<tr bgcolor="#E9F2F8"><td colspan="10" align="left">'.mysql_num_rows($res).' Registro(s)</td>';
	//if($_POST['imei']!="") echo '<td align="center">'.$kms.'</td>';
	 echo'</tr></table>';
	 //echo '</div>';
exit();
mysql_select_db("gamn_gps");
}

if($_POST['cmd']==1){
	$base='gamn_gps_skymedia';
	mysql_select_db($base);
	$select= " SELECT a.*, b.dispositivo, b.placa FROM gps_objects_history a
			inner join gps_objects b on a.imei = b.imei
	            WHERE a.cve = '".$_POST['reg']."'";
	$res=mysql_db_query($base,$select);
	if($row = mysql_fetch_array($res)){

		echo '<h1>Unidad '.$row['dispositivo'].' '.$row['placa'].'<br>
		Latitud: '.$row['latitud'].'<br>Longitud: '.$row['longitud'].'<br><span id="direccion"></span></h1>';
		
		echo '
		

		<script>
		var map = null;
		function iniciar3() {
			var mapOptions = {
				center: new google.maps.LatLng('.$row['latitud'].', '.$row['longitud'].'),
				zoom: 17,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			map = new google.maps.Map(document.getElementById("Resultados"),mapOptions);
			var marker = new google.maps.Marker({
		        position: map.getCenter()
		        , title: "Ultimo Punto"
		        , map: map
		        , });
			var latlng = new google.maps.LatLng('.$row['latitud'].', '.$row['longitud'].');
			geocoder = new google.maps.Geocoder();
			geocoder.geocode({"latLng": latlng}, function(results, status)
			{
				if (status == google.maps.GeocoderStatus.OK)
				{
					if (results[0])
					{
						$("#direccion").html("<p><strong>Direcci&oacute;n: </strong>" + results[0].formatted_address + "</p>");
					}
					else
					{
						dir = "<p>No se ha podido obtener ninguna dirección en esas coordenadas.</p>";
						$("#direccion").html("");
					}
				}
				else
				{
					dir = "<p>El Servicio de Codificación Geográfica ha fallado con el siguiente error: " + status + ".</p>";
					$("#direccion").html("");
				}

			});
		}     
		function iniciar2() {
			var mapOptions = {
				center: new google.maps.LatLng(23.275306,-106.433777),
				zoom: 17,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			map = new google.maps.Map(document.getElementById("Resultados"),mapOptions);
			/*var place = new google.maps.LatLng(23.275735,-106.435507);
			var marker = new google.maps.Marker({
		        position: place
		        , title: "OXXO"
		        , map: map
		        , });*/
			var marker = new google.maps.Marker({
		        position: map.getCenter()
		        , title: "Ultimo Punto"
		        , map: map
		        , });
			//globo de informacion del marcador 2
			var popup = new google.maps.InfoWindow({
			        content: "Esta es la tienda Carlos III"});
			popup.open(map, marker2);    
			//globo de informacion al dar un clic en el marcador 2
			function showInfo() {
			    map.setZoom(16); //aumenta el zoom
			    map.setCenter(marker.getPosition());
			    var contentString = "Ubicación Actual";
			    var infowindow = new google.maps.InfoWindow({
			    content: \'Aqui es donde estudio, lee mas información en:<a href="http://norfipc.com">NorfiPC</a>\'});
			     infowindow.open(map,marker);}
			         
			//Dispara accion al dar un clic en el marcador          
			google.maps.event.addListener(marker, \'click\', showInfo);
		}  
		</script>';

		//Listado
		echo '<div id="Resultados" style="width:100%;height:500px;">';
		echo '</div>';
	}
	else{
		echo '<div id="Resultados" style="width:100%;height:500px;">';
		echo '<b>No se encontro la unidad</b>';
		echo '</div>';
	}
	echo '<script src="http://maps.google.com/maps/api/js?key=AIzaSyCkz855QnPp8U5ayVATsNQ96EDtLhwWBAY&sensor=false&callback=iniciar3">
		</script>';
	exit();
}



 top($_SESSION);
 if($_POST['cmd']==2){
 	mysql_db_query("gamn_gps_skymedia","UPDATE gps_objects_history SET fecha=DATE(DATE_ADD(dt_server, INTERVAL -".$_POST['minutos']." MINUTE)),
 		hora = TIME(DATE_ADD(dt_server, INTERVAL -".$_POST['minutos']." MINUTE))");
 	mysql_db_query("gamn_gps_skymedia","INSERT minutos_atrasados SET minutos='".$_POST['minutos']."',fecha=NOW(),usuario='".$_POST['cveusuario']."'");
 	$_POST['cmd']=0;
 }




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
		echo '<tr><td>Dispositivo</td><td><select name="imei" id="imei"><option value="">Seleccione</option>';
		foreach($array_imei as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
		if($usuarioempresa!='') echo ' style="display:none;"';
		echo '><td>Empresa</td><td><select name="usuario" id="usuario"><option value="">Seleccione</option>';
		foreach($array_usuario as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		//echo '<tr><td>Minutos Atrasados</td><td><input type="text" name="minutos" id="minutos" value="'.$minutos.'">
		//&nbsp;<a href="#" onClick="atcr(\'\',\'\',2,0)">'.$imgeditar.'</a></td></tr>';
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
			objeto.open("POST","auto_history.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&usuario="+document.getElementById("usuario").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&imei="+document.getElementById("imei").value+"&plaza="+document.getElementById("plaza").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
