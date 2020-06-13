<?php
session_start();
include ("main.php");

$rsMotivo=mysql_query("SELECT * FROM plazas ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_plaza[$Motivo['cve']]=$Motivo['nombre'];
}
$array_estatus=array(1=>'Alta',2=>'Baja');
if($_POST['ajax']==1){
	mysql_select_db('gamn_gps_otra_plataforma');

	//	$select= " SELECT * FROM sms_comandos WHERE plaza = '{$_SESSION['plaza_seleccionada']}'";
	if($_POST['plazausuario'] == 1)
		$select= " SELECT * FROM rutas WHERE 1";
	else
		$select= " SELECT * FROM rutas WHERE plaza IN (0, ".$_POST['plazausuario'].")";
	if($_POST['comando']!="") $select .= " AND nombre like '%".$_POST['comando']."'%";
//	else if($_POST['apodo']!=0) $select .= " AND id = '".$_POST['apodo']."'";
	//if($_POST['empresa']!="") $select .= " AND empresa= '".$_POST['empresa']."'";
	$select .= " ORDER BY cve asc";
	$res=mysql_query($select) or die(mysql_error());
	//echo''.$select.'';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8">
		  <th width="50"></th><th>Ruta</th><th>Empresa</th>
		  </tr>';
	while($row=mysql_fetch_array($res)){
		rowb();
		echo'<td align="center"><a href="#" onClick="atcr(\'rutas2.php\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
//	echo'<td align="left"></td>';

		echo'<td align="left">'.$row['nombre'].'</td>';
		echo'<td align="left">'.$array_plaza[$row['plaza']].'</td>';
		echo'</tr>';
	}
	echo'<tr bgcolor="#E9F2F8"><td align="left" colspan="5">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table>';
exit();
}
mysql_select_db('gamn_gps');
 top($_SESSION);
  if($_POST['cmd']==2){
  	mysql_select_db('gamn_gps_otra_plataforma');
	  $str_error = '';
	if ($_POST['reg']>0){
		$sSQL="update rutas
				SET plaza='" . $_POST['plaza'] . "' where cve='".$_POST['reg']."'";
		mysql_query($sSQL);
	}

	if($str_error != '')
		$_POST['cmd'] = 1;
	else
		$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	mysql_select_db('gamn_gps_otra_plataforma');
	    $select=" SELECT * FROM rutas WHERE cve='".$_POST['reg']."' ";
	    $rsprovedor=mysql_query($select);
	    $provedor=mysql_fetch_array($rsprovedor);
	

    echo'
	    <a href="#" onClick="atcr(\'rutas2.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver </a>
		<a href="#" onClick="atcr(\'rutas2.php\',\'\',\'2\',\''.$provedor['cve'].'\');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br>
		<table>
		<tr>';
		echo'<tr><td>Nombre:</td><td>' . $provedor['nombre'] . '</td></tr>';
		echo '<tr><td>Empresa:</td><td>';
		if($provedor['plaza']==0){
			echo '<select name="plaza" id="plaza"><option value="0">Seleccione</option>';
			foreach($array_plaza as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
			echo '</select>';
		}
		else{
			echo $array_plaza[$provedor['plaza']].'<input type="hidden" name="plaza" id="plaza" value="'.$provedor['plaza'].'">';
		}
		echo '</td></tr>';
		echo'</table>';
		echo '
<Script language="javascript">
	$(document).ready(function(){';

	if(isset($str_error) && $str_error != '')
		echo 'alert("' . $str_error . '")';
	echo '
	});
	 
	</Script>';
		}

 	if ($_POST['cmd']<1) {
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Ruta:</td><td><input type="text" class="textField" size="" name="comando" id="comando"></td>';
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
			objeto.open("POST","rutas2.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&comando="+document.getElementById("comando").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
