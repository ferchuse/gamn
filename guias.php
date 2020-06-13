<?php
include("main.php");
$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$array_taquilla=array();
$res=mysql_db_query($base,"SELECT * FROM taquillas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_taquilla[$row['cve']]=$row['nombre'];
}

$array_recaudacion=array();
$res=mysql_db_query($base,"SELECT * FROM recaudaciones ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_recaudacion[$row['cve']]=$row['nombre'];
}

$res=mysql_db_query($base,"SELECT * FROM costo_boletos ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_costo[$row['cve']]=$row['nombre'];
}

if($_POST['cmd']==100){
	ini_set("session.auto_start", 0);
	include('fpdf153/fpdf.php');
	include("numlet.php");	
	$pdf=new FPDF('P','mm','LETTER');
	$x=0;
	$select="SELECT a.*,COUNT(b.cve) as cantidad,SUM(b.monto) as importe FROM guia a 
	LEFT JOIN boletos b ON a.taquilla = b.taquilla AND a.folio = b.guia AND b.estatus=0";
	$select.=" WHERE a.taquilla>0 AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['no_eco']!='') $select.=" AND a.no_eco='".$_POST['no_eco']."'";
	if($_POST['folio_recaudacion']!='') $select.=" AND a.folio_recaudacion='".$_POST['folio_recaudacion']."'";
	if($_POST['taquilla']!='all') $select.=" AND a.taquilla='".$_POST['taquilla']."'";
	if($_POST['usuario']!='') $select.=" AND a.usuario='".$_POST['usuario']."'";
	$select.=" GROUP BY a.taquilla, a.folio ORDER BY a.fecha DESC, a.hora DESC";
	$res=mysql_db_query($base,$select) or die(mysql_error());
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',18);
	$pdf->Cell(190,10,'Guias',0,0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(95,4,'Preriodo: ' .$_POST['fecha_ini'].' - '.$_POST['fecha_fin'],0,0,'L');
	$pdf->Cell(95,4,'Fecha de Impresion: '.fechaLocal(),0,0,'R');
	
	$pdf->Ln();
	 if($_POST['taquilla']!='all'){	$pdf->Cell(95,4,'Taquilla: '.$array_taquilla[$_POST['taquilla']],0,0,'L');
	  }else{$pdf->Cell(95,4,'Taquilla: Todas ',0,0,'L');}
	$pdf->Cell(95,4,'Usuario de Impresion: '.$array_usuario[$_POST['cveusuario']],0,0,'R');
	$pdf->Ln();
	 if($_POST['usuario']!=''){	$pdf->Cell(95,4,'Usuario: '.$array_usuario[$_POST['usuario']],0,0,'L');
	  }else{$pdf->Cell(95,4,'Usuario: Todos ',0,0,'L');}
//	$pdf->Cell(95,4,'',0,0,'R');
	
	 if($_POST['folio_recaudacion']!=''){$pdf->Ln();	$pdf->Cell(95,4,'Folio: '.$_POST['folio_recaudacion'],0,0,'L');}
//	$pdf->Cell(95,4,'Usuario de Impresion: '.$array_usuario[$_SESSION[CveUsuario]],0,0,'R');
	 if($_POST['no_eco']!=''){$pdf->Ln();	$pdf->Cell(95,4,'No Economico: '.$_POST['no_eco'],0,0,'L');}
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(10,4,'Folio',1,0,'C');

	$pdf->Cell(34,4,'Taquilla',1,0,'C');
	$pdf->Cell(17,4,'Fecha',1,0,'C');
	$pdf->Cell(17,4,'Hora Inicio',1,0,'C');
	$pdf->Cell(17,4,'Hora Fin',1,0,'C');
	$pdf->Cell(15,4,'No Eco',1,0,'C');
	$pdf->Cell(17,4,'Boletos',1,0,'C');
	$pdf->Cell(17,4,'Importe',1,0,'C');
	$pdf->Cell(17,4,'Usuario',1,0,'C');
	$pdf->Cell(19,4,'Folio',1,0,'C');
	$pdf->Cell(19,4,'Fecha',1,0,'C');
	$pdf->Ln();
/*	$pdf->Cell(10,4,'',0,0,'C');
	$pdf->Cell(34,4,'',0,0,'C');
	$pdf->Cell(17,4,'',0,0,'C');
	$pdf->Cell(17,4,'',0,0,'C');
	$pdf->Cell(17,4,'',0,0,'C');
	$pdf->Cell(15,4,'',0,0,'C');
	$pdf->Cell(17,4,'Boletos',0,0,'C');
	$pdf->Cell(17,4,'Boletos',0,0,'C');
	$pdf->Cell(17,4,'',0,0,'C');
	$pdf->Cell(19,4,'Recaudacion',0,0,'C');
	$pdf->Cell(19,4,'Recaucadion',0,0,'C');*/
	$pdf->Ln();
	$nivelUsuario = nivelUsuario();
	$totales=array();
	while($row=mysql_fetch_array($res)){
		$factor=1;
		$pdf->Cell(10,4.2,''.$row['folio'],1,0,'C');
		$pdf->Cell(34,4.2,''.utf8_encode($array_taquilla[$row['taquilla']]),1,0,'C');
		$pdf->Cell(17,4.2,''.$row['fecha'],1,0,'C');
		$pdf->Cell(17,4.2,''.$row['hora'],1,0,'C');
		$pdf->Cell(17,4.2,''.$row['hora_fin'],1,0,'C');
		$pdf->Cell(15,4.2,''.$row['no_eco'],1,0,'C');
		$c=0;
		$totales[$c]+=round($row['cantidad']*$factor,0);$c++;
		$pdf->Cell(17,4.2,''.number_format($row['cantidad']*$factor,0),1,0,'C');
		$totales[$c]+=round($row['importe']*$factor,2);$c++;
		$pdf->Cell(17,4.2,''.number_format($row['importe']*$factor,2),1,0,'C');
		$pdf->Cell(17,4.2,''.$array_usuario[$row['usuario']],1,0,'C');
		$pdf->Cell(19,4.2,''.$row['folio_recaudacion'],1,0,'C');
		$pdf->Cell(19,4.2,''.$row['fecha_recaudacion'],1,0,'C');
		$x++;
		$pdf->Ln();
	}
	$pdf->Cell(95,8,''.$x.' Registros',1,0,'L');
	$pdf->Cell(15,8,' Total',1,0,'R');
	foreach($totales as $total){
		$pdf->Cell(17,8,''.number_format($total,2),1,0,'C');
	  }
	
	$pdf->Output();
	exit();
}

if($_POST['ajax']==1){
	$nivelUsuario = nivelUsuario();
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	echo '<th>Folio</th><th>Taquilla</th>';
	echo '<th>Fecha</th><th>Hora Inicio</th><th>Hora Fin</th><th>No Eco</th><th>Cantidad Boletos</th><th>Importe Boletos</th><th>Usuario</th>
	<th>Folio Recaudacion</th><th>Fecha Recaudacion</th>
	</tr>'; 
	$x=0;
	$select="SELECT a.*,COUNT(b.cve) as cantidad,SUM(b.monto) as importe FROM guia a 
	LEFT JOIN boletos b ON a.taquilla = b.taquilla AND a.folio = b.guia AND b.estatus=0";
	$select.=" WHERE a.taquilla>0";
	if($_POST['fecha_ini']!='') $select .= " AND a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!='') $select .= " AND a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['fecha_rec_ini']!='') $select .= " AND a.fecha_recaudacion>='".$_POST['fecha_rec_ini']."'";
	if($_POST['fecha_rec_fin']!='') $select .= " AND a.fecha_recaudacion<='".$_POST['fecha_rec_fin']."'";
	if($_POST['no_eco']!='') $select.=" AND a.no_eco='".$_POST['no_eco']."'";
	if($_POST['folio_recaudacion']!='') $select.=" AND a.folio_recaudacion='".$_POST['folio_recaudacion']."'";
	if($_POST['taquilla']!='all') $select.=" AND a.taquilla='".$_POST['taquilla']."'";
	if($_POST['usuario']!='') $select.=" AND a.usuario='".$_POST['usuario']."'";
	if($_POST['mostrar']==1) $select.=" AND a.folio_recaudacion > 0";
	elseif($_POST['mostrar']==2) $select.=" AND a.folio_recaudacion = 0";
	$select.=" GROUP BY a.taquilla, a.folio ORDER BY a.fecha DESC, a.hora DESC";
	$res=mysql_db_query($base,$select) or die(mysql_error());
	$totales=array();
	while($row=mysql_fetch_array($res)){
		rowb();
		$factor=1;
		echo '<td align="center">';
		echo '<a href="#" onClick="atcr(\'guias.php\',\'\',1,\''.$row['taquilla'].'|'.$row['folio'].'\')"><img src="images/buscar.gif" border="0" title="Ver"></a>';
		echo '</td>';
		echo '<td align="center">'.$row['folio'].'</td>';
		echo '<td align="center">'.utf8_encode($array_taquilla[$row['taquilla']]).'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		echo '<td align="center">'.$row['hora'].'</td>';
		echo '<td align="center">'.$row['hora_fin'].'</td>';
		echo '<td align="center">'.$row['no_eco'].'</td>';
		$c=0;
		echo '<td align="right">'.number_format($row['cantidad']*$factor,2).'</td>';
		$totales[$c]+=round($row['cantidad']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['importe']*$factor,2).'</td>';
		$totales[$c]+=round($row['importe']*$factor,2);$c++;
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		echo '<td align="center">'.$row['folio_recaudacion'].'</td>';
		echo '<td align="center">'.$row['fecha_recaudacion'].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="7" align="left">'.$x.' Registro(s)</th>';
	foreach($totales as $total)
		echo '<th align="right">'.number_format($total,2).'</th>';
	echo '<th colspan="3">&nbsp;</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==3){
	if($_POST['clavecancelacion'] == $clavecancelacion){
		mysql_db_query($base,"UPDATE boletos SET estatus='1',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['usuario']."' WHERE cve='".$_POST['id']."'");
	}
	else{
		echo '1';
	}
	exit();
}

top($_SESSION);



if($_POST['cmd']==1){
	echo '<table>';
	echo '<tr>';
	echo '<td><a href="#" onClick="atcr(\'guias.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	$datos = explode("|", $_POST['reg']);
	echo '<input type="hidden" name="taquilla" id="taquilla" value="'.$datos[0].'">';
	echo '<input type="hidden" name="guia" id="guia" value="'.$datos[1].'">';

	echo '<table>';
	echo '<tr><td class="tableEnc">Boletos de la guia # '.$datos[1].' de la taquilla '.$array_taquilla[$datos[0]].'</td></tr>';
	echo '</table>';

	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	echo '<th>Folio</th>';
	echo '<th>Fecha</th><th>Hora</th><th>No Eco</th><th>Boleto</th><th>Costo</th><th>Usuario</th>
	<th>Folio Recaudacion</th><th>Fecha Recaudacion</th>
	</tr>'; 
	$x=0;
	$nivelUsuario = nivelUsuario();
	$select="SELECT * FROM boletos WHERE taquilla = '".$datos[0]."' AND guia = '".$datos[1]."'";
	$select.=" ORDER BY folio";
	$res=mysql_db_query($base,$select) or die(mysql_error());
	$totales=array();
	while($row=mysql_fetch_array($res)){
		rowb();
		$factor=1;
		echo '<td align="center">';
		if($row['estatus']==1){
			echo 'CANCELADO';
			$factor=0;
		}
		elseif($nivelUsuario > 2 && $row['folio_recaudacion']==0 && $row['estatus']==0){
			echo '<a href="#" onClick="if(cancelarRegistro('.$row['cve'].', \'guias.php\', 3)) $(this).parents(\'tr:first\').remove();/*if(confirm(\'Esta seguro de cancelar el boleto?\')) atcr(\'guias.php\',\'\',3,\''.$row['cve'].'\')*/"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
		}
		else{
			echo '&nbsp;';
		}
		echo '</td>';
		echo '<td align="center">'.$row['folio'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		echo '<td align="center">'.$row['hora'].'</td>';
		echo '<td align="center">'.$row['no_eco'].'</td>';
		echo '<td align="center">'.utf8_encode($array_costo[$row['costo']]).'</td>';
		$c=0;
		echo '<td align="right">'.number_format($row['monto']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto']*$factor,2);$c++;
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		echo '<td align="center">'.$row['folio_recaudacion'].'</td>';
		echo '<td align="center">'.$row['fecha_recaudacion'].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="6" align="left">'.$x.' Registro(s)</th>';
	foreach($totales as $total)
		echo '<th align="right">'.number_format($total,2).'</th>';
	echo '<th colspan="3">&nbsp;</th></tr>';
	echo '</table>';
}

if($_POST['cmd']==0){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir"></a>Imprimir</td>
		</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="'.fechaLocal().'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="'.fechaLocal().'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Recaudacion Inicial</td><td><input type="text" name="fecha_rec_ini" id="fecha_rec_ini" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_rec_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Recaudacion Final</td><td><input type="text" name="fecha_rec_fin" id="fecha_rec_fin" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_rec_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Taquilla</td><td><select name="taquilla" id="taquilla"><option value="all">--- Todos ---</option>';
	foreach($array_taquilla as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Folio Recaudacion</td><td><input type="text" name="folio_recaudacion" id="folio_recaudacion" class="textField" size="12" value=""></td></tr>';
	echo '<tr><td>No. Eco</td><td><input type="text" name="no_eco" id="no_eco" class="textField" size="12" value=""></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">--- Todos ---</option>';
	foreach($array_usuario as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Mostrar</td><td><select name="mostrar" id="mostrar"><option value="0">Todos</option><option value="1">Recaudados</option><option value="2">Sin Recaudar</option></select></td></tr>';
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
			objeto.open("POST","guias.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&mostrar="+document.getElementById("mostrar").value+"&fecha_rec_ini="+document.getElementById("fecha_rec_ini").value+"&fecha_rec_fin="+document.getElementById("fecha_rec_fin").value+"&folio_recaudacion="+document.getElementById("folio_recaudacion").value+"&taquilla="+document.getElementById("taquilla").value+"&no_eco="+document.getElementById("no_eco").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&usuario="+document.getElementById("usuario").value);
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
}
bottom();

?>