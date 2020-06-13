<?php
include("main.php");
include("numlet.php");

$res=mysql_db_query($base,"SELECT * FROM parque ORDER BY cve");
while($row=mysql_fetch_array($res)){
	$array_parque[$row['cve']]=$row['no_eco'];
	$array_parque_[$row['no_eco']]=$row['cve'];
}

$array_propietario=array();
$res=mysql_db_query($base,"SELECT * FROM ".$pre."propietarios ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_propietario[$row['cve']]=$row['nombre'];
}

$array_derrotero=array();
$res=mysql_db_query($base,"SELECT * FROM ".$pre."derroteros ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_derrotero[$row['cve']]=$row['nombre'];
}

if($_POST['ajax']==1){
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</td>';
	echo '<th>Abono</th><th>Folio por</br>Terminal</th><th>Terminal</th><th>Fecha</th><th>Unidad</th><th>Permisionario</th><th>Derrotero</th>
	<th>Monto</th><th>Usuario</th><th>Folio Recaudacion</th><th>Fecha Recaudacion</th>
	</tr>'; 
	$x=0;
	$t=0;
	$filtro="";
	$filtroconductor="";
	$filtrounidad="";
		$nivelUsuario = nivelUsuario();
	if($_POST['fecha_ini']!="") $filtro.=" AND a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!="") $filtro.=" AND a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['derrotero']!='all') $filtro.=" AND c.derrotero='".$_POST['derrotero']."'";
	if($_POST['no_eco']!="") $filtro=" AND c.no_eco='".$_POST['no_eco']."'";
	if($_POST['usuario']!='') $filtro.=" AND a.idusuario='".$_POST['usuario']."'";
	if($_POST['terminal']!='') $filtro.=" AND a.idterminal='".$_POST['terminal']."'";
	$res=mysql_query("SELECT a.*, c.derrotero, c.propietario FROM abono_unidad_taquillamovil as a 
	INNER JOIN parque as c on (c.cve=a.unidad) 
	WHERE 1 $filtro ORDER BY a.cve DESC") or die(mysql_error());
	$t=0;
	$t2=0;
	while($row=mysql_fetch_array($res)){
		rowb();
		$aux='';
		echo '<td align="center">';
		if($row['estatus']!='C'){
			if($nivelUsuario >2){
				echo '&nbsp;<a href="#" onClick="if(cancelarRegistro('.$row['cve'].', \'abono_unidad_taqmovil.php\', 3)) buscarRegistros();/*if(confirm(\'Esta seguro de cancelar?\')){ obs=prompt(\'Motivo:\'); atcr(\'abono_unidad_taqmovil.php?obs=\'+obs,\'\',3,\''.$row['cve'].'\');}*/"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
			}
		}
		else{
			$row['monto']=0;
			$row['gastos']=0;
			echo 'CANCELADO<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
		}
		echo '</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['folio'].'</td>';
		echo '<td align="center">'.$row['terminal'].'</td>';
		echo '<td align="center">'.$row['fecha'].' '.$row['hora'].'</td>';
		echo '<td align="center">'.$array_parque[$row['unidad']].'</td>';
		echo '<td align="left">'.utf8_encode($array_propietario[$row['propietario']]).'</td>';
		echo '<td align="left">'.utf8_encode($array_derrotero[$row['derrotero']]).'</td>';
		echo '<td align="right">'.number_format($row['monto'],2).'</td>';
		echo '<td align="left">'.$row['usuario'].'</td>';
		echo '<td align="center">'.$row['folio_recaudacion'].'</td>';
		echo '<td align="center">'.$row['fecha_recaudacion'].'</td>';
		echo '</tr>';
		$x++;
		$t+=$row['monto'];
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="7" align="left">'.$x.' Registro(s)</th><th align="right">Total</th><th align="left">'.number_format($t,2).'</th><th colspan="3">&nbsp;</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==3){
	if($_POST['clavecancelacion'] == $clavecancelacion){
			mysql_query("UPDATE abono_unidad_taquillamovil SET estatus='C',obscan='".$_POST['obs']."',usucan='".$_POST['usuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['id']."'");
		$_POST['cmd']=0;
	}
	else{
		echo '1';
	}
	exit();
}

top($_SESSION);

if($_POST['cmd']==-10){
	$res=mysql_query("SELECT * FROM recaudacion_unidad WHERE cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$mensaje="&nbsp;";
	$texto ='|';
		$texto.=" ABONO";
		$texto.='||';
		$texto.="FOLIO: ".$row['cve'];
		$texto.='|';
		$texto.=" EMPRESA: ".$array_empresa[$row['empresa']];
		$texto.='|';
		$texto.=" FECHA DIA: ".$row['fecha'];
		$texto.='|';
		$texto.=" FECHA CUENTA: ".$row['fecha_tarjeta'];
		$texto.='|';
		$texto.=" PROPIETARIO: ".$array_permisionario[$row['permisionario']];
		$texto.='||';
		$texto.="NUM ECO: ".$array_unidad[$row['unidad']];
		$texto.='||';
		$texto.="MONTO: ".number_format($row['monto'],2);
		$texto.='|';
		$texto.=" ".numlet($row['monto']);
		$texto.='|';
		$texto.="GASTOS: ".number_format($row['gastos'],2);
		$texto.='|';
		$texto.="OBSERVACIONES:|".$row['observaciones'];

		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&textoimp='.$texto.'&barcode=11'.sprintf("%010s",$row['cve']).'" width=200 height=200></iframe>';
	
	$_POST['cmd']=0;
}



if($_POST['cmd']==3){
	if(nivelUsuario() > 2){
			mysql_query("UPDATE abono_unidad_taquillamovil SET estatus='C',obscan='".$_GET['obs']."',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."'");
		$_POST['cmd']=0;
	}
}



if($_POST['cmd']==0){
	if($mensaje!=""){
		echo $mensaje;
		echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
	}
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="'.fechaLocal().'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="'.fechaLocal().'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>No Eco</td><td><input type="text" name="no_eco" id="no_eco" class="textField" size="5" value=""></td></tr>';
	echo '<tr><td>Derrotero</td><td><select name="derrotero" id="derrotero"><option value="all">--- Todos ---</option>';
	foreach($array_derrotero as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Terminal</td><td><select name="terminal" id="terminal"><option value="">--- Todos ---</option>';
	$res1 = mysql_query("SELECT idterminal,terminal FROM abono_unidad_taquillamovil GROUP BY terminal ORDER BY terminal");
	while($row1 = mysql_fetch_array($res1)){
		echo '<option value="'.$row1[0].'">'.$row1[1].'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">--- Todos ---</option>';
	$res = mysql_query("SELECT idusuario,usuario FROM abono_unidad_taquillamovil GROUP BY usuario ORDER BY usuario");
	while($row = mysql_fetch_array($res)){
		echo '<option value="'.$row[0].'">'.$row[1].'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';
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
			objeto.open("POST","abono_unidad_taqmovil.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&no_eco="+document.getElementById("no_eco").value+"&derrotero="+document.getElementById("derrotero").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&usuario="+document.getElementById("usuario").value+"&terminal="+document.getElementById("terminal").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}

	
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
	
	</Script>
';
?>