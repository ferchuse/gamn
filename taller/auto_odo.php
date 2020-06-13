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

if($_POST['ajax']==1){
	$base='gamn_gps_skymedia';
	mysql_select_db($base);
	function CalcularOdometro2($lat1, $lon1, $lat2, $lon2)
	{
		$theta = $lon1 - $lon2; 
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$km = $dist * 60 * 1.1515 * 1.609344;
		
		return sprintf("%01.6f", $km);
	}

	function calcular_kms_dia($imei)
	{
		$res = mysql_query("SELECT * FROM gps_objects_history WHERE imei = '".$imei."' AND fecha = CURDATE() ORDER BY hora");
		$primera = true;
		$kms = 0;
		while($row = mysql_fetch_assoc($res))
		{
			if(!$primera){
				if($anterior['latitud']!=0 && $anterior['longitud']!=0 && $anterior['velocidad']>3 && $row['latitud']!=0 && $row['longitud']!=0 && $row['velocidad']>3){
					$km = CalcularOdometro2($anterior['latitud'], $anterior['longitud'], $row['latitud'], $row['longitud']);
				}
				$kms+=$km;
			}
			$anterior = $row;
			$primera = false;
		}
		return $kms;
	}


	//echo '<div style="height: 350px; overflow: auto;">';
	
	echo '
			<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			
	echo'<tr bgcolor="#E9F2F8">';
	echo'<th>Dispositivo</th><th>Placa</th><th>IMEI</th><th>Odometro</th><th>Kms Recorridos</th><!--<th>Odometro Calculado</th><th>Kms Recorridos Calculados</th>-->';
	echo'</tr>';
			   
	$select= " SELECT a.*, b.odo_actual, c.odo_anterior, d.odo_actual_calculado, d.kms_recorridos_calculados FROM gps_objects a
	left join (select MAX(odo) as odo_actual, imei from gps_objects_odo where fecha between '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."'";
	if($_POST['imei']!="") $select .= " AND imei='".$_POST['imei']."'";
	$select.=" group by imei) b on a.imei = b.imei
	left join (select MAX(odo) as odo_anterior, imei from gps_objects_odo where fecha < '".$_POST['fecha_ini']."'";
	if($_POST['imei']!="") $select .= " AND imei='".$_POST['imei']."'";
	$select.=" group by imei) c on a.imei = c.imei
	left join (select SUM(odo) as odo_actual_calculado, 
	SUM(IF(fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."',odo,0)) as kms_recorridos_calculados, imei 
	from gps_objects_odo_calculado where fecha <= '".$_POST['fecha_fin']."'";
	if($_POST['imei']!="") $select .= " AND imei='".$_POST['imei']."'";
	$select.=" group by imei) d on a.imei = d.imei
	        WHERE 1 ";
	if($usuarioempresa!='') $select .= " AND a.usuario='$usuarioempresa'";
	if($_POST['imei']!="") {$select .= " AND a.imei='".$_POST['imei']."'";}
	$res=mysql_db_query($base,$select) or die(mysql_error());

	$kms = 0;
	while($row=mysql_fetch_array($res)){
		rowb();
		if($row['odo_actual']=='') $row['odo_actual']=$row['odo_anterior'];
		echo '<td align="center">'.$row['dispositivo'].'</td>';
		echo '<td align="center">'.$row['placa'].'</td>';
		echo '<td align="center">'.$row['imei'].'</td>';
		echo '<td align="center">'.number_format($row['odo_actual'],2).'</td>';
		echo '<td align="center"><a href="#" onClick="atcr(\'auto_odo.php\',\'\',10,\''.$row['imei'].'\')">'.number_format($row['odo_actual']-$row['odo_anterior'],2).'</a></td>';
		/*echo '<td align="center">'.number_format($row['odo_actual_calculado']+$row['odometro_inicial'],2).'</td>';
		$kms = 0;
		if(date('Y-m-d') >= $_POST['fecha_ini'] && date('Y-m-d') <= $_POST['fecha_fin']){
			$kms = calcular_kms_dia($row['imei']);
		}
		echo '<td align="center">'.number_format($row['kms_recorridos_calculados']+$kms,2).'</td>';*/
		echo '</tr>';		 

	}
	echo'<tr bgcolor="#E9F2F8"><td colspan="5" align="left">'.mysql_num_rows($res).' Registro(s)</td>';
	 echo'</tr></table>';
	 //echo '</div>';
exit();
mysql_select_db("gamn_gps");
}


 top($_SESSION);
 if($_POST['cmd'] == 10){
 	function CalcularOdometro($lat1, $lon1, $lat2, $lon2)
	{
		$theta = $lon1 - $lon2; 
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$km = $dist * 60 * 1.1515 * 1.609344;
		
		return sprintf("%01.6f", $km);
	}

     $fecha_actual = date('Y-m-d');
     $fecha_ini = isset($_POST['fecha_ini']) ? $_POST['fecha_ini'] : $fecha_actual;
     $fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : $fecha_actual;
     $imei = $_POST['reg'];
     $base='gamn_gps_skymedia';
     mysql_select_db($base);
	$query = "select * from gps_objects where imei = '$imei'";
	$result = mysql_query($query);
	$result = mysql_fetch_assoc($result);
	$nombre = $result['dispositivo'].' '.$result['placa'];


     $res = mysql_query("SELECT latitud, longitud, CONCAT(fecha, ' ', hora) as fecha FROM gps_objects_history WHERE imei='".$_POST['reg']."' AND fecha BETWEEN '".$fecha_ini."' AND '".$_POST['fecha_fin']."'");
     $size = mysql_num_rows($res);
     $apuntos = array();
     while($row = mysql_fetch_assoc($res)){
     	$apuntos[] = $row;
     }
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

     echo('
     <table width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
         <tbody>
             <tr>
                 <td>
                     <table>
                         <tbody>
						 	<tr>
								<td><a href="#" onclick="atcr(\'\', \'\', 0, 0);">'.$imgvolver.' Regresar</a></td>
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

 	if ($_POST['cmd']<1) {
		$base='gamn_gps_skymedia';
		if($usuarioempresa!='')
			$select="SELECT * FROM gps_objects where usuario='$usuarioempresa'  order by imei";
		else
			$select="SELECT * FROM gps_objects where 1  order by imei";
		$rsMotivo=mysql_db_query($base,$select);
		while($Motivo=mysql_fetch_array($rsMotivo)){
			$array_imei[$Motivo['imei']]=$Motivo['imei'].', Placa: '.$Motivo['placa'].', Nombre: '.$Motivo['dispositivo'];
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
		echo '<tr><td>Imei</td><td><select name="imei" id="imei"><option value="">Seleccione</option>';
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
			objeto.open("POST","auto_odo.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&imei="+document.getElementById("imei").value+"&plaza="+document.getElementById("plaza").value);
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
