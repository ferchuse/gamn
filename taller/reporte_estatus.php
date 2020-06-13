<?php
include ("main.php");
$res = mysql_query("SELECT usuario FROM plazas WHERE cve = '".$_POST['plazausuario']."' ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$usuarioempresa = $row[0];

$res = mysql_query("SELECT kmsodo FROM usuarios WHERE cve = '1' ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$kmsodo = $row[0];





function CalcularOdometro($lat1, $lon1, $lat2, $lon2)
{
	$theta = $lon1 - $lon2; 
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
	$dist = acos($dist); 
	$dist = rad2deg($dist); 
	$km = $dist * 60 * 1.1515 * 1.609344;
	
	return sprintf("%01.6f", $km);
}


if($_POST['cmd']==100){
	$base='gamn_gps_otra_plataforma';
	mysql_select_db($base);
	$select= " SELECT a.*, b.nombre FROM posiciones a
			inner join dispositivos b on a.dispositivo = b.cve
	            WHERE a.cve = '".$_POST['reg']."'";
	$res=mysql_db_query($base,$select);
	if($row = mysql_fetch_array($res)){

		echo '<h1>Unidad '.$row['nombre'].'<br>
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



if($_POST['ajax']==1){

	$base='gamn_gps_otra_plataforma';
	mysql_select_db($base);
	
	echo '
			<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			
	echo'<tr bgcolor="#E9F2F8">';
	echo '<th>Dispositivo</th>
		<th>Fecha y Hora Dispositivo</th>
		<th>Latitud</th>
		<th>Longitud</th>
		<th>Altitud</th>
		<th>status</th>
		<th>Power</th>
		</tr>';

	$filtro = "";
	if($_POST['fecha_ini']!='') $filtro .= " and fecha>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!='') $filtro .= " and fecha<='".$_POST['fecha_fin']."'";
	if($_POST['dispositivo']!=''){
		$device = explode(',', $_POST['dispositivo']);
		$filtro .= " and a.base='".$device[0]."' and a.dispositivo='".$device[1]."'";
	}
			   
	$consulta = mysql_query("select a.cve,a.fecha, a.hora, a.fechadispositivo, a.fechafix, a.idserver, b.nombre as dispositivo,a.latitud,a.longitud,a.altitud, TIMEDIFF(CONCAT(a.fecha,' ',a.hora),a.fechadispositivo) as difdispositivo,
		TIMEDIFF(CONCAT(a.fecha,' ',a.hora),a.fechafix) as diffix, motor_encendido, en_marcha, a.distancia, a.totaldistancia, a.event, a.sat, a.hdop, a.runtime, a.status, a.adc1, a.adc2, a.adc3, a.batery, a.power 
		from posiciones a inner join dispositivos b on b.cvebase = a.dispositivo and b.base = a.base
		where (a.status=258 OR a.power=0) AND (b.modelo LIKE '%ZENDA%' OR b.modelo LIKE '%Mvt-100%' OR b.modelo LIKE '%T366G%') $filtro order by a.fecha,a.hora") or die (mysql_error());
	$primera=true;
	$totalodometro = 0;
	while ($dato = mysql_fetch_assoc($consulta)) {
		
			$lat1 = $dato['latitud'];
			$anterior = $dato['fecha']." ".$dato['hora'];
			echo "<tr>";
			echo "<td> ".$dato['dispositivo'].'</td>';
			echo "<td> ".$dato['fechadispositivo'].'</td>';
			
			echo '<td><a href="#" onClick="atcr(\'reporte_posiciones.php\',\'_blank\',\'100\',\''.$dato['cve'].'\')">'.$dato['latitud'].'</a></td>';
			echo '<td> <a href="#" onClick="atcr(\'reporte_posiciones.php\',\'_blank\',\'100\',\''.$dato['cve'].'\')">'.$dato['longitud'].'</a></td>';
			echo "<td> ".$dato['altitud'].'</td>';
			
			echo "<td> ".$dato['status'].'</td>';
			echo "<td> ".$dato['power'].'</td>';
			echo '</tr>';
			
			$i++;

	}
	$c=7;
	echo '<tr bgcolor="#E9F2F8"><th colspan="'.$c.'">'.$i.' Registro(s)</th></tr>';
	echo '</table>';
	exit();
}


 top($_SESSION);

 	if ($_POST['cmd']<1) {
		$base='gamn_gps_otra_plataforma';
		if($_POST['plazausuario']==1)
			$select="SELECT a.* FROM dispositivos a  where a.modelo like '%ZENDA%' order by a.nombre";
		else
			$select="SELECT a.* FROM dispositivos a inner join rutas b on a.ruta = b.cvebase AND a.base = b.base where (a.modelo LIKE '%ZENDA%' OR a.modelo LIKE '%Mvt-100%' OR a.modelo LIKE '%T366G%') AND b.plaza IN (0,".$_POST['plazausuario'].")  order by a.nombre";
		
		$rsMotivo=mysql_db_query($base,$select);
		while($Motivo=mysql_fetch_array($rsMotivo)){
			$array_imei[$Motivo['base'].','.$Motivo['cvebase']]=$Motivo['nombre'].'('.$array_base[$Motivo['base']].')';
		}

		//Busqueda
		echo '<table><input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	//	echo''.$select.'';
		echo '<tr>
				<td><a href="#" onclick="if(document.forma.dispositivo.value==\'\') alert(\'Necesita seleccionar el dispositivo\'); else buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>';
		if($_POST['cveusuario']==1) echo '<td><a href="#" onClick="atcr(\'reporte_posiciones.php\',\'\',\'3\',\'\')">'.$imgborrar.'</a>Borrar</td>';

		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr ><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.date('Y-m-d').'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.date('Y-m-d').'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Eco</td><td><select name="dispositivo" id="dispositivo"><option value="">Seleccione</option>';
		foreach($array_imei as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k == $_POST['dispositivo']) echo ' selected';
			echo '>'.$v.'</option>';
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
			objeto.open("POST","reporte_estatus.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&dispositivo="+document.getElementById("dispositivo").value+"&plaza="+document.getElementById("plaza").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
			objeto.onreadystatechange = function(){
				if (objeto.readyState==4)
				{
					document.getElementById("Resultados").innerHTML = objeto.responseText;
					setTimeout("buscarRegistros()",60000);
				}
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
