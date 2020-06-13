<?
include("main.php");
include("numlet.php");

//ARREGLOS
$rsPlaza=mysql_db_query($base,"SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['nombre'];
}

$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$res=mysql_db_query($base,"SELECT * FROM propietarios order by nombre");
while($row=mysql_fetch_array($res)){
	$array_nompropietario[$row['cve']]=$row['nombre'];
}

$res=mysql_db_query($base,"SELECT * FROM motivos_descuentos order by nombre");
$array_descuentos=array();
while($row=mysql_fetch_array($res)){
	$array_descuentos[$row['cve']]=$row['nombre'];
}

$res=mysql_db_query($base,"SELECT * FROM conductores");
while($row=mysql_fetch_array($res)){
	$array_cveconductor[$row['cve']]=$row['credencial'];
	$array_nomconductor[$row['cve']]=$row['nombre'];
}


$res=mysql_db_query($base,"SELECT * FROM gruposuni order by nombre");
while($row=mysql_fetch_array($res)){
	$array_grupos[$row['cve']]=$row['nombre'];
}

$rsUnidad=mysql_db_query($base,"SELECT * FROM parque");
while($Unidad=mysql_fetch_array($rsUnidad)){
	$array_unidad[$Unidad['cve']]=$Unidad['no_eco'];
	$array_unidadmotor[$Unidad['cve']]=$Unidad['motor'];
	$array_unidadserie[$Unidad['cve']]=$Unidad['serie'];
	$array_unidadmodelo[$Unidad['cve']]=$Unidad['modelo'];
	$array_propietario[$Unidad['cve']]=$array_nompropietario[$Unidad['propietario']];
	$array_unidadrec[$Unidad['cve']]=$Unidad['tipo_recaudacion'];
	//$array_tipo_unidad[$Unidad['cve']]=$array_tipo_vehiculo[$Unidad['tipo_vehiculo']];
	$array_saldo_inicial[$Unidad['cve']]=$Unidad['saldo_inicial'];
}



$abono=0;

if($_POST['cmd']==102){
	include('fpdf153/fpdf.php');
	include("numlet.php");	
	class FPDF2 extends PDF_MC_Table {
		function Header(){
			global $_POST,$array_grupos;
			$tit='';
			if($_POST['cvegrupo']!="all") $tit.='<br>Grupo: '.$array_grupos[$_POST['cvegrupo']];
			$this->SetFont('Arial','B',16);
			//$this->Cell(190,10,'Autobuses Rapidos del Valle de Mexico',0,0,'C');
			//$this->Ln();
			$this->SetY(23);
			$this->MultiCell(275,5,'Listado de abonos de cuenta de unidades del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'],0,'C');
			$this->MultiCell(275,5,$tit,0,'C');
			$this->Ln();
			$this->SetFont('Arial','B',9);
			$this->Cell(15,4,'Folio',0,0,'C',0);
			$this->Cell(15,4,'Fecha',0,0,'C',0);
			$this->Cell(20,4,'Fecha C',0,0,'C',0);
			$this->Cell(15,4,'Unidad',0,0,'C',0);
			$this->Cell(50,4,'Conductor',0,0,'C',0);
			$this->Cell(20,4,'Cuenta',0,0,'C',0);
			$this->Cell(20,4,'Inversiones',0,0,'C',0);
			$this->Cell(20,4,'Monto',0,0,'C',0);
			$this->Cell(20,4,'Deuda',0,0,'C',0);
			$this->Cell(50,4,'Observaciones',0,0,'C',0);
			$this->Cell(30,4,'Usuario',0,0,'C',0);
			$this->Ln();		
		}
		//Pie de página
		function Footer(){
			//Posición: a 1,5 cm del final
			$this->SetY(-15);
			//Arial bold 12
			$this->SetFont('Arial','B',11);
			//Número de página
			$this->Cell(0,10,'Página '.$this->PageNo().' de {nb}',0,0,'C');
		}
	}
	$pdf=new FPDF2('L','mm','LETTER');
	$pdf->AliasNbPages();
	$pdf->AddPage('L');
	$pdf->SetFont('Arial','',9);
	$pdf->SetWidths(array(15,15,20,15,50,20,20,20,20,50,30));
	$pdf->SetAligns(array('C','C','C','C','L','R','R','R','R','L','L'));
	$sumacargo=array();
	$filtro="";
	$select= " SELECT a.* FROM parque_abono as a INNER JOIN parque as b ON (b.cve=a.unidad";
	if($_POST['no_eco']!="") $select.=" AND b.no_eco='".$_POST['no_eco']."'";
	if($_POST['cvegrupo']!="all") $select.=" AND b.cvegrupo='".$_POST['cvegrupo']."'";
	$select.=") WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	if($_POST['tipo_recaudacion']!="all") $select.=" AND b.tipo_recaudacion='".$_POST['tipo_recaudacion']."'";
	//if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; $filtro=" AND a.plaza='".$_POST['plaza']."'";}
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	if($_POST['estatus']!="all") $select.=" AND a.estatus='".$_POST['estatus']."'";
	$select.=" ORDER BY a.cve DESC";
	$rsabonos=mysql_db_query($base,$select) or die(mysql_error());
	$x=0;
	while ($Abono=mysql_fetch_array($rsabonos)){	
		$estatus='';
		if($Abono['estatus']=='C'){
			$estatus='(C)';
			if($_POST['estatus']!='C'){
				$Abono['monto_cuenta']=0;
				$Abono['monto_vuelta']=0;
				$Abono['monto_descuentos']=0;
				$Abono['monto']=0;
				$Abono['deuda_ope']=0;
			}
		}
		$renglon=array();
		$renglon[]=$Abono['cve'].$estatus;
		$renglon[]=$Abono['fecha'];
		$renglon[]=$Abono['fecha_rec'];
		$renglon[]=$array_unidad[$Abono['unidad']];
		$renglon[]=$array_nomconductor[$Abono['conductor']];
		if($Abono['tipo_recaudacion']==0)
			$cuenta=round($Abono['monto_cuenta'],2);
		else
			$cuenta=round($Abono['monto_vuelta']*$Abono['vueltas'],2);
		$renglon[]=number_format($cuenta,2);
		$renglon[]=number_format($Abono['monto_descuentos'],2);
		$renglon[]=number_format($Abono['monto'],2);
		$renglon[]=number_format($Abono['deuda_ope'],2);
		$renglon[]=$Abono['obs'];
		$renglon[]=$array_usuario[$Abono['usuario']];
		$pdf->Row($renglon);
		$x++;
		$sumacargo[0]+=$cuenta;
		$sumacargo[1]+=$Abono['monto_descuentos'];
		$sumacargo[2]+=$Abono['monto'];
		$sumacargo[3]+=$Abono['deuda_ope'];
	}
	$pdf->Ln();
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(90,4,$x.' Registro(s)',0,0,'C',0);
	$pdf->Cell(25,4,'Totales',0,0,'R',0);
	foreach($sumacargo as $v){
		$pdf->Cell(20,4,number_format($v,2),0,0,'R',0);
	}
	$pdf->Output();
	exit();	
}	

if($_POST['cmd']==101){
	$filtro="";
	$usuario="Todos";
	if($_POST['usu']!="all"){
		$filtro.=" AND usuario='".$_POST['usuario']."'";
		$usuario=$array_usuario[$_POST['usu']];
	}
	if($_POST['tipo_recaudacion']!="all") $filtro.=" AND b.tipo_recaudacion='".$_POST['tipo_recaudacion']."'";
	$res=mysql_db_query($base,"SELECT b.tipo_recaudacion,count(a.cve),SUM(if(a.estatus!='C',a.monto,0)) FROM parque_abono as a INNER JOIN parque as b ON (b.cve=a.unidad) WHERE a.fecha='".$_POST['fecha_ini']."' $filtro GROUP BY b.tipo_recaudacion ORDER BY b.tipo_recaudacion") or die(mysql_error());
	
	$varimp="Corte Abono Autobuses||Fecha Corte: ".$_POST['fecha_ini']."|";
	$varimp.="Usuario: ".$usuario.'|';
	$varimp.=fechaLocal()." ".horaLocal()."||";
	$tot1=$tot2=0;
	while($row=mysql_fetch_array($res)){
		$varimp.=$array_tipo_recaudacion[$row[0]].'|';
		$varimp.="No Reg: ".$row[1]."||";
		$varimp.="Importe: $ ".number_format($row[2],2)."||";
		$tot1+=$row[1];
		$tot2+=$row[2];
	}
	if($_POST['tipo_recaudacion']=="all"){
		$varimp.='Totales:|';
		$varimp.="No Reg: ".$tot1."||";
		$varimp.="Importe: $ ".number_format($tot2,2)."||";
	}
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$varimp.'&copia=1" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",2000);</script>';
	exit();
}

if($_POST['cmd']==100){
	$res=mysql_db_query($base,"SELECT * FROM parque_abono WHERE cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$res1=mysql_db_query($base,"SELECT * FROM parque_tarjeta WHERE cve='".$row['cvetar']."'");
	$row1=mysql_fetch_array($res1);
	$varimp=chr(27)."@"."|Abono Unidades||Folio: ".$row['cve']."|";
	$varimp.="Usuario: ".$array_usuario[$row['usuario']].'|';
	$varimp.=$row['fecha']." ".$row['hora']."||";
	$varimp.="Tipo Rec: ".$array_tipo_recaudacion[$row['tipo_recaudacion']]."|";
	$varimp.="Unidad: ".$array_unidad[$row['unidad']]." Autobus|";
	$varimp.="Conductor: ".$array_cveconductor[$row['conductor']]." Autobus|";
	if($row['tipo_recaudacion']==0){
		$varimp.="Monto Cuenta: $ ".number_format($row['monto_cuenta'],2)."|";
		$cuenta=$row['monto_cuenta'];
	}
	else{
		$varimp.="Monto Vuelta: $ ".number_format($row['monto_vuelta'],2)."|";
		$varimp.="Vueltas: $ ".number_format($row['vueltas'],2)."|";
		$cuenta=$row['monto_vuelta']*$row['vueltas'];
	}
	$varimp.="Tot Cuenta: $ ".number_format($cuenta,2)."|";
	$varimp.="-----------------------------------|Inversiones|";
	$res1=mysql_db_query($base,"SELECT * FROM parque_abonodescuentos WHERE abono='".$_POST['reg']."' ORDER BY cve");
	while($row1=mysql_fetch_array($res1)){
		$varimp.=$array_descuentos[$row1['cvedescuento']].": $ ".number_format($row1['monto'],2)."|";
	}
	$varimp.="-----------------------------------|";
	$varimp.="Tot Ainveriones: $ ".number_format($row['monto_descuentos'],2)."|";
	$varimp.="Tot a Recaudar: $ ".number_format($cuenta-$row['monto_descuentos'],2)."|";
	$varimp.="Monto: $ ".number_format($row['monto'],2)."|";
	$varimp.="Deuda: $ ".number_format($row['deuda_ope'],2)."|";
	$varimp.="|||_____________________________|Firma|";
	$varimp.=chr(29)."h".chr(80).chr(29)."H".chr(2).chr(29)."k".chr(2)."1".sprintf("%011s",(intval($row['cve'])))." |";
	$impresion='<iframe src="http://localhost/impresiongeneral.php?textoimp='.$varimp.'" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",2000);</script>';
	exit();
}

if($_POST['cmd']==2){
	if($_POST['unidad']==0){
		$rsUni=mysql_db_query($base,"SELECT cve FROM parque WHERE plaza='".$_POST['plaza']."' AND no_eco='".strtoupper($_POST['no_eco'])."' AND estatus='1'");
		if($Uni=mysql_fetch_array($rsUni))
			$_POST['unidad']=$Uni['cve'];
		else
			$mensaje="Error en la unidad";
	}
	if($_POST['unidad']>0){
		$res=mysql_db_query($base,"SELECT * FROM parque_abono WHERE estatus!='C' ORDER BY cve DESC LIMIT 1");
		$row=mysql_fetch_array($res);
		if($row['unidad']!=$_POST['unidad'] || $_POST['monto']!=$row['monto']){
			//$res1=mysql_db_query($base,"SELECT * FROM parque_tarjeta WHERE cve='".$_POST['cvetar']."'");
			//$row1=mysql_fetch_array($res1);
			mysql_db_query($base,"INSERT parque_abono SET fecha='".fechaLocal()."',hora='".horaLocal()."',km='".$_POST['km']."',fecha_rec='".$_POST['fecha_rec']."',
			unidad='".$_POST['unidad']."',conductor='".$_POST['conductor']."',monto='".$_POST['monto']."',estatus='A',usuario='".$_SESSION['CveUsuario']."',
			cvetar='".$_POST['cvetar']."',tipo_recaudacion='".$_POST['tipo_recaudacion']."',monto_vuelta='".$_POST['imp_vuelta']."',
			monto_cuenta='".$_POST['imp_cuenta']."',obs='".$_POST['obs']."',vueltas='".$_POST['vueltas']."',
			monto_descuentos='".$_POST['imp_descuentos']."',deuda_ope='".$_POST['deuda_ope']."'") or die(mysql_error());
			$abono=mysql_insert_id();
			for($i=0;$i<$_POST['cantdescuentos'];$i++){
				if($_POST['montodesc'.$i]>0){
					mysql_db_query($base,"INSERT parque_abonodescuentos SET abono='".$abono."',cvedescuento='".$_POST['motivodesc'.$i]."',
						monto='".$_POST['montodesc'.$i]."'");
				}
			}
			//mysql_db_query($base,"UPDATE parque_tarjeta SET estatus='P' WHERE cve='".$_POST['cvetar']."'");
			$res=mysql_db_query($base,"SELECT * FROM parque_abono WHERE cve='".$abono."'");
			$row=mysql_fetch_array($res);
			/*$varimp="Emplacamiento||Folio: ".$row['cve']."|";
			$varimp.="Usuario: ".$array_usuario[$row['usuario']].'|';
			$varimp.=$row['fecha']." ".$row['hora']."||";
			$varimp.="Unidad: ".$array_unidad[$row['unidad']]." Camioneta|";
			$varimp.=$array_propietario[$row['unidad']]."||";
			$varimp.="Monto: $ ".number_format($row['monto'],2)."|";
			$varimp.=numlet($row['monto'])."||";
			$varimp.=chr(29)."h".chr(80).chr(29)."H".chr(2).chr(29)."k".chr(2)."1".sprintf("%011s",(intval($row['cve'])))." |";
			$impresion='<iframe src="http://localhost/impresiongeneral.php?textoimp='.$varimp.'&copia=1" width=200 height=200></iframe>';*/
		}
		else{
			$mensaje='Error no se puede recaudar la misma unidad con el mismo monto de manera consecutiva';
		}
	}
	$_POST['cmd']=1;
}

if($_POST['ajax']==1){
	$filtro="";
	$select= " SELECT a.* FROM parque_abono as a INNER JOIN parque as b ON (b.cve=a.unidad";
	if($_POST['no_eco']!="") $select.=" AND b.no_eco='".$_POST['no_eco']."'";
	if($_POST['cvegrupo']!="all") $select.=" AND b.cvegrupo='".$_POST['cvegrupo']."'";
	$select.=") WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	if($_POST['tipo_recaudacion']!="all") $select.=" AND a.tipo_recaudacion='".$_POST['tipo_recaudacion']."'";
	//if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; $filtro=" AND a.plaza='".$_POST['plaza']."'";}
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	if($_POST['estatus']!="all") $select.=" AND a.estatus='".$_POST['estatus']."'";
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
	$rsabonos=mysql_db_query($base,$select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		if($_SESSION['PlazaUsuario']==0) $col=16;
		else $col=15;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.mysql_num_rows($rsabonos).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		if($_SESSION['PlazaUsuario']==0) 
			echo '<th>Plaza</th>';
		echo '<th><a href="#" onClick="buscarRegistros(0,'.$tipoorden0.')">Folio</a></th><th>Fecha</th><th>Fecha Cuenta</th>
		<th><a href="#" onClick="buscarRegistros(1,'.$tipoorden1.')">Unidad</a></th><th>Conductor</th>
		<th>Tipo Recaudacion</th><th>Total Cuenta</th><th>Inversiones</th>
		<th>Monto</th><th>Deuda</th>
		<th>Observacion</th>
		<th>Usuario<br>';
		echo '<select name="usuario" onchange="document.forma.usu.value=this.value;buscarRegistros('.$_POST['orden'].','.$_POST['tipoorden'].');"><option value="all">---Todos---</option>';
		$res1=mysql_db_query($base,"SELECT a.usuario FROM parque_abono as a WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' $filtro GROUP BY a.usuario ORDER BY a.usuario");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['usuario'].'"';
			if($row1['usuario']==$_POST['usu']) echo ' selected';
			echo '>'.$array_usuario[$row1['usuario']].'</option>';
		}
		echo '</select></th></tr>'; 
		$sumacargo=array();
		$x=0;
		while ($Abono=mysql_fetch_array($rsabonos)){	
			rowb();
			$estatus='';
			if($Abono['estatus']=='C'){
				$estatus='(CANCELADO)';
				if($_POST['estatus']!='C'){
					$Abono['monto_cuenta']=0;
					$Abono['monto_vuelta']=0;
					$Abono['monto_descuentos']=0;
					$Abono['monto']=0;
					$Abono['deuda_ope']=0;
				}
				echo '<td align="center">CANCELADO</td>';
			}
			else{
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'parque_abono.php\',\'_blank\',\'100\','.$Abono['cve'].');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				if($_SESSION[$archivo[(count($archivo)-1)]]>2)
					echo '&nbsp;&nbsp;<a href="#" onClick="cancelarAbono('.$Abono['cve'].');"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></a>';
				echo '</td>';
			}
			if($_SESSION['PlazaUsuario']==0)
				echo '<td>'.htmlentities($array_plaza[$Abono['plaza']]).'</td>';
			echo '<td align="center">'.$Abono['cve'].'</td>';
			echo '<td align="center">'.$Abono['fecha'].'</td>';
			echo '<td align="center">'.$Abono['fecha_rec'].'</td>';
			echo '<td align="center">'.$array_unidad[$Abono['unidad']].'</td>';
			echo '<td align="center">'.$array_nomconductor[$Abono['conductor']].'</td>';
			echo '<td align="center">'.$array_tipo_recaudacion[$Abono['tipo_recaudacion']].'</td>';
			if($Abono['tipo_recaudacion']==0)
				$cuenta=round($Abono['monto_cuenta'],2);
			else
				$cuenta=round($Abono['monto_vuelta']*$Abono['vueltas'],2);
			echo '<td align="right">'.number_format($cuenta,2).'</td>';
			echo '<td align="right">'.number_format($Abono['monto_descuentos'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['monto'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['deuda_ope'],2).'</td>';
			echo '<td align="left">'.$Abono['obs'].'</td>';
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$sumacargo[0]+=$cuenta;
			$sumacargo[1]+=$Abono['monto_descuentos'];
			$sumacargo[2]+=$Abono['monto'];
			$sumacargo[3]+=$Abono['deuda_ope'];
		}
		if($_SESSION['PlazaUsuario']==0) $col=7;
		else $col=6;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		foreach($sumacargo as $v)
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
		echo '<td bgcolor="#E9F2F8" colspan="3">&nbsp;</td>';
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
	mysql_db_query($base,"UPDATE parque_abono SET estatus='C',usucan='".$_SESSION['CveUsuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['abono']."'");
	exit();
}

if($_POST['ajax']==5){
	$rsParque=mysql_db_query($base,"SELECT * FROM parque WHERE plaza='".$_POST['plaza']."' AND estatus='1' ORDER BY no_eco");
	while($Parque=mysql_fetch_array($rsParque)){
		echo $Parque['cve'].','.$Parque['no_eco'].'|';
	}
	exit();
}	

if($_POST['ajax']==6){
	if($_POST['unidad']!=0){
		$res=mysql_db_query($base,"SELECT * FROM parque WHERE cve='".$_POST['unidad']."' AND estatus='1'");
		if($row=mysql_fetch_array($res)){
			$_POST['unidad']=$row['cve'];
			$tipo_vehiculo=$array_tipo_vehiculo[$row['tipo_vehiculo']];
			$pagodecenal=$row['monto_tarjeta']+$row['monto_administracion'];
			$cuenta=$row['monto_cuenta'];
			//$rsCosto=mysql_db_query($base,"SELECT * FROM costos_piso WHERE tipo_unidad='".$row['tipo_vehiculo']."' AND plaza='".$row['plaza']."' ORDER BY cve DESC");
			//$Costo=mysql_fetch_array($rsCosto);
			//$abo_diario=$Costo['costo'];
		}
		else{
			$_POST['unidad']=0;
		}	
	}
	elseif($_POST['no_eco']!=""){
		$res=mysql_db_query($base,"SELECT * FROM parque WHERE plaza='".$_POST['plaza']."' AND no_eco='".$_POST['no_eco']."' AND estatus='1'");
		if($row=mysql_fetch_array($res)){
			$_POST['unidad']=$row['cve'];
			$tipo_vehiculo=$array_tipo_vehiculo[$row['tipo_vehiculo']];
			$pagodecenal=$row['monto_tarjeta']+$row['monto_administracion'];
			$cuenta=$row['monto_cuenta'];
			//$rsCosto=mysql_db_query($base,"SELECT * FROM costos_piso WHERE tipo_unidad='".$row['tipo_vehiculo']."' AND plaza='".$row['plaza']."' ORDER BY cve DESC");
			//$Costo=mysql_fetch_array($rsCosto);
			//$abo_diario=$Costo['costo'];
		}
		else{
			$_POST['unidad']=0;
		}	
	}
	else{
		$_POST['unidad']=0;
	}
	$abo_diario='';
	echo $_POST['unidad'].'|'.$abo_diario.'|Camioneta|'.$array_unidadrec[$_POST['unidad']].'|'.round($pagodecenal,2).'|'.$cuenta;
	exit();
}

if($_POST['ajax']==7){
	$res=mysql_db_query($base,"SELECT * FROM parque WHERE no_eco='".$_POST['no_eco']."'");
	if($row=mysql_fetch_array($res)){
		if($row['estatus']!=1){
			echo '-1|';
		}
		else{
			$res1=mysql_db_query($base,"SELECT * FROM parque WHERE cve='".$row['cve']."'");
			$row1=mysql_fetch_array($res1);
			$rsAbonos=mysql_db_query($base,"SELECT sum(monto) as abonos FROM parque_abono WHERE unidad='".$row['unidad']."'  AND estatus!='C' GROUP BY unidad");
			$Abono=mysql_fetch_array($rsAbonos);
			$rsCargos=mysql_db_query($base,"SELECT SUM(monto) FROM cargos_parque WHERE unidad='".$row['unidad']."'");
			$Cargo=mysql_fetch_array($rsCargos);
			$saldoanterior=$array_saldo_inicial[$row1['cve']]+$Abono[0]-$Cargo[0];
			echo $row['cve'].'|'.$row1['cve'].'|'.$array_unidad[$row1['cve']],'|'.$row1['conductor'].'|'.$array_nomconductor[$row1['conductor']].'|'.$row1['monto_cuenta'].'|'.$row1['monto_vuelta'];
		}
	}
	else{
		echo '0|';
	}
	exit();
}


/*if($_POST['ajax']==7){
	$res=mysql_db_query($base,"SELECT * FROM parque_tarjeta WHERE folio='".$_POST['tarjeta']."'");
	if($row=mysql_fetch_array($res)){
		if($row['estatus']=='P'){
			echo '-1|';
		}
		elseif($row['estatus']=='C'){
			echo '-2|';
		}
		else{
			$res1=mysql_db_query($base,"SELECT * FROM parque WHERE cve='".$row['unidad']."'");
			$row1=mysql_fetch_array($res1);
			$rsAbonos=mysql_db_query($base,"SELECT sum(monto) as abonos FROM parque_abono WHERE unidad='".$row['unidad']."'  AND estatus!='C' GROUP BY unidad");
			$Abono=mysql_fetch_array($rsAbonos);
			$rsCargos=mysql_db_query($base,"SELECT SUM(monto) FROM cargos_parque WHERE unidad='".$row['unidad']."'");
			$Cargo=mysql_fetch_array($rsCargos);
			$saldoanterior=$array_saldo_inicial[$row['unidad']]+$Abono[0]-$Cargo[0];
			echo $row['cve'].'|'.$row['fecha'].'|'.$array_unidad[$row['unidad']].'|'.$array_cveconductor[$row['conductor']].'|'.$array_unidadrec[$row['unidad']].'|'.round($row1['monto_tarjeta']+$row1['monto_administracion'],2).'|'.$row1['monto_cuenta'].'|'.round($saldoanterior,2);
		}
	}
	else{
		echo '0|';
	}
	exit();
}*/

top($_SESSION);

if($_POST['cmd']==1){
	if($mensaje=="Error en la unidad"){
		echo '<script>alert("'.$mensaje.'");</script>';
	}
	elseif($mensaje!=""){
		echo $mensaje;
		echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
	}
	if($abono>0){
		echo '<script>atcr("parque_abono.php","_blank","100",'.$abono.');</script>';
	}
	echo '<table><tr>';
	if($_SESSION[$archivo[(count($archivo)-1)]]>1){
		echo '<td><a href="#" onClick="
		if(document.forma.plaza.value==\'0\')
			alert(\'Necesita seleccionar la plaza\');
		else if(document.forma.unidad.value==\'\')
			alert(\'Necesita seleccionar la unidad\');
		else if(document.forma.conductor.value==\'\' || document.forma.conductor.value==\'0\')
			alert(\'No se encontro conductor\');
		else if(document.forma.clickguardar.value==\'no\'){
			atcr(\'parque_abono.php\',\'\',2,\'0\');
			document.forma.clickguardar.value=\'si\';
		}
		"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	}
	echo '<td><a href="#" onclick="atcr(\'parque_abono.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
	echo '</tr></table>';
	echo '<br>';
	echo '<table>';
	if($_SESSION['PlazaUsuario']==0){
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField" onChange="traeUnidades(this.value);"><option value="0">---Todas---</option>';
		foreach($array_plaza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
	}
	else{
		echo '<input type="hidden" name="plaza" id="plaza" value="'.$_SESSION['PlazaUsuario'].'">';
	}
	echo '<tr><td align="left">Fecha</td><td><input type="text" name="fechahoy" id="fechahoy"  size="15" class="readOnly" value="'.fechaLocal().'" readOnly>&nbsp;</td></tr>';
	echo '<tr><td align="left">Fecha Cuenta</td><td><input type="text" name="fecha_rec" id="fecha_rec"  size="15" class="readOnly" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_rec,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	//echo '<input type="hidden" name="cvetar" id="cvetar" size="10" value="" class="readOnly" readOnly>';
	//echo '<input type="hidden" name="fecha_viaje" id="fecha_viaje" size="10" value="" class="readOnly" readOnly>';
	//echo '<tr><td>Salida</td><td><input type="text" name="tarjeta" id="tarjeta" size="10" class="textField" value="" onKeyUp="if(event.keyCode==13){ traeTarjeta();}"></td></tr>';
	echo '<tr><td>Tipo Recaudacion</td><td><select name="tipo_recaudacion" id="tipo_recaudacion" onChange="
	$(\'.ccuenta\').hide();
	$(\'.cvueltas\').hide();
	if(this.value==0) $(\'.ccuenta\').show();
	else $(\'.cvueltas\').show();">';
	foreach($array_tipo_recaudacion as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<input type="hidden" name="unidad" id="unidad" size="10" value="" class="readOnly" readOnly>';
	echo '<tr><td>Unidad</td><td><input type="text" name="no_eco" id="no_eco" size="10" class="textField" value="" onKeyUp="if(event.keyCode==13){ traeUnidad();}"></td></tr>';
	echo '<input type="hidden" name="conductor" id="conductor" size="10" value="" class="readOnly" readOnly>';
	echo '<tr><td>Conductor</td><td><input type="text" name="nomconductor" id="nomconductor" size="50" class="readOnly" value="" readOnly></td></tr>';
	echo '<tr class="ccuenta"><td>Importe de la cuenta</td><td><input type="text" name="imp_cuenta" id="imp_cuenta" size="10" class="readOnly" value="" readOnly></td></tr>';
	echo '<tr style="display:none" class="cvueltas"><td>Importe por vuelta</td><td><input type="text" name="imp_vuelta" id="imp_vuelta" size="10" class="readOnly" value="" readOnly></td></tr>';
	echo '<tr style="display:none" class="cvueltas"><td>Vueltas</td><td><input type="text" name="vueltas" id="vueltas" size="10" class="textField" onKeyUp="calcular()"></td></tr>';
	echo '<tr><td>Total Cuenta</td><td><input type="text" name="tot_cuenta" id="tot_cuenta" size="10" class="readOnly" readOnly></td></tr>';
	echo '<tr><th align="left">Inversiones<br><input type="button" class="textField" value="Agregar" onClick="agregarDescuentos()"></th><td>
	<table id="tabladescuentos"><tr><th>Motivo</th><th>Monto</th></tr><tr><td>
	<select name="motivodesc0" id="motivodesc0"><option value="0">Seleccione</option>';
	foreach($array_descuentos as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td><td><input type="text" class="textField" size="10" name="montodesc0" id="montodesc0" onKeyUp="calcular()"></td></tr></table></td></tr>';
	echo '<input type="hidden" name="cantdescuentos" id="cantdescuentos" value="1">';
	echo '<tr><td>Total Inveriones</td><td><input type="text" name="imp_descuentos" id="imp_descuentos" size="10" class="readOnly" readOnly></td></tr>';
	echo '<tr><td>Total a recaudar</td><td><input type="text" name="tot_recaudar" id="tot_recaudar" size="10" class="readOnly" readOnly></td></tr>';
	echo '<tr><td>Monto</td><td><input type="text" name="monto" id="monto" size="10" class="textField" onKeyUp="calcular();"></td></tr>';
	echo '<tr><td>Deuda operador</td><td><input type="text" name="deuda_ope" id="deuda_ope" size="10" class="readOnly" readOnly></td></tr>';
	echo '<tr><td>Observaciones</td><td><textarea name="obs" id="obs" cols="30" rows="3"></textarea></td></tr>';
	echo '<input type="hidden" name="tiporec" value="">';
	echo '</table>';
	echo '<input type="hidden" name="clickguardar" id="clickguardar" value="no">';
	echo '<div id="idcargos"></div>';
	echo '<script>
			//document.forma.tarjeta.focus();
			
			function calcular(){
				var total=0;
				for(i=0;i<(document.forma.cantdescuentos.value/1);i++){
					total+=(document.forma["montodesc"+i].value/1);
				}
				document.forma.imp_descuentos.value=total.toFixed(2);
				if(document.forma.tipo_recaudacion.value==\'0\'){
					total=document.forma.imp_cuenta.value/1;
				}
				else{
					total=document.forma.imp_vuelta.value*(document.forma.vueltas.value/1);
				}
				document.forma.tot_cuenta.value=total.toFixed(2);
				total=document.forma.tot_cuenta.value-document.forma.imp_descuentos.value;
				document.forma.tot_recaudar.value=total.toFixed(2);
				total=document.forma.tot_recaudar.value-document.forma.monto.value;
				document.forma.deuda_ope.value=total.toFixed(2);
				if(document.forma.deuda_ope.value<0) document.forma.deuda_ope.value=0;
			}
			
			function agregarDescuentos(){
				ren=document.forma.cantdescuentos.value;
				$("#tabladescuentos").append(\'<tr><td><select name="motivodesc\'+ren+\'" id="motivodesc\'+ren+\'"><option value="0">Seleccione</option>';
				foreach($array_descuentos as $k=>$v){
					echo '<option value="'.$k.'">'.$v.'</option>';
				}
				echo '</select></td><td><input type="text" class="textField" size="10" name="montodesc\'+ren+\'" id="montodesc\'+ren+\'" onKeyUp="calcular()"></td></tr>\');
				ren++;
				document.forma.cantdescuentos.value=ren;
			}
			
			function validaDatos(){
				if(document.forma.plaza.value==\'0\')
					alert(\'Necesita seleccionar la plaza\');
				else if(document.forma.cvetar.value==\'\')
					alert(\'Necesita cargar la tarjeta\');
				else if(document.forma.monto.value==\'\')
					alert(\'Necesita ingresar el monto del abono\');
				else if(document.forma.clickguardar.value=="no"){
					atcr(\'parque_abono.php\',\'\',2,\'0\');
					document.forma.clickguardar.value="si";
				}
			}
			
			function traeUnidad(){
				objeto1=crearObjeto();
				if (objeto1.readyState != 0) {
					alert("Error: El Navegador no soporta AJAX");
				} else {
					objeto1.open("POST","parque_abono.php",true);
					objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto1.send("ajax=7&plaza="+1+"&no_eco="+document.forma.no_eco.value);
					objeto1.onreadystatechange = function(){
						if (objeto1.readyState==4){
							//document.forma.km.value="";
							//$("#idkms").hide();
							var opciones2=objeto1.responseText.split("|");
							if(opciones2[0]=="-1"){
								alert("La unidad esta dada de baja");
								document.forma.unidad.value="";
								document.forma.no_eco.value="";
								document.forma.conductor.value="";
								document.forma.nomconductor.value="";
								document.forma.vueltas.value="";
								document.forma.imp_vuelta.value="";
								document.forma.imp_cuenta.value="";
								document.forma.saldo.value="";
								document.forma.tiporec.value="";

							}
							else if(opciones2[0]=="0"){
								alert("La unidad no existe");
								document.forma.unidad.value="";
								document.forma.no_eco.value="";
								document.forma.conductor.value="";
								document.forma.nomconductor.value="";
								document.forma.vueltas.value="";
								document.forma.imp_vuelta.value="";
								document.forma.imp_cuenta.value="";
								document.forma.saldo.value="";
								document.forma.tiporec.value="";
							}
							else{
								document.forma.unidad.value=opciones2[1];
								document.forma.no_eco.value=opciones2[2];
								document.forma.conductor.value=opciones2[3];
								document.forma.nomconductor.value=opciones2[4];
								document.forma.imp_vuelta.value=opciones2[6];
								document.forma.imp_cuenta.value=opciones2[5];
							}
							calcular();
						}
					}
				}
			}
			
			function traeTarjeta(){
				objeto1=crearObjeto();
				if (objeto1.readyState != 0) {
					alert("Error: El Navegador no soporta AJAX");
				} else {
					objeto1.open("POST","parque_abono.php",true);
					objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto1.send("ajax=7&plaza="+1+"&tarjeta="+document.forma.tarjeta.value);
					objeto1.onreadystatechange = function(){
						if (objeto1.readyState==4){
							document.forma.km.value="";
							$("#idkms").hide();
							var opciones2=objeto1.responseText.split("|");
							if(opciones2[0]=="-1"){
								alert("La tarjeta ya fue cobrada");
								document.forma.cvetar.value="";
								document.forma.fecha_viaje.value="";
								document.forma.no_eco.value="";
								document.forma.clave.value="";
								document.forma.imp_decenal.value="";
								document.forma.imp_cuenta.value="";
								document.forma.saldo.value="";

							}
							else if(opciones2[0]=="-2"){
								alert("La tarjeta esta cancelada");
								document.forma.cvetar.value="";
								document.forma.fecha_viaje.value="";
								document.forma.no_eco.value="";
								document.forma.clave.value="";
								document.forma.imp_decenal.value="";
								document.forma.imp_cuenta.value="";
								document.forma.saldo.value="";

							}
							else if(opciones2[0]=="0"){
								alert("La tarjeta no existe");
								document.forma.cvetar.value="";
								document.forma.fecha_viaje.value="";
								document.forma.no_eco.value="";
								document.forma.clave.value="";
								document.forma.imp_decenal.value="";
								document.forma.imp_cuenta.value="";
								document.forma.saldo.value="";
							}
							else{
								document.forma.cvetar.value=opciones2[0];
								document.forma.fecha_viaje.value=opciones2[1];
								document.forma.no_eco.value=opciones2[2];
								document.forma.clave.value=opciones2[3];
								document.forma.imp_decenal.value=opciones2[5];
								document.forma.imp_cuenta.value=opciones2[6];
								document.forma.saldo.value=opciones2[7];
								if(opciones2[4]=="0"){
									$("#idkms").show();
									document.forma.km.focus();
								}
							}
						}
					}
				}
			}
			
			
		  </script>';
}

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'parque_abono.php\',\'\',1,0);"><img src="images/nuevo.gif" border="0">&nbsp;&nbsp;Abonar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'parque_abono.php\',\'_blank\',101,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Corte</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'parque_abono.php\',\'_blank\',102,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Listado</a>&nbsp;&nbsp;</td>
			  </tr>';
		echo '</table>';
		echo '<table>';
		if($_SESSION['PlazaUsuario']==0){
			echo '<tr><td>Plaza</td><td><select name="searchplaza" id="searchplaza" class="textField"><option value="all">---Todas---</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		else{
			echo '<input type="hidden" name="searchplaza" id="searchplaza" value="'.$_SESSION['PlazaUsuario'].'">';
		}
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">No. Economico</td><td><input type="text" name="no_eco" id="no_eco" size="10"></td></tr>';
		echo '<tr><td align="left">Estatus Abono</td><td><select name="estatus" id="estatus"><option value="all">--- Todos ---</option>
		<option value="A">Activos</option><option value="C">Cancelados</option></select></td></tr>';
		echo '<tr><td>Tipo Recaudacion</td><td><select name="tipo_recaudacion" id="tipo_recaudacion"><option value="all" selected>--- Todos ---</option>';
		foreach($array_tipo_recaudacion as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Grupo</td><td><select name="cvegrupo" id="cvegrupo"><option value="all">---Todos---</option>';
		foreach($array_grupos as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
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
			objeto.send("ajax=1&orden="+orden+"&tipoorden="+tipoorden+"&cvegrupo="+document.getElementById("cvegrupo").value+"&estatus="+document.getElementById("estatus").value+"&tipo_recaudacion="+document.getElementById("tipo_recaudacion").value+"&no_eco="+document.getElementById("no_eco").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plaza="+document.getElementById("searchplaza").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","parque_abono.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&abono="+numabono);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(0,1);}
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
		var ValidChars = "0123456789.";
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