<?php
session_start();
include ("main.php");

if($_POST['ajax']==1){

	mysql_select_db('gamn_gps_otra_plataforma');
	$select= " SELECT * FROM telefonia WHERE 1";
	if($_POST['nombre']!="") $select .= " AND nombre like '%".$_POST['nombre']."'%";
	$select .= " ORDER BY nombre ";
	$res=mysql_query($select) or die(mysql_error());
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8">
		  <th width="50">&nbsp;</th><th>Nombre</th>
		  </tr>';
	while($row=mysql_fetch_array($res)){
		rowb();
		echo'<td align="center"><a href="#" onClick="atcr(\'telefonia2.php\',\'\',\'1\','.$row['cve'].')">'.$imgeditar.'</a></td>';

		echo'<td align="left">'.$row['nombre'].'</td>';

		echo'</tr>';
	}
	echo'<tr bgcolor="#E9F2F8"><td align="left" colspan="5">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table>';
exit();
}

 top($_SESSION);
  if($_POST['cmd']==2){
  		mysql_select_db('gamn_gps_otra_plataforma');
	  $str_error = '';
	if ($_POST['reg']>0){
        $select=" SELECT * FROM telefonia WHERE cve='".$_POST['reg']."' ";
       $res=mysql_query($select);
       $row=mysql_fetch_array($res);
	   if($Usuario['nombre']!=$_POST['nombre']){
			mysql_query("INSERT historial SET tabla='telefonia',registro='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			campo='Nombre',nuevo='".$_POST['nombre']."',anterior='".$row['nombre']."',usuario='".$_POST['cveusuario']."'");
		}

		$sSQL="update telefonia
				SET nombre='" . $_POST['nombre'] . "' where cve='".$_POST['reg']."'";
		mysql_query($sSQL);
	}else{
			$sSQL="INSERT telefonia
					SET nombre='" . $_POST['nombre'] . "'";
			mysql_query($sSQL);
			$cveusu=mysql_insert_id();
	}

	$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	mysql_select_db('gamn_gps_otra_plataforma');
	    $select=" SELECT * FROM telefonia WHERE cve='".$_POST['reg']."' ";
	    $res=mysql_query($select);
	    $row=mysql_fetch_array($row);
	

    echo'
    	<a href="#" onClick="atcr(\'telefonia2.php\',\'\',\'2\',\''.$row['cve'].'\');">&nbsp;'.$imgguardar.'&nbsp;Guardar</a>&nbsp;&nbsp;
	    <a href="#" onClick="atcr(\'telefonia2.php\',\'\',\'0\',\'0\');">'.$imgvolver.'&nbsp;Volver </a>
		</br></br>
		<table>
		<tr>';
		echo'<tr><td>Nombre:</td><td><input type="text" name="nombre" id="nombre" value="' . $row['nombre'] . '"/></td></tr>';
		
		echo'</table>';
		
}

 if ($_POST['cmd']<1) {
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
				<a href="#" onClick="atcr(\'telefonia2.php\',\'\',\'1\',\'0\');" id="nuevo" name="nuevo" ><img src="images/nuevo.gif"></a>Nuevo</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Nombre:</td><td><input type="text" class="textField" size="" name="nombre" id="nombre"></td>';
		echo'</tr>';
		echo '</table>
		';
		echo '<br>';
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
			objeto.open("POST","telefonia2.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nombre="+document.getElementById("nombre").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
