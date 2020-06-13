<?
include ("main.php"); 
$base2="enero_aaz";
/*** ARREGLOS ***********************************************************/

$rsUsuario=mysql_db_query($base,"SELECT * FROM ".$pre."usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}


$rsconductor=mysql_db_query($base,"SELECT * FROM ".$pre."parque");
while($Conductor=mysql_fetch_array($rsconductor)){
	$array_unidad[$Conductor['cve']]=$Conductor['no_eco'];
}

$res=mysql_db_query($base,"SELECT * FROM ".$pre."propietarios ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_propietario[$row['cve']]=$row['nombre'];
}

$rsMotivos=mysql_db_query($base,"SELECT * FROM ".$pre."cat_cargos_variables");
while($Motivo=mysql_fetch_array($rsMotivos)){
	$array_cargosv_unidades[$Motivo['cve']]=$Motivo['nombre'];
}

if($_POST['cmd']==100){
    ob_end_clean();
    include('fpdf153/fpdf.php');
	include("numlet.php");
	$pdf=new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	$pdf->SetXY(20,20);
	$pdf->Image('images/membrete.JPG',30,3,150,15);
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','',17);
	$pdf->Cell(0,10,"Listado de Estado de Cuenta Mutualidad de Unidades del ".$_POST['fecha_ini']." al ".$_POST['fecha_fin'],0,0,"L");
    $pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','',9);
	$pdf->Cell(0,6,"".fechaLocal()."  ".horaLocal(),0,0,"L");
	$pdf->Ln();$pdf->Cell(28,6,"No Economico",1,0,'C');
	$pdf->Cell(28,6,"Estatus",1,0,'C');
	$pdf->Cell(28,6,"Fecha Estaud",1,0,'C');
	$pdf->Cell(28,6,"Saldo Anterior",1,0,'C');
	$pdf->Cell(28,6,"Cargos",1,0,'C');
	$pdf->Cell(28,6,"Abonos",1,0,'C');
	$pdf->Cell(28,6,"Saldo",1,0,'C');
	$pdf->Ln();
	$i=0;
	$x=0;
	$totales=array();
		for($i=0;$i<count($_POST['unidades']);$i++){
		  $select= " SELECT * FROM parque WHERE cve='".$_POST['unidades'][$i]."' ";
	      if ($_POST['unidad']!="") { $select.=" AND no_eco='".$_POST['unidad']."'"; }
	      if ($_POST['estatus']!="all") { $select.=" AND estatus='".$_POST['estatus']."'"; }
	      $select.=" ORDER BY no_eco";
	      $res=mysql_db_query($base,$select);
		  $row=mysql_fetch_array($res);
         // 
	     // $pdf->Ln();
			$pdf->Cell(28,6,"".$row['no_eco'],1,0,'C');
			$pdf->Cell(28,6,"".$array_estatus_parque[$row['estatus']],1,0,'C');
			$pdf->Cell(28,6,"".$row['fecha_sta'],1,0,'C');
			$saldo_anterior = saldo_unidadm($row['cve'],1,0,$_POST['fecha_ini'],"");
			$cargo = saldo_unidadm($row['cve'],2,1,$_POST['fecha_ini'],$_POST['fecha_fin']);
			$abono = saldo_unidadm($row['cve'],2,2,$_POST['fecha_ini'],$_POST['fecha_fin']);
			$pdf->Cell(28,6,"".number_format($saldo_anterior,2),1,0,'C');
			$pdf->Cell(28,6,"".number_format($cargo,2),1,0,'C');
			$pdf->Cell(28,6,"".number_format($abono,2),1,0,'C');
			$pdf->Cell(28,6,"".number_format($saldo_anterior+$abono-$cargo,2),1,0,'C');
			$totales[0]+=$saldo_anterior;
			$totales[1]+=$cargo;
			$totales[2]+=$abono;
			$totales[3]+=$saldo_anterior+$abono-$cargo;
			$pdf->Ln();
			}
			$pdf->Cell(28,6,"".$i." Registro(s)",0,0,'L');
			$pdf->Cell(28,6,"",0,0,'');
			$pdf->Cell(28,6,"Totales",0,0,'C');
		foreach($totales as $v){
			$pdf->Cell(28,6,"".number_format($v,2),0,0,'C');
			}
		
	
	$pdf->Output();
	exit();	
}
if($_POST['cmd']==102){
    include('fpdf153/fpdf.php');
	include("numlet.php");
	$pdf=new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	$pdf->SetXY(20,20);
	$pdf->Image('images/membrete.JPG',30,3,150,15);
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$rsMotivos=mysql_db_query($base2,"SELECT * FROM cat_cargos_unidades");
	while($Motivo=mysql_fetch_array($rsMotivos)){
		$array_motivo[$Motivo['cve']]=$Motivo['nombre'];
	}

	$rsMotivos=mysql_db_query($base2,"SELECT * FROM motivos_entradas");
	while($Motivo=mysql_fetch_array($rsMotivos)){
		$array_motivo_var[$Motivo['cve']]=$Motivo['nombre'];
	}
	$i=0;
	for($i=0;$i<count($_POST['unidades']);$i++){
		$res=mysql_db_query($base,"SELECT * FROM parque WHERE cve='".$_POST['unidades'][$i]."'");
		$row=mysql_fetch_array($res);
		$cveori=$row['cve_ori'];
		$pdf->SetFont('Arial','',13);
		$pdf->Cell(0,10,"Estado de Cuenta Mutualidad de la Unidad ".$row['no_eco']." Propietario ".$array_propietario[$row['propietario']]." ".fechaLocal()." ".horaLocal(),0,0,"L");
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetFont('Arial','',9);
		$pdf->Cell(18,6,"Fecha",1,0,'C');
		$pdf->Cell(65,6,"Motivo",1,0,'C');
		$pdf->Cell(20,6,"Cargo",1,0,'C');
		$pdf->Cell(20,6,"Abono",1,0,'C');
		$pdf->Cell(20,6,"Saldo",1,0,'C');
		$pdf->Cell(55,6,"Observaciones",1,0,'C');
		$pdf->Ln();
		$saldo = saldo_unidadm($row['cve'],1,0,$_POST['fecha_ini'],"");
		$x=$abono=$cargo=0;
		$fecha=$_POST['fecha_ini'];
		$pdf->Cell(18,6,$fecha,1,0,'C');
		$pdf->Cell(65,6,"Saldo Anterior",1,0,'C');
		$pdf->Cell(20,6," ",1,0,'C');
		$pdf->Cell(20,6," ",1,0,'C');
		$pdf->Cell(20,6,number_format($saldo,2),1,0,'C');
		$pdf->Cell(55,6," ",1,0,'C');
		$pdf->Ln();
		$pdf->Ln();
		while($fecha<=$_POST['fecha_fin']){
			
			$res=mysql_db_query($base,"SELECT * FROM parque_abono WHERE unidad='".$_POST['unidades'][$i]."' AND estatus!='C' AND mutualidad > 0 AND fecha>='2016-11-30' AND fecha='$fecha'");
			while($row=mysql_fetch_array($res)){
					$abono+=$row['mutualidad'];
					$saldo+=$row['mutualidad'];
					$pdf->Cell(18,6,$fecha,1,0,'C');
					$pdf->Cell(65,6,"Mutualidad Abono # ".$row['cve'],1,0,'C');
					$pdf->Cell(20,6,number_format(0,2),1,0,'C');
					$pdf->Cell(20,6,number_format($row['mutualidad'],2),1,0,'C');
					$pdf->Cell(20,6,number_format($saldo,2),1,0,'C');
					$pdf->Cell(55,6,$row['concepto'],1,0,'C');
					$pdf->Ln();
					$x++;
			}
			$fecha=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fecha) ) );
		}
		$pdf->Cell(18,6,$x.' Registros',1,0,'C');
		$pdf->Cell(65,6," ",1,0,'C');
		$pdf->Cell(20,6,number_format($cargo,2),1,0,'C');
		$pdf->Cell(20,6,number_format($abono,2),1,0,'C');
		$pdf->Cell(20,6,number_format($saldo,2),1,0,'C');
		$pdf->Cell(55,6," ",1,0,'C');
		$pdf->Ln();
	}
	$pdf->Output();
	exit();	
}

if($_POST['cmd']==101){
	echo '<html><body>';
	$rsMotivos=mysql_db_query($base2,"SELECT * FROM cat_cargos_unidades");
	while($Motivo=mysql_fetch_array($rsMotivos)){
		$array_motivo[$Motivo['cve']]=$Motivo['nombre'];
	}

	$rsMotivos=mysql_db_query($base2,"SELECT * FROM motivos_entradas");
	while($Motivo=mysql_fetch_array($rsMotivos)){
		$array_motivo_var[$Motivo['cve']]=$Motivo['nombre'];
	}
	$res=mysql_db_query($base,"SELECT * FROM parque WHERE cve=".$_POST['reg']);
	$row=mysql_fetch_array($res);
	$cveori=$row['cve_ori'];
	echo '<table align="center">';
	echo '<tr><td><img src="images/membrete.JPG"></td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<h1>Estado de Cuenta Mutualidad de la Unidad '.$row['no_eco'].' Propietario '.$array_propietario[$row['propietario']].'</h1>'.fechaLocal().' '.horaLocal().'</br>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Fecha</th><th>Motivo</th><th>Cargo</th><th>Abono</th><th>Saldo</th><th>Observaciones</th>';
	echo '</tr>';
	$saldo = saldo_unidadm($row['cve'],1,0,$_POST['fecha_ini'],"");
	$x=$abono=$cargo=0;
	rowb();
	$fecha=$_POST['fecha_ini'];
	echo '<td align=center>&nbsp;'.$fecha.'</td>';
	echo '<td align=left>&nbsp;Saldo Anterior</td>';
	echo '<td align="right">&nbsp;</td>';
	echo '<td align="right">&nbsp;</td>';
	echo '<td align="right">'.number_format($saldo,2).'</td>';
	echo '<td align="left">&nbsp;</td>';
	echo '</tr>';
	while($fecha<=$_POST['fecha_fin']){
		$res=mysql_db_query($base,"SELECT * FROM parque_abono WHERE unidad='".$_POST['reg']."' AND fecha>='2016-11-30' AND mutualidad>0 AND estatus!='C' AND fecha='$fecha'");
		while($row=mysql_fetch_array($res)){
				$abono+=$row['mutualidad'];
				$saldo+=$row['mutualidad'];
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.'</td>';
				echo '<td align=left>&nbsp;Mutualidad Abono # '.$row['cve'].'</td>';
				echo '<td align="right">'.number_format(0,2).'</td>';
				echo '<td align="right">'.number_format($row['mutualidad'],2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align=left>'.$row['concepto'].'&nbsp;</td>';
				echo '</tr>';
				$x++;
		}
		$fecha=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fecha) ) );
	}
	echo '	
			<tr>
			<td colspan="2" bgcolor="#E9F2F8">'.$x.' Registro(s)</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($cargo,2).'</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($abono,2).'</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($saldo,2).'</td>
			<td colspan="2" bgcolor="#E9F2F8">&nbsp;</td>
			</tr>';
	echo '</table>';
	echo '<script>window.print();</script>
	</body></html>';
	exit();
}

if($_POST['ajax']==1){
	$select= " SELECT * FROM parque WHERE 1 ";
	if ($_POST['unidad']!="") { $select.=" AND no_eco='".$_POST['unidad']."'"; }
	if ($_POST['estatus']!="all") { $select.=" AND estatus='".$_POST['estatus']."'"; }
	$select.=" ORDER BY no_eco";
	$res=mysql_db_query($base,$select);
	if(mysql_num_rows($rsconductor)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="10">'.mysql_num_rows($rsconductor).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8">';
		echo '<th><input type="checkbox" onClick="if(this.checked) $(\'.chks\').attr(\'checked\',\'checked\'); else $(\'.chks\').removeAttr(\'checked\');"></th><th>No Economico</th><th>Estatus</th><th>Fecha Estatus</th><th>Saldo Anterior</th><th>Cargos<th>Abonos</th><th>Saldo</th>';
		echo '</tr>';
		$i=0;
		$x=0;
		$totales=array();
		while($row=mysql_fetch_array($res)){
			rowb();
			echo'<td align="center"><input type="checkbox" name="unidades[]" class="chks" value="'.$row['cve'].'"></td>';
			echo '<td align="center">'.$row['no_eco'].'</td>';
			echo '<td align="center">'.$array_estatus_parque[$row['estatus']].'</td>';
			echo '<td align="center">'.$row['fecha_sta'].'</td>';
			$saldo_anterior = saldo_unidadm($row['cve'],1,0,$_POST['fecha_ini'],"");
			$cargo = saldo_unidadm($row['cve'],2,1,$_POST['fecha_ini'],$_POST['fecha_fin']);
			$abono = saldo_unidadm($row['cve'],2,2,$_POST['fecha_ini'],$_POST['fecha_fin']);
			echo '<td align="right">'.number_format($saldo_anterior,2).'</td>';
			echo '<td align="right">'.number_format($cargo,2).'</td>';
			echo '<td align="right">'.number_format($abono,2).'</td>';
			echo '<td align="right"><a href="#" onClick="atcr(\'edo_cuenta_parquem.php\',\'\',1,'.$row['cve'].')">'.number_format($saldo_anterior+$abono-$cargo,2).'</a></td>';
			echo '</tr>';
			$totales[0]+=$saldo_anterior;
			$totales[1]+=$cargo;
			$totales[2]+=$abono;
			$totales[3]+=$saldo_anterior+$abono-$cargo;
			$i++;
		}
		echo '	
			<tr>
			<td colspan="3" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
			<td bgcolor="#E9F2F8" align="right">Totales</td>';
		foreach($totales as $v)
			echo '<td bgcolor="#E9F2F8" align="right">'.number_format($v,2).'</td>';
		echo '
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

if($_POST['ajax']==2){
	$rsMotivos=mysql_db_query($base2,"SELECT * FROM cat_cargos_unidades");
	while($Motivo=mysql_fetch_array($rsMotivos)){
		$array_motivo[$Motivo['cve']]=$Motivo['nombre'];
	}

	$rsMotivos=mysql_db_query($base2,"SELECT * FROM motivos_entradas");
	while($Motivo=mysql_fetch_array($rsMotivos)){
		$array_motivo_var[$Motivo['cve']]=$Motivo['nombre'];
	}
	$res=mysql_db_query($base,"SELECT * FROM parque WHERE cve=".$_POST['unidad']);
	$row=mysql_fetch_array($res);
	$cveori=$row['cve_ori'];
	echo '<table width="100%">';
	echo '<tr><td class="tableEnc">Estado de Cuenta Mutualidad de la Unidad '.$row['no_eco'].' Propietario '.$array_propietario[$row['propietario']].'</td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Fecha</th><th>Motivo</th><th>Cargo</th><th>Abono</th><th>Saldo</th><th>Observaciones</th>';
	echo '</tr>';
	$saldo = saldo_unidadm($row['cve'],1,0,$_POST['fecha_ini'],"");
	$x=$abono=$cargo=0;
	rowb();
	$fecha=$_POST['fecha_ini'];
	echo '<td align=center>&nbsp;'.$fecha.'</td>';
	echo '<td align=left>&nbsp;Saldo Anterior</td>';
	echo '<td align="right">&nbsp;</td>';
	echo '<td align="right">&nbsp;</td>';
	echo '<td align="right">'.number_format($saldo,2).'</td>';
	echo '<td align="left">&nbsp;</td>';
	echo '</tr>';
	while($fecha<=$_POST['fecha_fin']){
		$res=mysql_db_query($base,"SELECT * FROM parque_abono WHERE unidad='".$_POST['unidad']."' AND estatus!='C' AND mutualidad>0 AND fecha>='2016-11-30' AND fecha='$fecha'");
		while($row=mysql_fetch_array($res)){
				$abono+=$row['mutualidad'];
				$saldo+=$row['mutualidad'];
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.'</td>';
				echo '<td align=left>&nbsp;Mutualidad Abono # '.$row['cve'].'</td>';
				echo '<td align="right">'.number_format(0,2).'</td>';
				echo '<td align="right">'.number_format($row['mutualidad'],2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align=left>'.$row['concepto'].'&nbsp;</td>';
				echo '</tr>';
				$x++;
		}
		$fecha=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fecha) ) );
	}
	echo '	
			<tr>
			<td colspan="2" bgcolor="#E9F2F8">'.$x.' Registro(s)</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($cargo,2).'</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($abono,2).'</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($saldo,2).'</td>
			<td colspan="2" bgcolor="#E9F2F8">&nbsp;</td>
			</tr>';
	echo '</table>';
	exit();
}

top($_SESSION);

if($_POST['cmd']==1){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscar_cargos(\''.$_POST['reg'].'\');"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar Cargos</a></td>
			<td><a href="#" onclick="atcr(\'edo_cuenta_parquem.php\',\'_blank\',101,'.$_POST['reg'].');"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a></td>
			<td><a href="#" onclick="atcr(\'edo_cuenta_parquem.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>
		</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.$_POST['fecha_ini'].'" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.$_POST['fecha_fin'].'" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<input type="hidden" name="sel[]" value="'.$_POST['reg'].'">';
	//Listado
	echo '<div id="idCargos">';
	echo '</div>';
	echo '
	<script>
	function buscar_cargos(unidad)
	{
		if(document.forma.fecha_ini.value<"2009-09-01") document.forma.fecha_ini.value="2009-09-01";
		if(document.forma.fecha_fin.value<"2009-09-01") document.forma.fecha_fin.value="2009-09-01";
		document.getElementById("idCargos").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","edo_cuenta_parquem.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&unidad="+unidad+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("idCargos").innerHTML = objeto.responseText;}
			}
		}
	}
	window.onload = function () {
			buscar_cargos(\''.$_POST['reg'].'\'); //Realizar consulta de todos los registros al iniciar la forma.
	}
	</script>';
}


if($_POST['cmd']<1){
	/*** PAGINA PRINCIPAL **************************************************/

		//Busqueda
		//<td><a href="#" onclick="atcr(\'edo_cuenta_parquem.php\',\'_blank\',101,'.$_POST['reg'].');"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir Detalles</td>
				
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'edo_cuenta_parquem.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td>
				<td><a href="#" onclick="atcr(\'edo_cuenta_parquem.php\',\'_blank\',102,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir Detalle</td>
				<!--<td><a href="#" onclick="validar_seleccion2();"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td>-->
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.substr(fechaLocal(),0,8).'01'.'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus" class="textField"><option value="all">---Todos---</option>';
		foreach($array_estatus_parque as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==1) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>No Eco</td><td><input type="text" class="textField" name="unidad" id="unidad" size="10"></td></tr>';
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
			objeto.open("POST","edo_cuenta_parquem.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&unidad="+document.getElementById("unidad").value+"&estatus="+document.getElementById("estatus").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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