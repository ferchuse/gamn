<?php
session_start();
include ("main.php");


$res = mysql_query("SELECT usuario FROM plazas WHERE cve = '".$_POST['plazausuario']."' ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$usuarioempresa = $row[0];

mysql_select_db('gamn_gps_otra_plataforma');
if($usuarioempresa != '')
	$rsMotivo=mysql_query("SELECT * FROM rutas WHERE plaza='".$_POST['plazausuario']."' ORDER BY nombre");
else
	$rsMotivo=mysql_query("SELECT * FROM rutas WHERE plaza='".$_POST['plazausuario']."' ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_rutas[$Motivo['cve']]=$Motivo['nombre'];
}
mysql_select_db('gamn_gps');
if($_POST['ajax']==1){
	mysql_select_db('gamn_gps_otra_plataforma');
	$select= " SELECT * FROM geocercas WHERE plaza='".$_POST['plazausuario']."'";
//	if($usuarioempresa != '') $select .= " AND usuario='$usuarioempresa'";
//	if($_POST['imei']!="") $select .= " AND imei = '".$_POST['imei']."'";
//	if($_POST['usuario']!="") $select .= " AND usuario = '".$_POST['usuario']."'";
	if($_POST['nom']!="") $select .= " AND nombre like '%".$_POST['nom']."%'";
	if($_POST['ruta']!="") $select .= " AND ruta= '".$_POST['ruta']."'";
	$select .= " ORDER BY orden";
	$res=mysql_query($select) or die(mysql_error());
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8">
		  <th>Nombre</th><th>Codigo</th><th>Ruta</th>';
//		  if($_POST['cveusuario']==1) echo '<th>Borrar</th>';
	echo '
		  </tr>';
	while($row=mysql_fetch_array($res)){
		rowb();
	//	echo'<td align="center"><a href="#" onClick="atcr(\'n_interfaz_geocercas.php\',\'\',\'1\','.$row['cve'].')">'.$imgeditar.'</a></td>';
//	echo'<td align="left"></td>';

		echo'<td align="center">'.$row['nombre'].'</td>';
		echo'<td align="center">'.$row['codigo'].'</td>';
		echo'<td align="center">'.$array_rutas[$row['ruta']].'</td>';
	//	echo'<td align="center">'.$row['orden'].'</td>';
	//	echo'<td align="center">'.$row['usuario'].'</td>';
		//if($_POST['cveusuario']==1)
		//	echo'<td align="center"><a href="#" onClick="if(confirm(\'Esta seguro de eliminar el registro?\'))atcr(\'n_interfaz_geocercas.php\',\'\',\'3\','.$row['cve'].')">'.$imgborrar.'</a></td>';
		echo'</tr>';
	}
	echo'<tr bgcolor="#E9F2F8"><td align="left" colspan="8">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table>';
	 mysql_select_db('gamn_gps');
	exit();
}



 top($_SESSION);
   if($_POST['cmd']==3){
   	mysql_select_db('gamn_gps_otra_plataforma');
	$sSQL="delete from geocercas where cve='".$_POST['reg']."'";
	mysql_query($sSQL);
	mysql_select_db('gps');
   	$_POST['cmd']=0;
   }
  if($_POST['cmd']==2){
	 mysql_select_db('gamn_gps_otra_plataforma');
	$sSQL="update geocercas
			SET ruta='".$_POST['ruta']."',orden='".$_POST['orden']."'	where cve='".$_POST['reg']."'";
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
	    <a href="#" onClick="atcr(\'n_interfaz_geocercas.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver </a>
		<a href="#" onClick="atcr(\'n_interfaz_geocercas.php\',\'\',\'2\',\''.$provedor['cve'].'\');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br>
		<table>
		<tr>';
		echo'<tr><td>Nombre</td><td>' . $provedor['nombre'] . '</td></tr>';
		echo'<tr><td>Empresa</td><td>' . $provedor['usuario'] . '</td></tr>';
		
		echo '<tr><td>Ruta</td><td><select name="ruta" id="ruta" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_rutas as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($provedor['ruta']==$k){echo'selected';}echo'>'.$v.'</option>';
			}
		echo '</select></td></tr>';
		echo'<tr><td>Orden</td><td><input type="text" class="textField" size="5" name="orden" id="orden" value="'.$provedor['orden'].'"></td></tr>';
		
		echo'</table>';
		mysql_select_db('gps');
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
		echo '<tr';
		if($usuarioempresa!='') echo ' style="display:none;"';
		echo '><td>Empresa</td><td><select name="usuario" id="usuario"><option value="">Todas</option>';
		mysql_select_db('gamn_gps_otra_plataforma');
		$res = mysql_query("SELECT usuario FROM geocercas GROUP BY usuario ORDER BY usuario");
		while($row = mysql_fetch_array($res)) echo '<option value="'.$row[0].'">'.$row[0].'</option>';
		mysql_select_db('gamn_gps');
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
			objeto.open("POST","n_interfaz_geocercas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&usuario="+document.getElementById("usuario").value+"&ruta="+document.getElementById("ruta").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
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
