<?
include("main.php");
$tipo_vehiculo=3;
$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
	$array_nomusuario[$Usuario['cve']]=$Usuario['nombre'];
}

$res=mysql_db_query($base,"SELECT * FROM conductores");
while($row=mysql_fetch_array($res)){
	$array_conductor[$row['cve']]=$row['credencial'];
	$array_nomconductor[$row['cve']]=$row['nombre'];
	$array_mutuconductor[$row['cve']]=$row['mutualidad'];
}


$rsUnidad=mysql_db_query($base,"SELECT * FROM parque");
while($Unidad=mysql_fetch_array($rsUnidad)){
	$array_unidad[$Unidad['cve']]=$Unidad['no_eco'].$Unidad['letra'];
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
			//$this->Image('images/membrete.JPG',60,3,150,15);
			//$this->Cell(190,10,"PASEOS DE SAN JUAN",0,0,'C');
			$this->Ln(5);
			//Arial bold 15
			$this->SetFont('Arial','B',15);
			//Título
			$this->MultiCell(0,10,'NEXTLALPAN
			Lista de Folios de Mutualidad del dia: '.$fecha1.' al dia '.$fecha2,0,'C');
			//Salto de línea
			$this->Ln(5);
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

	$filtrofechaini="a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['hora_ini'] != '') $filtrofechaini="CONCAT(a.fecha,' ',a.hora)>='".$_POST['fecha_ini']." ".$_POST['hora_ini'].":00'";
	$filtrofechafin="a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['hora_fin'] != '') $filtrofechafin="CONCAT(a.fecha,' ',a.hora)<='".$_POST['fecha_fin']." ".$_POST['hora_fin'].":59'";
	//Creación del objeto de la clase heredada
	$pdf=new PDF1('P');
	$pdf->AliasNbPages();
	$pdf->AddPage('P');
	$pdf->SetFont('Arial','B',10);
	$select= " SELECT a.* FROM parque_abono as a INNER JOIN conductores b ON (b.cve = a.conductor) INNER JOIN parque c ON (c.cve=a.unidad)";
	$select.=" WHERE {$filtrofechaini} AND {$filtrofechafin} ";
	if(trim($_POST['credencial'])!="")$select.=" AND b.credencial='".strtoupper($_POST['credencial'])."'";
	if(trim($_POST['nombre'])!="")$select.=" AND b.nombre LIKE '%".strtoupper($_POST['nombre'])."%'";
	if(nivelUsuario()<=2){
		$_POST['usu']=$_POST['cveusuario'];
	}
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	$pdf->Cell(20,5,'Folio',1,0,'C');
	$pdf->Cell(20,5,'Fecha',1,0,'C');
	$pdf->Cell(20,5,'Eco',1,0,'C');
	$pdf->Cell(60,5,'Conductor',1,0,'L');
	$pdf->Cell(30,5,'Monto',1,0,'C');
	$pdf->Cell(30,5,'Usuario',1,0,'C');
	$pdf->SetFont('Arial','',8);
	$i=0;
	$total=0;
	$res=mysql_db_query($base,$select.=" ORDER BY cve DESC");
	while ($row=mysql_fetch_array($res)){	
		$pdf->Ln();
		$estatus='';
		if($row['estatus']=='C'){
			$estatus='(CANCELADO)';
			$row['mutualidad']=0;
		}
		$pdf->Cell(20,5,$row['folio'].$estatus,1,0,'C');
		$pdf->Cell(20,5,$row['fecha'],1,0,'C');
		$pdf->Cell(20,5,$array_unidad[$row['unidad']],1,0,'L');
		$pdf->Cell(60,5,$array_nomconductor[$row['conductor']],1,0,'L');
		$pdf->Cell(30,5,number_format($row['mutualidad'],2),1,0,'R');
		$pdf->Cell(30,5,$array_usuario[$row['usuario']],1,0,'C');
		$i++;
		$total+=$row['mutualidad'];
	}
	$pdf->Ln();
	$pdf->Cell(60,5,$i." Registro(s)",0,0,'L');
	$pdf->Cell(60,5,"Total: ",0,0,'R');
	$pdf->Cell(30,5,number_format($total,2),0,0,'R');
	$pdf->Output();
	exit();

}


if($_POST['ajax']==1){

	$filtrofechaini="a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['hora_ini'] != '') $filtrofechaini="CONCAT(a.fecha,' ',a.hora)>='".$_POST['fecha_ini']." ".$_POST['hora_ini'].":00'";
	$filtrofechafin="a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['hora_fin'] != '') $filtrofechafin="CONCAT(a.fecha,' ',a.hora)<='".$_POST['fecha_fin']." ".$_POST['hora_fin'].":59'";
	$filtro="";
	$select= " SELECT a.* FROM parque_abono as a INNER JOIN conductores b ON (b.cve = a.conductor) INNER JOIN parque c ON (c.cve=a.unidad)";
	$select.=" WHERE {$filtrofechaini} AND {$filtrofechafin} ";
	if(trim($_POST['credencial'])!="")$select.=" AND b.credencial='".strtoupper($_POST['credencial'])."'";
	if(trim($_POST['nombre'])!="")$select.=" AND b.nombre LIKE '%".strtoupper($_POST['nombre'])."%'";
	if(nivelUsuario()<=2){
		$_POST['usu']=$_POST['cveusuario'];
	}
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	$select.=" ORDER BY a.cve DESC";
	$rsabonos=mysql_db_query($base,$select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$col=12;
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		echo '<th>Folio</th><th>Fecha</th>
		<th>Unidad</th>
		<th>Conductor</th>
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
				$Abono['mutualidad']=0;
				if($_SESSION['CveUsuario']==1)
					echo '<td align="center">CANCELADO<br>'.$array_usuario[$Abono['usucan']].'</td>';
				else
					echo '<td align="center">CANCELADO</td>';
			}
			else{
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'mutualidad.php\',\'\',\'201\','.$Abono['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['cve'].'"></a>';
				//if(nivelUsuario()>2)
				//	echo '&nbsp;&nbsp;<a href="#" onClick="cancelarAbono('.$Abono['cve'].');"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['cve'].'"></a>';
				echo '</td>';
			}
			echo '<td align="center">'.$Abono['folio'].'</td>';
			echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';
			echo '<td align="center">'.$array_unidad[$Abono['unidad']].'</td>';
			echo '<td align="left">'.utf8_encode($array_nomconductor[$Abono['conductor']]).'</td>';
			echo '<td align="right">'.number_format($Abono['mutualidad']*$fac,2).'</td>';
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$array_total[0]+=$Abono['mutualidad']*$fac;
		}
		$col=4;
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



top($_SESSION);


if($_POST['cmd']==201){
	$res = mysql_db_query($base,"SELECT * FROM parque_abono WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	
	$varimp=" Mutualidad||Folio: ".$row['folio']."|";
	$varimp.="Usuario: ".$array_usuario[$row['usuario']].'|';
	$varimp.="Fecha Cuenta: ".$row['fecha_cuenta'].'|';
	$varimp.=$row['fecha']." ".$row['hora']."||";
	$varimp.="Unidad: ".$array_unidad[$row['unidad']]."|";
	//$varimp.="Conductor: ";
	//$varimp.=$array_conductor[$row['conductor']]."|";
	$varimp.="Monto: $ ".number_format($row['mutualidad'],2)."|";
	//$varimp.=numlet($row['monto'])."||";
	$varimp.=chr(29)."h".chr(80).chr(29)."H".chr(2).chr(29)."k".chr(2)."1".sprintf("%011s",(intval($row['cve'])))." |";
	$impresion='<iframe src="http://localhost/impresiongeneral.php?textoimp='.$varimp.'&copia=1" width=200 height=200></iframe>';
	$_POST['cmd']=0;
}



	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		if($impresion != ""){
			echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
		}
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>';
		echo '
				<td><a href="#" onClick="atcr(\'mutualidad.php\',\'_blank\',\'200\',\'\')"><img src="images/b_print.png" border="0" title="Imprimir">&nbsp;Imprimir</a></td>
			  </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Hora Inicial</td><td><input type="text" size="10" class="textField" name="hora_ini" id="hora_ini"><small>HH:MM</small></td></tr>';
		echo '<tr><td>Hora Final</td><td><input type="text" size="10" class="textField" name="hora_fin" id="hora_fin"><small>HH:MM</small></td></tr>';
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
			objeto.open("POST","mutualidad.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&orden="+orden+"&tipoorden="+tipoorden+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&hora_ini="+document.getElementById("hora_ini").value+"&hora_fin="+document.getElementById("hora_fin").value+"&credencial="+document.getElementById("credencial").value+"&nombre="+document.getElementById("nombre").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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
			objeto.open("POST","mutualidad.php",true);
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