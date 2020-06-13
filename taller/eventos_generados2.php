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

if($_POST['cmd']==100){
	$base='gamn_gps_otra_plataforma';
	mysql_select_db($base);
	$select= " SELECT a.*, b.nombre FROM eventos a
			inner join dispositivos b on a.dispositivo = b.cvebase AND a.base = b.base
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


	
	echo '
			<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			
	echo'<tr bgcolor="#E9F2F8">';
	echo '<th>Tipo</th>
		<th>Fecha y Hora</th>
		<th>Diferencia</th>
		<th>Geocera</th>
		<th>Dispositivo</th><th>Latitud</th><th>Longitud</th><th>Velocidad</th></tr>';

	$base='gamn_gps_otra_plataforma';
	mysql_select_db($base);
	$filtro = "";
	if($_POST['fecha_ini']!='') $filtro .= " and a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!='') $filtro .= " and a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['tipo']!='') $filtro .= " and a.tipo='".$_POST['tipo']."'";
	if($_POST['geocerca']!='') $filtro .= " and b.cve='".$_POST['geocerca']."'";
	if($_POST['device']!=''){
		$device = explode(',', $_POST['device']);
		$filtro .= " and a.base='".$device[0]."' and a.dispositivo='".$device[1]."'";
	}
	if($_POST['ruta']!=''){
		$device = explode(',', $_POST['ruta']);
		$filtro .= " and d.base='".$device[0]."' and d.ruta='".$device[1]."'";
	}
		$sel="select a.tipo, concat(a.fecha, ' ', a.hora) as servertime, concat(b.cvebase,' ',b.codigo) as geocerca, d.nombre as device,a.latitud, a.longitud, a.velocidad   
	from eventos a 
	inner join dispositivos d on d.cvebase = a.dispositivo  AND d.base = a.base
	left join geocercas b on b.cvebase = a.geocerca AND b.base = a.base and b.ruta = d.ruta
	
		where 1 $filtro order by fecha,hora,a.cve";	
	$consulta = mysql_query($sel);//ifnull(a.geocerca,0) > 0
	$primera=true;
	$i=0;

	while ($dato = mysql_fetch_assoc($consulta)){
		echo "<tr><td align='center'> ".$dato['tipo'].'</td>';
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
		echo '<td align="center"><a href="#" onClick="atcr(\'eventos_generados2.php\',\'_blank\',\'100\',\''.$dato['cve'].'\')">'.$dato['latitud'].'</a></td>';
		echo '<td align="center"><a href="#" onClick="atcr(\'eventos_generados2.php\',\'_blank\',\'100\',\''.$dato['cve'].'\')">'.$dato['longitud'].'</a></td>';
		echo "<td align='center'> ".number_format($dato['velocidad']*1.85,3).'</td>';
		echo '</tr>';
		$i++;

	}
	echo '<tr bgcolor="#E9F2F8"><td colspan="5">'.$i.' Registro(s)</td></tr>';
	echo '</table>';
	exit();
}

mysql_select_db("gamn_gps");
 top($_SESSION);


 	if ($_POST['cmd']<1) {
		$base='gamn_gps_otra_plataforma';
		if($_POST['plazausuario']==1)
			$select="SELECT a.* FROM dispositivos a   order by a.nombre";
		else
			$select="SELECT a.* FROM dispositivos a inner join rutas b on a.ruta = b.cvebase AND a.base = b.base where b.plaza IN (0,".$_POST['plazausuario'].")  order by a.nombre";
		
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
		echo '<tr><td>Tipo</td><td><select name="tipo" id="tipo"><option value="">Seleccione</option>';
		foreach($array_tipo as $k=>$v){
			echo '<option value="'.$v.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Geocera</td><td><select name="geocerca" id="geocerca"><option value="">Seleccione</option>';
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
			objeto.open("POST","eventos_generados2.php",true);
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
