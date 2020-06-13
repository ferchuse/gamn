<?php 

include ("main2.php"); 

/*** ARREGLOS ***********************************************************/

$rsPlaza=mysql_db_query($base,"SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['nombre'];
}

$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}


if($_POST['cmd']==100){
	include('fpdf153/fpdf.php');
	include("numlet.php");	
	class FPDF2 extends PDF_MC_Table {
		function Header(){
			global $_POST;
			$this->Image('images/membrete.JPG',60,3,150,15);
			$this->SetFont('Arial','B',16);
			//$this->Cell(190,10,'Autobuses Rapidos del Valle de Mexico',0,0,'C');
			//$this->Ln();
			$this->SetY(23);
			$tit='';
			$this->MultiCell(180,5,'Registros del Sistema de la fecha '.$_POST['fecha'],0,'C');
			$this->Ln();
			$this->Ln();
			$this->SetFont('Arial','B',11);
			$this->Cell(50,4,'Usuario',0,0,'C',0);
			$this->Cell(50,4,'Entrada',0,0,'C',0);
			$this->Cell(50,4,'IP',0,0,'C',0);
			//$this->Cell(50,4,'Salida',0,0,'C',0);
			//$this->Cell(30,4,'Tiempo',0,0,'C',0);
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
	$pdf=new FPDF2('P','mm','LETTER');
	$pdf->AliasNbPages();
	$pdf->AddPage('P');
	$pdf->SetFont('Arial','',11);
	$select= " SELECT usuario, entrada , salida, ip FROM registros_sistema WHERE 1 ";
	if ($_POST['fecha']!="") { $select.=" AND left(entrada,10)>='".$_POST['fecha']."'"; }
	if ($_POST['fechaf']!="") { $select.=" AND left(entrada,10)<='".$_POST['fechaf']."'"; }
	if($_POST['usuario']!="all") { $select.=" AND usuario='".$_POST['usuario']."'";}
	$rsentradas=mysql_db_query($base,$select);
	$totalRegistros = mysql_num_rows($rsentradas);
	$select .= " ORDER BY entrada desc";
	$rsentradas=mysql_db_query($base,$select);
	$pdf->SetWidths(array(50,50,50));
	$pdf->SetAligns(array('C','C','C'));
	while($Entradas=mysql_fetch_array($rsentradas)) {
		$renglon=array();
		$renglon[]=$array_usuario[$Entradas['usuario']];
		$renglon[]=$Entradas['entrada'];
		/*$renglon[]=$Entradas['salida'];
		$fecha1=explode("-",substr($Entradas['entrada'],0,10));
		$tiempo1=explode(":",substr($Entradas['entrada'],11,8));
		if($Entradas['salida']=="0000-00-00 00:00:00"){
			$fechahora=fechahoraLocal();
			$fecha2=explode("-",substr($fechahora,0,10));
			$tiempo2=explode(":",substr($fechahora,11,8));
		}
		else{
			$fecha2=explode("-",substr($Entradas['salida'],0,10));
			$tiempo2=explode(":",substr($Entradas['salida'],11,8));
		}
		$t1=mktime($tiempo1[0],$tiempo1[1],$tiempo1[2],$fecha1[1],$fecha1[2],$fecha1[0]);
		$t2=mktime($tiempo2[0],$tiempo2[1],$tiempo2[2],$fecha2[1],$fecha2[2],$fecha2[0]);
		$tiempo=$t2-$t1;
		$horas = intval($tiempo / 3600);
		$min = intval(($tiempo-$horas*3600)/60);
		$seg = $tiempo-$horas*3600-$min*60;
		if($horas<10) $horas="0".$horas;
		if($min<10) $min="0".$min;
		if($seg<10) $seg="0".$seg;
		$tiempo_final=$horas.':'.$min.':'.$seg;
		$renglon[]=$tiempo_final;*/
		$renglon[]=$Entradas['ip'];
		$pdf->Row($renglon);
	}
	$pdf->SetFont('Arial','B',11);
	$pdf->Cell(180,5,$totalRegistros.' Registro(s)');
	$pdf->Output();
	exit();
}



/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de Parque
		$select= " SELECT usuario, entrada , ip FROM registros_sistema WHERE 1 ";
		if ($_POST['fecha']!="") { $select.=" AND left(entrada,10)>='".$_POST['fecha']."'"; }
		if ($_POST['fechaf']!="") { $select.=" AND left(entrada,10)<='".$_POST['fechaf']."'"; }
		if($_POST['usuario']!="all") { $select.=" AND usuario='".$_POST['usuario']."'";}
		$rsentradas=mysql_db_query($base,$select);
		$totalRegistros = mysql_num_rows($rsentradas);
		/*if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		$select .= " ORDER BY entrada desc  LIMIT ".$primerRegistro.",".$eRegistrosPagina;*/
		$select .= " ORDER BY entrada desc";
		$rsentradas=mysql_db_query($base,$select);
		
		if(mysql_num_rows($rsentradas)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr class="grid_header">';
			echo '<th>Usuario</th><th>Entrada</th><!--<th>Salida</th>';
			echo '<th>Tiempo</th>--><th>IP</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			while($Entradas=mysql_fetch_array($rsentradas)) {
				rowb();
				echo '<td align="center">'.htmlentities($array_usuario[$Entradas['usuario']]).'</td>';
				echo '<td align="center">'.$Entradas['entrada'].'</td>';
				/*echo '<td align="center">'.$Entradas['salida'].'</td>';
				$fecha1=explode("-",substr($Entradas['entrada'],0,10));
				$tiempo1=explode(":",substr($Entradas['entrada'],11,8));
				if($Entradas['salida']=="0000-00-00 00:00:00"){
					$fechahora=fechahoraLocal();
					$fecha2=explode("-",substr($fechahora,0,10));
					$tiempo2=explode(":",substr($fechahora,11,8));
				}
				else{
					$fecha2=explode("-",substr($Entradas['salida'],0,10));
					$tiempo2=explode(":",substr($Entradas['salida'],11,8));
				}
	
				$t1=mktime($tiempo1[0],$tiempo1[1],$tiempo1[2],$fecha1[1],$fecha1[2],$fecha1[0]);
				$t2=mktime($tiempo2[0],$tiempo2[1],$tiempo2[2],$fecha2[1],$fecha2[2],$fecha2[0]);
				$tiempo=$t2-$t1;
				$horas = intval($tiempo / 3600);
				$min = intval(($tiempo-$horas*3600)/60);
				$seg = $tiempo-$horas*3600-$min*60;
				if($horas<10) $horas="0".$horas;
				if($min<10) $min="0".$min;
				if($seg<10) $seg="0".$seg;
				$tiempo_final=$horas.':'.$min.':'.$seg;
				echo '<td align="center">'.$tiempo_final.'</td>';*/
				echo '<td align="center">'.$Entradas['ip'].'</td>';
				$i++;
				echo '</tr>';
			}
			
			echo '	
				<tr>
				<td colspan="5" class="grid_header">';menunavegacion(); echo '</td>
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


top($_SESSION);



/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();">'.$imgbuscar.'</a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;<a href="#" onClick="atcr(\'registros_sistema.php\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		$fecha=date("Y-m-d");
		echo '<tr><th align="left">Fecha Inicial</th><td><input type="text" name="fecha" id="fecha" class="readOnly" size="15" value="'.$fecha.'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Fecha Final</th><td><input type="text" name="fechaf" id="fechaf" class="readOnly" size="15" value="'.$fecha.'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fechaf,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Usuario</th><td><select name="usuario" id="usuario"><option value="all">--- Todos ---</option>';
		$estatus=array("A"=>"Activo","I"=>"Inactivo");
		$res=mysql_db_query($base,"SELECT * FROM usuarios ORDER BY usuario");
		while($row=mysql_fetch_array($res)){
			echo '<option value="'.$row['cve'].'">'.$row['usuario'].' ('.$estatus[$row['estatus']].')</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
		echo '<script language="javascript">
			//Funcion para navegacion de Registros. 20 por pagina.
			function moverPagina(x) {
				document.getElementById("numeroPagina").value = x;
				buscarRegistros();
			} </script>';
	}
	
bottom();



/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","registros_sistema.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha="+document.getElementById("fecha").value+"&fechaf="+document.getElementById("fechaf").value+"&usuario="+document.getElementById("usuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

