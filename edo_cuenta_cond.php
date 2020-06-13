<?
include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsUsuario=mysql_db_query($base,"SELECT * FROM ".$pre."usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}


$rsconductor=mysql_db_query($base,"SELECT * FROM ".$pre."conductores");
while($Conductor=mysql_fetch_array($rsconductor)){
	$array_conductor[$Conductor['cve']]='('.$Conductor['credencial'].') '.$Conductor['nombre'];
	$array_nomconductor[$Conductor['cve']]=$Conductor['nombre'];
}

if($_POST['ajax']==1){
	$select= " SELECT * FROM ".$pre."conductores WHERE 1 ";
	if ($_POST['clave']!="") { $select.=" AND credencial='".$_POST['clave']."'"; }
	if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%'"; }
	if ($_POST['estatus']!="all") { $select.=" AND estatus='".$_POST['estatus']."'"; }
	$select.=" ORDER BY nombre";
	$rsconductor=mysql_db_query($base,$select);
	if(mysql_num_rows($rsconductor)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="10">'.mysql_num_rows($rsconductor).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8">';
		echo '<th>Conductor</th><th>Estatus</th><th>Fecha Estatus</th><th>Cargos<th>Abonos</th><th>Saldo</th>';
		echo '</tr>';
		$i=0;
		$x=0;
		$cargos=0;
		$abonos=0;
		$saldo_favor=0;
		while($Conductor=mysql_fetch_array($rsconductor)) {
			rowb();
			echo '<td align="left">('.$Conductor['credencial'].') '.htmlentities(utf8_encode($Conductor['nombre'])).'</td>';
			echo '<td align="center">'.$array_estatus_parque[$Conductor['estatus']].'</td>';
			echo '<td align="center">'.$Conductor['fecha_sta'].'</td>';
			echo '<td align="right"> </td>';
			echo '<td align="right"> </td>';
			echo '<td align="right"> </td>';
			echo '</tr>';
			$i++;
		}
		echo '	
			<tr>
			<td colspan="3" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
			<td bgcolor="#E9F2F8" align="right">Totales</td>
			<td bgcolor="#E9F2F8" align="right"> </td>
			<td bgcolor="#E9F2F8" align="right"> </td>
			<td bgcolor="#E9F2F8" align="right"> </td>
			</tr>
		</table>';
	} else {
		echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
	}
	exit();	
}

top($_SESSION);

if($_POST['cmd']<1){
	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
				<!--<td><a href="#" onclick="validar_seleccion2();"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td>-->
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.substr(fechaLocal(),0,8).'01'.'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus" class="textField"><option value="all">---Todos---</option>';
		foreach($array_estatus_conductores as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==1) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Clave Conductor</td><td><input type="text" class="textField" name="clave" id="clave" size="10"></td></tr>';
		echo '<tr><td>Nombre Conductor</td><td><input type="text" class="textField" name="nom" id="nom" size="10"></td></tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}

}
bottom();
echo '
<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","edo_cuenta_cond.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&clave="+document.getElementById("clave").value+"&estatus="+document.getElementById("estatus").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}

	echo '
	
	</Script>
';


?>