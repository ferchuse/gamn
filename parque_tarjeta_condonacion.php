<?
include("main.php");
$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
	$array_nomusuario[$Usuario['cve']]=$Usuario['nombre'];
}

$array_motivo=array();
$res=mysql_db_query($base,"SELECT * FROM motivos_condonacion ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivo[$row['cve']]=$row['nombre'];
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
			//$this->Cell(190,10,"PASEOS DE SAN JUAN",0,0,'C');
			$this->Ln(5);
			//Arial bold 15
			$this->SetFont('Arial','B',15);
			//Título
			$this->MultiCell(0,10,'Lista de Folios de Condonacion de Tarjetas a Unidades del dia: '.$fecha1.' al dia '.$fecha2,0,'C');
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
	$select= " SELECT a.* FROM tarjeta_condonacion as a INNER JOIN parque as b ON (b.cve=a.unidad";
	if(trim($_POST['no_eco'])!="")$select.=" AND b.no_eco='".strtoupper($_POST['no_eco'])."'";
	$select.=") INNER JOIN conductores as c ON (c.cve=a.conductor";
	if(trim($_POST['credencial'])!="")$select.=" AND c.credencial='".strtoupper($_POST['credencial'])."'";
	if(trim($_POST['nombre'])!="")$select.=" AND c.nombre LIKE '%".strtoupper($_POST['nombre'])."%'";
	$select.=") WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	//if($_POST['no_eco']!="") $select.=" AND a.no_eco='".$_POST['no_eco']."'"; 
	if(nivelUsuario()<=2){
		$_POST['usu']=$_POST['cveusuario'];
	}
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	$pdf->Cell(20,5,'Folio',1,0,'C');
	$pdf->Cell(20,5,'Fecha',1,0,'C');
	$pdf->Cell(20,5,'Fec. Cuenta',1,0,'C');
	$pdf->Cell(20,5,'Unidad',1,0,'C');
	$pdf->Cell(70,5,'Conductor',1,0,'L');
	$pdf->Cell(20,5,'Monto',1,0,'C');
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
		$pdf->Cell(20,5,$row['cve'].$estatus,1,0,'C');
		$pdf->Cell(20,5,$row['fecha'],1,0,'C');
		$pdf->Cell(20,5,$row['fecha_cuenta'],1,0,'C');
		$pdf->Cell(20,5,$array_unidad[$row['unidad']],1,0,'L');
		$pdf->Cell(70,5,$array_nomconductor[$row['conductor']],1,0,'L');
		$pdf->Cell(20,5,number_format($row['monto'],2),1,0,'R');
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
	$select= " SELECT a.* FROM tarjeta_condonacion as a INNER JOIN parque as b ON (b.cve=a.unidad";
	if($_POST['tar']!="")$select.=" AND a.cvetar='".$_POST['tar']."'";
	if(trim($_POST['no_eco'])!="")$select.=" AND b.no_eco='".strtoupper($_POST['no_eco'])."'";
	$select.=") INNER JOIN conductores as c ON (c.cve=a.conductor";
	if(trim($_POST['credencial'])!="")$select.=" AND c.credencial='".strtoupper($_POST['credencial'])."'";
	if(trim($_POST['nombre'])!="")$select.=" AND c.nombre LIKE '%".strtoupper($_POST['nombre'])."%'";
	$select.=") WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	//if($_POST['no_eco']!="") $select.=" AND a.no_eco='".$_POST['no_eco']."'"; 
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
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$col=12;
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		echo '<th><a href="#" onClick="buscarRegistros(0,'.$tipoorden0.')">Folio</a></th><th>Fecha</th><th>Fecha Cuenta</th><th>Tarjeta</th>
		<th><!--<a href="#" onClick="buscarRegistros(1,'.$tipoorden1.')">-->Unidad<!--</a>--></th>
		<th>Conductor</th>
		<th>Motivo</th>
		<th>Monto</th>
		<th>Observaciones</th>
		<th>Usuario<br>';
		echo '<select name="usuario" onchange="document.forma.usu.value=this.value;buscarRegistros('.$_POST['orden'].','.$_POST['tipoorden'].');"><option value="all">---Todos---</option>';
		$res1=mysql_db_query($base,"SELECT a.usuario FROM tarjeta_condonacion as a WHERE 1 $filtro GROUP BY a.usuario ORDER BY a.usuario");
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
			$res1=mysql_db_query($base,"SELECT a.*,IFNULL(c.cve,0) as recaudado FROM parque_tarjetas a LEFT JOIN parque_abono as c ON (c.tarjeta=a.cve AND c.estatus!='C') WHERE a.cve='".$Abono['cvetar']."'");
			$row1=mysql_fetch_array($res1);
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
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'parque_tarjeta_condonacion.php\',\'\',\'201\','.$Abono['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				if(nivelUsuario()>2 && $row1['recaudado'] == 0)
					echo '&nbsp;&nbsp;<a href="#" onClick="cancelarAbono('.$Abono['cve'].');"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></a>';
				echo '</td>';
			}
			echo '<td align="center">'.$Abono['cve'].'</td>';
			echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';
			echo '<td align="center">'.$Abono['fecha_cuenta'].'</td>';
			echo '<td align="center">'.$row1['cve'].'</td>';
			echo '<td align="center">'.$array_unidad[$Abono['unidad']].'</td>';
			echo '<td align="left">'.$array_nomconductor[$Abono['conductor']].'</td>';
			echo '<td align="left">'.$array_motivo[$Abono['motivo']].'</td>';
			echo '<td align="right">'.number_format($Abono['monto']*$fac,2).'</td>';
			echo '<td align="left">'.$Abono['obs'].'</td>';
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$array_total[0]+=$Abono['monto']*$fac;
		}
		$col=7;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		foreach($array_total as $v)
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
		echo '<td bgcolor="#E9F2F8" colspan="2">&nbsp;</td>';
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
		mysql_db_query($base,"UPDATE tarjeta_condonacion SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['usuario']."',obscan='".$_POST['obs']."' WHERE cve='".$_POST['id']."'") or die(mysql_error());
	}
	else{
		echo '1';
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
				echo $row['cve'].'|'.$row['fecha_cuenta'].'|'.$row['unidad'].'|'.$array_unidad[$row['unidad']].'|'.$row['conductor'].'|'.$array_nomconductor[$row['conductor']].'|'.round($row['cuenta']-$row1[0],2);
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
	$res = mysql_db_query($base,"SELECT * FROM tarjeta_condonacion WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	
	$varimp="Condonacion Tarjeta de Unidades||Folio: ".$row['cve']."|";
	$varimp.="Usuario: ".$array_usuario[$row['usuario']].'|';
	$varimp.="Fecha Cuenta: ".$row['fecha_cuenta'].'|';
	$varimp.=$row['fecha']." ".$row['hora']."||";
	$varimp.="Tarjeta: ".$row['cvetar']."|";
	$varimp.="Unidad: ".$array_unidad[$row['unidad']]."|";
	$varimp.="Conductor: ";
	$varimp.=$array_conductor[$row['conductor']]."|";
	$varimp.="Motivo: ".$array_motivo[$row['motivo']]."|";
	$varimp.="Monto: $ ".number_format($row['monto'],2)."|";
	//$varimp.=numlet($row['monto'])."||";
	$varimp.=chr(29)."h".chr(80).chr(29)."H".chr(2).chr(29)."k".chr(2)."1".sprintf("%011s",(intval($row['cve'])))." |";
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$varimp.'&copia=1&logo=GAMN" width=200 height=200></iframe>';
	$_POST['cmd']=0;
}


if($_POST['cmd']==2){
		mysql_db_query($base,"INSERT tarjeta_condonacion SET cvetar='".$_POST['tarjeta']."',fecha_cuenta='".$_POST['fecha_cuenta']."',
		fecha='".fechaLocal()."',hora='".horaLocal()."',motivo='".$_POST['motivo']."',
		unidad='".$_POST['unidad']."',monto='".$_POST['monto']."',estatus='A',usuario='".$_POST['cveusuario']."',
		conductor='".$_POST['conductor']."',obs='".$_POST['obs']."',saldo='".$_POST['saldo']."'") or die(mysql_error()."1");
		$abono=mysql_insert_id();
		$res=mysql_db_query($base,"SELECT * FROM tarjeta_condonacion WHERE cve='$abono'");
		$row=mysql_fetch_array($res);
		
		$varimp="Condonacion Tarjeta de Unidades||Folio: ".$row['cve']."|";
		$varimp.="Usuario: ".$array_usuario[$row['usuario']].'|';
		$varimp.="Fecha Cuenta: ".$row['fecha_cuenta'].'|';
		$varimp.=$row['fecha']." ".$row['hora']."||";
		$varimp.="Tarjeta: ".$row['cvetar']."|";
		$varimp.="Unidad: ".$array_unidad[$row['unidad']]."|";
		$varimp.="Conductor: ";
		$varimp.=$array_conductor[$row['conductor']]."|";
		$varimp.="Motivo: ".$array_motivo[$row['motivo']]."|";
		$varimp.="Monto: $ ".number_format($row['monto'],2)."|";
		//$varimp.=numlet($row['monto'])."||";
		$varimp.=chr(29)."h".chr(80).chr(29)."H".chr(2).chr(29)."k".chr(2)."1".sprintf("%011s",(intval($row['cve'])))." |";
		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$varimp.'&copia=1&logo=GAMN" width=200 height=200></iframe>';
	$_POST['cmd']=0;
}



if($_POST['cmd']==1){
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
		else if(document.forma.motivo.value==\'0\'){
			$(\'#panel\').hide();
			alert(\'Necesita seleccionar el motivo\');
		}
		else if(document.forma.unidad.value==\'\'){
			$(\'#panel\').hide();
			alert(\'Necesita seleccionar la unidad\');
		}
		else if(document.forma.monto.value==\'\'){
			alert(\'Necesita ingresar el monto de la tarjeta_condonacion\');
			$(\'#panel\').hide();
		}
		else if((document.forma.monto.value/1)==0){
			alert(\'El monto no puede ser cero\');
			$(\'#panel\').hide();
		}
		else if((document.forma.monto.value/1)>(document.forma.saldo.value/1)){
			alert(\'El el monto no puede ser mayor al saldo\');
			$(\'#panel\').hide();
		}
		else{
			atcr(\'parque_tarjeta_condonacion.php\',\'\',2,\'0\');
		}
		"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	}
	echo '<td><a href="#" onclick="$(\'#panel\').show();atcr(\'parque_tarjeta_condonacion.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
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
	echo '<tr><td>Conductor</td><td colspan="2"><input type="text" name="nomconductor" id="nomconductor" size="50" value="" class="readOnly" readOnly></td></tr>';
	echo '<tr><td>Motivo</td><td colspan="2"><select name="motivo" id="motivo"><option value="0">Seleccione</option>';
	foreach($array_motivo as $k=>$v){
		echo '<option value="'.$k.'"';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Saldo</td><td colspan="2"><input type="text" name="saldo" id="saldo" size="10" value="" class="readOnly" readOnly></td></tr>';
	echo '<tr><td>Monto</td><td colspan="2"><input type="text" name="monto" id="monto" size="10" class="textField"></td></tr>';
	echo '<tr><td>Observaciones</td><td><textarea name="obs" id="obs" cols="50" rows="5"></textarea></td></tr>';
	echo '</table>';
	echo '<script>
	
			
			function traeTarjeta(){
				objeto1=crearObjeto();
				if (objeto1.readyState != 0) {
					alert("Error: El Navegador no soporta AJAX");
				} else {
					objeto1.open("POST","parque_tarjeta_condonacion.php",true);
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
								document.forma.saldo.value=datos[6];
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
				document.forma.saldo.value="";
			}
			
			
						
			';
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
				<td><a href="#" onclick="atcr(\'parque_tarjeta_condonacion.php\',\'\',1,0);"><img src="images/nuevo.gif" border="0">&nbsp;&nbsp;Condonar</a>&nbsp;&nbsp;</td>';
		echo '
				<!--<td><a href="#" onClick="atcr(\'parque_tarjeta_condonacion.php\',\'_blank\',\'200\',\'\')"><img src="images/b_print.png" border="0" title="Imprimir">&nbsp;Imprimir</a></td>-->
			  </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>No. Economico</td><td><input type="text" size="5" class="textField" name="no_eco" id="no_eco"></td></tr>';
		echo '<tr><td>Credencial</td><td><input type="text" size="5" class="textField" name="credencial" id="credencial"></td></tr>';
		echo '<tr><td>Tarjeta</td><td><input type="text" size="5" class="textField" name="tar" id="tar" value=""></td></tr>';
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
			objeto.open("POST","parque_tarjeta_condonacion.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&orden="+orden+"&tipoorden="+tipoorden+"&credencial="+document.getElementById("credencial").value+"&nombre="+document.getElementById("nombre").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&no_eco="+document.getElementById("no_eco").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&tar="+document.getElementById("tar").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	function cancelarAbono(numabono){
		if(cancelarRegistro(numabono, \'parque_tarjeta_condonacion.php\', 2))
			buscarRegistros(0,1);
	  /*if(confirm("¿Esta seguro de cancelar la tarjeta_condonacion?")){
		obs=prompt("Observaciones:");
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","parque_tarjeta_condonacion.php",true);
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