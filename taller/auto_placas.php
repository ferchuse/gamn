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


if($_POST['ajax']==1){
$candado = array(
	0 => 'Desactivado',
	1 => 'Activado'
);

$candadoEstilo = array(
	0 => 'red',
	1 => 'green'
); 
$base='gamn_gps_skymedia';
	$selec= " SELECT column_name FROM information_schema.columns
WHERE table_schema = 'gamn_gps_skymedia' 
AND table_name = 'placas' ";
	$re=mysql_db_query($base,$selec) or die(mysql_error());
//	echo''.$select.'';
	//echo '<div style="height: 350px; overflow: auto;">';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			
			echo'<tr bgcolor="#E9F2F8">';
			while($row=mysql_fetch_array($re)){
			$i=0;
	      echo'
			<th>'.$row[$i].'</th>';
				$i++;
			}
			echo'</tr>';
			   
	$select= " SELECT * FROM placas
	            WHERE 1 ";
		//	if($_POST['no_eco']!="") $select .= " AND a.idunidad = '".$array_cveeconomico[$_POST['no_eco']]."'";
//	if($_POST['folio']!="") $select .= " AND a.id = '".$_POST['folio']."'";
//	if($_POST['terminal']!="") $select .= " AND a.idterminal = '".$_POST['terminal']."'";
	if($_POST['id_placa']!="") $select .= " AND idplaca= '".$_POST['id_placa']."'";
	if($_POST['imei']!="") {$select .= " AND idtracker='".$_POST['imei']."'";}else{if($_POST['plaza']==1){$plaz=" and idempresa='4'";} }
	//if($_POST['usu']!="") $select .= " AND b.nombre='".$_POST['usu']."'";
	$select .= " ".$plaz." ORDER BY idplaca desc";
	$res=mysql_db_query($base,$select) or die(mysql_error());
//	echo''.$select.'';

	while($row=mysql_fetch_array($res)){
	rowb();
	
		echo'<td align="center">'.$row[0].'</td>';
		echo'<td align="center">'.$row[1].'</td>';
		echo'<td align="center">'.$row[2].'</td>';
		echo'<td align="center">'.$row[3].'</td>';
		echo'<td align="center">'.$row[4].'</td>';
		echo'<td align="center">'.$row[5].'</td>';
		echo'<td align="center">'.$row[6].'</td>';
		echo'<td align="center">'.$row[7].'</td>';
		echo'<td align="center">'.$row[8].'</td>';
		echo'<td align="center">'.$row[9].'</td>';
		echo'<td align="center">'.$row[10].'</td>';
		echo'<td align="center">'.$row[11].'</td>';
		echo'<td align="center">'.$row[12].'</td>';
		echo'<td align="center">'.$row[13].'</td>';
		echo'<td align="center">'.$row[14].'</td>';
		echo'<td align="center">'.$row[15].'</td>';
		echo'<td align="center">'.$row[16].'</td>';
		echo'<td align="center">'.$row[17].'</td>';
		echo'<td align="center">'.$row[18].'</td>';
		echo'<td align="center">'.$row[19].'</td>';
		echo'<td align="center">'.$row[20].'</td>';
		echo'<td align="center">'.$row[21].'</td>';
		echo'<td align="center">'.$row[22].'</td>';
		echo'<td align="center">'.$row[23].'</td>';
		echo'<td align="center">'.$row[24].'</td>';
		echo'<td align="center">'.$row[25].'</td>';
		echo'<td align="center">'.$row[26].'</td>';
		echo'<td align="center">'.$row[27].'</td>';
		echo'<td align="center">'.utf8_encode($row[28]).'</td>';
		echo'<td align="center">'.$row[29].'</td>';
		echo '<td align="center" style="color: ' . $candadoEstilo[$row[30]] . ';">'.$candado[$row[30]].'</td>';
		
		
		echo'</tr>';		 

	}
	echo'<tr bgcolor="#E9F2F8"><td colspan="18" align="left">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table>';
	 //echo '</div>';
exit();
}


 top($_SESSION);


 	if ($_POST['cmd']<1) {
	if($_POST['plazausuario']==1){$plaz=" and idempresa='4'";}else{$plaz="";}
		$base='gamn_gps_skymedia';
		$select="SELECT * FROM placas where 1 ".$plaz." order by idplaca asc";
		$rsMotivo=mysql_db_query($base,$select);
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_imei[$Motivo['idplaca']]=$Motivo['idtracker'];
}
		//Busqueda
		echo '<table><input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	//	echo''.$select.'';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>

			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr style="display:none;"><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr style="display:none;"><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		//echo '<tr><td>No Eco</td><td><input type="text" class="textField" size="5" name="no_eco" id="no_eco"></td></tr>';
		echo '<tr><td>Id</td><td><input type="text" class="textField" size="5" name="id_placa" id="id_placa"></td></tr>';
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
		echo '<tr><td>Imei</td><td><select name="imei" id="imei"><option value="">Todos</option>';
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
			objeto.open("POST","auto_placas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&imei="+document.getElementById("imei").value+"&plaza="+document.getElementById("plaza").value+"&id_placa="+document.getElementById("id_placa").value);
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
