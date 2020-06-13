<?php
session_start();
include ("main.php");
/*$rsMotivo=mysql_query("SELECT * FROM empresas ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_empresa[$Motivo['id']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM opciones");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_opcion[$Motivo['id']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM  economicos where empresa='".$_POST['cveempresa']."' and estatus='1'");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_economico[$Motivo['id']]=$Motivo['numero'];
	$array_cveeconomico[$Motivo['numero']]=$Motivo['id'];
}
$rsMotivo=mysql_query("SELECT * FROM  operadores where empresa='".$_POST['cveempresa']."' and estatus='1'");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_operador[$Motivo['id']]=$Motivo['numero'];
	$array_cveoperador[$Motivo['numero']]=$Motivo['id'];
}*/
$rsMotivo=mysql_query("SELECT * FROM usuarios ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_personal[$Motivo['cve']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM cat_rutas WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_rutas[$Motivo['cve']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM cat_orden WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_orden[$Motivo['cve']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM cat_sentido WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_sentido[$Motivo['cve']]=$Motivo['nombre'];
}
$res = mysql_query("SELECT usuario FROM plazas WHERE cve = '".$_POST['plazausuario']."' ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$usuarioempresa = $row[0];
$array_estatus=array(1=>'Alta',2=>'Baja');
mysql_select_db('gamn_gps_skymedia');
$rsMotivo=mysql_query("SELECT * FROM rutas_gps WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_rutasgps[$Motivo['cve']]=$Motivo['nombre'];
}
mysql_select_db('gamn_gps');

if($_POST['ajax'] == 2){
	mysql_select_db('gps_skymedia');
	$query = "update gps_objects set candado = {$_POST['checked']} where id = {$_POST['id']}";
	$check = $_POST['checked'] == 1 ? 'activado' : 'desactivado'; 

	if(mysql_query($query))
		echo(json_encode(array('validado' => true, 'check' => $check)));
	else
		echo(json_encode(array('validado' => false)));

	mysql_select_db('gamn_gps');
	exit();
}

if($_POST['ajax']==1){
	mysql_select_db('gamn_gps_skymedia');
	$select= " SELECT * FROM gps_objects WHERE 1";
	if($usuarioempresa!="") $select .= " AND usuario='$usuarioempresa'";
	if($_POST['imei']!="") $select .= " AND imei = '".$_POST['imei']."'";
	if($_POST['placa']!="") $select .= " AND placa = '".$_POST['placa']."'";
	if($_POST['nom']!="") $select .= " AND dispositivo like '%".$_POST['imei']."%'";
	if($_POST['ruta']!="") $select .= " AND ruta= '".$_POST['ruta']."'";
	$select .= " ORDER BY dispositivo";
	$res=mysql_query($select) or die(mysql_error());
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8">
		  <th width="50"></th><th>Nombre</th><th>Candado</th><th>IMEI</th><th>Placa</th><th>Ruta</th><th>Ruta GPS</th>';
		 if($_POST['cveusuario']==1) echo '<th>Borrar</th>';
	echo '
		  </tr>';
	while($row=mysql_fetch_array($res)){
		rowb();
		echo'<td align="center"><a href="#" onClick="atcr(\'auto_dispositivos.php\',\'\',\'1\','.$row['id'].')">'.$imgeditar.'</a></td>';
//	echo'<td align="left"></td>';

		$candado = $row['candado'] == 1 ? 'checked' : '';

		echo'<td align="center">'.$row['dispositivo'].'</td>';
		echo'<td align="center"><input type="checkbox" name="candado" id="candado' . $row[id] . '" onclick="encadenar(' . $row['id'] . ');" ' . $candado . ' /></td>';
		echo'<td align="center">'.$row['imei'].'</td>';
		echo'<td align="center">'.$row['placa'].'</td>';
		echo'<td align="center">'.$array_rutas[$row['ruta']].'</td>';
		echo'<td align="center">'.$array_rutasgps[$row['ruta_gps']].'</td>';

		if($_POST['cveusuario']==1)
			echo'<td align="center"><a href="#" onClick="if(confirm(\'Esta seguro de eliminar el registro?\'))atcr(\'auto_dispositivos.php\',\'\',\'3\','.$row['id'].')">'.$imgborrar.'</a></td>';
		echo'</tr>';
	}
	echo'<tr bgcolor="#E9F2F8"><td align="left" colspan="8">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table>';
	 mysql_select_db('gamn_gps');
exit();
}

 top($_SESSION);
   if($_POST['cmd']==3){
   	mysql_select_db('gps_skymedia');
	$sSQL="delete from gps_objects where id='".$_POST['reg']."'";
	mysql_query($sSQL);
	mysql_select_db('gps');
   	$_POST['cmd']=0;
   }
  if($_POST['cmd']==2){
	 mysql_select_db('gps_skymedia');
	$sSQL="update gps_objects
			SET ruta='".$_POST['ruta']."',ruta_gps='".$_POST['ruta_gps']."'	where id='".$_POST['reg']."'";
	mysql_query($sSQL);
	mysql_select_db('gps');
	$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	mysql_select_db('gps_skymedia');
	    $select=" SELECT * FROM gps_objects WHERE id='".$_POST['reg']."' ";
	    $rsprovedor=mysql_query($select);
	    $provedor=mysql_fetch_array($rsprovedor);
	$rsMotivo=mysql_query("SELECT * FROM rutas_gps WHERE usuario='".$provedor['usuario']."' ORDER BY nombre");
	while($Motivo=mysql_fetch_array($rsMotivo)){
		$array_rutasgps[$Motivo['cve']]=$Motivo['nombre'];
	}

    echo'
	    <a href="#" onClick="atcr(\'auto_dispositivos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver </a>
		<a href="#" onClick="atcr(\'auto_dispositivos.php\',\'\',\'2\',\''.$provedor['id'].'\');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br>
		<table>
		<tr>';
		echo'<tr><td>Nombre</td><td>' . $provedor['dispositivo'] . '</td></tr>';
		echo'<tr><td>IMEI</td><td>' . $provedor['imei'] . '</td></tr>';
		echo'<tr><td>Placa</td><td>' . $provedor['placa'] . '</td></tr>';
		echo '<tr><td>Ruta</td><td><select name="ruta" id="ruta" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_rutas as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($provedor['ruta']==$k){echo'selected';}echo'>'.$v.'</option>';
			}
		echo '</select></td></tr>';

		echo '<tr><td>Ruta</td><td><select name="ruta_gps" id="ruta_gps" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_rutasgps as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($provedor['ruta_gps']==$k){echo'selected';}echo'>'.$v.'</option>';
			}
		echo '</select></td></tr>';
		echo '<tr';
		if($_POST['cveusuario'] != 1) echo ' style="display:none;"';
		echo '><td>Odometro Inicial</td><td><input type="text" class="textField" value="'.$provedor['odometro_incial'].'" name="odometro_incial" id="odometro_incial"></td>';
		
		echo'</table>';
		mysql_select_db('gamn_gps');
	}

 	if ($_POST['cmd']<1) {
//		$query = "select id, apodo from movil_dispositivos where plaza = '{$_SESSION['plaza_seleccionada']}'";
//		$result = mysql_query($query);
//		$apodos = '<option value="0">(Seleccionar un apodo)</option>';

//		while($row = mysql_fetch_assoc($result))
//			$apodos .= '<option value="' . $row['id'] . '">' . $row['apodo'] . '</option>';
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Ruta</td><td><select name="ruta" id="ruta" class="textField"><option value="">Todas</option>';
		foreach ($array_rutas as $k=>$v) { 
	    	echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
//		echo '<tr><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
//		echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>IMEI</td><td><input type="text" class="textField" name="imei" id="imei"></td>';
		echo '<tr><td>Placa</td><td><input type="text" class="textField" name="placa" id="placa"></td>';
		echo '<tr><td>Nombre</td><td><input type="text" class="textField" name="nom" id="nom"></td>';
//		echo '<td>Apodo:</td><td><select name="apodo" id="apodo">' . $apodos . '</select></td></tr>';
		//echo '<tr style="display:none;"><td>Id Tipo Evento</td><td><input type="text" class="textField" size="5" name="idtipo" id="idtipo"></td></tr>';
//	echo '<tr style="display:none;"><td>Empresa</td><td><select name="empresa" id="empresa"><option value="0">--Seleccione--</option>';
//		foreach($array_empresa as $k=>$v){
//			echo '<option value="'.$k.'">'.$v.'</option>';
//		}
//		echo '</select></td>
		echo'</tr>';
		echo '</table>
		';
		echo '<br>';
		//echo 'El numeo de credencial parpadeando significa que no tiene asignacion vigente';
		//Listado
		echo '<div id="Resultados">';

		echo '</div>';



/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">
	function encadenar(id){
		var checked = $("#candado" + id).is(":checked") ? 1 : 0;

		$.post(
			"auto_dispositivos.php",
			{
				ajax: 2,
				id: id,
				checked: checked
			},
			function(json){
				if(json.validado)
					alert("El candado ah sido " + json.check);
				else
					alert("Problemas al activar/desactivar el candado");
			},
			"json"
		);
	}

	function buscarRegistros(){
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else{
			objeto.open("POST","auto_dispositivos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&placa="+document.getElementById("placa").value+"&imei="+document.getElementById("imei").value+"&ruta="+document.getElementById("ruta").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
			objeto.onreadystatechange = function(){
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}//buscarRegistros();
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}';
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	</Script>';
	}
 ?>
<?
bottom();
?>
