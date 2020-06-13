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

if($_POST['cmd']==200){
	require('fpdf153/fpdf.php');
	include("numlet.php");
	$fecha1=$_POST['fecha_ini'];
	$fecha2=$_POST['fecha_fin'];
	class PDF1 extends FPDF{
		//Cabecera de página
		function Header(){
			global $fecha1,$fecha2;
			//$this->Image('images/membrete.JPG',30,3,150,15);
			//$this->Cell(270,10,"GAAZ",0,0,'C');
			$this->Ln(5);
			//Arial bold 15
			$this->SetFont('Arial','B',15);
			//Título
			$this->MultiCell(0,10,'NEXTLALPAN
			Lista de Folios de Abono a Unidades del dia: '.$fecha1.' al dia '.$fecha2,0,'C');
			//Salto de línea
			//$this->Ln(20);
		}

		//Pie de página
		function Footer(){
			//Posición: a 1,5 cm del final
			$this->SetY(-15);
			//Arial italic 8
			$this->SetFont('Arial','I',8);
			//Número de página
			$this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
		}
	}

	//Creación del objeto de la clase heredada
	$pdf=new PDF1('P');
	$pdf->AliasNbPages();
	$pdf->AddPage('P');
	$pdf->SetFont('Arial','B',10);
	$select= " SELECT a.* FROM parque_abono as a INNER JOIN parque as b ON (b.cve=a.unidad";
	if(trim($_POST['no_eco'])!="")$select.=" AND b.no_eco='".strtoupper($_POST['no_eco'])."'";
	$select.=") INNER JOIN conductores as c ON (c.cve=a.conductor";
	if(trim($_POST['credencial'])!="")$select.=" AND c.credencial='".strtoupper($_POST['credencial'])."'";
	if(trim($_POST['nombre'])!="")$select.=" AND c.nombre LIKE '%".strtoupper($_POST['nombre'])."%'";
	$select.=") WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	//if($_POST['no_eco']!="") $select.=" AND a.no_eco='".$_POST['no_eco']."'"; 
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	if($_POST['orden']==0 && $_POST['tipoorden']==1){
		$select.=" ORDER BY a.folio DESC";
	}
	elseif($_POST['orden']==0 && $_POST['tipoorden']==0){
		$select.=" ORDER BY a.folio";
	}
	elseif($_POST['orden']==1 && $_POST['tipoorden']==1){
		$select.=" ORDER BY b.no_eco DESC,a.folio DESC";
	}
	elseif($_POST['orden']==1 && $_POST['tipoorden']==0){
		$select.=" ORDER BY b.no_eco,a.folio DESC";
	}
	$pdf->Cell(20,5,'Folio',1,0,'C');
	$pdf->Cell(20,5,'Fecha',1,0,'C');
	$pdf->Cell(20,5,'Fec. Cuenta',1,0,'C');
	$pdf->Cell(10,5,'Eco',1,0,'C');
	$pdf->Cell(60,5,'Conductor',1,0,'L');
	$pdf->Cell(30,5,'Monto',1,0,'C');
	$pdf->Cell(30,5,'Usuario',1,0,'C');
	$pdf->SetFont('Arial','',8);
	$i=0;
	$total=0;
	$res=mysql_db_query($base,$select);
	while ($row=mysql_fetch_array($res)){	
		$pdf->Ln();
		$estatus='';
		if($row['estatus']=='C'){
			$estatus='(CANCELADO)';
			$row['monto']=0;
		}
		$pdf->Cell(20,5,$row['folio'].$estatus,1,0,'C');
		$pdf->Cell(20,5,$row['fecha'],1,0,'C');
		$pdf->Cell(20,5,$row['fecha_cuenta'],1,0,'C');
		$pdf->Cell(10,5,$array_unidad[$row['unidad']],1,0,'L');
		$pdf->Cell(60,5,$array_nomconductor[$row['conductor']],1,0,'L');
		$pdf->Cell(30,5,number_format($row['monto'],2),1,0,'R');
		$pdf->Cell(30,5,$array_usuario[$row['usuario']],1,0,'C');
		$i++;
		$total+=$row['monto'];
	}
	$pdf->Ln();
	$pdf->Cell(60,5,$i." Registro(s)",0,0,'L');
	$pdf->Cell(70,5,"Total: ",0,0,'R');
	$pdf->Cell(30,5,number_format($total,2),0,0,'R');
	$pdf->Output();
	exit();

}

if($_POST['cmd']==6)
{
	$_POST['unidad'] = 0;
	if($_POST['unidad']==0){
		$rsUni=mysql_db_query($base,"SELECT cve FROM parque WHERE recaudacion_local=1 AND no_eco='".strtoupper($_POST['eco2'])."'  AND estatus='1'");
		$Uni = mysql_fetch_array($rsUni);
		if($Uni['cve'] > 0)
			$_POST['unidad']=$Uni['cve'];
		else
			$mensaje="Error en la unidad";
	}
	if($_POST['unidad']>0){
		//if($Uni['tipo_vehiculo']>0){
			$res1=mysql_db_query($base,"SELECT * FROM parque_tarjetas WHERE fecha_cuenta='".$_POST['fechahoy2']."' AND unidad='".$_POST['unidad']."' AND estatus!='C'");
			if(mysql_num_rows($res1)==0){
				$res1=mysql_db_query($base,"SELECT * FROM parque_tarjetas WHERE fecha_cuenta='".$_POST['fechahoy2']."' AND conductor='".$_POST['conductor2']."' AND estatus!='C'");
				if(mysql_num_rows($res1)==0){
					mysql_db_query($base,"INSERT parque_tarjetas SET fecha_cuenta='".$_POST['fechahoy2']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
					unidad='".$_POST['unidad']."',estatus='A',derrotero='".$array_uniderrotero[$_POST['unidad']]."',usuario='".$_POST['cveusuario']."',concepto='".$_POST['concepto']."',
					conductor='".$_POST['conductor2']."',cuenta='".$array_cuenta[$array_uniderrotero[$_POST['unidad']]]."'") or die(mysql_error());
					$abono=mysql_insert_id();
					$mensaje.="<br><b>Se genero el Folio de tarjeta a unidades: ".$abono." de la unidad ".$array_unidad[$_POST['unidad']]."</b>";
					$res = mysql_db_query($base,"SELECT * FROM parque_tarjetas WHERE cve='".$abono."'");
					$row = mysql_fetch_array($res);
					
					$texto ='|';
					$texto.=chr(27).'!'.chr(40)."NEXTLALPAN";
					$texto.='||';
					$texto.=chr(27).'!'.chr(40)."FOLIO: ".$row['cve'];
					$texto.='|';
					$texto.=chr(27).'!'.chr(10)."TTAQUILLERO: ".$array_usuario[$row['usuario']];
					$texto.='|';
					$texto.=chr(27).'!'.chr(10)."DDERROTERO: ".$array_derroteros[$row['derrotero']];
					$texto.=chr(27).'!'.chr(10).'||';
					$texto.="FECHA:    ".fechaLocal()."   ".horaLocal().'|';
					$texto.="FECHA CUENTA:    ".$_POST['fechahoy2'];
					$texto.='||';
					$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
					$texto.='|';
					$texto.=chr(27).'!'.chr(10)."CCREDENCIAL ".$array_conductor[$row['conductor']];
					$texto.='||';
					$texto.=chr(27).'!'.chr(10);
					$texto.="SSALIDA         DESTINO           FIRMA";
					$texto.='||';
					$texto.="______________  ________________  ______________";
					$texto.='||';
					$texto.="______________  ________________  ______________";
					$texto.='||';
					$texto.="______________  ________________  ______________";
					$texto.='||';
					$texto.="______________  ________________  ______________";
					$texto.='||';
					$texto.="______________  ________________  ______________";
					$texto.='||';
					$texto.="______________  ________________  ______________";
					$texto.='||';
					$texto.="______________  ________________  ______________";
					$texto.='||';
					$texto.="______________  ________________  ______________";
					$texto.='||';
					$texto.='|';
					$texto.="  ___________________  ____________________";
					$texto.='|';
					$texto.="  CONDUCTOR             DESPACHADOR";
					$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$texto.'&logo=GAMN" width=200 height=200></iframe>';
					echo '<html><body>'.$impresion.'</body></html>';
				}
				else{
					echo '<script>alert("Al conductor ya se le genero la tarjeta en la fecha");</script>';
				}
			}
			else{
				echo '<script>alert("A la unidad ya se le genero la tarjeta en la fecha");</script>';
			}
		//}
		//else{
		//	echo '<script>alert("A la unidad no se le ha indicado el tipo de vehiculo");</script>';
		//}
	}
	else{
		echo '<script>alert("Error en la unidad");</script>';
	}
	echo '<script>setTimeout("window.close()",2000);</script>';
	exit();
}

if($_POST['ajax']==1){
	$filtro="";
	$select= " SELECT a.* FROM parque_abono as a INNER JOIN parque as b ON (b.cve=a.unidad";
	if(trim($_POST['no_eco'])!="")$select.=" AND b.no_eco='".strtoupper($_POST['no_eco'])."'";
	$select.=") INNER JOIN conductores as c ON (c.cve=a.conductor";
	if(trim($_POST['credencial'])!="")$select.=" AND c.credencial='".strtoupper($_POST['credencial'])."'";
	if(trim($_POST['nombre'])!="")$select.=" AND c.nombre LIKE '%".strtoupper($_POST['nombre'])."%'";
	$select.=") WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	if($_POST['orden']==0 && $_POST['tipoorden']==1){
		$select.=" ORDER BY a.folio DESC";
		$tipoorden0=0;
		$tipoorden1=0;
	}
	elseif($_POST['orden']==0 && $_POST['tipoorden']==0){
		$select.=" ORDER BY a.folio";
		$tipoorden0=1;
		$tipoorden1=0;
	}
	elseif($_POST['orden']==1 && $_POST['tipoorden']==1){
		$select.=" ORDER BY b.no_eco DESC,a.folio DESC";
		$tipoorden0=1;
		$tipoorden1=0;
	}
	elseif($_POST['orden']==1 && $_POST['tipoorden']==0){
		$select.=" ORDER BY b.no_eco,a.folio DESC";
		$tipoorden0=1;
		$tipoorden1=1;
	}
	$rsabonos=mysql_db_query($base,$select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		echo '<input type="hidden" name="tipoorden" id="tipoorden" value="'.$_POST['tipoorden'].'">';
		echo '<input type="hidden" name="orden" id="orden" value="'.$_POST['orden'].'">';
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$col=15;
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		echo '<th><a href="#" onClick="buscarRegistros(0,'.$tipoorden0.')">Folio</a></th><th>Fecha</th><th>Tarjeta</th><th>Fecha Cuenta</th>
		<th><a href="#" onClick="buscarRegistros(1,'.$tipoorden1.')">Unidad</a></th>
		<th>Conductor</th>
		<th>Cuenta</th>
		<th>Condonacion</th>
		<th>Total Cuenta</th>
		<th>Monto</th>
		<th>Usuario<br>';
		echo '<select name="usuario" onchange="document.forma.usu.value=this.value;buscarRegistros('.$_POST['orden'].','.$_POST['tipoorden'].');"><option value="all">---Todos---</option>';
		$res1=mysql_db_query($base,"SELECT a.usuario FROM parque_abono as a WHERE 1 $filtro GROUP BY a.usuario ORDER BY a.usuario");
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
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'parque_abono.php\',\'\',\'201\','.$Abono['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				if(nivelUsuario()>2)
					echo '&nbsp;&nbsp;<a href="#" onClick="cancelarAbono('.$Abono['cve'].');"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></a>';
				echo '</td>';
			}
			echo '<td align="center">'.$Abono['folio'].'</td>';
			echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';
			if($Abono['estatus']=='C')
				echo '<td align="center"><font color="RED">'.$Abono['tarjeta'].'</font></td>';
			else
				echo '<td align="center">'.$Abono['tarjeta'].'</td>';
			echo '<td align="center">'.$Abono['fecha_cuenta'].'</td>';
			echo '<td align="center">'.$array_unidad[$Abono['unidad']].'</td>';
			echo '<td align="left">'.$array_nomconductor[$Abono['conductor']].'</td>';
			echo '<td align="right">'.number_format($Abono['cuenta']*$fac,2).'</td>';
			echo '<td align="right">'.number_format($Abono['condonacion']*$fac,2).'</td>';
			echo '<td align="right">'.number_format(($Abono['cuenta']-$Abono['condonacion'])*$fac,2).'</td>';
			echo '<td align="right">'.number_format($Abono['monto']*$fac,2).'</td>';
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$array_total[0]+=$Abono['cuenta']*$fac;
			$array_total[1]+=$Abono['condonacion']*$fac;
			$array_total[2]+=($Abono['cuenta']-$Abono['condonacion'])*$fac;
			$array_total[3]+=$Abono['monto']*$fac;
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

if($_POST['ajax']==2){
	mysql_db_query($base,"UPDATE parque_abono SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['usuario']."',obscan='".$_POST['obs']."' WHERE cve='".$_POST['abono']."'") or die(mysql_error());
	exit();
}

if($_POST['ajax']==3){
	$rsUni=mysql_db_query($base,"SELECT cve,propietario FROM parque WHERE recaudacion_local=1 AND no_eco='".strtoupper($_POST['eco2'])."'  AND estatus='1'");
	$Uni = mysql_fetch_array($rsUni);
	if($Uni['cve'] > 0){
		$correcto = true;
		if($correcto){
			$res = mysql_db_query($base,"SELECT MAX(fecha_cuenta) FROM parque_tarjetas WHERE unidad=".$Uni['cve']." AND estatus!='C'");
			$row=mysql_fetch_array($res);
			if($row[0] == "") $row[0] = fechaLocal();
			echo '0|'.$array_propietario[$Uni['propietario']].'|';
			echo date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($row[0]) ) );
			echo '|'.$Uni['cve'];
		}
		else{
			echo "-2";
		}
	}
	else{
		echo '-1|';
	}
	exit();
}

if($_POST['ajax']==7){
	$res=mysql_db_query($base,"SELECT * FROM parque_tarjetas WHERE cve='".$_POST['ftarjeta']."'");
	if($row=mysql_fetch_array($res)){
		if($row['estatus']=='C'){
			echo "-3|";
		}
		else{
			$res1=mysql_db_query($base,"SELECT cve FROM parque_abono WHERE tarjeta='".$row['cve']."' AND estatus!='C'");
			if(mysql_num_rows($res1)>0){
				echo "-2|";
			}
			else{
				$res1=mysql_db_query($base,"SELECT SUM(monto) FROM tarjeta_condonacion WHERE cvetar='".$row['cve']."' AND estatus!='C'");
				$row1=mysql_fetch_array($res1);
				echo $row['cve'].'|'.$row['fecha_cuenta'].'|'.$row['unidad'].'|'.$array_unidad[$row['unidad']].'|'.$row['conductor'].'|'.$array_nomconductor[$row['conductor']].'|'.$array_cuenta[$row['derrotero']].'|'.round($row1[0],2).'|'.$array_derroteros[$row['derrotero']];
			}
		}
	}
	else{
		echo "-1|";
	}
	
	exit();
}

top($_SESSION);


if($_POST['cmd']==201){
	$res = mysql_db_query($base,"SELECT * FROM parque_abono WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	
	$varimp="|Folio: ".$row['folio']."|";
	$varimp.="Usuario: ".$array_usuario[$row['usuario']].'|';
	$varimp.="Fecha Cuenta: ".$row['fecha_cuenta'].'|';
	$varimp.=$row['fecha']." ".$row['hora']."||";
	$varimp.="Unidad: ".$array_unidad[$row['unidad']]."|";
	$varimp.="Conductor: ";
	$varimp.=$array_conductor[$row['conductor']]."|";
	$varimp.="Cuenta: $ ".number_format($row['cuenta'],2)."|";
	$varimp.="Condonacion: $ ".number_format($row['condonacion'],2)."|";
	$varimp.="Total: $ ".number_format($row['cuenta']-$row['condonacion'],2)."|";
	$varimp.="Abono Unidad: $ ".number_format($row['monto'],2)."|";
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


if($_POST['cmd']==2){
	$res=mysql_db_query($base,"SELECT cve FROM parque_abono WHERE tarjeta='".$_POST['tarjeta']."' AND estatus!='C'");
	if(mysql_num_rows($res)==0){
		$res=mysql_db_query($base,"SELECT IFNULL(MAX(folio)+1,1) as folio FROM parque_abono");
		$row=mysql_fetch_array($res);
		$folio=$row['folio'];
		if($_POST['cverec']>0){
			mysql_db_query($base,"UPDATE parque_abono SET tarjeta='".$_POST['tarjeta']."',fecha_cuenta='".$_POST['fecha_cuenta']."',
			fecha='".fechaLocal()."',hora='".horaLocal()."',
			unidad='".$_POST['unidad']."',monto='".$_POST['monto']."',estatus='A',usuario='".$_POST['cveusuario']."',
			conductor='".$_POST['conductor']."',cuenta='".$_POST['cuenta']."',
			folio='$folio',condonacion='".$_POST['condonacion']."',mutualidad='".$_POST['mutualidad']."'
			WHERE cve='".$_POST['cverec']."'") or die(mysql_error()."2");
			$abono=$_POST['cverec'];
		}
		else{
			mysql_db_query($base,"INSERT parque_abono SET tarjeta='".$_POST['tarjeta']."',fecha_cuenta='".$_POST['fecha_cuenta']."',
			fecha='".fechaLocal()."',hora='".horaLocal()."',
			unidad='".$_POST['unidad']."',monto='".$_POST['monto']."',estatus='A',usuario='".$_POST['cveusuario']."',
			conductor='".$_POST['conductor']."',cuenta='".$_POST['cuenta']."',
			folio='$folio',condonacion='".$_POST['condonacion']."',mutualidad='".$_POST['mutualidad']."'") or die(mysql_error()."1");
			$abono=mysql_insert_id();
		}
		
		
		
		$res=mysql_db_query($base,"SELECT * FROM parque_abono WHERE cve='$abono'");
		$row=mysql_fetch_array($res);
		$mensaje.="<br><b>Se genero el Folio de Abono a unidades: ".$row['folio']." de la unidad ".$_POST['no_eco'].", con monto ".$_POST['monto']."</b>";
		
		$varimp="|Folio: ".$row['folio']."|";
		$varimp.="Usuario: ".$array_usuario[$row['usuario']].'|';
		$varimp.="Fecha Cuenta: ".$row['fecha_cuenta'].'|';
		$varimp.=$row['fecha']." ".$row['hora']."||";
		$varimp.="Unidad: ".$array_unidad[$row['unidad']]."|";
		$varimp.="Conductor: ";
		$varimp.=$array_conductor[$row['conductor']]."|";
		$varimp.="Cuenta: $ ".number_format($row['cuenta'],2)."|";
		$varimp.="Condonacion: $ ".number_format($row['condonacion'],2)."|";
		$varimp.="Total: $ ".number_format($row['cuenta']-$row['condonacion'],2)."|";
		$varimp.="Abono Unidad: $ ".number_format($row['monto'],2)."|";
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
	}
	else{
		$mensaje="La tarjeta ya se recaudo";
	}
	$_POST['recaudacion'] = 0;
	$_POST['cmd']=1;
}

if($_POST['cmd']==1){
	echo '<input type="hidden" name="cverec" value="0">';
	echo '<style>
		.divM {
			background:#DFE6EF;
			top:180px;
			left:150px;
			padding:5px;
			float:left;
			display:none;
			position:absolute;
			border-style: outset;
			width: 600px;
			heigth: 170px;
		}
	</style>';
	echo '<div class="divM" id="MostrarEco"><table border=1 width="100%">';
	echo '<input type="hidden" name="uni2" id="uni2" value="">';
	echo '<tr><th align="left">No. Eco.</th><td><input type="text" class="textField" name="eco2" id="eco2" size="5" onKeyUp="if(event.keyCode==13){ traeUni2();} else{ document.forma.uni2.value=\'\';}"><small><font color="RED">Dar enter</font></small></td></tr>';
	echo '<tr><th align="left">Propietario</th><td><input type="text" class="readOnly" name="propietario2" id="propietario2" size="50" readOnly></td></tr>';
	echo '<tr><th align="left">Conductor</td><td><select name="conductor2" id="conductor2"><option value="0">--- Seleccione ---</option>';
	$res=mysql_db_query($base,"SELECT * FROM conductores WHERE estatus=1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
	}
	echo '</select></td></tr>';
	//$fecha_cuenta=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime(fechaLocal()) ) );
	$fecha_cuenta="";
	echo '<tr><td align="left"><blink><font color="RED">Fecha de la Cuenta</font></blink></td><td><input type="text" name="fechahoy2" id="fechahoy2" class="readOnly" size="15" value="'.$fecha_cuenta.'" readOnly>';
	echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fechahoy2,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
	echo '</td></tr>';
	echo '<tr><td colspan="2"><input type="button" value="Guardar" onClick="if(document.forma.uni2.value==\'\') alert(\'La unidad no esta cargada correctamente\'); else if(document.forma.conductor2.value==\'0\') alert(\'Seleccione conductor\');  else if(document.forma.fechahoy2.value==\'\') alert(\'Seleccione la fecha\'); else{ atcr(\'parque_abono.php\',\'_blank\',6,\'0\'); document.forma.eco2.value=\'\';document.forma.uni2.value=\'\';document.forma.fechahoy2.value=\'\';document.forma.propietario2.value=\'\';document.forma.conductor2.options[0].selected=true;$(\'#MostrarEco\').hide();}">&nbsp;&nbsp;<input type="button" value="Cerrar" onClick="document.forma.eco2.value=\'\';document.forma.uni2.value=\'\';document.forma.conductor2.options[0].selected=true;document.forma.propietario2.value=\'\';$(\'#MostrarEco\').hide();">
		</td></tr>';
	echo '</table></div>';
	if($mensaje=="Error en la unidad"){
		echo '<script>alert("'.$mensaje.'");</script>';
	}
	elseif($mensaje!=""){
		echo $mensaje;
		echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
	}
	echo '<table><tr>';
	if(nivelUsuario()>1){
		echo '<td><a href="#" onClick="
		$(\'#panel\').show();
		if(document.forma.tarjeta.value==\'\'){
			$(\'#panel\').hide();
			alert(\'Necesita seleccionar la tarjeta\');
		}
		else if(document.forma.conductor.value==\'\'){
			$(\'#panel\').hide();
			alert(\'Necesita seleccionar el conductor\');
		}
		else if(document.forma.unidad.value==\'\'){
			$(\'#panel\').hide();
			alert(\'Necesita seleccionar la unidad\');
		}
		else if(document.forma.monto.value==\'\'){
			$(\'#panel\').hide();
			alert(\'Necesita ingresar el monto\');
		}
		else if((document.forma.monto.value/1)!=(document.forma.tcuenta.value/1)){
			$(\'#panel\').hide();
			alert(\'El total de la cuenta y el monto de la unidad deben de ser iguales\');
		}
		else{
			atcr(\'parque_abono.php\',\'\',2,\'0\');
		}
		"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onclick="$(\'#MostrarEco\').show();document.forma.fechahoy2.value=\'\';"><img src="images/nuevo.gif" border="0">&nbsp;&nbsp;Generar Tarjeta</a>&nbsp;&nbsp;</td>';
	}
	echo '<td><a href="#" onclick="$(\'#panel\').show();atcr(\'parque_abono.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
	echo '</tr></table>';
	echo '<br>';
	echo '<table>';
	$fecha=date( "Y-m-d" , strtotime ( "-1 day" , strtotime(fechaLocal()) ) );
	echo '<input type="hidden" name="unidad" id="unidad" value="">';
	echo '<input type="hidden" name="conductor" id="conductor" value="">';
	echo '<input type="hidden" name="tarjeta" id="tarjeta" value="">';
	echo '<tr><td>Tarjeta</td><td colspan="2"><input type="text" name="ftarjeta" id="ftarjeta" size="10" value="" class="textField" onKeyUp="if(event.keyCode==13){ traeTarjeta();}">
	&nbsp;<input style="display:none" id="limpiartarjeta" value="Quitar Tarjeta" onClick="quita_tarjeta()" class="textField"></td></tr>';
	echo '<tr><td align="left">Fecha</td><td colspan="2"><input type="text" name="fecha_cuenta" id="fecha_cuenta" class="readOnly" size="15" value="" readOnly>';
	echo '</td></tr>';
	echo '<tr><td>Num. Eco.</td><td colspan="2"><input type="text" name="num_eco" id="num_eco" size="10" value="" class="readOnly" readOnly></td></tr>';
	echo '<tr><td>Conductor</td><td colspan="2"><input type="text" name="nomconductor" id="nomconductor" size="100" value="" class="readOnly" readOnly></td></tr>';
	echo '<tr><td>Mutualidad</td><td colspan="2"><input type="text" name="mutualidad" id="mutualidad" size="10" value="" class="textField"></td></tr>';
	echo '<tr><td>Derrotero</td><td colspan="2"><input type="text" name="nomderrotero" id="nomderrotero" size="100" value="" class="readOnly" readOnly></td></tr>';
	echo '<tr><td>Cuenta</td><td colspan="2"><input type="text" name="cuenta" id="cuenta" size="10" value="" class="readOnly" readOnly></td></tr>';
	echo '<tr><td>Condonacion</td><td colspan="2"><input type="text" name="condonacion" id="condonacion" size="10" value="" class="readOnly" readOnly></td></tr>';
	echo '<tr><td>Total</td><td colspan="2"><input type="text" name="tcuenta" id="tcuenta" size="10" value="" class="readOnly" readOnly></td></tr>';
	echo '<tr><td>Abono Unidad</td><td colspan="2"><input type="text" name="monto" id="monto" size="10" class="textField"></td></tr>';
	//echo '<tr><td>Observaciones</td><td><textarea name="concepto" id="concepto" cols="50" rows="5"></textarea></td></tr>';
	echo '</table>';
	echo '<script>
	
			function traeTarjeta(){
				objeto1=crearObjeto();
				if (objeto1.readyState != 0) {
					alert("Error: El Navegador no soporta AJAX");
				} else {
					objeto1.open("POST","parque_abono.php",true);
					objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto1.send("ajax=7&ftarjeta="+document.forma.ftarjeta.value);
					objeto1.onreadystatechange = function(){
						if (objeto1.readyState==4){
							var datos=objeto1.responseText.split("|");
							if(datos[0]=="-1"){
								alert("La tarjeta no existe");
								quita_tarjeta();
							}
							else if(datos[0]=="-2"){
								alert("La tarjeta ya esta recaudada");
								quita_tarjeta();
							}
							else if(datos[0]=="-3"){
								alert("La tarjeta esta cancelada");
								quita_tarjeta();
							}
							else{
								document.forma.ftarjeta.readOnly=true;
								document.forma.tarjeta.value=datos[0];
								document.forma.fecha_cuenta.value=datos[1];
								document.forma.unidad.value=datos[2];
								document.forma.num_eco.value=datos[3];
								document.forma.conductor.value=datos[4];
								document.forma.nomconductor.value=datos[5];
								document.forma.cuenta.value=datos[6];
								document.forma.condonacion.value=datos[7];
								document.forma.nomderrotero.value=datos[8];
								tcuenta = datos[6]-datos[7];
								document.forma.tcuenta.value=tcuenta.toFixed(2);
								document.forma.mutualidad.value=20.00;
							}
						}
					}
				}
			}
			
			
			function quita_tarjeta(){
				document.forma.tarjeta.value="";
				document.forma.ftarjeta.value="";
				document.forma.fecha_cuenta.value="";
				document.forma.unidad.value="";
				document.forma.num_eco.value="";
				document.forma.conductor.value="";
				document.forma.nomconductor.value="";
				document.forma.tarjeta.value="";
				document.forma.cuenta.value="";
				document.forma.condonacion.value="";
				document.forma.tcuenta.value="";
				document.forma.nomderrotero.value="";
				document.forma.mutualidad.value="";
			}
			
			function traeUni2(){
				objeto1=crearObjeto();
				if (objeto1.readyState != 0) {
					alert("Error: El Navegador no soporta AJAX");
				} else {
					objeto1.open("POST","parque_abono.php",true);
					objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto1.send("ajax=3&eco2="+document.forma.eco2.value);
					objeto1.onreadystatechange = function(){
						if (objeto1.readyState==4){
							var datos=objeto1.responseText.split("|");
							if(datos[0]=="-1"){
								alert("Error en la unidad");
								document.forma.propietario2.value="";
								document.forma.fechahoy2.value="";
								document.forma.uni2.value="";
							}
							else if(datos[0]=="-2"){
								alert("La unidad tiene tarjetas sin recaudar");
								document.forma.propietario2.value="";
								document.forma.fechahoy2.value="";
								document.forma.uni2.value="";
							}
							else{
								document.forma.propietario2.value=datos[1];
								document.forma.fechahoy2.value=datos[2];
								document.forma.uni2.value=datos[3];
							}
						}
					}
				}
			}
			
						
			';
			echo '
		  </script>';
}

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		if($impresion != ""){
			echo '<div style="visibility:hidden;position:absolute;">'.$impresion.'</div>';
		}
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'parque_abono.php\',\'\',1,0);"><img src="images/nuevo.gif" border="0">&nbsp;&nbsp;Abonar</a>&nbsp;&nbsp;</td>';
		echo '
				<td><a href="#" onClick="atcr(\'parque_abono.php\',\'_blank\',\'200\',\'\')"><img src="images/b_print.png" border="0" title="Imprimir">&nbsp;Imprimir</a></td>
			  </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>No. Economico</td><td><input type="text" size="5" class="textField" name="no_eco" id="no_eco"></td></tr>';
		echo '<tr><td>Credencial</td><td><input type="text" size="5" class="textField" name="credencial" id="credencial"></td></tr>';
		echo '<tr><td>Nombre</td><td><input type="text" size="50" class="textField" name="nombre" id="nombre"></td></tr>';
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
			objeto.open("POST","parque_abono.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&orden="+orden+"&tipoorden="+tipoorden+"&credencial="+document.getElementById("credencial").value+"&nombre="+document.getElementById("nombre").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&no_eco="+document.getElementById("no_eco").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	function cancelarAbono(numabono){
	  if(confirm("¿Esta seguro de cancelar el abono?")){
		obs=prompt("Observaciones:");
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","parque_abono.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&abono="+numabono+"&obs="+obs+"&usuario='.$_POST['cveusuario'].'");
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
				//alert(objeto.responseText);
				buscarRegistros(0,1);}
			}
		}
	  }
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