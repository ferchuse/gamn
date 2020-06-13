<?
include("main.php");
$tipo_vehiculo=3;
$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
	$array_nomusuario[$Usuario['cve']]=$Usuario['nombre'];
}

$array_motivo=array();
$res=mysql_db_query($base,"SELECT * FROM motivos ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivo[$row['cve']]=$row['nombre'];
}

$array_unidad=array();
$res=mysql_db_query($base,"SELECT * FROM parque ORDER BY no_eco");
while($row=mysql_fetch_array($res)){
	$array_unidad[$row['cve']]=$row['no_eco'];
}

$array_depositantes=array();
$res=mysql_db_query($base,"SELECT * FROM beneficiarios ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_depositantes[$row['cve']]=$row['nombre'];
}

//$array_estatus=array("A"=>"En proceso","P"=>"Pagada","C"=>"Cancelada");

if($_POST['cmd']==201){
	require('fpdf153/fpdf.php');
	include("numlet.php");
	$res = mysql_db_query($base,"SELECT * FROM recibos_entrada WHERE cve='".$_POST['reg']."'");
	$Salida = mysql_fetch_array($res);
	$pdf=new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	//$pdf->Image('images/membrete.JPG',30,3,150,15);
	$pdf->SetFont('Arial','B',16);
	$pdf->MultiCell(190,5,'NEXTLALPAN',0,'C');
	$pdf->Ln();
	$pdf->SetY(23);
	$pdf->Cell(95,10,'Recibo de Entrada',0,0,'L');
	$pdf->Cell(95,10,'Folio: '.$_POST['reg'],0,0,'R');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(95,5,'Unidad: '.$array_unidad[$Salida['unidad']],0,0,'L');
	$pdf->Cell(95,5,'Bueno por: $ '.number_format($Salida['monto'],2),0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,5,'Motivo: '.$array_motivo[$Salida['motivo']],0,0,'L');
	$pdf->Cell(95,5,'Fecha: '.fecha_letra($Salida['fecha']),0,0,'R');
	$pdf->Ln();
	if($Salida['estatus']=='C'){
		$pdf->SetFont('Arial','B',16);
		$pdf->Cell(190,6,'CANCELADO',1,0,'C');
		$pdf->Ln();
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(190,6,'('.$Salida['obscan'].')',1,0,'C');
	}
	$pdf->Ln();
	$pdf->SetFont('Arial','',12);
	$pdf->MultiCell(190,5,"Recibi la cantidad de ".numlet($Salida['monto']),0,"R");
	$pdf->Ln();
	$pdf->MultiCell(190,5,"Por Concepto de: ".$Salida['concepto'],0,"R");
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','U',12);
	$pdf->Cell(190,5,$array_depositantes[$Salida['depositante']],0,0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','',12);
	$pdf->Cell(190,5,"Depositante",0,0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();

	$pdf->SetFont('Arial','',10);
	$pdf->Cell(95,5,'Impreso por: '.$array_usuario[$_POST['cveusuario']],0,0,'L');
	$pdf->Cell(95,5,'Creado por: '.$array_usuario[$Salida['usuario']],0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,5,'Fec. Impresion: '.fechaLocal().' '.horaLocal(),0,0,'L');
	$pdf->Cell(95,5,'Fec. Creacion: '.$Salida['fecha'].' '.$Salida['hora'],0,0,'R');
	
	$pdf->SetXY(10,125);
	$pdf->Cell(190,5,"---------------------------------------------------------------------------------------------------------------",0,0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','B',16);
	$pdf->MultiCell(190,5,'NEXTLALPAN',0,'C');
	//$pdf->Image('images/membrete.JPG',30,130,150,15);
	$pdf->Ln();
	$pdf->SetY(150);
	$pdf->Cell(95,10,'Recibo de Entrada  - COPIA',0,0,'L');
	$pdf->Cell(95,10,'Folio: '.$_POST['reg'],0,0,'R');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(95,5,'Unidad: '.$array_unidad[$Salida['unidad']],0,0,'L');
	$pdf->Cell(95,5,'Bueno por: $ '.number_format($Salida['monto'],2),0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,5,'Motivo: '.$array_motivo[$Salida['motivo']],0,0,'L');
	$pdf->Cell(95,5,'Fecha: '.fecha_letra($Salida['fecha']),0,0,'R');
	$pdf->Ln();
	if($Salida['estatus']=='C'){
		$pdf->SetFont('Arial','B',16);
		$pdf->Cell(190,6,'CANCELADO',1,0,'C');
		$pdf->Ln();
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(190,6,'('.$Salida['obscan'].')',1,0,'C');
	}
	$pdf->Ln();
	$pdf->SetFont('Arial','',12);
	$pdf->MultiCell(190,5,"Recibi la cantidad de ".numlet($Salida['monto']),0,"R");
	$pdf->Ln();
	$pdf->MultiCell(190,5,"Por Concepto de: ".$Salida['concepto'],0,"R");
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','U',12);
	$pdf->Cell(190,5,$array_depositantes[$Salida['depositante']],0,0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','',12);
	$pdf->Cell(190,5,"Depositante",0,0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();

	$pdf->SetFont('Arial','',10);
	$pdf->Cell(95,5,'Impreso por: '.$array_usuario[$_POST['cveusuario']],0,0,'L');
	$pdf->Cell(95,5,'Creado por: '.$array_usuario[$Salida['usuario']],0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,5,'Fec. Impresion: '.fechaLocal().' '.horaLocal(),0,0,'L');
	$pdf->Cell(95,5,'Fec. Creacion: '.$Salida['fecha'].' '.$Salida['hora'],0,0,'R');
	$pdf->Output();
	exit();
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
			//$this->Cell(190,10,"PASEOS DE SAN JUAN",0,0,'C');
			$this->Ln(5);
			//Arial bold 15
			$this->SetFont('Arial','B',15);
			//Título
			$this->MultiCell(0,10,'Lista de Folios de Recibos de Entrada del dia: '.$fecha1.' al dia '.$fecha2,0,'C');
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
	$select= " SELECT a.* FROM recibos_entrada as a WHERE a.".$_POST['ffecha'].">='".$_POST['fecha_ini']."' AND a.".$_POST['ffecha']."<='".$_POST['fecha_fin']."' ";
	if ($_POST['unidad']!="all") { $select.=" AND a.unidad='".$_POST['unidad']."'"; }
	if ($_POST['motivo']!="all") { $select.=" AND a.motivo='".$_POST['motivo']."'"; }
	if ($_POST['depositante']!="all") { $select.=" AND a.depositante='".$_POST['depositante']."'"; }
	if(nivelUsuario()<=2){
		$_POST['usu']=$_POST['cveusuario'];
	}
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	$pdf->Cell(20,5,'Folio',1,0,'C');
	$pdf->Cell(20,5,'Fecha',1,0,'C');
	$pdf->Cell(40,5,'Motivo',1,0,'C');
	$pdf->Cell(50,5,'Depositante',1,0,'L');
	$pdf->Cell(20,5,'Unidad',1,0,'C');
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
		$pdf->Cell(40,5,$array_motivo[$row['motivo']],1,0,'L');
		$pdf->Cell(50,5,$array_depositantes[$row['depositante']],1,0,'L');
		$pdf->Cell(20,5,$array_unidad[$row['unidad']],1,0,'C');
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
	$filtrofechaini="a.".$_POST['ffecha'].">='".$_POST['fecha_ini']."'";
	if($_POST['hora_ini'] != '') $filtrofechaini="CONCAT(a.".$_POST['ffecha'].",' ',a.hora)>='".$_POST['fecha_ini']." ".$_POST['hora_ini'].":00'";
	$filtrofechafin="a.".$_POST['ffecha']."<='".$_POST['fecha_fin']."'";
	if($_POST['hora_fin'] != '') $filtrofechafin="CONCAT(a.".$_POST['ffecha'].",' ',a.hora)<='".$_POST['fecha_fin']." ".$_POST['hora_fin'].":59'";
	/*$select= " SELECT a.* FROM recibos_entrada as a  WHERE a.".$_POST['ffecha'].">='".$_POST['fecha_ini']."' AND a.".$_POST['ffecha']."<='".$_POST['fecha_fin']."'
			  and a.hora between '".$_POST['hora_ini']."' and '".$_POST['hora_fin']."'";*/
	$select= " SELECT a.* FROM recibos_entrada as a  WHERE {$filtrofechaini} AND {$filtrofechafin}";
	if(nivelUsuario()<=2){
		$_POST['usu']=$_POST['cveusuario'];
	}
//	if ($_POST['hora_ini']>"00:00:00" and $_POST['hora_fin']>"00:00:00"){$select.=" and a.hora between '".$_POST['hora_ini']."' and '".$_POST['hora_fin']."'";}
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	if ($_POST['unidad']!="all") { $select.=" AND a.unidad='".$_POST['unidad']."'"; }
	if ($_POST['motivo']!="all") { $select.=" AND a.motivo='".$_POST['motivo']."'"; }
	if ($_POST['depositante']!="all") { $select.=" AND a.depositante='".$_POST['depositante']."'"; }
	$select.=" ORDER BY a.cve DESC";
	//echo''.$select.'';
	$res=mysql_db_query($base,$select) or die(mysql_error());
	if(mysql_num_rows($res)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$col=12;
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		echo '<th>Folio</th><th>Fecha</th><th>Motivo</th><th>Depositante</th><th>Unidad</th>
		<th>Monto</th>
		<th>Concepto</th>
		<th>Usuario<br>';
		echo '<select name="usuario" onchange="document.forma.usu.value=this.value;buscarRegistros('.$_POST['orden'].','.$_POST['tipoorden'].');"><option value="all">---Todos---</option>';
		$res1=mysql_db_query($base,"SELECT a.usuario FROM recibos_entrada as a WHERE 1 $filtro GROUP BY a.usuario ORDER BY a.usuario");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['usuario'].'"';
			if($row1['usuario']==$_POST['usu']) echo ' selected';
			echo '>'.$array_usuario[$row1['usuario']].'</option>';
		}
		echo '</select></th>';
		echo '</tr>'; 
		$array_total=array();
		$x=0;
		while ($row=mysql_fetch_array($res)){	
			rowb();
			$estatus='';
			if($row['estatus']=='C'){
				$row['monto']=0;
				if($_POST['cveusuario']==1)
					echo '<td align="center">CANCELADO<br>'.$array_usuario[$row['usucan']].'</td>';
				else
					echo '<td align="center">CANCELADO</td>';
			}
			else{
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'recibos_entradas.php\',\'_blank\',\'201\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
				if(nivelUsuario()>2)
					echo '&nbsp;&nbsp;<a href="#" onClick="cancelarSalida('.$row['cve'].');"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				echo '</td>';
			}
			echo '<td align="center">'.$row['cve'].'</td>';
			echo '<td align="center">'.$row['fecha'].' '.$row['hora'].'</td>';
			echo '<td align="center">'.$array_motivo[$row['motivo']].'</td>';
			echo '<td align="left">'.$array_depositantes[$row['depositante']].'</td>';
			echo '<td align="center">'.$array_unidad[$row['unidad']].'</td>';
			echo '<td align="right">'.number_format($row['monto'],2).'</td>';
			echo '<td align="left">'.$row['concepto'].'</td>';
			echo '<td align="center">'.$array_usuario[$row['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$array_total[0]+=$row['monto'];
		}
		$col=5;
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
		mysql_db_query($base,"UPDATE recibos_entrada SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['usuario']."',obscan='".$_POST['obs']."' WHERE cve='".$_POST['id']."'") or die(mysql_error());
	}
	else{
		echo '1';
	}
	exit();
}



top($_SESSION);



if($_POST['cmd']==2){
	mysql_db_query($base,"INSERT recibos_entrada SET fecha='".fechaLocal()."',hora='".horaLocal()."',unidad='".$_POST['unidad']."',
	motivo='".$_POST['motivo']."',depositante='".$_POST['depositante']."',monto='".$_POST['monto']."',estatus='A',usuario='".$_POST['cveusuario']."',
	concepto='".$_POST['concepto']."'") or die(mysql_error()."1");
	$salida=mysql_insert_id();
	
	$_POST['cmd']=0;
}



if($_POST['cmd']==1){
	echo '<table><tr>';
	if(nivelUsuario()>1){
		echo '<td><a href="#" onClick="
		$(\'#panel\').show();
		if(document.forma.motivo.value==\'0\'){
			$(\'#panel\').hide();
			alert(\'Necesita seleccionar el motivo\');
		}
		else if(document.forma.depositante.value==\'0\'){
			$(\'#panel\').hide();
			alert(\'Necesita seleccionar el depositante\');
		}
		else if(document.forma.unidad.value==\'0\'){
			$(\'#panel\').hide();
			alert(\'Necesita seleccionar la unidad\');
		}
		else if(document.forma.monto.value==\'\'){
			alert(\'Necesita ingresar el monto\');
			$(\'#panel\').hide();
		}
		else if((document.forma.monto.value/1)==0){
			alert(\'El monto no puede ser cero\');
			$(\'#panel\').hide();
		}
		else{
			atcr(\'recibos_entradas.php\',\'\',2,\'0\');
		}
		"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	}
	echo '<td><a href="#" onclick="$(\'#panel\').show();atcr(\'recibos_entradas.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
	echo '</tr></table>';
	echo '<br>';
	echo '<table>';
	echo '<tr><td align="left">Fecha</td><td colspan="2"><input type="text" name="fecha" id="fecha" class="readOnly" size="15" value="'.fechaLocal().'" readOnly>';
	echo '</td></tr>';
	echo '<tr><td>Motivo</td><td colspan="2"><select name="motivo" id="motivo"><option value="0">Seleccione</option>';
	foreach($array_motivo as $k=>$v){
		echo '<option value="'.$k.'"';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Depositante</td><td colspan="2"><select name="depositante" id="depositante"><option value="0">Seleccione</option>';
	foreach($array_depositantes as $k=>$v){
		echo '<option value="'.$k.'"';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Unidad</td><td colspan="2"><select name="unidad" id="unidad"><option value="0">Seleccione</option>';
	foreach($array_unidad as $k=>$v){
		echo '<option value="'.$k.'"';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Monto</td><td colspan="2"><input type="text" name="monto" id="monto" size="10" class="textField"></td></tr>';
	echo '<tr><td>Concepto</td><td><textarea name="concepto" id="concepto" cols="50" rows="5"></textarea></td></tr>';
	echo '</table>';
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
				<td><a href="#" onclick="atcr(\'recibos_entradas.php\',\'\',1,0);"><img src="images/nuevo.gif" border="0">&nbsp;&nbsp;Nuevo</a>&nbsp;&nbsp;</td>';
		echo '
				<!--<td><a href="#" onClick="atcr(\'recibos_entradas.php\',\'_blank\',\'200\',\'\')"><img src="images/b_print.png" border="0" title="Imprimir">&nbsp;Imprimir</a></td>-->
			  </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr style="display:none;"><td>Tipo Fecha</td><td><select name="ffecha" id="ffecha"><option value="fecha">Fecha de Creacion</option><option value="fechamov">Fecha de Pago o Cancelacion</option></td></tr>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Hora Inicial</td><td><input type="text" size="10" class="textField" name="hora_ini" id="hora_ini"><small>HH:MM</small></td></tr>';
		echo '<tr><td>Hora Final</td><td><input type="text" size="10" class="textField" name="hora_fin" id="hora_fin"><small>HH:MM</small></td></tr>';
		echo '<tr><td>Unidad</td><td colspan="2"><select name="unidad" id="unidad"><option value="all">Todas</option>';
		foreach($array_unidad as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Motivo</td><td colspan="2"><select name="motivo" id="motivo"><option value="all">Todos</option>';
		foreach($array_motivo as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Depositante</td><td colspan="2"><select name="depositante" id="depositante"><option value="all">Todos</option>';
		foreach($array_depositantes as $k=>$v){
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
			objeto.open("POST","recibos_entradas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&orden="+orden+"&tipoorden="+tipoorden+"&ffecha="+document.getElementById("ffecha").value+"&depositante="+document.getElementById("depositante").value+"&unidad="+document.getElementById("unidad").value+"&motivo="+document.getElementById("motivo").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&hora_fin="+document.getElementById("hora_fin").value+"&hora_ini="+document.getElementById("hora_ini").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	function cancelarSalida(salida){
		if(cancelarRegistro(salida, \'recibos_entradas.php\', 2))
			buscarRegistros(0,1);
	  /*if(confirm("¿Esta seguro de cancelar la entrada?")){
		obs=prompt("Observaciones:");
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","recibos_entradas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&salida="+salida+"&obs="+obs+"&usuario='.$_POST['cveusuario'].'");
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