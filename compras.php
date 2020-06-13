<?php
include("main.php");
$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}
$rsMotivo=mysql_db_query($base,"SELECT * FROM productos");
$array_productos=array();
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_productos[$Motivo['cve']]=$Motivo['nombre'];
	$array_unidades[$Motivo['cve']]=$Motivo['unidad'];
}

$res=mysql_db_query($base,"SELECT * FROM compradores order by nombre");
while($row=mysql_fetch_array($res)){
	$array_comprador[$row['cve']]=$row['nombre'];
}
$res=mysql_db_query($base,"SELECT * FROM provedores order by nombre");
while($row=mysql_fetch_array($res)){
	$array_provedores[$row['cve']]=$row['nombre'];
}
$option_producto='';
$option_producto.='<option value="0">-Seleccione-</option>';
$res = mysql_db_query($base,"SELECT * FROM productos");
while($row=mysql_fetch_array($res)){
	$array_productos[$row['cve']] = $row['nombre'];
	$array_productos_costo[$row['cve']] = $row['costo'];
	
	$option_producto.='<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
}

if($_POST['cmd']==101){
	if($_POST['mostrar'] == 1){
		$select="SELECT a.cve,a.fecha,a.hora,a.comprador,a.obs,a.estatus,a.usuario,a.usucan,a.fechacan,b.producto,b.cantidad
		FROM entradas_productos a INNER JOIN entradas_productos_detalle b ON a.cve = b.cveentrada WHERE a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if($_POST['comprador']!="all") $select.=" AND a.comprador='".$_POST['comprador']."'";
		$select.=" ORDER BY a.cve DESC";
	}
	else{
		$select="SELECT * FROM entradas_productos WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if($_POST['comprador']!="all") $select.=" AND comprador='".$_POST['comprador']."'";
		$select.=" ORDER BY cve DESC";
	}
	$res=mysql_db_query($base,$select);
	$totalRegistros = mysql_num_rows($res);
		
	if(mysql_num_rows($res)>0) {

		$reporte = '<h1>Reporte de Entradas de Productos de '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h1><table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$reporte .= '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Comprador</th>';
		if($_POST['mostrar'] == 1) $reporte .= '<th>Producto</th><th>Cantidad</th>';
		$reporte .= '<th>Observaciones</th><th>Folio Factura</th><th>Provedor</th><th>Monto</th><th>Usuario</th>';
		$reporte .= '</tr>';
		$data=array();
		$x=0;
		$total=0;
		while($row=mysql_fetch_array($res)) {
			$reporte .= rowb(1);
			$reporte .= '<td align="center" width="40" nowrap>';
			if($row['estatus']=='C'){
				$reporte .= 'CANCELADO<br>'.$row['fechacan'].'<br>'.$array_usuario[$row['usucan']];
			}
			$reporte .= '</td>';
			$reporte .= '<td align="center">'.htmlentities($row['cve']).'</td>';
			$reporte .= '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';

			$reporte .= '<td>'.htmlentities($array_comprador[$row['comprador']]).'</td>';
			if($_POST['mostrar'] == 1) $reporte .= '<td>'.htmlentities($array_productos[$row['producto']]).'</td><td align="right">'.$row['cantidad'].'</td>';
			$reporte .= '<td>'.htmlentities($row['obs']).'</td>';
						$reporte .= '<td>'.$row['folio_fac'].'</td>';
			$reporte .= '<td>'.htmlentities($array_provedores[$row['provedor_fac']]).'</td>';
			$reporte .= '<td align="right">'.htmlentities(number_format($row['monto'],2)).'</td>';
			$reporte .= '<td>'.htmlentities($array_usuario[$row['usuario']]).'</td>';
			$reporte .= '</tr>';
			$x++;
			$total=$total + $row['monto'];
		}
		$reporte .= '	
			<tr bgcolor="#E9F2F8">
			<td colspan="7" bgcolor="#E9F2F8">'.$x.' Registro(s)</td>
			<td align="right">'.number_format($total,2).'</td>
			<td></td>
			</tr>
		</table>';
		
		echo $reporte;
		
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

if($_POST['cmd']==100){
	$select=" SELECT * FROM entradas_productos WHERE cve='".$_POST['reg']."' ";
	$rsmotivo=mysql_db_query($base,$select);
	$Motivo=mysql_fetch_array($rsmotivo);
	echo '<html><body><p align="center">';
	echo '<table align="left">';
	echo '<tr><th style="font-size:30px" align="left">Entradas de Productos</th></tr>';
	echo '</table>';
	echo '<br>';
	echo '<table width="100%">';
	echo '<tr style="font-size:20px"><th align="left" width="10%">Folio</th><td width="40%">'.$Motivo['cve'].'</td></tr>';
	echo '<tr style="font-size:20px"><th align="left" width="10%">Fecha</th><td width="40%">'.$Motivo['fecha'].'</td></tr>';
	echo '<tr style="font-size:20px"><th align="left" width="10%">Folio Factura</th><td width="40%">'.$Motivo['folio_fac'].'</td></tr>';
	echo '<tr style="font-size:20px"><th align="left" width="10%">Provedor</th><td width="40%">'.$array_provedores[$Motivo['provedor_fac']].'</td></tr>';
	echo '<tr style="font-size:20px"><th align="left">Comprador</th><td>'.$array_comprador[$Motivo['comprador']].'</td></tr>';
	echo '<tr style="font-size:20px"><th align="left">Monto</th><td>'.number_format($Motivo['monto'],2).'</td></tr>';
	echo '<tr style="font-size:20px"><th align="left">Productos</th><td><table border="1">
	<tr><th>Producto</th><th>Unidad</th><th>Cantidad</th></tr>';
	$res=mysql_db_query($base,"SELECT * FROM entradas_productos_detalle WHERE cveentrada='".$_POST['reg']."'");
	while($row=mysql_fetch_array($res)){
		echo '<tr>';
		echo '<td>'.$array_productos[$row['producto']].'</td>';
		echo '<td>'.$array_unidades[$row['producto']].'</td>';
		echo '<td>'.$row['cantidad'].'</td>';
		echo '</tr>';
	}
	echo '</table></td></tr>';
	echo '<tr style="font-size:20px"><th align="left">Observaciones</th><td>'.$Motivo['observaciones'].'</td></tr>';
	echo '</table>';	
	echo '</p></body></html>';
	echo '<script>window.print();</script>';
	exit();
}

if($_POST['ajax']==1){
//	if($_POST['mostrar'] == 1){
		$select="SELECT a.cve,a.fecha,a.hora,a.comprador,a.obs,a.estatus,a.usuario,a.usucan,a.fechacan,b.cantidad,a.provedor_fac,a.folio_fac, a.fecha_creacion, a.total
		FROM entradas_productos a INNER JOIN entradas_productos_detalle b ON a.cve = b.cveentrada WHERE a.fecha_creacion BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	//	if($_POST['comprador']!="all") $select.=" AND a.comprador='".$_POST['comprador']."'";
		$select.=" GROUP BY a.cve ORDER BY a.cve DESC";
//	}
//	else{
//		$select="SELECT * FROM entradas_productos WHERE fecha_creacion BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	//	if($_POST['comprador']!="all") $select.=" AND comprador='".$_POST['comprador']."'";
//		$select.=" ORDER BY cve DESC";
//	}
//echo $select;
	$res=mysql_db_query($base,$select) or die(mysql_error());
	$totalRegistros = mysql_num_rows($res);
		
	if(mysql_num_rows($res)>0) {
		$reporte = '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$reporte .= '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><!--<th>Comprador</th>';
//		if($_POST['mostrar'] == 1) $reporte .= '<th>Producto</th><th>Cantidad</th>';
		$reporte .= '--><th>Observaciones</th><!--<th>Factura Y/O Nota</th>--><th>Folio Factura</th><th>Provedor</th><th>Total</th><th>Usuario</th>';
		$reporte .= '</tr>';
		$data=array();
		$x=0;
		$total=0;
		while($row=mysql_fetch_array($res)) {
			$reporte .= rowb(1);
			$reporte .= '<td align="center" width="40" nowrap>';
			if($row['estatus']=='A'){

				$reporte .= '&nbsp;<a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].');"><img src="images/buscar.gif" border="0" title="Ver '.$row['cve'].'"></a>';
				if(nivelUsuario()>1)	
					$reporte .= '&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')) atcr(\'\',\'\',\'3\','.$row['cve'].');"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
			}

			if($row['estatus']=='C'){
				$reporte .= 'CANCELADO<br>'.$row['fechacan'].'<br>'.$array_usuario[$row['usucan']];
			}
			$reporte .= '</td>';
			$reporte .= '<td align="center">'.htmlentities($row['cve']).'</td>';
			$reporte .= '<td align="center">'.htmlentities($row['fecha_creacion'].' '.$row['hora']).'</td>';

			$reporte .= '<td>'.htmlentities($row['obs']).'</td>';
			$reporte .= '<td>'.htmlentities($row['folio_fac']).'</td>';
			$reporte .= '<td>'.htmlentities($array_provedores[$row['provedor_fac']]).'</td>';
			$reporte .= '<td align="right">'.number_format($row['total'],2).'</td>';
			$reporte .= '<td>'.htmlentities($array_usuario[$row['usuario']]).'</td>';
			$reporte .= '</tr>';
			$x++;
			$total=$total + $row['total'];
		}
		$reporte .= '	
			<tr bgcolor="#E9F2F8">
			<td colspan="6" >'.$x.' Registro(s)</td>
			<td align="right">'.number_format($total,2).'</td><td></td>
			</tr>
		</table>';
		/*if(count($data)>0){
			$data2 = array();
			foreach($data as $datos){
				$data2[] = array($datos[0], $datos[1]);
			}
			//$reporte.='<img src="graficabar.php?fecha_ini='.$_POST['fecha_ini'].'&fecha_fin='.$_POST['fecha_fin'].'&reporte=desglose_cuentas_grupo">';
			require_once("../phplot/phplot.php");
			$plot = new PHPlot(1000,800);
			$plot->SetFileFormat("jpg");
			$plot->SetFailureImage(False);
			//$plot->SetPrintImage(False);
			$plot->SetIsInline(True);
			$plot->SetOutputFile("grafica.jpg");
			$plot->SetImageBorderType('plain');
			$plot->SetDataType('text-data-yx');
			$plot->SetXDataLabelPos('plotin');
			$plot->SetDataValues($data2);
			$plot->SetPlotType('bars');
			//foreach ($data as $row) $plot->SetLegend($row[0]); // Copy labels to legend
			$plot->SetXTickLabelPos('none');
			$plot->SetXTickPos('none');
			$plot->DrawGraph();
			$reporte .= '<img src="grafica.jpg?'.date("Y-m-d H:i:s").'">';
		}*/
		echo $reporte;
		
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


if ($_POST['cmd']==3) {
	$delete= "UPDATE entradas_productos SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['usuario']."' WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_db_query($base,$delete);
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/


if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE entradas_productos 
						SET comprador='".$_POST['comprador']."',fecha_fac='".$_POST['fecha_fac']."',folio_fac='".$_POST['folio_fac']."',fac_nota='".$_POST['fac_nota']."',
							provedor_fac='".$_POST['provedor_fac']."',estatus='A',obs='".$_POST['obs']."',total='".$_POST['total']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_db_query($base,$update);	
			$cveentrada = $_POST['reg'];
			mysql_db_query($base,"DELETE FROM entradas_productos_detalle FROM cveentrada='$cveentrada'");		
	} else {
			//Insertar el Registro
			$insert = " INSERT entradas_productos 
						SET comprador='".$_POST['comprador']."',estatus='A',obs='".$_POST['obs']."',usuario='".$_POST['usuario']."',
							fecha='".$_POST['fecha']."',fecha_fac='".$_POST['fecha_fac']."',folio_fac='".$_POST['folio_fac']."',fac_nota='".$_POST['fac_nota']."',
							provedor_fac='".$_POST['provedor_fac']."',total='".$_POST['total']."',
						    fecha_creacion='".fechaLocal()."',hora='".horaLocal()."'";
			$ejecutar = mysql_db_query($base,$insert)or die(mysql_error()) ;
			$cveentrada = mysql_insert_id();
	}
	$i=0;
	foreach($_POST['cant'] as $k=>$v){
		if($v>0){
			mysql_query("INSERT entradas_productos_detalle SET cveentrada='$cveentrada',cantidad='".$v."',producto='".addslashes($_POST['producto'][$k])."',
			precio='".$_POST['precio'][$k]."',importe='".$_POST['importe'][$k]."'");
			$i++;
			if($_POST['precio'][$k]>$array_productos_costo[$_POST['producto'][$k]]){
				mysql_query("UPDATE productos SET costo = '{$_POST['precio'][$k]}' WHERE cve = '{$_POST['producto'][$k]}'");
			}
		}
	}
	
	$_POST['cmd']=0;
	
}

top($_SESSION);

/*** EDICION  **************************************************/

if ($_POST['cmd']==1) {
	
	$select=" SELECT * FROM entradas_productos WHERE cve='".$_POST['reg']."' ";
	$rssalida=mysql_db_query($base,$select);
	$Salida=mysql_fetch_array($rssalida);
	if($_POST['reg']>0){
		$fecha=$Salida['fecha'];
		$Encabezado = 'Folio No.'.$_POST['reg'];
	}
	else{
		$fecha=fechaLocal();
		$Encabezado = 'Nueva Entrada de Producto';
	}
	//Menu
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1 && $_POST['reg']==0){
			echo '<td><a href="#" onClick="
			if(document.forma.folio_fac.value==\'\')
				alert(\'Necesita ingresar el Folio de la Factura\');
			else if(document.forma.fecha_fac.value==\'\')
				alert(\'Ingrese la Fecha de la Factura\');
			else if(document.forma.provedor_fac.value==\'0\')
				alert(\'Seleccione el Proveedor\');
			else if(!validar_detalle())
			{
				alert(\'Hay detalles Vacios\');
				$(\'#panel\').hide();
			}
			else{
				atcr(\'compras.php\',\'\',\'2\',\''.$Salida['cve'].'\');
			}	
			"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		}
		echo '<td><a href="#" onClick="atcr(\'compras.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	echo '<input type="hidden" name="clickguardar" value="0">';
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Edicion Entradas de Productos</td></tr>';
	echo '</table>';
	
	echo '<table>';
	echo '<tr><th align="left">'.$Encabezado.'</th><td>&nbsp;</td></tr>';
	echo '<tr><th align="left">Folio de la Factura</th><td><input type="text" name="folio_fac" id="folio_fac" value="'.$Salida['folio_fac'].'"></td></tr>';
	echo '<tr><th align="left">Fecha Factura</th><td><input type="text" name="fecha_fac" id="fecha_fac" class="readOnly" size="15" value="'.$Salida['fecha_fac'].'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fac,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	
	echo '<tr><td>&nbsp;</td></tr><tr><th align="left">Proveedor</th><td><select name="provedor_fac" id="provedor_fac" class="textField"><option value="0">---Seleccione---</option>';
			foreach ($array_provedores as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($Salida['provedor_fac']==$k){echo'selected';}echo'>'.$v.'</option>';
		}
		echo '</select></td></tr>';

//	echo '<tr><th align="left">Factura Y/O Nota</th><td><input type="text" name="fac_nota" id="fac_nota" value="'.$Salida['fac_nota'].'"></td></tr>';
	echo '</table>';
	echo '<input type="hidden" name="clickguardar" id="clickguardar" value="no">';
		echo '<table id="tablaproductos"><tr>';
		echo '<th>Id Producto</th><th>Descripcion</th><th>Cantidad</th>';
		echo '<th>Precio Unitario</th><th>Importe</th></tr>';
		$i=0;
		if($_POST['reg']>0){
			$res1 = mysql_query("SELECT a.*, b.nombre, b.id_producto FROM entradas_productos_detalle a INNER JOIN productos b ON b.cve = a.producto WHERE a.cveentrada='{$_POST['reg']}'");
			while($row1 = mysql_fetch_array($res1)){
				echo '<tr>';
				echo '<td align="center"><input type="text" class="textField" id="clavesp_'.$i.'" size="15" value="'.$row1['id_producto'].'"><input type="hidden" name="producto['.$i.']" id="producto'.$i.'" class="cproductos" value="'.$row1['producto'].'"></td>';
				echo '<td align="center"><input type="text" class="readOnly" id="nomproducto'.$i.'" size="30" value="'.$row1['nombre'].'" readOnly></td>';
				echo '<td align="center"><input type="text" class="textField" size="10" name="cant['.$i.']" id="cant'.$i.'" value="'.$row1['cantidad'].'"  onKeyUp="sumarproductos()"></td>';
				echo '<td align="center"><input type="text" class="textField" size="10" name="precio['.$i.']" id="precio'.$i.'" value="'.$row1['precio'].'"></td>';
				echo '<td align="center"><input type="text" class="readOnly" size="10" name="importe['.$i.']" id="importe'.$i.'" value="'.$row1['importe'].'" readOnly></td>';
				echo '</tr>';
				$i++;
			}
		}
		if($i==0){
			echo '<tr>';
			echo '<td align="center"><input type="text" class="textField" id="clavesp_'.$i.'" size="15" value=""><input type="hidden" name="producto['.$i.']" id="producto'.$i.'" class="cproductos" value=""></td>';
			echo '<td align="center"><input type="text" class="readOnly" id="nomproducto'.$i.'" size="30" value="" readOnly></td>';
			echo '<td align="center"><input type="text" class="textField" size="10" name="cant['.$i.']" id="cant'.$i.'" value=""  onKeyUp="sumarproductos()"></td>';
			echo '<td align="center"><input type="text" class="textField" size="10" name="precio['.$i.']" id="precio'.$i.'" value=""  onKeyUp="sumarproductos()"></td>';
			echo '<td align="center"><input type="text" class="readOnly" size="10" name="importe['.$i.']" id="importe'.$i.'" value="" readOnly></td>';
			echo '</tr>';
			$i++;
		}

		echo '<tr id="idtotal"><th align="right" colspan="4">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total" id="total" value="'.$Salida['total'].'" readOnly></td></tr>';
		echo '</table>';		
		if($_POST['reg']==0)
			echo '<input type="button" value="Agregar" onClick="agregarproducto()" class="textField">';
		echo '<input type="hidden" name="cantprod" value="'.$i.'">';
		
		echo '<table><tr><td></td></tr><tr><th align="left" valign="top">Observaciones</th><td><textarea name="obs" id="obs" rows="6" cols="60">'.$row['obs'].'</textarea></td></tr></table>';
		echo '<script>

			function generarbuscador(i){
				$( "#clavesp_"+i ).autocomplete({
			      source: "ventas.php?ajax=3",
			      minLength: 2,
			      select: function( event, ui ) {
			      	document.getElementById("clavesp_"+i).value=ui.item.clave;
			        document.getElementById("producto"+i).value=ui.item.producto_id;
			        document.getElementById("nomproducto"+i).value=ui.item.nombre;
			        document.getElementById("cant"+i).focus();
			        sumarproductos();
			      }
			    });
			}

			function validar_detalle(){
				regresar = true;
				for(i=0; i<(document.forma.cantprod.value/1); i++){
					if((document.getElementById("cant"+i).value/1)==0 ){
						regresar = false;
					}
					if((document.getElementById("producto"+i).value/1)==0){
						regresar = false;
					
					}
					if((document.getElementById("precio"+i).value/1)==0){
						regresar = false;
					
					}
				}
				return regresar;
			}
					
			function agregarproducto(){
				
				
				tot=$("#total").val();
				$("#idtotal").remove();
				num=document.forma.cantprod.value;
				$("#tablaproductos").append(\'<tr>\
				<td align="center"><input type="text" class="textField" id="clavesp_\'+num+\'" size="15" value=""><input type="hidden" name="producto[\'+num+\']" id="producto\'+num+\'" class="cproductos" value=""></td>\
				<td><input type="text" class="readOnly" id="nomproducto\'+num+\'" size="30" value="" readOnly></td>\
				<td align="center"><input type="text" class="textField" size="10" name="cant[\'+num+\']" id="cant\'+num+\'" value=""  onKeyUp="sumarproductos()"></td>\
				<td align="center"><input type="text" class="textField" size="10" name="precio[\'+num+\']" id="precio\'+num+\'" value=""></td>\
				<td align="center"><input type="text" class="readOnly" size="10" name="importe[\'+num+\']" id="importe\'+num+\'" value="" readOnly></td>\
				</tr>\
				<tr id="idtotal"><th align="right" colspan="4">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total" id="total" value="\'+tot+\'" readOnly></td></tr>\');
				generarbuscador(num);
				num++;
				document.forma.cantprod.value=num;
			}
			
			function sumarproductos(){
				var sumar=0;
				var iv=0;
				var iv_ret=0;
				var is_ret=0;
				var desc = 0;
				for(i=0;i<(document.forma.cantprod.value/1);i++){
					impo=(document.getElementById("cant"+i).value/1)*(document.getElementById("precio"+i).value/1);
					document.getElementById("importe"+i).value=impo.toFixed(2);

					sumar+=(document.getElementById("importe"+i).value/1);

				}

				
		
				document.forma.total.value=sumar.toFixed(2);
			}
			';
			if($_POST['reg']==0) echo 'generarbuscador(0);';
			echo '
			
		  </script>';
	
}
/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'compras.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
				<!--<td><a href="#" onClick="atcr(\'compras.php\',\'_blank\',\'101\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>-->
			 </tr>';
		echo '</table>';
		echo '<table>';
		
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr style="display:none"><td>Comprador</td><td><select name="comprador" id="comprador"><option value="all">---Todos---</option>';
		foreach($array_comprador as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none"><td>Mostrar</td><td><select name="mostrar" id="mostrar" onChange="
		document.forma.producto.value=\'0\';
		if(this.value==\'1\') 
			$(\'#producto\').parents(\'tr:first\').show();
		else
			$(\'#producto\').parents(\'tr:first\').hide();"><option value="0">Agrupado por Folio</option>
		<option value="1">Detallado por producto</option></select></td></tr>';
		echo '<tr style="display:none;"><td>Producto</td><td><select name="producto" id="producto"><option value="0">Todos</option>';
		foreach($array_productos as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
	



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
			objeto.open("POST","compras.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&mostrar="+document.getElementById("mostrar").value+"&producto="+document.getElementById("producto").value+"&comprador="+document.getElementById("comprador").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	buscarRegistros();
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
	echo '<input type="hidden" name="usuario" value="'.$_SESSION['CveUsuario'].'">';
bottom();
?>
