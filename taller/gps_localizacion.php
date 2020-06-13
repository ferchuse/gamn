<?php
include ("main.php");
mysql_select_db('gamn_gps_otra_plataforma');
$rsMotivo=mysql_query("SELECT * FROM usuarios ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_personal[$Motivo['cve']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM dispositivos a where 1 ORDER by nombre desc");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_unidad[$Motivo['cve']]=$Motivo['nombre'];
	$array_unidad_[$Motivo['nombre']]=$Motivo['cve'];
	$array_unidad_cve[$Motivo['uniqueid']]=$Motivo['cve'];
}
$rsMotivo=mysql_query("SELECT * FROM usuarios where 1 ORDER by usuario asc");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_usuarios_movil[$Motivo['idpersonal']]=$Motivo['login'];
	$array_usuarios_movi[$Motivo['id']]=$Motivo['idpersonal'];
}

mysql_select_db('gamn_gps');
if($_POST['ajax']==1){
mysql_select_db('gamn_gps_otra_plataforma');
	$select= " SELECT b.nombre,a.fechafix,a.latitud,a.longitud FROM posiciones as a inner join dispositivos b on b.cvebase = a.dispositivo and b.base = a.base WHERE LEFT(a.fechafix,10) >= '".$_POST['fecha_ini']."' AND LEFT(a.fechafix,10) <= '".$_POST['fecha_fin']."' ";
	if($_POST['no_eco']!="") $select .= " AND b.nombre = '".$array_unidad_[$_POST['no_eco']]."'";
//	if($_POST['folio']!="") $select .= " AND a.id = '".$_POST['folio']."'";
//	if($_POST['terminal']!="") $select .= " AND a.idterminal = '".$_POST['terminal']."'";
//	if($_POST['empresa']!="") $select .= " AND b.empresa= '".$_POST['empresa']."'";
//	if($_POST['terminal']!="") $select .= " AND b.id='".$_POST['terminal']."'";
	//if($_POST['usu']!="") $select .= " AND b.nombre='".$_POST['usu']."'";
	$select .= " ORDER BY a.cve desc";
	$res=mysql_query($select) or die(mysql_error());
//	echo''.$select.'';
	echo '<div style="height: 350px; overflow: auto;"><table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8">
		  <th>Unidad</th><th>Fecha</th><th>Latitud</th><th>Longitud</th><th>IMEI</th>
		  </tr>';
	while($row=mysql_fetch_array($res)){
		rowb();
		echo'<td align="center">'.$row['nombre'].'</td>';
		echo'<td align="center">'.$row['fechafix'].'</td>';
		echo'<td align="center"><a href="http://maps.google.com/maps?q='.$row['latitud'].','.$row['longitud'].'&t=m" target="_blank">'.$row['latitud'].'</a></td>';
		echo'<td align="center"><a href="http://maps.google.com/maps?q='.$row['latitud'].','.$row['longitud'].'&t=m" target="_blank">'.$row['longitud'].'</a></td>';
		echo'<td align="center">'.$row['imei'].'</td>';
		echo'</tr>';
		$total=$total+number_format($row['importe'],2);
	}
	echo'<tr bgcolor="#E9F2F8"><td colspan="7" align="left">'.mysql_num_rows($res).' Registro(s)</td><!--<td align="right">Total</td><td colspan="3">'.number_format($total,2).'</td>-->';

	 echo'</table></div>';
exit();
}

if($_POST['ajax'] == 2){
	mysql_select_db('gamn_gps');
	$busqueda = $_POST['busqueda'];
	$actividad = $_POST['actividad'];
	$html = '';
	$intervalo = 30;
	$query = 'select * from movil_configuraciongps';
	$result = mysql_query($query);

	if($result = mysql_fetch_assoc($result))
		$intervalo = $result['intervalo'];

	$respuesta = array();
	$fecha_final = date('Y-m-d H:i:s');
 	$fecha_inicial = date('Y-m-d H:i:s', strtotime('-' . $intervalo . ' second', strtotime($fecha_final)));

 	$query = " select ml.imei, ml.latitud, ml.longitud, ml.fecha, ml.fecha_servidor as fecha_servidor, md.nombre, md.apodo from movil_localizacion as ml inner join movil_dispositivos as md on md.imei = ml.imei where";

	if($actividad == 'A')
		$query .= " ml.fecha_servidor between '$fecha_inicial' and '$fecha_final' and ";

	if($busqueda != '')
		$query .= " (md.nombre like '%$busqueda%' or md.apodo like '%$busqueda%' or md.imei like '%$busqueda%') and ";

	$query .= " md.estatus = 'A' and ml.plaza = '{$_SESSION['plaza_seleccionada']}' group by ml.imei ";

	if($actividad == 'I')
		$query .= " having max(ml.fecha_servidor) < '$fecha_inicial'";

	$respuesta['query'] = $query;

 	$result = mysql_query($query);
	$respuesta['total'] = 0;
	$respuesta['ubicaciones'] = array();

	$query = "insert into movil_total_ubicados set total = '{$respuesta['total']}', fecha = now(), intervalo = '$intervalo', plaza = '{$_SESSION['plaza_seleccionada']}'";
	mysql_query($query);

 	while($row = mysql_fetch_assoc($result)){
		$row['nombre'] = iconv('ISO-8859-1', 'UTF-8', $row['nombre']);

		if($row['fecha_servidor'] >= $fecha_inicial && $row['fecha_servidor'] <= $fecha_final){
			$respuesta['ubicaciones'][] = $row;
		    $estatus = '<img src="images/circulo_activo.png"/>';
			$respuesta['total'] += 1;
		}else
		    $estatus = '<img src="images/circulo_inactivo.png"/>';

		$html .= '
		<div style="height: 60px; width: 100%; border-bottom: thin solid #ccc;">
			<table style="width: 100%">
				<tr>
					<td>Nombre</td>
					<td>' . $row['nombre'] . '</td>
					<td>' . $estatus . '</td>
				</tr>
				<tr>
					<td>Apodo</td>
					<td>' . $row['apodo'] . '</td>
				</tr>
				<tr>
					<td>IMEI</td>
					<td>' . $row['imei'] . '</td>
				</tr>
			</table>
		</div>';
	}

	$respuesta['html'] = $html;
	$query = "select * from movil_geocercas where plaza = '{$_SESSION['plaza_seleccionada']}'";
	$result = mysql_query($query);
	$respuesta['geocercas'] = array();

	while($row = mysql_fetch_assoc($result))
		$respuesta['geocercas'][] = $row;

	echo(json_encode($respuesta));
	exit();
}

if($_POST['ajax'] == 3){
	mysql_select_db('gamn_gps');
	$respuesta = array();
	$query = "select * from movil_configuraciongps where plaza = '{$_SESSION['plaza_seleccionada']}'";
	$result = mysql_query($query);

	if($result = mysql_fetch_assoc($result)){
		$query = "update movil_configuraciongps set intervalo = '{$_POST['segundos']}' where plaza = '{$_SESSION['plaza_seleccionada']}'";
	}else{
		$query = "insert into movil_configuraciongps set intervalo = '{$_POST['segundos']}', plaza = '{$_SESSION['plaza_seleccionada']}'";
	}


	if(mysql_query($query))
		$respuesta['validado'] = true;
	else
		$respuesta['validado'] = false;

	echo(json_encode($respuesta));
	exit();
}

 top($_SESSION);

if($_POST['cmd'] == 7){
	mysql_select_db('gamn_gps');
	$query = "delete from movil_geocercas where id = '{$_POST['reg']}'";
    mysql_query($query);
    $_POST['cmd'] = 4;
}

if($_POST['cmd'] == 6){
	mysql_select_db('gamn_gps');
	$str_error = '';

    if($_POST['nombre'] != '' && $_POST['latitud'] != '' && $_POST['longitud'] != '' && $_POST['radio'] != ''){
        $aux = "set nombre = '{$_POST['nombre']}', latitud = '{$_POST['latitud']}', longitud = '{$_POST['longitud']}', radio = '{$_POST['radio']}', plaza = '{$_SESSION['plaza_seleccionada']}'";

        if($_POST['reg'] == 0){
            $query = "select id from movil_geocercas where nombre = '{$_POST['nombre']}'";
            $result = mysql_query($query);

            if(!mysql_fetch_assoc($result)){
                $query = "select id from movil_geocercas where latitud = '{$_POST['latitud']}' and longitud = '{$_POST['longitud']}'";
                $result = mysql_query($query);

                if(!mysql_fetch_assoc($result)){
                    $query = "insert into movil_geocercas $aux, fecha = now()";
                    mysql_query($query);
                }else
                    $str_error = 'Ya existe una geocerca con la latitud y longitud seleccionadas';
            }else
                $str_error = 'El nombre de la geocerca ya esta registrado';
        }else{
            $query = "update movil_geocercas $aux where id = '{$_POST['reg']}'";
            mysql_query($query);
        }
    }else
        $str_error = 'Favor de llenar todos los campos';

    $_POST['cmd'] = $str_error == '' ? 4 : 5;
}

if($_POST['cmd'] == 5){
	mysql_select_db('gamn_gps');
	$query = "select * from movil_geocercas where id = '{$_POST['reg']}'";
    $result = mysql_query($query);
    $geocerca = mysql_fetch_assoc($result);

	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="atcr(\'\', \'\', 4, 0);">Regresar</a></td>
			<td><a href="#" onclick="atcr(\'\', \'\', 6, ' . $_POST['reg'] . ');">Guardar</a></td>
		 </tr>';
	echo '</table>';
    echo '
    <table>
        <tr>
            <td>Nombre:</td>
            <td><input type="text" name="nombre" id="nombre" value="' . iconv('', '', $geocerca['nombre']) . '"/></td>
            <td>Latitud:</td>
            <td><input type="text" name="latitud" id="latitud" value="' . $geocerca['latitud'] . '" readonly/></td>
            <td>Longitud:</td>
            <td><input type="text" name="longitud" id="longitud" value="' . $geocerca['longitud'] . '" readonly/></td>
            <td>Radio (Metros):</td>
            <td><input type="number" name="radio" id="radio" value="' . $geocerca['radio'] . '" onchange="setRadio();"/></td>
        </tr>
        <tr>
            <td colspan="9">' . $str_error . '</td>
        </tr>
    </table>
    <br/>
	<div id="mapa" style="height:70%; width: 80%; position: absolute;">
	   <div style="height: 100%; width: 100%;" id="map-canvas"></div>
	 </div>
	<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCkz855QnPp8U5ayVATsNQ96EDtLhwWBAY&v=3&libraries=geometry"></script>
    <script type="text/javascript">
        let map;
        let geocerca = null;
        google.maps.event.addDomListener(window, "load", initMap);

        function initMap(){
            map = new google.maps.Map(document.getElementById("map-canvas"), {
                zoom: 12,
                center: {lat: 19.422887245219854, lng: -99.12860870361328},
                mapTypeId: google.maps.MapTypeId.TERRAIN
            });';

            if($_POST['reg'] > 0){
                echo '
                geocerca = new google.maps.Circle({
                    strokeColor: "#FF0000",
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: "#FF0000",
                    fillOpacity: 0.35,
                    center: {lat: ' . $geocerca['latitud'] . ', lng: ' . $geocerca['longitud'] . '},
                    radius: ' . $geocerca['radio'] . '
                });
                geocerca.setMap(map);
                google.maps.event.addListener(geocerca, "click", function(){
                    geocerca.setMap(null);
                    geocerca = null;
                    $("#latitud").val("");
                    $("#longitud").val("");
                });';
            }

            echo '
            google.maps.event.addListener(map, "click", agregarGeocerca);
        }

        function agregarGeocerca(event){
            if(geocerca == null){
                let latitud = event.latLng.lat();
                let longitud = event.latLng.lng();
                $("#latitud").val(latitud);
                $("#longitud").val(longitud)

                if($("#radio").val() != "" && parseInt($("#radio").val()) > 0){
                    let radio = parseInt(Math.abs($("#radio").val()));
                    crearGeocerca(latitud, longitud, radio);
                }
            }
        }

        function crearGeocerca(latitud, longitud, radio){
            geocerca = new google.maps.Circle({
                strokeColor: "#FF0000",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#FF0000",
                fillOpacity: 0.35,
                center: {lat: latitud, lng: longitud},
                radius: radio
            });
            geocerca.setMap(map);
            google.maps.event.addListener(geocerca, "click", function(){
                geocerca.setMap(null);
                geocerca = null;
                $("#latitud").val("");
                $("#longitud").val("");
                $("#radio").val("");
            });
        }

        function setRadio(){
            let radio = $("#radio").val() == "" ? 0 : parseInt($("#radio").val());

            if(geocerca != null)
                geocerca.setRadius(radio);
        }
    </script>';
}

if($_POST['cmd'] == 4){
	mysql_select_db('gamn_gps');
	$query = "select * from movil_geocercas where plaza = '{$_SESSION['plaza_seleccionada']}' order by nombre";
	$result = mysql_query($query);

	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="atcr(\'\', \'\', 2, 0);">Regresar</a></td>
			<td><a href="#" onclick="atcr(\'\', \'\', 5, 0);">Nuevo</a></td>
		 </tr>';
	echo '</table>';
	echo '
	<div style="height: 400px; overflow: auto;">
	<table width="100%" border="0" cellpadding="4" cellspacing="1" >
		<thead>
			<tr bgcolor="#E9F2F8">
				<th>&nbsp;</th>
				<th>Nombre</th>
				<th>Latitud</th>
				<th>Longitud</th>
				<th>Radio</th>
				<th>Fecha de Creacion</th>
			</tr>
		</thead>
		<tbody>';

	while($row = mysql_fetch_assoc($result)){
		rowb();
		echo '
			<td>
				<a href="#" onclick="editar(' . $row['id'] . ')">Editar</a>
				<span>&nbsp;&nbsp;|&nbsp;&nbsp;</span>
				<a href="#" onclick="eliminar(' . $row['id'] . ')">Eliminar</a>
			</td>
			<td>' . iconv('ISO-8859-1', 'UTF-8', $row['nombre']) . '</td>
			<td align="right">' . $row['latitud'] . '</td>
			<td align="right">' . $row['longitud'] . '</td>
			<td align="right">' . number_format(($row['radio'] / 1000), 2) . ' Km</td>
			<td align="center">' . date('d/m/Y H:i:s', strtotime($row['fecha'])) . '</td>
		</tr>';
	}

	echo '<tbody></table>
	</div>
	<script type="text/javascript">
		function editar(id){
			$("#reg").val(id);
			atcr(\'\', \'\', 5, id)
		}

		function eliminar(id){
			$("#reg").val(id);
			atcr(\'\', \'\', 7, id)
		}
	</script>';

}

 if($_POST['cmd'] == 3){
 mysql_select_db('gamn_gps_otra_plataforma');
	 
 	function CalcularOdometro($lat1, $lon1, $lat2, $lon2)
	{
		$theta = $lon1 - $lon2; 
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$km = $dist * 60 * 1.1515 * 1.609344;
		
		return sprintf("%01.6f", $km);
	}

     require_once('gps_info.php');
     $fecha_actual = date('Y-m-d');
     $fecha_ini = isset($_POST['fecha_ini']) ? $_POST['fecha_ini'] : $fecha_actual;
     $fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : $fecha_actual;
     $imei = $_POST['imei'];
	 $dispo = $array_unidad_cve[$_POST['imei']];
	 $plaza=0;

	$query = "select p.nombre from dispositivos as p where p.uniqueid = '$imei'";
	$result = mysql_query($query);
	$result = mysql_fetch_assoc($result);
	$nombre = $result['nombre'];


     $gps_info = new GPSInfo();
     $apuntos = $gps_info->getPuntosPorFecha($fecha_ini, $fecha_fin, $dispo, $plaza);
     $size = count($apuntos);
//	 echo''.$size.'';
     $centro = '';
	 $puntos_google = '';

	 $distancia = 0;

     for($i = 0; $i < $size; $i++){
         if($i == 0){
             $centro = "new google.maps.LatLng('{$apuntos[$i]['latitud']}', '{$apuntos[$i]['longitud']}')";
         }
         else{
         	if($apuntos[$i-1]['latitud']!=0 && $apuntos[$i-1]['longitud']!=0 && $apuntos[$i]['latitud']!=0 && $apuntos[$i]['longitud']!=0){
         		$odo = CalcularOdometro($apuntos[$i-1]['latitud'], $apuntos[$i-1]['longitud'], $apuntos[$i]['latitud'], $apuntos[$i]['longitud']);
         		if($odo > 0)
					$distancia += $odo;
			}
         }

         if(strcmp($puntos_google, '') != 0) {
             //$puntos .= ', ';
             $puntos_google .= ', ';
         }

         //$puntos .= "[{$apuntos[$i]['latitud']}, {$apuntos[$i]['longitud']}]";
        //$puntos_google .= "new google.maps.LatLng('{$apuntos[$i]['latitud']}', '{$apuntos[$i]['longitud']}')";
		$puntos_google .= json_encode($apuntos[$i]);
	 }

    // $puntos = "[$puntos]";
     $puntos_google = "[$puntos_google]";

     if(strcmp($puntos_google, '') == 0){
         //$puntos = '[]';
         $puntos_google = '[]';
     }

    $query = "select
			md.uniqueid,
			md.nombre
		from dispositivos as md
		where
			md.estatus = 0 
		order by
			md.nombre";

     $result = mysql_query($query);
     $imeis = '<option value="0">(Seleccionar)</option>';

     while($row = mysql_fetch_assoc($result)){
         $imeis .= '<option value="' . $row['uniqueid'] . '"';

         if($row['uniqueid'] == $imei)
             $imeis .= ' selected';

         $imeis .= '>' . iconv('ISO-8859-1', 'UTF-8', $row['nombre']) . '</option>';
     }

     echo('
     <table width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
         <tbody>
             <tr>
                 <td>
                     <table>
                         <tbody>
						 	<tr>
								<td><a href="#" onclick="atcr(\'\', \'\', 0, 0);">Regresar</a></td>
							</tr>
                             <tr>
                                 <td>Fecha Inicial:</td>
                                 <td><input type="date" name="fecha_ini" value="' . $fecha_ini . '"/></td>
                                 <td>Fecha Final:</td>
                                 <td><input type="date" name="fecha_fin" value="' . $fecha_fin . '"/></td>
                                 <td>Dispositivo:</td>
                                 <td><select name="imei">' . $imeis . '</select></td>
                                 <td>&nbsp;&nbsp;&nbsp;<a href="#" onclick="atcr(\'\',\'\', 3, 0);"><img src="images/buscar.gif" border="0"></a></td>
                             </tr>
                         </tbody>
                     </table>
                     <br/>
                     <h3>Distancia: <span id="hdistancia"></span>&nbsp;&nbsp;&nbsp;' . $nombre . ' - ' . $imei . '</h3>
                 </td>
             </tr>
         </tbody>
     </table>
     <br/>');
	 echo('<div id="mapa" style="height:70%; width: 80%; position: absolute;">
 		    <div style="height: 100%; width: 100%;" id="map-canvas"></div>
 		  </div>');

     if($centro != ''){
         echo('
		 <!--<script type="text/javascript" src="js/moment.js"></script>-->
		 <script type="text/javascript" src="http://momentjs.com/downloads/moment.min.js"></script>
         <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCkz855QnPp8U5ayVATsNQ96EDtLhwWBAY&v=3&libraries=geometry"></script>
         <script type="text/javascript">
		 	var map;
			 var puntos = ' . $puntos_google . ';
			 var directionsDisplay = new google.maps.DirectionsRenderer();
			 var directionsService = new google.maps.DirectionsService();

			 $(document).ready(function(){
				initialize();
			 });

			 /*function initialize() {
                 var myOptions = {
                     center: ' . $centro . ',
                     zoom: 10,
                     mapTypeId: google.maps.MapTypeId.ROADMAP
                 };
                 var map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
                 var lineas = new google.maps.Polyline({
					 path: puntos,
                     map: map,
                     strokeColor: "#6877FF",
                     strokeWeight: 2,
                     strokeOpacity: 0.6,
                     clickable: false
                 });

				 lineas.setMap(map);

                 calcularDistancia();
             }*/

             function initialize() {
                 var myOptions = {
                     center: ' . $centro . ',
                     zoom: 15,
                     mapTypeId: google.maps.MapTypeId.ROADMAP
                 };
                 map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
				 directionsDisplay.setMap(map);
                 calcularDistancia();
             }

			 /*function calcularDistancia(){
                 var distancia = 0, p1, p2;
                 var j = 1;

                 for(var i = 0; i < puntos.length; i++, j++){
                     if(j == puntos.length)
                         break;

                     p1 = puntos[i];//new google.maps.LatLng(puntos[i][0], puntos[i][1]);
                     p2 = puntos[j];//new google.maps.LatLng(puntos[j][0], puntos[j][1]);
                     distancia += google.maps.geometry.spherical.computeDistanceBetween(p1, p2);
                 }

                 document.getElementById("hdistancia").innerHTML = number_format((distancia / 1000), 2, ".", ",") + \' KM\';
             }*/

             function calcularDistancia(){
                 var distancia = 0, aux = 0, p1, p2;
				 var decimales = 3;
				 var trazo = []

                 for(var i = 0, j = 1; i < puntos.length; i++, j++){
                     if(j == puntos.length)
                         break;

					 var fecha1 = moment(puntos[i].fecha);
					 var fecha2 = moment(puntos[j].fecha);
					 var diff = fecha2.diff(fecha1, "seconds");
					 console.log(fecha2.diff(fecha1, "seconds"));

					 var bool = (number_format(puntos[i].latitud, decimales) != number_format(puntos[j].latitud, decimales)) || (number_format(puntos[i].longitud, decimales) != number_format(puntos[j].longitud, decimales))

					 if(diff == 2 && bool){
	                     p1 = /*puntos[i]*/new google.maps.LatLng(parseFloat(number_format(puntos[i].latitud, decimales)), parseFloat(number_format(puntos[i].longitud, decimales)));
	                     p2 = /*puntos[j]*/new google.maps.LatLng(parseFloat(number_format(puntos[j].latitud, decimales)), parseFloat(number_format(puntos[j].longitud, decimales)));
	                     aux += google.maps.geometry.spherical.computeDistanceBetween(p1, p2);
						 trazo = [p1, p2];
						 var lineas = new google.maps.Polyline({
							 path: trazo,
							 map: map,
							 strokeColor: "#FF0000",
							 strokeWeight: 3,
							 strokeOpacity: 0.8,
							 clickable: false
						 });
					 }else{
						 distancia += aux;
						 aux = 0;
					 }
                 }
				 //distancia = aux;
				 distancia = '.($distancia*1000).';
                 document.getElementById("hdistancia").innerHTML = number_format((distancia / 1000), 2, ".", ",") + \' KM\';
             }

             function number_format(number, decimals, dec_point, thousands_sep) {
                 // Strip all characters but numerical ones.
                 number = (number + "").replace(/[^0-9+\-Ee.]/g, "");
                 var n = !isFinite(+number) ? 0 : +number,
                     prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                     sep = (typeof thousands_sep === "undefined") ? "," : thousands_sep,
                     dec = (typeof dec_point === "undefined") ? "." : dec_point,
                     s = "",
                     toFixedFix = function (n, prec) {
                         var k = Math.pow(10, prec);
                         return "" + Math.round(n * k) / k;
                     };
                 // Fix for IE parseFloat(0.55).toFixed(0) = 0;
                 s = (prec ? toFixedFix(n, prec) : "" + Math.round(n)).split(".");
                 if (s[0].length > 3) {
                     s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
                 }
                 if ((s[1] || "").length < prec) {
                     s[1] = s[1] || "";
                     s[1] += new Array(prec - s[1].length + 1).join("0");
                 }
                 return s.join(dec);
             }

            //google.maps.event.addDomListener(window, "load", initialize);
         </script>
         ');
     }else
         echo('<h3>No se encontraron coordenadas en el rango de fechas establecido</h3>');
 }

if($_POST['cmd'] == 2){
	mysql_select_db('gamn_gps');
	$latitud = 19.4284700;
	$longitud =  -99.1276600;
	$intervalo = 10;

	$query = "select * from movil_configuraciongps where plaza = '{$_SESSION['plaza_seleccionada']}'";
	$result = mysql_query($query);

	if($result = mysql_fetch_assoc($result))
		$intervalo = $result['intervalo'];

	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="atcr(\'\', \'\', 0, 0);">Regresar</a></td>
			<td><a href="#" onclick="atcr(\'\', \'\', 4, 0);">Geocercas</a></td>
		 </tr>';
	echo '</table>';
	echo('<table>
			<tr>
				<td colspan="3">Dispositivos ubicados: <span id="total_ubicados">0</span></td>
			</tr>
			<tr>
				<td>Intervalo de tiempo (segundos):</td>
				<td><input type="number" name="intervalo" id="intervalo" value="' . $intervalo . '"</td>
				<td><input type="button" value="Cambiar" onclick="cambiarIntervalo();"/></td>
			</tr>
		</table>');

	echo('
	 	<div id="contenedor_listado_dispositivos" style="height: 70%; width: 30%; vertical-align: top; display: inline-block; border-style: groove; margin-right: 5px;">
			<div id="panel_listado_dispositivos" style="height: 20%;">
				<table style="width="100%">
					<tr>
						<td width="70%">
							<input type="text" name="busqueda_panel" id="busqueda_panel" value="" title="Buscar por Nombre/Apodo/Imei" placeholder="Buscar por Nombre/Apodo/Imei">
						</td>
						<td>
							<select name="actividad_panel" id="actividad_panel">
								<option value="A">Activos</option>
								<option value="I">Inactivos</option>
							</select>
						</td>
						<td width="30%">
							<a href="#"><img src="images/buscar.gif" onclick="getUbicaciones();"></a>
						</td>
					</tr>
				</table>
			</div>
			<hr/>
			<div style="height: 412px;" id="listado_dispositivos"></div>
	 	</div>
 		<div id="mapa" style="height:70%; width: 50%; position: absolute; vertical-align: top; display: inline-block; border-style: groove;">
 			<div style="height: 100%; width: 100%;" id="map-canvas"></div>
 		</div>');

	echo('
 	<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCkz855QnPp8U5ayVATsNQ96EDtLhwWBAY&callback=initialize">
     </script>
     <script type="text/javascript">
	    var map;
		var markers = [];
		var geocercas = [];
		var intervalo;

	 	$(document).ready(function(){
			getUbicaciones();
			intervalo = setInterval(getUbicaciones, (' . $intervalo . ' * 1000));
		});

		function cambiarIntervalo(){
			var segundos = $("#intervalo").val();

			if(segundos != "" && segundos > 0){
				$.post(
					"gps_localizacion.php",
					{
						ajax: 3,
						segundos: segundos
					},
					function(json){
						if(json.validado){
							clearInterval(intervalo);
							intervalo = setInterval(getUbicaciones, (segundos * 1000));
						}else
							alert("Problemas al guardar el intervalo de tiempo, porfavor intente mas tarde");
					},
					"json"
				);
			}else
				alert("El intervalo no puede estar vacio o tener 0");
		}

		function getUbicaciones(){
			var busqueda = $("#busqueda_panel").val();
			var actividad = $("#actividad_panel").val();

			$.post(
				"gps_localizacion.php",
				{
					ajax: 2,
					busqueda: busqueda,
					actividad: actividad
				},
				function(json){
					if(json.total == null || json.total == undefined)
						$("#total_ubicados").text("0");
					else
						$("#total_ubicados").text(json.total);

					quitarGeocercas();
					pintarGeocercas(json.geocercas);
					quitarGlobos();
					armarMarcadores(json.ubicaciones);
					pintarGlobos();
					$("#listado_dispositivos").html(json.html);
				},
				"json"
			);
		}

		function quitarGeocercas(){
			for(var i = 0; i < geocercas.length; i++)
				geocercas[i].setMap(null);
		}

		function pintarGeocercas(ageocercas){
			for(var i = 0; i < ageocercas.length; i++){
				var nombre = ageocercas[i].nombre;
				var geocerca = new google.maps.Circle({
	                strokeColor: "#FF0000",
	                strokeOpacity: 0.8,
	                strokeWeight: 2,
	                fillColor: "#FF0000",
	                fillOpacity: 0.35,
	                center: {lat: parseFloat(ageocercas[i].latitud), lng: parseFloat(ageocercas[i].longitud)},
	                radius: parseFloat(ageocercas[i].radio)
	            });
				geocerca.setMap(map);
				geocercas.push(geocerca);
			}
		}

		function armarMarcadores(json){
			markers = [];
			for(var i = 0; i < json.length; i++){
				marker = new google.maps.Marker({
	  				position: new google.maps.LatLng(parseFloat(json[i].latitud), parseFloat(json[i].longitud)),
	  				title: json[i].apodo
	  			});
				/*google.maps.event.addListener(marker, "click", (function(marker, i) {
	              return function() {
	                infowindow.setContent("Nombre: " + json[i].nombre + "<br/>Apodo: " + json[i].apodo + "<br/>IMEI: " + json[i].imei + "<br/>Latitud: " + json[i].latitud + "<br/>Longitud: " + json[i].longitud);
	                infowindow.open(map, marker);
	              }
	            })(marker, i));
				markers.push(marker);*/
				pintarMarca(json[i]);
			}

		}

		function pintarMarca(json){
			var latlng = {lat: parseFloat(json.latitud), lng: parseFloat(json.longitud)};
			var infowindow = new google.maps.InfoWindow();
			var geocoder = new google.maps.Geocoder;
			var marker = new google.maps.Marker({
			  position: latlng,
			  map: map
			});

			 geocoder.geocode({"location": latlng}, function(places, status){

				if(status == "OK"){
					console.log("entro");
					google.maps.event.addListener(marker, "click", (function() {
					  return function() {
						infowindow.setContent("Nombre: " + json.nombre + "<br/>Apodo: " + json.apodo + "<br/>IMEI: " + json.imei + "<br/>Direccion: " + places[0].formatted_address + "<br/>Latitud: " + json.latitud + "<br/>Longitud: " + json.longitud);
						infowindow.open(map, marker);
					  }
				  	})(marker));
					markers.push(marker);
				}
			});
		}

         function initialize() {
           map = new google.maps.Map(document.getElementById("map-canvas"), {
             zoom: 10,
             center: new google.maps.LatLng(' . $latitud . ', ' . $longitud . '),
             mapTypeId: google.maps.MapTypeId.ROADMAP
           });
         }

		function quitarGlobos(){
			for(var i = 0; i < markers.length; i++) {
			  markers[i].setMap(null);
			}
		}

		function pintarGlobos(){
			for(var i = 0; i < markers.length; i++) {
			  markers[i].setMap(map);
			}
		}

         google.maps.event.addDomListener(window, "load", initialize);
     </script>');
}

 	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'\', \'\', 2, 0);">Ver Mapa</a></td>
				<td><a href="#" onclick="atcr(\'\', \'\', 3, 0);">Ver Recorrido</a></td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>No Eco</td><td><input type="text" class="textField" size="5" name="no_eco" id="no_eco"></td></tr>';
		echo '<tr style="display:none;"><td>Folio</td><td><input type="text" class="textField" size="5" name="folio" id="folio"></td></tr>';
		//echo '<tr style="display:none;"><td>Id Tipo Evento</td><td><input type="text" class="textField" size="5" name="idtipo" id="idtipo"></td></tr>';
		/*echo '<tr style="display:none;"><td>Empresa</td><td><select name="empresa" id="empresa"><option value="">Todas</option>';
		foreach($array_empresa as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr"><td>Terminal</td><td><select name="terminal" id="terminal"><option value="">Todos</option>';
		foreach($array_terminal as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';*/
		/*echo '<tr><td></td><td><select name="terminal" id="terminal"><option value="">-- Seleccione un Usuario --</option>';
		foreach($array_terminal as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';*/
		echo '</table>
		';
		echo '<br><hr/>';

		//Listado
		echo '<div id="Resultados" style="height: 400px; overflow: auto;">';

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
			objeto.open("POST","gps_localizacion.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&no_eco="+document.getElementById("no_eco").value);
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
		buscarRegistros();
	}';
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			//buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	</Script>';
	}
 ?>
<?
bottom();
?>
