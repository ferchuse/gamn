<?php
include("main.php");

$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios WHERE estatus!='I'");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
	$array_nomusuario[$Usuario['cve']]=$Usuario['nombre'];
}

$array_taquilla=array();
$res=mysql_db_query($base,"SELECT * FROM taquillas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_taquilla[$row['cve']]=$row['nombre'];
}

if($_POST['ajax']==1){
	$array_usuarios = array();
	$array_datos = array();
	$filtro = "";
	$filtro2= "";
	$filtro3="";
	$filtro4="";
	if($_POST['usuario'] > 0){
		$filtro .= " AND usuario = '".$_POST['usuario']."'";
		$filtro2 .=" AND a.usu = '".$_POST['usuario']."'";
		$filtro3 .= " AND id_usuario = -1";
		$filtro4 .= " AND idusuario = -1";
	}
	elseif($_POST['usuario'] < 0){
		$filtro .= " AND usuario = -1";
		$filtro2 .= " AND a.usu = -1";
		$filtro3 .= " AND id_usuario = '".abs($_POST['usuario'])."'";
		$filtro4 .= " AND idusuario = '".abs($_POST['usuario'])."'";
	}
	$filtrofecha="fecha";
	if($_POST['hora_ini']!='' && $_POST['hora_fin']!=''){
		$filtrofecha="CONCAT(fecha,' ',hora)";
		$_POST['fecha_ini'].= ' '.$_POST['hora_ini'];
		$_POST['fecha_fin'].=' '.$_POST['hora_fin'];
	}

	$res = mysql_query("SELECT usuario,MIN(CONCAT(fecha,' ',hora)),MAX(CONCAT(fecha,' ',hora)),SUM(monto),SUM(mutualidad) FROM parque_abono WHERE estatus!='C' AND {$filtrofecha} BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'".$filtro." GROUP BY usuario");
	while($row = mysql_fetch_array($res)){
		$array_usuarios[$row['usuario']] = $array_usuario[$row['usuario']];
		$array_datos[$row['usuario']]['fecha_ini_abono'] = $row[1];
		$array_datos[$row['usuario']]['fecha_ini_mutualidad'] = $row[1];
		$array_datos[$row['usuario']]['fecha_fin_abono'] = $row[2];
		$array_datos[$row['usuario']]['fecha_fin_mutualidad'] = $row[2];
		$array_datos[$row['usuario']]['abono'] = $row[3];
		$array_datos[$row['usuario']]['mutualidad'] = $row[4];
	}
	$res = mysql_query("SELECT usuario,MIN(CONCAT(fecha,' ',hora)),MAX(CONCAT(fecha,' ',hora)),SUM(monto) FROM recibos_entrada WHERE estatus!='C' AND {$filtrofecha} BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'".$filtro." GROUP BY usuario");
	while($row = mysql_fetch_array($res)){
		$array_usuarios[$row['usuario']] = $array_usuario[$row['usuario']];
		$array_datos[$row['usuario']]['fecha_ini_entrada'] = $row[1];
		$array_datos[$row['usuario']]['fecha_fin_entrada'] = $row[2];
		$array_datos[$row['usuario']]['entradas'] = $row[3];
	}

	$res = mysql_query("SELECT a.usu,MIN(CONCAT(a.fecha,' ',a.hora)),MAX(CONCAT(a.fecha,' ',a.hora)),SUM(b.total) 
		FROM desglosedinero a 
		INNER JOIN 
			(SELECT cvedesg, SUM(denomin*cant) as total FROM desglosedineromov WHERE tipo!=1 GROUP BY cvedesg) b ON a.cve = b.cvedesg
		WHERE a.estatus!='C' AND {$filtrofecha} BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'".$filtro2." GROUP BY a.usu");
	while($row = mysql_fetch_array($res)){
		$array_usuarios[$row['usu']] = $array_usuario[$row['usu']];
		$array_datos[$row['usu']]['fecha_ini_desglose'] = $row[1];
		$array_datos[$row['usu']]['fecha_fin_desglose'] = $row[2];
		$array_datos[$row['usu']]['desglose'] = $row[3];
	}

	$res = mysql_query("SELECT usuario, taquilla, MIN(CONCAT(fecha,' ',hora)),MAX(CONCAT(fecha,' ',hora)),SUM(monto) FROM boletos WHERE taquilla > 0 AND estatus!='1' AND {$filtrofecha} BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'".$filtro." GROUP BY usuario, taquilla");
	while($row = mysql_fetch_array($res)){
		$array_usuarios[$row['usuario']] = $array_usuario[$row['usuario']];
		$array_datos[$row['usuario']]['taquillas'][$row['taquilla']]['fecha_ini'] = $row[2];
		$array_datos[$row['usuario']]['taquillas'][$row['taquilla']]['fecha_fin'] = $row[3];
		$array_datos[$row['usuario']]['taquillas'][$row['taquilla']]['monto'] = $row[4];
		$array_datos[$row['usuario']]['taquilla'] += $row[4];
	}
	$res = mysql_query("SELECT id_usuario, usuario, terminal, MIN(CONCAT(fecha,' ',hora)),MAX(CONCAT(fecha,' ',hora)),SUM(monto) FROM boletos_taquillamovil WHERE terminal > 0 AND estatus='A' AND {$filtrofecha} BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'".$filtro3." GROUP BY id_usuario, terminal");
	while($row = mysql_fetch_array($res)){
		$array_usuarios['-'.$row['id_usuario']] = $row['usuario'];
		$array_datos['-'.$row['id_usuario']]['taquillasmovil'][$row['terminal']]['fecha_ini'] = $row[3];
		$array_datos['-'.$row['id_usuario']]['taquillasmovil'][$row['terminal']]['fecha_fin'] = $row[4];
		$array_datos['-'.$row['id_usuario']]['taquillasmovil'][$row['terminal']]['monto'] = $row[5];
		$array_datos['-'.$row['id_usuario']]['taquillamovil'] += $row[5];
	}
	$res = mysql_query("SELECT idusuario, usuario, terminal, MIN(CONCAT(fecha,' ',hora)),MAX(CONCAT(fecha,' ',hora)),SUM(monto) FROM abono_unidad_taquillamovil WHERE estatus='A' AND {$filtrofecha} BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'".$filtro4." GROUP BY idusuario, terminal");
	while($row = mysql_fetch_array($res)){
		$array_usuarios['-'.$row['idusuario']] = $row['usuario'];
		$array_datos['-'.$row['idusuario']]['abonotaquillasmovil'][$row['terminal']]['fecha_ini'] = $row[3];
		$array_datos['-'.$row['idusuario']]['abonotaquillasmovil'][$row['terminal']]['fecha_fin'] = $row[4];
		$array_datos['-'.$row['idusuario']]['abonotaquillasmovil'][$row['terminal']]['monto'] = $row[5];
		$array_datos['-'.$row['idusuario']]['abonotaquillamovil'] += $row[5];
	}
	asort($array_usuarios);
	foreach($array_usuarios as $k=>$v){
		$total=0;
		echo '<h3>'.$v.'</h3>';
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>Movimiento</th><th>Importe</th><th>Primer Movimiento</th><th>Ultimo Movimiento</th></tr>';
		if($array_datos[$k]['abono'] > 0){
			rowb();
			echo '<td>Abonos</td><td align="right">'.number_format($array_datos[$k]['abono'],2).'</td>';
			echo '<td align="center">'.$array_datos[$k]['fecha_ini_abono'].'</td>';
			echo '<td align="center">'.$array_datos[$k]['fecha_fin_abono'].'</td></tr>';
		}
		if($array_datos[$k]['entradas'] > 0){
			rowb();
			echo '<td>Recibos de Entrada</td><td align="right">'.number_format($array_datos[$k]['entradas'],2).'</td>';
			echo '<td align="center">'.$array_datos[$k]['fecha_ini_entrada'].'</td>';
			echo '<td align="center">'.$array_datos[$k]['fecha_fin_entrada'].'</td></tr>';
		}
		if($array_datos[$k]['mutualidad'] > 0){
			rowb();
			echo '<td>Mutualidad</td><td align="right">'.number_format($array_datos[$k]['mutualidad'],2).'</td>';
			echo '<td align="center">'.$array_datos[$k]['fecha_ini_mutualidad'].'</td>';
			echo '<td align="center">'.$array_datos[$k]['fecha_fin_mutualidad'].'</td></tr>';
		}
		if($array_datos[$k]['desglose'] > 0){
			rowb();
			echo '<td>Desglose de Dinero</td><td align="right">'.number_format($array_datos[$k]['desglose'],2).'</td>';
			echo '<td align="center">'.$array_datos[$k]['fecha_ini_desglose'].'</td>';
			echo '<td align="center">'.$array_datos[$k]['fecha_fin_desglose'].'</td></tr>';
		}
		foreach($array_datos[$k]['taquillas'] as $taquilla => $datos){
			rowb();
			echo '<td>Taquilla '.$array_taquilla[$taquilla].'</td><td align="right">'.number_format($datos['monto'],2).'</td>';
			echo '<td align="center">'.$datos['fecha_ini'].'</td>';
			echo '<td align="center">'.$datos['fecha_fin'].'</td></tr>';
		}
		foreach($array_datos[$k]['taquillasmovil'] as $taquilla => $datos){
			rowb();
			echo '<td>Taquilla Movil Terminal '.$taquilla.'</td><td align="right">'.number_format($datos['monto'],2).'</td>';
			echo '<td align="center">'.$datos['fecha_ini'].'</td>';
			echo '<td align="center">'.$datos['fecha_fin'].'</td></tr>';
		}
		foreach($array_datos[$k]['abonotaquillasmovil'] as $taquilla => $datos){
			rowb();
			echo '<td>Abono Movil Terminal '.$taquilla.'</td><td align="right">'.number_format($datos['monto'],2).'</td>';
			echo '<td align="center">'.$datos['fecha_ini'].'</td>';
			echo '<td align="center">'.$datos['fecha_fin'].'</td></tr>';
		}
		echo '<tr bgcolor="#E9F2F8"><th>Total</th>
		<th align="right">'.number_format($array_datos[$k]['abono']+$array_datos[$k]['entradas']+$array_datos[$k]['mutualidad']+$array_datos[$k]['desglose']+$array_datos[$k]['taquilla']+$array_datos[$k]['taquillamovil']+$array_datos[$k]['abonotaquillamovil'],2).'</th><th colspan="2">&nbsp;</th></tr></table>';
	}
	exit();
}
top($_SESSION);

if ($_POST['cmd']<1) {
	$array_usuarios = array();
	$res = mysql_query("SELECT usuario FROM parque_abono WHERE estatus!='C' GROUP BY usuario");
	while($row = mysql_fetch_array($res)){
		if(array_key_exists($row['usuario'], $array_usuario))
			$array_usuarios[$row['usuario']] = $array_usuario[$row['usuario']];
	}
	$res = mysql_query("SELECT usuario FROM recibos_entrada WHERE estatus!='C'  GROUP BY usuario");
	while($row = mysql_fetch_array($res)){
		if(array_key_exists($row['usuario'], $array_usuario))
			$array_usuarios[$row['usuario']] = $array_usuario[$row['usuario']];
	}
	$res = mysql_query("SELECT usu FROM desglosedinero WHERE estatus!='C'  GROUP BY usuario");
	while($row = mysql_fetch_array($res)){
		if(array_key_exists($row['usuario'], $array_usuario))
			$array_usuarios[$row['usu']] = $array_usuario[$row['usu']];
	}
	$res = mysql_query("SELECT usuario FROM boletos WHERE taquilla > 0 AND estatus!='1'  GROUP BY usuario");
	while($row = mysql_fetch_array($res)){
		if(array_key_exists($row['usuario'], $array_usuario))
			$array_usuarios[$row['usuario']] = $array_usuario[$row['usuario']];
	}
	$res = mysql_query("SELECT id_usuario, usuario FROM boletos_taquillamovil WHERE id_usuario > 0 AND estatus!='C'  GROUP BY id_usuario");
	while($row = mysql_fetch_array($res)){
		$array_usuarios['-'.$row['id_usuario']] = $row['usuario'];
	}
	$res = mysql_query("SELECT idusuario, usuario FROM abono_unidad_taquillamovil WHERE idusuario > 0 AND estatus!='C'  GROUP BY idusuario");
	while($row = mysql_fetch_array($res)){
		$array_usuarios['-'.$row['idusuario']] = $row['usuario'];
	}
	asort($array_usuarios);
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
		  </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Hora Inicial</td><td><input type="text" size="10" class="textField" name="hora_ini" id="hora_ini"><small>HH:MM</small></td></tr>';
		echo '<tr><td>Hora Final</td><td><input type="text" size="10" class="textField" name="hora_fin" id="hora_fin"><small>HH:MM</small></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="0">Todos</option>';
	foreach($array_usuarios as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo '<br>';
	//Listado
	echo '<div id="Resultados">';
	echo '</div>';
	echo '
	<Script language="javascript">

		function buscarRegistros()
		{
			document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","corteusuario.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=1&usuario="+document.getElementById("usuario").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&hora_ini="+document.getElementById("hora_ini").value+"&hora_fin="+document.getElementById("hora_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
				objeto.onreadystatechange = function()
				{
					if (objeto.readyState==4)
					{document.getElementById("Resultados").innerHTML = objeto.responseText;}
				}
			}
			document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
		}
		</script>';

}
bottom();
?>