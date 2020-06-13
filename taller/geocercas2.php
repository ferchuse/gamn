<?php
session_start();
include ("main.php");


$res = mysql_query("SELECT usuario FROM plazas WHERE cve = '".$_POST['plazausuario']."' ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$usuarioempresa = $row[0];

$array_tipo = array('Recorrido', 'Especiales', 'Taller');
$direccion = array(0=>'Ida', 1=>'Vuelta');

mysql_select_db('gamn_gps_otra_plataforma');
if($_POST['plazausuario'] == 1)
	$rsMotivo=mysql_query("SELECT * FROM rutas WHERE 1 ORDER BY nombre");
else
	$rsMotivo=mysql_query("SELECT * FROM rutas WHERE plaza IN (0, ".$_POST['plazausuario'].") ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_rutasgps[$Motivo['base']][$Motivo['cvebase']]=$Motivo['nombre'];
}

if($_POST['ajax']==1){
	mysql_select_db('gamn_gps_otra_plataforma');
	$filtroruta='(';
	foreach($array_rutasgps as $base=>$rutas){
		if($filtroruta!='(') $filtroruta.=" OR ";
		$filtroruta .= "(base = '$base' AND ruta IN (";
		$primero = true;
		foreach($rutas as $k=>$v){
			if(!$primero) $filtroruta.=",";
			$filtroruta.="'".$k."'";
			$primero = false;
		}
		$filtroruta.="))";
	}
	$filtroruta.=")";
	$select= " SELECT * FROM geocercas WHERE ruta = 0 OR $filtroruta";
	if($_POST['nom']!="") $select .= " AND nombre like '%".$_POST['nom']."%'";
	if($_POST['ruta']!=""){
		$ruta = explode(',',$_POST['ruta']);
		$select .= " AND base='".$ruta[0]."' AND ruta= '".$ruta[1]."'";
	}
	$select .= " ORDER BY orden";
	$res=mysql_query($select);
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8">
		  <th width="50">&nbsp;</th><th>Cve</th><th>Nombre</th><th>Descripcion</th><th>Ruta</th><th>Tipo</th><th>Minutos</th><th>Orden</th><th>Direccion</th>';
		  if($_POST['cveusuario']==1) echo '<th>Borrar</th>';
	echo '
		  </tr>';
	while($row=mysql_fetch_array($res)){
		rowb();
		echo'<td align="center"><a href="#" onClick="atcr(\'geocercas2.php\',\'\',\'1\','.$row['cve'].')">'.$imgeditar.'</a></td>';
		echo'<td align="center">'.$row['cve'].'</td>';
		echo'<td align="center">'.$row['cvebase'].' '.$row['codigo'].'</td>';
		echo'<td align="center">'.$row['nombre'].'</td>';
		echo'<td align="center">'.$array_rutasgps[$row['base']][$row['ruta']].'</td>';
		echo'<td align="center">'.$array_tipo[$row['tipo']].'</td>';
		echo'<td align="center">'.$row['minutos'].'</td>';
		echo'<td align="center">'.$row['orden'].'</td>';
		echo'<td align="center">'.$direccion[$row['direccion']].'</td>';
		echo'</tr>';
	}
	echo'<tr bgcolor="#E9F2F8"><td align="left" colspan="10">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table>';
	 mysql_select_db('gamn_gps');
	exit();
}

 mysql_select_db('gamn_gps');

 top($_SESSION);

  if($_POST['cmd']==2){
	 mysql_select_db('gamn_gps_otra_plataforma');
	$sSQL="update geocercas
			SET orden='".$_POST['orden']."',tipo='".$_POST['tipo']."',minutos='".$_POST['minutos']."',duracion='".$_POST['duracion']."', direccion='".$_POST['direccion']."'	where cve='".$_POST['reg']."'";
	mysql_query($sSQL);
	mysql_select_db('gamn_gps');
	$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	mysql_select_db('gamn_gps_otra_plataforma');
	    $select=" SELECT * FROM geocercas WHERE cve='".$_POST['reg']."' ";
	    $rsprovedor=mysql_query($select);
	    $provedor=mysql_fetch_array($rsprovedor);
	

    echo'
	    <a href="#" onClick="atcr(\'geocercas2.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver </a>
		<a href="#" onClick="atcr(\'geocercas2.php\',\'\',\'2\',\''.$provedor['cve'].'\');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br>
		<table>
		<tr>';
		echo'<tr><td>Nombre</td><td>' . $provedor['codigo'] . '</td></tr>';
		echo'<tr><td>Descripcion</td><td>' . $provedor['nombre'] . '</td></tr>';
		
		echo '<tr><td>Ruta</td><td>' . $array_rutasgps[$provedor['base']][$provedor['ruta']] . '</td></td></tr>';
		echo '<tr><td>Tipo</td><td><select name="tipo" id="tipo">';
		foreach($array_tipo as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$provedor['tipo']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo'<tr><td>Minutos</td><td><input type="text" class="textField" size="5" name="minutos" id="minutos" value="'.$provedor['minutos'].'"></td></tr>';
		echo'<tr><td>Orden</td><td><input type="text" class="textField" size="5" name="orden" id="orden" value="'.$provedor['orden'].'"></td></tr>';
		echo'<tr><td>Duracion en Minutos</td><td><input type="text" class="textField" size="5" name="duracion" id="duracion" value="'.$provedor['duracion'].'"></td></tr>';
		echo '<tr><td>Direccion</td><td><select name="direccion" id="direccion">';
		foreach($direccion as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$provedor['direccion']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		
		echo'</table>';
		mysql_select_db('gamn_gps');
	}

 	if ($_POST['cmd']<1) {
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Ruta</td><td><select name="ruta" id="ruta" class="textField"><option value="">Todas</option>';
		foreach ($array_rutasgps as $base=>$rutas) { 
			foreach ($rutas as $k=>$v) { 
	    		echo '<option value="'.$base.','.$k.'">'.$v.'</option>';
	    	}
		}
		echo '</select></td></tr>';
		echo '<tr><td>Nombre</td><td><input type="text" class="textField" name="nom" id="nom"></td>';
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
	function buscarRegistros(){
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else{
			objeto.open("POST","geocercas2.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&ruta="+document.getElementById("ruta").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
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
