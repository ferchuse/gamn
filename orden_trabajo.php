<?php 
include ("main.php");
$res=mysql_db_query($base,"SELECT * FROM ".$pre."propietarios ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_propietario[$row['cve']]=$row['nombre'];
}
$res=mysql_db_query($base," SELECT * FROM ".$pre."propietarios_operadores WHERE 1 group by cve_propietario ORDER BY cve_propietario");
while($row=mysql_fetch_array($res)){
	$array_propietario_opera[$row['cve_propietario']]=$array_propietario[$row['cve_propietario']];
}

$res=mysql_db_query($base,"SELECT * FROM ".$pre."conductores ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_conductor[$row['cve']]=$row['nombre'];
}
$rsUsuario=mysql_db_query($base,"SELECT * FROM ".$pre."usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}
$array_estatus_parque=array(1=>"Activo",2=>"Cancelado");
/*** CONSULTA AJAX  **************************************************/
if($_POST['cmd']==100) {
ini_set("session.auto_start", 0);
	include('fpdf153/fpdf.php');
	include("numlet.php");	
	$pdf=new FPDF('P','mm','LETTER');
	$rssalida=mysql_query("SELECT * FROM ".$pre."orden_trabajo WHERE cve='".$_POST['reg']."'");
	$Salida=mysql_fetch_array($rssalida);
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',16);
	$pdf->Cell(190,10,'NEXTLALPAN',0,0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',18);
	$pdf->Cell(190,10,'Orden de Trabajo',0,0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',14);
	$pdf->Cell(190,10,'Folio: '.$_POST['reg'],0,0,'R');
	$pdf->SetFont('Arial','B',10);
	$pdf->Ln();
	$pdf->Cell(95,5,'Fecha de Orden: '.$Salida['fecha'].' / '.$Salida['hora'],0,0,'L');
	$pdf->Cell(95,5,'Usuario de Orden: '.$array_usuario[$Salida['usuario']],0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,5,'Fecha de Impresion: '.fechaLocal().' / '.horaLocal(),0,0,'L');
	$pdf->Cell(95,5,'Usuario de Impresion: '.$array_usuario[$_POST['cveusuario']],0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,10,'',0,0,'L');
	$pdf->Ln();
	$pdf->Cell(190,5,'Yo Titular de la Unidad Autorizo Trabajar del dia: '.$Salida['fecha_ini'].' al '.$Salida['fecha_fin'].' en las Unidades',0,0,'C');
	$pdf->Ln();
		$unidades="";
		$x=0;
		$select= " SELECT * FROM ".$pre."parque WHERE propietario='".$Salida['propietario']."' and estatus='1' ";
		$select .= " ORDER BY no_eco";
		
		$res=mysql_db_query($base,$select) or die(mysql_error());
		$cant=mysql_num_rows($res);
		while($row=mysql_fetch_array($res)){
		$x++;
		$unidades.="".$row['no_eco']."";if($x<$cant){$unidades.=",";}		
			}
	$pdf->Cell(190,5,'( '.$unidades.' )',0,0,'C');
	$pdf->Ln();
	$pdf->Cell(190,5,'al Conductor :'.$array_conductor[$Salida['operador']],0,0,'C');

	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->MultiCell(190,4,'____________________________________
	Titular
	'.$array_propietario[$Salida['propietario']],0,'C');
	//'.$array_tipobenef[$Salida['tipo_beneficiario']].' '.$array_benef[$Salida['tipo_beneficiario']][$Salida['beneficiario']],0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Cell(95,4,'________________________________________',0,0,'C');
	$pdf->Cell(95,4,'________________________________________',0,0,'C');
	$pdf->Ln();
	$pdf->Cell(95,4,'Conductor',0,0,'C');
	$pdf->Cell(95,4,'Jefe de Personal',0,0,'C');
	$pdf->Ln();
	$pdf->Cell(95,4,$array_conductor[$Salida['operador']],0,0,'C');
	$pdf->Cell(95,4,$array_nomusuario[$Salida['']],0,0,'C');
	$pdf->Output();
	exit();
}

if($_POST['ajax']==1) {
		//Listado de derroteros
		$select= " SELECT * FROM ".$pre."orden_trabajo WHERE 1 and fecha between '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."' ";
		if ($_POST['propietario']!="") { $select.=" AND cve_propietario= '".$_POST['propietario']."' "; }
		if ($_POST['folio']!="") { $select.=" AND cve= '".$_POST['folio']."' "; }
		if ($_POST['estatus']!="") { $select.=" AND estatus= '".$_POST['estatus']."' "; }
		$select .= " ORDER BY cve desc ";
		$res=mysql_db_query($base,$select);
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="6" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="6">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th><th>Folio</th><th>Fecha</th><th>Periodo</th></th><th>Propietario</th><th>Conductor</th></tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				

				rowb();
				echo'<td align="center">';
				if($row['estatus']==2){echo'Cancelado';}
				else{echo '<a href="#" onClick="atcr(\'orden_trabajo.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title=" '.$row['nombre'].'"></a>
					 ';
					 //if($_POST['cveusuario']==1){
					 echo'<a href="#" onClick="atcr(\'\',\'_blank\',\'100\','.$row['cve'].');"><img src="images/b_print.png" border="0" title="Imprimir"></a>';}
				echo '</td><td align="center">'.htmlentities($row['cve']).'';
				echo '<td align="center">'.htmlentities($row['fecha']).'';
				echo '<td align="center">'.htmlentities($row['fecha_ini'].' - '.$row['fecha_fin']).'';
				echo '<td align="">'.htmlentities($array_propietario[$row['propietario']]).'';
				echo '<td align="">'.htmlentities($array_conductor[$row['operador']]).'';
		/*$selec= " SELECT * FROM ".$pre."parque WHERE propietario='".$row['cve_propietario']."' ";
		$selec .= " ORDER BY cve desc";
		$ress=mysql_db_query($base,$selec);
		while($row1=mysql_fetch_array($ress)){echo'-'.$row1['no_eco'].' ';}*/
				
				echo'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="6" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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

if($_POST['ajax']==-2){
          
		if($_POST['operador']!=""){		//Insertar el Registro
		$insert = " INSERT INTO ".$pre."propietarios_operadores 
					(cve_propietario,operador,fecha,hora,usuario)
					VALUES 
					('".$_POST['propietario']."','".$_POST['operador']."','".fechaLocal()."','".horaLocal()."','".$_SESSION['CveUsuario']."')";
		$ejecutar = mysql_db_query($base,$insert);
		$id=mysql_insert_id();
		
		$insert = " INSERT INTO ".$pre."propietarios_operadores_historial 
					(cve_aux,estado,dato,fecha,hora,usuario)
					VALUES 
					('".$_POST['propietario']."','Agrego','".$_POST['operador']."','".fechaLocal()."','".horaLocal()."','".$_SESSION['CveUsuario']."')";
		$ejecutar = mysql_db_query($base,$insert);
		
		}
		
		echo'<tr id="Resultados1"><td>';
		$select= " SELECT * FROM ".$pre."propietarios_operadores WHERE cve_propietario='".$_POST['propietario']."' ";
		$select .= " ORDER BY cve desc";
		$res=mysql_db_query($base,$select);
		
		echo '<table width="" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="2" align="center">Conductores</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Eliminar</th><th>Nombre</th></tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="" nowrap><a href="#" onClick="borrar_cond('.$row['cve'].');"><img src="images/validono.gif" border="0" title="eliminar '.$row['nombre'].'"></a></td>';
				echo '<td>'.htmlentities($array_conductor[$row['operador']]).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="2" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				</tr>
			</table></td></tr>';

	exit();
}
if ($_POST['ajax']==-3) {
		$sel= " SELECT * FROM ".$pre."propietarios_operadores WHERE cve='".$_POST['cod']."' ";
		$sel.= " ORDER BY cve desc ";
		$re=mysql_db_query($base,$sel);
		$row=mysql_fetch_array($re);
		
		$insert = " INSERT INTO ".$pre."propietarios_operadores_historial 
					(cve_aux,estado,dato,fecha,hora,usuario)
					VALUES 
					('".$_POST['propietario']."','Elimino','".$row['operador']."','".fechaLocal()."','".horaLocal()."','".$_SESSION['CveUsuario']."')";
		$ejecutar = mysql_db_query($base,$insert);
		
		$update = " DELETE from ".$pre."propietarios_operadores WHERE cve='".$_POST['cod']."' " ;
		$ejecutar = mysql_db_query($base,$update);

		
		
		
		$selec= " SELECT * FROM ".$pre."propietarios_operadores WHERE cve_propietario='".$_POST['propietario']."' ";
		$selec.= " ORDER BY cve desc ";
		$res=mysql_db_query($base,$selec);
		if(mysql_num_rows($res)>0){echo'2';}else{echo'1';}
		
		exit();
		}
if($_POST['ajax']==4){
          
		

$res=mysql_db_query($base," SELECT * FROM ".$pre."propietarios_operadores WHERE cve_propietario='".$_POST['propietario']."' group by operador ORDER BY operador");
while($row=mysql_fetch_array($res)){
	$array_operador_prop[$row['operador']]=$array_conductor[$row['operador']];
}
		echo '<td id="Resultados2"><select name="operador" id="operador" class="textField" onchange=""><option value="">---Todos---</option>';
		foreach($array_operador_prop as $k=>$v){
			echo '<option value="'.$k.'"';
		//	if($k==$row['cve']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';

	exit();
}
top($_SESSION);
/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==3) {


		//Actualizar el Registro
		$update = " UPDATE ".$pre."orden_trabajo 
					SET estatus='2',fecha_can='".fechaLocal()."',hora_can='".horaLocal()."',usu_can='".$_POST['cveusuario']."' WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_db_query($base,$update) or die(mysql_error());			
	
	$_POST['cmd']=0;
	
}
if ($_POST['cmd']==2) {

	if($_POST['reg']==-1) {
		//Actualizar el Registro
		$update = " UPDATE ".$pre."propieta 
					SET nombre='".$_POST['nombre']."',usuario='".$_POST['usuario']."',pass='".$_POST['pass']."' WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_db_query($base,$update);			
	} else {
		//Insertar el Registro
		$insert = " INSERT INTO ".$pre."orden_trabajo 
					(propietario,operador,fecha,hora,usuario,estatus,fecha_ini,fecha_fin)
					VALUES 
					('".$_POST['propietario']."','".$_POST['operador']."','".fechaLocal()."','".horaLocal()."','".$_POST['cveusuario']."','1','".$_POST['fecha_ini']."','".$_POST['fecha_fin']."')";
		$ejecutar = mysql_db_query($base,$insert) or die(mysql_error());
	}
	$_POST['cmd']=0;
	
}

/*** EDICION  **************************************************/

if ($_POST['cmd']==1) {
	$cve="";
	$select=" SELECT * FROM ".$pre."orden_trabajo WHERE cve='".$_POST['reg']."' ";
	$res=mysql_db_query($base,$select);
	$row=mysql_fetch_array($res);
	
	//Menu
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();if(document.forma.propietario.value==\'\'){$(\'#panel\').hide();alert(\'Necesita introducir el propietario\');}if(document.forma.operador.value==\'\'){$(\'#panel\').hide();alert(\'Necesita introducir el operador\');}else{atcr(\'orden_trabajo.php\',\'\',\'2\','.$_POST['reg'].');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'orden_trabajo.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Edicion de Oreden de Trabajo</td></tr>';
	echo '</table>';
	echo '<table>
	<tr>
	       <td><span>Fecha inicial</span></td>
           <td><input size="10" value="'.fechaLocal().'" name="fecha_ini" id="fecha_ini" type="text" class="readOnly" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td>
           </tr>
           <tr>
           <td><span>Fecha final</span></td>
           <td><input size="10" value="'.fechaLocal().'" name="fecha_fin" id="fecha_fin" type="text" class="readOnly" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td>
           </tr>';
	
	/*if($_POST['reg']>0){
		echo'<tr><td>Propietario</td><td><input type="hidden" name="propietario" id="propietario" value="'.$row['cve_propietario'].'" class="readOnly" readonly><input type="text" size="50" name="" id="" value="'.$array_propietario[$row['cve_propietario']].'" class="readOnly" readonly></td></tr>';
		$cve=$row['cve_propietario'];
	}else{*/echo '<tr><td>Propietario</td><td><select name="propietario" id="propietario" class="textField" onchange="traer_ope()"><option value="">---Todos---</option>';
		foreach($array_propietario_opera as $k=>$v){
			echo '<option value="'.$k.'"';
		//	if($k==$row['cve']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		$cve=$k;
		//}

	
	echo '<!--<tr><td>Unidades</td><td id="Resulta"><textarea></textarea></td></tr>-->
	<tr><td>Operador</td><td id="Resultados2"><select name="operador" id="operador" class="textField"><option value="">---Seleccione---</option>';
		foreach($array_conduct as $k=>$v){
			echo '<option value="'.$k.'"';
	//		if($k==$row['cve']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo'</td></tr>';
//		echo '</select>&nbsp;<input type="button" value="Agregar Conductor" class="textField" name="agregar" onClick="$(\'#panel\').show();if(document.forma.propietario.value==\'\'){$(\'#panel\').hide();alert(\'Necesita introducir el Propietario\');}elseif(document.forma.operador.value==\'\'){$(\'#panel\').hide();alert(\'Necesita introducir el Operador\');}else{agregarconductor('.$row['cve'].');}"></td></tr>';
//	echo '</select>&nbsp;<input type="button" value="Agregar Conductor" class="textField" name="agregar" onClick="agregarconductor('.$row['cve'].');"></td></tr>';
	echo '</table>';
	/*echo'<table><tr id="Resultados1"><td>';
		$selec= " SELECT * FROM ".$pre."propietarios_operadores WHERE cve_propietario='".$cve."' ";
		$selec .= " ORDER BY cve desc ";
		$res=mysql_db_query($base,$selec);
		echo '<table width="" border="0" cellpadding="4" cellspacing="1" class="">';
//			echo '<tr><td bgcolor="#E9F2F8" colspan="3">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr><td bgcolor="#E9F2F8" colspan="2" align="center">Conductores</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Eliminar</th><th>Nombre</th></tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="" nowrap><a href="#" onClick="borrar_cond('.$row['cve'].');"><img src="images/validono.gif" border="0" title="eliminar '.$row['nombre'].'"></a></td>';
				echo '<td>'.htmlentities($array_conductor[$row['operador']]).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="2" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				</tr>
			</table>';	
	echo '</td></tr>';
	echo '</table>';
	echo'<table><tr><td>';
		$selec= " SELECT * FROM ".$pre."propietarios_operadores_historial WHERE cve_aux='".$cve."' ";
		$selec .= " ORDER BY cve desc ";
		$res=mysql_db_query($base,$selec);
		echo '<table width="" border="0" cellpadding="4" cellspacing="1" class="">';
//			echo '<tr><td bgcolor="#E9F2F8" colspan="3">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr><td bgcolor="#E9F2F8" colspan="5" align="center">Historial</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Estado</th><th>Dato</th><th>Fecha</th><th>Hora</th><th>Usuario</th></tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td>'.htmlentities($row['estado']).'</td>';
				echo '<td>'.htmlentities($array_conductor[$row['dato']]).'</td>';
				echo '<td>'.htmlentities($row['fecha']).'</td>';
				echo '<td>'.htmlentities($row['hora']).'</td>';
				echo '<td>'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="5" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				</tr>
			</table>';	
	echo '</td></tr>';
	echo '</table>';*/
	
	echo '<script>
		function agregarconductor(cod)
		{
		
			if(document.getElementById("propietario").value==""  )
	   {
               alert("Necesita introducir el Propietario");
       }else{
			document.getElementById("Resultados1").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","orden_trabajo.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=2&propietario="+document.getElementById("propietario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&operador="+document.getElementById("operador").value);
				objeto.onreadystatechange = function()
				{
					if (objeto.readyState==4)
					{
					document.getElementById("operador").value = "";
					document.getElementById("Resultados1").innerHTML = objeto.responseText;}
				}
			}
			document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
			}
		}
		function traer_ope(cod)
		{
		
			document.getElementById("Resultados2").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","orden_trabajo.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=4&propietario="+document.getElementById("propietario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
				objeto.onreadystatechange = function()
				{
					if (objeto.readyState==4)
					{

					document.getElementById("Resultados2").innerHTML = objeto.responseText;}
				}
			}
			document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
			
		}
		function borrar_cond(reg)
		{

			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
			  
				objeto.open("POST","orden_trabajo.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=3&cod="+reg+"&numeroPagina="+document.getElementById("numeroPagina").value+"&propietario="+document.getElementById("propietario").value);
				objeto.onreadystatechange = function()
				{
					if (objeto.readyState==4)
					{
					opc=objeto.responseText;
					console.log(opc);
					if(opc==1){
                                       atcr("orden_trabajo.php","",0,reg); 
                                       }
                                       else{
									   document.getElementById("operador").value = "";
											agregarconductor();
									   

                                       }
						
					}

				}
			}
			document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
		}
		traer_ope();
	</script>';
}

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onClick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>Buscar&nbsp;
				<a href="#" onClick="atcr(\'orden_trabajo.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;';
		
			echo'</tr></table><table>
			<tr><td>&nbsp;</td></tr>
			<tr>
	       <td><span>Fecha inicial</span></td>
           <td><input size="10" value="'.fechaLocal().'" name="fecha_ini" id="fecha_ini" type="text" class="readOnly" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td>
           </tr>
           <tr>
           <td><span>Fecha final</span></td>
           <td><input size="10" value="'.fechaLocal().'" name="fecha_fin" id="fecha_fin" type="text" class="readOnly" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td>
           </tr>
			<tr><td>Folio</td><td><input type="text" name="folio" id="folio" size="10" value=""></td></tr>';
			echo '<tr><td>Propietario</td><td><select name="propietario" id="propietario" class="textField"><option value="">---Todos---</option>';
			$res=mysql_db_query($base," SELECT * FROM ".$pre."orden_trabajo WHERE 1 group by propietario ORDER BY propietario");
while($row=mysql_fetch_array($res)){
	$array_propietario_orden[$row['propietario']]=$array_propietario[$row['propietario']];
}
		foreach($array_propietario_orden as $k=>$v){
			echo '<option value="'.$k.'"';
//			if($k==1) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus" class="textField"><option value="">---Todos---</option>';
		foreach($array_estatus_parque as $k=>$v){
			echo '<option value="'.$k.'"';
//			if($k==1) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
	echo '</table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';
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
				objeto.open("POST","orden_trabajo.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=1&propietario="+document.getElementById("propietario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&folio="+document.getElementById("folio").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&estatus="+document.getElementById("estatus").value);
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
		}
		buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
		
		</Script>';
}	
bottom();
?>