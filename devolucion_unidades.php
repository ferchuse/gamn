<?
include("main.php");
$tipo_vehiculo=3;

$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
	$array_nomusuario[$Usuario['cve']]=$Usuario['nombre'];
}

$array_derroteros=array();
$res=mysql_db_query($base,"SELECT * FROM derroteros ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_derroteros[$row['cve']]=$row['nombre'];
	$array_cuenta[$row['cve']]=$row['monto_cuenta'];
}

$array_propietario=array();
$res=mysql_db_query($base,"SELECT * FROM ".$pre."propietarios ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_propietario[$row['cve']]=$row['nombre'];
}

$rsUnidad=mysql_db_query($base,"SELECT * FROM parque");
while($Unidad=mysql_fetch_array($rsUnidad)){
	$array_unidad[$Unidad['cve']]=$Unidad['no_eco'].$Unidad['letra'];
	$array_uniderrotero[$Unidad['cve']]=$Unidad['derrotero'];
	$array_unipropietario[$Unidad['cve']]=$array_propietario[$Unidad['propietario']];
}

$res=mysql_db_query($base,"SELECT * FROM conductores");
while($row=mysql_fetch_array($res)){
	$array_conductor[$row['cve']]=$row['credencial'];
	$array_nomconductor[$row['cve']]=$row['nombre'];
	$array_mutuconductor[$row['cve']]=$row['mutualidad'];
}


if($_POST['ajax']==1){
	$filtro="";
	$select= " SELECT a.* FROM devolucion_unidades as a INNER JOIN parque as b ON (b.cve=a.unidad";
	if(trim($_POST['no_eco'])!="")$select.=" AND b.no_eco='".strtoupper($_POST['no_eco'])."'";
	$select.=") WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' and a.hora between '".$_POST['hora_ini']."' and '".$_POST['hora_fin']."' ";
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	if($_POST['orden']==0 && $_POST['tipoorden']==1){
		$select.=" ORDER BY a.cve DESC";
		$tipoorden0=0;
		$tipoorden1=0;
	}
	elseif($_POST['orden']==0 && $_POST['tipoorden']==0){
		$select.=" ORDER BY a.cve";
		$tipoorden0=1;
		$tipoorden1=0;
	}
	elseif($_POST['orden']==1 && $_POST['tipoorden']==1){
		$select.=" ORDER BY b.no_eco DESC,a.cve DESC";
		$tipoorden0=1;
		$tipoorden1=0;
	}
	elseif($_POST['orden']==1 && $_POST['tipoorden']==0){
		$select.=" ORDER BY b.no_eco,a.cve DESC";
		$tipoorden0=1;
		$tipoorden1=1;
	}
//	echo''.$select.'';
	$rsabonos=mysql_db_query($base,$select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		echo '<input type="hidden" name="tipoorden" id="tipoorden" value="'.$_POST['tipoorden'].'">';
		echo '<input type="hidden" name="orden" id="orden" value="'.$_POST['orden'].'">';
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$col=15;
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		echo '<th><a href="#" onClick="buscarRegistros(0,'.$tipoorden0.')">Folio</a></th><th>Fecha</th><th>Recaudacion</th>
		<th><a href="#" onClick="buscarRegistros(1,'.$tipoorden1.')">Unidad</a></th>
		<th>Tarjeta</th>
		<th>Fecha Tarjeta</th>
		<th>Cuenta</th>
		<th>Ingreso</th>
		<th>Devolucion</th>
		<th>Usuario<br>';
		echo '<select name="usuario" onchange="document.forma.usu.value=this.value;buscarRegistros('.$_POST['orden'].','.$_POST['tipoorden'].');"><option value="all">---Todos---</option>';
		$res1=mysql_db_query($base,"SELECT a.usuario FROM devolucion_unidades as a WHERE 1 $filtro GROUP BY a.usuario ORDER BY a.usuario");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['usuario'].'"';
			if($row1['usuario']==$_POST['usu']) echo ' selected';
			echo '>'.$array_usuario[$row1['usuario']].'</option>';
		}
		echo '</select></th>';
		echo '</tr>'; 
		$array_total=array();
		$x=0;
		while ($Abono=mysql_fetch_array($rsabonos)){	
			$fac=1;
			rowb();
			$estatus='';
			if($Abono['estatus']=='C'){
				$fac=0;
				$estatus='(CANCELADO)';
				$Abono['monto']=0;
				if($_SESSION['CveUsuario']==1)
					echo '<td align="center">CANCELADO<br>'.$array_usuario[$Abono['usucan']].'</td>';
				else
					echo '<td align="center">CANCELADO</td>';
			}
			else{
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'devolucion_unidades.php\',\'\',\'201\','.$Abono['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['cve'].'"></a>';
				echo '</td>';
			}
			echo '<td align="center">'.$Abono['cve'].'</td>';
			echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';
			echo '<td align="center">'.$Abono['recaudacion'].'</td>';
			echo '<td align="center">'.$array_unidad[$Abono['unidad']].'</td>';
			echo '<td align="center">'.$Abono['tarjeta'].'</td>';
			echo '<td align="center">'.$Abono['fecha_tarjeta'].'</td>';
			echo '<td align="right">'.number_format($Abono['cuenta']*$fac,2).'</td>';
			echo '<td align="right">'.number_format($Abono['ingreso']*$fac,2).'</td>';
			echo '<td align="right">'.number_format($Abono['monto']*$fac,2).'</td>';
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$array_total[0]+=$Abono['cuenta']*$fac;
			$array_total[1]+=$Abono['ingreso']*$fac;
			$array_total[2]+=$Abono['monto']*$fac;
		}
		$col=6;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		foreach($array_total as $v)
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
		echo '<td bgcolor="#E9F2F8" colspan="1">&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
	}
	else {
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


if($_POST['cmd']==201){
	$res = mysql_db_query($base,"SELECT * FROM devolucion_unidades WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	
	$varimp="|Folio: ".$row['cve']."|";
	$varimp.="Usuario: ".$array_usuario[$row['usuario']].'|';
	$varimp.=$row['fecha']." ".$row['hora']."||";
	$varimp="|Folio Rec: ".$row['recaudacion']."|";
	$varimp.="Unidad: ".$array_unidad[$row['unidad']]."|";
	$varimp="|Folio Tarjeta: ".$row['tarjeta']."|";
	$varimp.="Fecha Tarjeta: ".$row['fecha_tarjeta'].'|';
	$varimp.="Cuenta: $ ".number_format($row['cuenta'],2)."|";
	$varimp.="Ingreso: $ ".number_format($row['ingreso'],2)."|";
	$varimp.="Devolucion: $ ".number_format($row['monto'],2)."|";
	$varimp.=chr(29)."h".chr(80).chr(29)."H".chr(2).chr(29)."k".chr(2)."1".sprintf("%011s",(intval($row['cve'])))." |";
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$varimp.'&copia=1&logo=GAMN" width=200 height=200></iframe>';
	/*$varimp2="|Folio Mutualidad: ".$row['folio']."|";
	$varimp2.="Usuario: ".$array_usuario[$row['usuario']].'|';
	$varimp2.="Fecha Cuenta: ".$row['fecha_cuenta'].'|';
	$varimp2.=$row['fecha']." ".$row['hora']."||";
	$varimp2.="Unidad: ".$array_unidad[$row['unidad']]."|";
	$varimp2.="Propietario: ";
	$varimp2.=$array_unipropietario[$row['unidad']]."|";
	$varimp2.="Mutualidad: $ ".number_format($row['mutualidad'],2)."|";
	$impresion='<iframe src="http://localhost/imp_gamn.php?textoimp='.$varimp.'&textoimp2='.$varimp2.'&copia=1&logo=GAMN" width=200 height=200></iframe>';*/
	
	
	$_POST['cmd']=0;
}



	if ($_POST['cmd']<1) {
		if($impresion != ""){
			echo '<div style="visibility:hidden;position:absolute;">'.$impresion.'</div>';
		}
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
			  </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>No. Economico</td><td><input type="text" size="5" class="textField" name="no_eco" id="no_eco"></td></tr>';
		echo  '<tr><td>Hora Inicial</td><td><input type="time" name="hora_ini" id="hora_ini" value="00:01" step="1"></td></tr>';
		echo  '<tr><td>Hora Final</td><td><input type="time" name="hora_fin"id="hora_fin" value="'.horaLocal().'" step="1"></td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
bottom();
echo '
<Script language="javascript">

	function buscarRegistros(orden,tipoorden)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","devolucion_unidades.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&orden="+orden+"&tipoorden="+tipoorden+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&no_eco="+document.getElementById("no_eco").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&hora_fin="+document.getElementById("hora_fin").value+"&hora_ini="+document.getElementById("hora_ini").value);
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
			buscarRegistros(0,1); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	function validanumero(campo) {
		var ValidChars = "0123456789.-";
		var cadena=campo.value;
		var cadenares="";
		var digito;
		for(i=0;i<cadena.length;i++) {
			digito=cadena.charAt(i);
			if (ValidChars.indexOf(digito) != -1)
				cadenares+=""+digito;
		}
		campo.value=cadenares;
	}

	</Script>
';

?>