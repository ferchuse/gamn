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

$rsMotivo=mysql_query("SELECT * FROM cat_rutas WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_rutas[$Motivo['cve']]=$Motivo['nombre'];
}

$res = mysql_query("SELECT usuario FROM plazas WHERE cve = '".$_POST['plazausuario']."' ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$usuarioempresa = $row[0];

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
	mysql_select_db('gamn_gps_skymedia');
	//echo '<div style="height: 350px; overflow: auto;">';
	if($_POST['imei']!=""){
		$res = mysql_query("SELECT latitud, longitud, velocidad FROM trackingps 
			WHERE imei = '".$_POST['imei']."' AND latitud != 0 AND longitud != 0 AND velocidad > 3 ORDER BY id");
		$primera = true;
		$kms = 0;
		while($row = mysql_fetch_array($res)){
			if(!$primera)
			{
				$kms += CalcularOdometro($anterior['latitud'], $anterior['longitud'], $row['latitud'], $row['longitud']);
			}
			$anterior = $row;
			$primera = false;
		}
	}
	echo '<h3>Kms Totales: '.$kms.'</h3>';
	echo '
			<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			
			echo'<tr bgcolor="#E9F2F8">';
			echo '<th>Id</th><th>Usuario</th><th>Tipo</th><th>Descripcion</th><th>Geocerca</th><th>IMEI</th><th>Placa</th><th>Dispositivo</th><th>Fecha Mexico</th><th>Fecha Dispositivo</th>
			<th>Latitud</th><th>Longitud</th><th>Altitud</th><th>Angulo</th><th>Velocidad</th><th>Estatus</th>
			<th>Fecha Creacion</th>';
			if($_POST['imei']!="") echo '<th>Kms</th>';
			echo'</tr>';
			   
	$select= " SELECT a.*, b.placa, b.dispositivo FROM trackingps a left join gps_objects b ON a.imei = b.imei
	            WHERE 1 ";

	if($usuarioempresa!='') $select .= " AND a.username = '$usuarioempresa'";
		//	if($_POST['no_eco']!="") $select .= " AND a.idunidad = '".$array_cveeconomico[$_POST['no_eco']]."'";
//	if($_POST['folio']!="") $select .= " AND a.id = '".$_POST['folio']."'";
//	if($_POST['terminal']!="") $select .= " AND a.idterminal = '".$_POST['terminal']."'";
	if($_POST['id_placa']!="") $select .= " AND idplaca= '".$_POST['id_placa']."'";
	if($_POST['imei']!="") {$select .= " AND a.imei='".$_POST['imei']."'";}//else{if($_POST['plaza']==1){$plaz=" and idempresa='4'";} }
	if($_POST['tipo']!="") {$select .= " AND a.tipo='".$_POST['tipo']."'";}//else{if($_POST['plaza']==1){$plaz=" and idempresa='4'";} }
	if($_POST['descripcion']!="") {$select .= " AND a.descripcion LIKE '%".$_POST['descripcion']."%'";}
	if($_POST['geocerca']!="") {$select .= " AND a.geocerca LIKE '%".$_POST['geocerca']."%'";}
	if($_POST['placa']!="") {$select .= " AND b.placa LIKE '%".$_POST['placa']."%'";}
	if($_POST['ruta']!="") {$select .= " AND b.ruta = '".$_POST['ruta']."'";}
	if($_POST['dispositivo']!="") {$select .= " AND b.dispositivo LIKE '%".$_POST['dispositivo']."%'";}
	if($_POST['usuario']!="") {$select .= " AND a.username = '".$_POST['usuario']."'";}
	//if($_POST['usu']!="") $select .= " AND b.nombre='".$_POST['usu']."'";
	echo $select .= "and a.fecham >= '".$_POST['fecha_ini']."' AND a.fecham <= '".$_POST['fecha_fin']."' ".$plaz." ORDER BY a.id";
	$res=mysql_db_query('gps_skymedia',$select) or die(mysql_error());
//	echo''.$select.'';
	$primera = true;
	$resultado = array();
	$kms = 0;
	while($row=mysql_fetch_array($res)){
		$parentesis = strpos($row[2], '(');
		if($parentesis === false){

		}
		else{
			$row[2] = substr($row[2], $parentesis+1, -1);
			mysql_db_query('gps_skymedia', "UPDATE trackingps SET descripcion='".$row[2]."' WHERE id='".$row[0]."' AND imei='".$row[3]."'");
		}
		$resultado[$row[0]]='<td align="center">'.$row['id'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['username'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['tipo'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['descripcion'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['geocerca'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['imei'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['placa'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['dispositivo'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['fecham'].' '.$row['horam'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['fecha'].' '.$row['hora'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['latitud'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['longitud'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['altitud'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['angulo'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['velocidad'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['estatus'].'</td>';
		$resultado[$row[0]].='<td align="center">'.$row['fecha_creacion'].' '.$row['hora_creacion'].'</td>';
		
		if($_POST['imei']!=""){
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
		}
		$resultado[$row[0]].='</tr>';		 

	}
	krsort($resultado);
	foreach($resultado as $html){
		rowb();
		echo $html;
	}
	echo'<tr bgcolor="#E9F2F8"><td colspan="16" align="left">'.mysql_num_rows($res).' Registro(s)</td>';
	if($_POST['imei']!="") echo '<td align="center">'.$kms.'</td>';
	 echo'</tr></table>';
	 //echo '</div>';
exit();
}


 top($_SESSION);


 	if ($_POST['cmd']<1) {
		$base='gamn_gps_skymedia';
		$select="SELECT * FROM gps_objects where 1  order by imei";
		$rsMotivo=mysql_db_query($base,$select);
		while($Motivo=mysql_fetch_array($rsMotivo)){
			$array_imei[$Motivo['imei']]=$Motivo['dispositivo'];
		}
		$select="SELECT * FROM tipotrakin where 1  order by nombre";
		$rsMotivo=mysql_db_query($base,$select);
		while($Motivo=mysql_fetch_array($rsMotivo)){
			$array_tipo[$Motivo['nombre']]=$Motivo['nombre'];
		}
		$sel="SELECT * FROM trackingps where username !='' group by username order by username";
		$rsM=mysql_db_query($base,$sel) or die(mysql_error());
		while($Motivo=mysql_fetch_array($rsM)){
			$array_username[$Motivo['username']]=$Motivo['username'];
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
		echo '<tr ><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		//echo '<tr><td>No Eco</td><td><input type="text" class="textField" size="5" name="no_eco" id="no_eco"></td></tr>';
		echo '<tr style="display:none;"><td>Id</td><td><input type="text" class="textField" size="5" name="id_placa" id="id_placa"></td></tr>';
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
		echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">Todos</option>';
		foreach($array_username as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Dispositivo</td><td><select name="imei" id="imei"><option value="">Todos</option>';
		foreach($array_imei as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Placa</td><td><input type="name="placa" id="placa" class="textField"></td></tr>';
		echo '<tr><td>Dispositivo</td><td><input type="name="dispositivo" id="dispositivo" class="textField"></td></tr>';
		echo '<tr><td>Tipo</td><td><select name="tipo" id="tipo"><option value="">Todos</option>';
		foreach($array_tipo as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Descripcion</td><td><input type="name="descripcion" id="descripcion" class="textField"></td></tr>';
		echo '<tr><td>Geocerca</td><td><input type="name="geocerca" id="geocerca" class="textField"></td></tr>';
		echo '<tr><td>Ruta</td><td><select name="ruta" id="ruta" class="textField"><option value="">Todas</option>';
		foreach ($array_rutas as $k=>$v) { 
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
			objeto.open("POST","auto_traking.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&ruta="+document.getElementById("ruta").value+"&usuario="+document.getElementById("usuario").value+"&geocerca="+document.getElementById("geocerca").value+"&dispositivo="+document.getElementById("dispositivo").value+"&placa="+document.getElementById("placa").value+"&descripcion="+document.getElementById("descripcion").value+"&tipo="+document.getElementById("tipo").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&imei="+document.getElementById("imei").value+"&plaza="+document.getElementById("plaza").value+"&id_placa="+document.getElementById("id_placa").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
