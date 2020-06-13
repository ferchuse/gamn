<?
include("main.php");
$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
	$array_nomusuario[$Usuario['cve']]=$Usuario['nombre'];
}

$array_propietario=array();
$res=mysql_db_query($base,"SELECT * FROM ".$pre."propietarios ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_nompropietario[$row['cve']]=$row['nombre'];
}

$array_derroteros=array();
$res=mysql_db_query($base,"SELECT * FROM derroteros ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_derroteros[$row['cve']]=$row['nombre'];
}

$rsUnidad=mysql_db_query($base,"SELECT * FROM parque");
while($Unidad=mysql_fetch_array($rsUnidad)){
	$array_unidad[$Unidad['cve']]=$Unidad['no_eco'].$Unidad['letra'];
	$array_cuenta[$Unidad['cve']]=$Unidad['cuenta'];
	$array_uniderrotero[$Unidad['cve']]=$Unidad['derrotero'];
	$array_propietario[$Unidad['cve']]=$array_nompropietario[$Unidad['propietario']];
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
			$this->Image('images/membrete.JPG',30,3,150,15);
			$this->Ln(5);
			//Arial bold 15
			$this->SetFont('Arial','B',15);
			//Título
			$this->MultiCell(0,10,'Lista de Folios de Tarjetas a Unidades del dia: '.$fecha1.' al dia '.$fecha2,0,'C');
			//Salto de línea
			$this->Ln(20);
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
	$pdf=new PDF1();
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',10);
	$select= " SELECT a.*,IFNULL(c.cve,0) as recaudado FROM parque_tarjetas as a INNER JOIN parque as b ON (b.cve=a.unidad";
	if(trim($_POST['no_eco'])!="")$select.=" AND b.no_eco='".strtoupper($_POST['no_eco'])."'";
	$select.=")	LEFT JOIN parque_abono as c ON (c.tarjeta=a.cve AND c.estatus!='C')
	WHERE 1";
	if($_POST['fecha_ini']!="") $select.=" AND a.fecha>='".$_POST['fecha_ini']."'"; 
	if($_POST['fecha_ini']!="") $select.=" AND a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['mostrar']=="1") $select.=" AND NOT ISNULL(c.cve)";
	elseif($_POST['mostrar']=="2") $select.=" AND ISNULL(c.cve)";
	if(nivelUsuario()<=2){
		$_POST['usu']=$_POST['cveusuario'];
	}
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	$pdf->Cell(20,5,'Folio',1,0,'C');
	$pdf->Cell(20,5,'Fecha',1,0,'C');
	$pdf->Cell(20,5,'Fec. Cuenta',1,0,'C');
	$pdf->Cell(20,5,'Unidad',1,0,'C');
	$pdf->Cell(60,5,'Conductor',1,0,'L');
	$pdf->Cell(30,5,'Derrotero',1,0,'C');
	$pdf->Cell(20,5,'Usuario',1,0,'C');
	$pdf->SetFont('Arial','',8);
	$i=0;
	$res=mysql_db_query($base,$select.=" ORDER BY cve DESC");
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
		$pdf->Cell(20,5,$array_unidad[$row['unidad']],1,0,'L');
		$pdf->Cell(60,5,$array_nomconductor[$row['conductor']],1,0,'L');
		$pdf->Cell(30,5,$array_derroteros[$row['derrotero']],1,0,'L');
		$pdf->Cell(20,5,$array_usuario[$row['usuario']],1,0,'C');
		$i++;
	}
	$pdf->Ln();
	$pdf->Cell(30,5,$i." Registro(s)",0,0,'L');
	$pdf->Output();
	exit();

}

if($_POST['ajax']==1){
	$filtro="";
	$select= " SELECT a.*,IFNULL(c.cve,0) as recaudado FROM parque_tarjetas as a INNER JOIN parque as b ON (b.cve=a.unidad";
	if($_POST['tar']!="")$select.=" AND a.cve='".$_POST['tar']."'";
	if(trim($_POST['no_eco'])!="")$select.=" AND b.no_eco='".strtoupper($_POST['no_eco'])."'";
	$select.=")	LEFT JOIN parque_abono as c ON (c.tarjeta=a.cve AND c.estatus!='C') WHERE 1";
	if($_POST['fecha_ini']!="") $select.=" AND a.fecha>='".$_POST['fecha_ini']."'"; 
	if($_POST['fecha_ini']!="") $select.=" AND a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['mostrar']=="1") $select.=" AND NOT ISNULL(c.cve)";
	elseif($_POST['mostrar']=="2") $select.=" AND ISNULL(c.cve) AND a.estatus!='C'";
	if(nivelUsuario()<=2){
		$_POST['usu']=$_POST['cveusuario'];
	}
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
	$rsabonos=mysql_db_query($base,$select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$col=9;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.mysql_num_rows($rsabonos).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		echo '<th><a href="#" onClick="buscarRegistros(0,'.$tipoorden0.')">Folio</a></th><th>Fecha</th><th>Fecha Viaje</th>
		<th><!--<a href="#" onClick="buscarRegistros(1,'.$tipoorden1.')">-->Unidad<!--</a>--></th><th>Conductor</th><th>Derrotero</th>
		<th>Usuario<br>';
		echo '<select name="usuario" onchange="document.forma.usu.value=this.value;buscarRegistros('.$_POST['orden'].','.$_POST['tipoorden'].');"><option value="all">---Todos---</option>';
		$res1=mysql_db_query($base,"SELECT a.usuario FROM ".$pre."parque_tarjetas as a WHERE 1 $filtro GROUP BY a.usuario ORDER BY a.usuario");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['usuario'].'"';
			if($row1['usuario']==$_POST['usu']) echo ' selected';
			echo '>'.$array_usuario[$row1['usuario']].'</option>';
		}
		echo '</select></th>';
		echo '</tr>'; 
		$sumacargo=0;
		$x=0;
		while ($Abono=mysql_fetch_array($rsabonos)){	
			rowb();
			$estatus='';
			if($Abono['estatus']=='C'){
				$estatus='(CANCELADO)';
				$Abono['monto']=0;
				if($_SESSION['CveUsuario']==1)
					echo '<td align="center">CANCELADO<br>'.$array_usuario[$Abono['usucan']].'</td>';
				else
					echo '<td align="center">CANCELADO</td>';
			}
			else{
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'parque_tarjetas.php\',\'\',\'201\','.$Abono['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				if(nivelUsuario()>2 && $Abono['recaudado']==0)
					echo '&nbsp;&nbsp;<a href="#" onClick="cancelarAbono('.$Abono['cve'].');"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></a>';
				echo '</td>';
			}
			echo '<td align="center">';
			if($Abono['recaudado']==0)
				echo $Abono['cve'];
			else
				echo '<font color="RED">'.$Abono['cve'].'</font>';
			echo '</td>';
			echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';
			echo '<td align="center">'.$Abono['fecha_cuenta'].'</td>';
			echo '<td align="center">'.$array_unidad[$Abono['unidad']].'</td>';
			echo '<td align="left">'.$array_nomconductor[$Abono['conductor']].'</td>';
			echo '<td align="left">'.$array_derroteros[$Abono['derrotero']].'</td>';
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$sumacargo+=$Abono['monto'];
		}
		$col=9;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.$x.' Registro(s)</td>';
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
	if($_POST['clavecancelacion'] == $clavecancelacion){
		mysql_db_query($base,"UPDATE parque_tarjetas SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['usuario']."',obscan='".$_POST['obs']."' WHERE cve='".$_POST['id']."'") or die(mysql_error());
	}
	else{
		echo '1';
	}
	exit();
}

if($_POST['ajax']==7){
	
	if($_POST['unidad']==0){
		$rsUni=mysql_db_query($base,"SELECT cve FROM parque WHERE no_eco='".strtoupper($_POST['num_eco'])."' AND recaudacion_local=1 AND estatus='1'");
		if($Uni=mysql_fetch_array($rsUni))
			$_POST['unidad']=$Uni['cve'];
		else
			$_POST['unidad']=0;
	}
	if($_POST['unidad']>0){
		echo $array_cuenta[$_POST['unidad']].'|'.$array_derroteros[$array_uniderrotero[$_POST['unidad']]];
	}
	else{
		echo "|error";
	}
	exit();
}

if($_POST['ajax']==10){
	if($_POST['unidad']==0){
		$rsUni=mysql_db_query($base,"SELECT cve FROM parque WHERE recaudacion_local='1' AND concat(no_eco,letra)='".strtoupper($_POST['num_eco'])."' AND estatus='1'");
		if($Uni=mysql_fetch_array($rsUni))
			$_POST['unidad']=$Uni['cve'];
		else
			$_POST['unidad']=0;
	}
	if($_POST['unidad']>0){
		$res2=mysql_db_query($base,"SELECT cve FROM ".$pre."parque_tarjetas WHERE unidad='".$_POST['unidad']."' AND estatus!='C' AND fecha_cuenta='".$_POST['fecha_cuenta']."'");
		if(mysql_num_rows($res2)==0){
			$res3=mysql_db_query($base,"SELECT cve FROM ".$pre."parque_tarjetas WHERE conductor='".$_POST['conductor']."' AND estatus!='C' AND fecha_cuenta='".$_POST['fecha_cuenta']."'");
			if(mysql_num_rows($res3)==0){
				echo "0";
			}
			else{
				echo "3";
			}
		}
		else{
			echo "2";
		}
	}
	else{
		echo "1";
	}
	exit();
}


top($_SESSION);


if($_POST['cmd']==201){
	$res = mysql_db_query($base,"SELECT * FROM parque_tarjetas WHERE cve='".$_POST['reg']."'");
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
	$texto.="FECHA:    ".$row['fecha']."   ".horaLocal().'|';
	$texto.="FECHA CUENTA:    ".$row['fecha_cuenta'];
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
	$_POST['cmd']=0;
}


if($_POST['cmd']==2){
	if($_POST['unidad']==0){
		$rsUni=mysql_db_query($base,"SELECT cve FROM parque WHERE recaudacion_local=1 AND no_eco='".strtoupper($_POST['num_eco'])."'  AND estatus='1'");
		if($Uni=mysql_fetch_array($rsUni))
			$_POST['unidad']=$Uni['cve'];
		else
			$mensaje="Error en la unidad";
	}
	if($_POST['unidad']>0){
		mysql_db_query($base,"INSERT parque_tarjetas SET fecha_cuenta='".$_POST['fecha_cuenta']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
		unidad='".$_POST['unidad']."',estatus='A',derrotero='".$array_uniderrotero[$_POST['unidad']]."',usuario='".$_POST['cveusuario']."',concepto='".$_POST['concepto']."',
		conductor='".$_POST['conductor']."',cuenta='".$array_cuenta[$_POST['unidad']]."'") or die(mysql_error());
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
		$texto.="FECHA:    ".$_POST['fecha_cuenta']."   ".horaLocal();
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
		$texto.='|';
		//$texto.=chr(27).'!'.chr(10)."((".$array_conductor[$row['conductor']].')'.$array_nomconductor[$row['conductor']];
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
		$impresion='<iframe src="http://localhost/impresiongeneral.php?textoimp='.$texto.'" width=200 height=200></iframe>';
	}
	
	$_POST['cmd']=1;
	$_POST['conductor']=0;
	$_POST['num_eco']="";
	$_POST['unidad']="";
}



if($_POST['cmd']==1){
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
		if(document.forma.conductor.value==\'0\'){
			$(\'#panel\').hide();
			alert(\'Necesita seleccionar el conductor\');
		}
		else if(document.forma.num_eco.value==\'\'){
			$(\'#panel\').hide();
			alert(\'Necesita seleccionar la unidad\');
		}
		else{
			validar_fecha();
			//atcr(\'parque_tarjetas.php\',\'\',2,\'0\');
		}
		"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	}
	echo '<td><a href="#" onclick="$(\'#panel\').show();atcr(\'parque_tarjetas.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
	echo '</tr></table>';
	echo '<br>';
	echo '<table>';
	//$fecha=date( "Y-m-d" , strtotime ( "-1 day" , strtotime(fechaLocal()) ) );
	$fecha=fechaLocal();
	echo '<tr><td align="left">Fecha</td><td colspan="2"><input type="text" name="fecha_cuenta" id="fecha_cuenta" class="readOnly" size="15" value="'.$fecha.'" readOnly>';
	echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_cuenta,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
	echo '</td></tr>';
	echo '<tr><td>Num. Eco.</td><td colspan="2"><input type="text" name="num_eco" id="num_eco" size="10" value="'.$_POST['num_eco'].'" class="textField" onKeyUp="if(event.keyCode==13){ traeTotalesUni(); document.forma.conductor.focus();}"></td></tr>';
	echo '<tr><td>Conductor</td><td colspan="2"><select name="conductor" id="conductor"><option value="0">--- Seleccione ---</option>';
	$filtroope="";
	$res=mysql_db_query($base,"SELECT * FROM ".$pre."conductores WHERE estatus='1' ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'"';
		if($_POST['conductor']==$row['cve']) echo ' selected';
		echo '>'.$row['nombre'].'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Cuenta Diaria</td><td colspan="2"><input type="text" name="cuenta" id="cuenta" size="10" value="'.$array_cuenta[$_POST['unidad']].'" class="readOnly" readOnly></td></tr>';
	echo '<tr><td>Derrotero</td><td colspan="2"><input type="text" name="derrotero" id="derrotero" size="20" class="readOnly" readOnly></td></tr>';
	echo '<tr><td>Observaciones</td><td><textarea name="concepto" id="concepto" cols="50" rows="5"></textarea></td></tr>';
	echo '</table>';
	echo '<div id="idcargos"></div>';
	echo '<script>
			
			function traeTotalesUni(){
				document.forma.cuenta.value="";
				objeto1=crearObjeto();
				if (objeto1.readyState != 0) {
					alert("Error: El Navegador no soporta AJAX");
				} else {
					objeto1.open("POST","parque_tarjetas.php",true);
					objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto1.send("ajax=7&num_eco="+document.forma.num_eco.value);
					objeto1.onreadystatechange = function(){
						if (objeto1.readyState==4){
							var opciones2=objeto1.responseText.split("|");
							if(opciones2[1]=="error"){
								alert("La unidad no existe");
								document.forma.num_eco.value="";
								document.forma.cuenta.value="";
								document.forma.derrotero.value="";
								document.forma.num_eco.focus();
							}
							else{
								document.forma.cuenta.value=opciones2[0];
								document.forma.derrotero.value=opciones2[1];
								//document.getElementById("idcargos").innerHTML=opciones2[3];
							}
						}
					}
				}
			}
			
			
			function validar_fecha(){
				objeto2=crearObjeto();
				if (objeto2.readyState != 0) {
					alert("Error: El Navegador no soporta AJAX");
				} else {
					objeto2.open("POST","parque_tarjetas.php",true);
					objeto2.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto2.send("ajax=10&num_eco="+document.forma.num_eco.value+"&conductor="+document.forma.conductor.value+"&fecha_cuenta="+document.forma.fecha_cuenta.value);
					objeto2.onreadystatechange = function(){
						if (objeto2.readyState==4){
							if(objeto2.responseText=="1"){
								alert("Error en la unidad");
								$("#panel").hide();
							}
							else if(objeto2.responseText=="2"){
								alert("A la unidad ya se le genero tarjeta en la fecha");
								$("#panel").hide();
							}
							else if(objeto2.responseText=="3"){
								alert("Al conductor ya se le genero tarjeta en la fecha");
								$("#panel").hide();
							}
							else{
								atcr(\'parque_tarjetas.php\',\'\',2,\'0\');
							}
						}
					}
				}
			}
						
			';
			if($_POST['num_eco']!="") echo 'traeTotalesUni();';
			echo '
		  </script>';
}

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		if($impresion != ""){
			echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
		}
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<!--<td><a href="#" onclick="atcr(\'parque_tarjetas.php\',\'\',1,0);"><img src="images/nuevo.gif" border="0">&nbsp;&nbsp;Nuevo</a>&nbsp;&nbsp;</td>-->';
		echo '
				<td><a href="#" onClick="atcr(\'parque_tarjetas.php\',\'_blank\',\'200\',\'\')"><img src="images/b_print.png" border="0" title="Imprimir">&nbsp;Imprimir</a></td>
			  </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>No. Economico</td><td><input type="text" size="5" class="textField" name="no_eco" id="no_eco"></td></tr>';
		echo '<tr><td>Tarjeta</td><td><input type="text" size="5" class="textField" name="tar" id="tar" value=""></td></tr>';
		echo '<tr><td>Mostrar</td><td><select name="mostrar" id="mostrar"><option value="0">Todos</option><option value="1">Recaudados</option><option value="2" selected>Sin Recaudar</option></select></td></tr>';
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
			objeto.open("POST","parque_tarjetas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&orden="+orden+"&tipoorden="+tipoorden+"&mostrar="+document.getElementById("mostrar").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&no_eco="+document.getElementById("no_eco").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&tar="+document.getElementById("tar").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	function cancelarAbono(numabono){
		if(cancelarRegistro(numabono, \'parque_tarjetas.php\', 2))
			buscarRegistros(0,1);
	  /*if(confirm("¿Esta seguro de cancelar la tarjeta?")){
		obs=prompt("Observaciones:");
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","parque_tarjetas.php",true);
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
	  }*/
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