<?php
include("main.php");
$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}



$res=mysql_db_query($base,"SELECT * FROM clientes order by nombre");
while($row=mysql_fetch_array($res)){
	$array_clientes[$row['cve']]=$row['nombre'];
}
$option_producto='';
$option_producto.='<option value="0" precio="0.00">-Seleccione-</option>';
$res = mysql_db_query($base,"SELECT * FROM productos");
while($row=mysql_fetch_array($res)){
	$array_productos[$row['cve']] = $row['nombre'];
	$array_productos_costo[$row['cve']] = $row['costo'];
	
	$option_producto.='<option value="'.$row['cve'].'" precio="'.round($row['costo']*1.08,2).'">'.$row['nombre'].'</option>';
}

if($_POST['cmd']==101){
	if($_POST['mostrar'] == 1){
		$select="SELECT a.cve,a.fecha,a.hora,a.comprador,a.obs,a.estatus,a.usuario,a.usucan,a.fechacan,b.producto,b.cantidad
		FROM salidas_productos a INNER JOIN salidas_productos_detalle b ON a.cve = b.cvesalida WHERE a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		$select.=" ORDER BY a.cve DESC";
	}
	else{
		$select="SELECT * FROM salidas_productos WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		$select.=" ORDER BY cve DESC";
	}
	$res=mysql_db_query($base,$select);
	$totalRegistros = mysql_num_rows($res);
		
	if(mysql_num_rows($res)>0) {

		$reporte = '<h1>Reporte de Salidas de Productos de '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h1><table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$reporte .= '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Cliente</th>';
		if($_POST['mostrar'] == 1) $reporte .= '<th>Producto</th><th>Cantidad</th><th>Precio</th>';
		$reporte .= '<th>Observaciones</th><th>Monto</th><th>Usuario</th>';
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

			$reporte .= '<td>'.htmlentities($array_clientes[$row['cliente']]).'</td>';
			if($_POST['mostrar'] == 1) $reporte .= '<td>'.htmlentities($array_productos[$row['producto']]).'</td><td align="right">'.$row['cantidad'].'</td><td align="right">'.$row['precio'].'</td>';
			$reporte .= '<td>'.htmlentities($row['obs']).'</td>';
			$reporte .= '<td align="right">'.htmlentities(number_format($row['total'],2)).'</td>';
			$reporte .= '<td>'.htmlentities($array_usuario[$row['usuario']]).'</td>';
			$reporte .= '</tr>';
			$x++;
			$total=$total + $row['monto'];
		}
		$reporte .= '	
			<tr bgcolor="#E9F2F8">
			<td colspan="8" bgcolor="#E9F2F8">'.$x.' Registro(s)</td>
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
	$select=" SELECT * FROM salidas_productos WHERE cve='".$_POST['reg']."' ";
	$rsmotivo=mysql_db_query($base,$select);
	$Motivo=mysql_fetch_array($rsmotivo);
	echo '<html><body><p align="center">';
	echo '<table align="left">';
	echo '<tr><th style="font-size:30px" align="left">Salidas de Productos</th></tr>';
	echo '</table>';
	echo '<br>';
	echo '<table width="100%">';
	echo '<tr style="font-size:20px"><th align="left" width="10%">Folio</th><td width="40%">'.$Motivo['cve'].'</td></tr>';
	echo '<tr style="font-size:20px"><th align="left" width="10%">Fecha</th><td width="40%">'.$Motivo['fecha'].'</td></tr>';
	echo '<tr style="font-size:20px"><th align="left" width="10%">Cliente</th><td width="40%">'.$array_clientes[$Motivo['cliente']].'</td></tr>';
	echo '<tr style="font-size:20px"><th align="left">Total</th><td>'.number_format($Motivo['total'],2).'</td></tr>';
	echo '<tr style="font-size:20px"><th align="left">Productos</th><td><table border="1">
	<tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Importe</th></tr>';
	$res=mysql_db_query($base,"SELECT * FROM salidas_productos_detalle WHERE cvesalida='".$_POST['reg']."'");
	while($row=mysql_fetch_array($res)){
		echo '<tr>';
		echo '<td>'.$array_productos[$row['producto']].'</td>';
		echo '<td align="right">'.$row['cantidad'].'</td>';
		echo '<td align="right">'.$row['precio'].'</td>';
		echo '<td align="right">'.$row['importe'].'</td>';
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
		$select="SELECT a.cve,a.fecha,a.hora,a.cliente,a.obs,a.estatus,a.usuario,a.usucan,a.fechacan
		FROM salidas_productos a  WHERE a.fecha_creacion BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		$select.=" ORDER BY a.cve DESC";
	$res=mysql_db_query($base,$select) or die(mysql_error());
	$totalRegistros = mysql_num_rows($res);
		
	if(mysql_num_rows($res)>0) {
		$reporte = '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$reporte .= '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Cliente</th>';
		$reporte .= '<th>Total</th><th>Observaciones</th><th>Usuario</th>';
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
				$row['total']=0;
			}
			$reporte .= '</td>';
			$reporte .= '<td align="center">'.htmlentities($row['cve']).'</td>';
			$reporte .= '<td align="center">'.htmlentities($row['fecha_creacion'].' '.$row['hora']).'</td>';
			$reporte .= '<td>'.htmlentities($array_clientes[$row['cliente']]).'</td>';
			$reporte .= '<td align="right">'.number_format($row['total'],2).'</td>';
			
			$reporte .= '<td>'.htmlentities($array_usuario[$row['usuario']]).'</td>';
			$reporte .= '</tr>';
			$x++;
			$total=$total + $row['monto'];
		}
		$reporte .= '	
			<tr bgcolor="#E9F2F8">
			<td colspan="4" >'.$x.' Registro(s)</td>
			<td align="right">'.number_format($total,2).'</td><td></td><td></td>
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

if($_GET['ajax']==3){
	$resultado = array();
	$res = mysql_query("SELECT * FROM productos WHERE id_producto like '%".$_GET['term']."%' OR nombre LIKE '%".$_GET['term']."%' ORDER BY id_producto, nombre LIMIT 10");
	while($row = mysql_fetch_array($res)){
		$resultado[] = array(
			'clave' => $row['id_producto'],
			'producto_id' => $row['cve'],
			'value' => $row['id_producto'],
			'label' => utf8_encode($row['id_producto'].' '.$row['nombre']),
			'nombre' => utf8_encode($row['nombre']),
			'precio_venta' => round($row['costo']*1.08,2)
		);
	}
	echo json_encode($resultado);
	exit();
}


if ($_POST['cmd']==3) {
	$delete= "UPDATE salidas_productos SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['usuario']."' WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_db_query($base,$delete);
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/


if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE salidas_productos 
						SET cliente='".$_POST['cliente']."',total='".$_POST['total']."',estatus='A',obs='".$_POST['obs']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_db_query($base,$update);	
			$cvesalida = $_POST['reg'];
			mysql_db_query($base,"DELETE FROM salidas_productos_detalle FROM cvesalida='$cveentrada'");		
	} else {
			//Insertar el Registro
			$insert = " INSERT salidas_productos 
						SET cliente='".$_POST['cliente']."',estatus='A',obs='".$_POST['obs']."',usuario='".$_POST['usuario']."',
							fecha='".$_POST['fecha']."',total='".$_POST['total']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."'";
			$ejecutar = mysql_db_query($base,$insert)or die(mysql_error()) ;
			$cvesalida = mysql_insert_id();
	}
	$i=0;
	foreach($_POST['cant'] as $k=>$v){
		if($v>0){
			mysql_query("INSERT salidas_productos_detalle SET cvesalida='$cvesalida',cantidad='".$v."',producto='".addslashes($_POST['producto'][$k])."',
			precio='".$_POST['precio'][$k]."',importe='".$_POST['importe'][$k]."', costo='".$array_productos_costo[$_POST['producto'][$k]]."'");
			$i++;
		}
	}
	
	$_POST['cmd']=0;
	
}

top($_SESSION);

/*** EDICION  **************************************************/

if ($_POST['cmd']==1) {
	
	$select=" SELECT * FROM salidas_productos WHERE cve='".$_POST['reg']."' ";
	$rssalida=mysql_db_query($base,$select);
	$Salida=mysql_fetch_array($rssalida);
	if($_POST['reg']>0){
		$fecha=$Salida['fecha'];
		$Encabezado = 'Folio No.'.$_POST['reg'];
	}
	else{
		$fecha=fechaLocal();
		$Encabezado = 'Nueva Salida de Producto';
	}
	//Menu
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1 && $_POST['reg']==0){
			echo '<td><a href="#" onClick="
			if(document.forma.cliente.value==\'0\')
				alert(\'Seleccione el Cliente\');
			else if(!validar_detalle())
			{
				alert(\'Hay detalles Vacios\');
				$(\'#panel\').hide();
			}
			else{
				atcr(\'ventas.php\',\'\',\'2\',\''.$Salida['cve'].'\');
			}	
			"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		}
		echo '<td><a href="#" onClick="atcr(\'ventas.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	echo '<input type="hidden" name="clickguardar" value="0">';
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Edicion Salidas de Productos</td></tr>';
	echo '</table>';
	
	echo '<table>';
	echo '<tr><th align="left">'.$Encabezado.'</th><td>&nbsp;</td></tr>';
	echo '<tr><th align="left">Cliente</th><td><select name="cliente" id="cliente" class="textField"><option value="0">---Seleccione---</option>';
		foreach ($array_clientes as $k=>$v) { 
	    	echo '<option value="'.$k.'"';
	    	if($row['cliente']==$k){
	    		echo'selected';
	    	}
	    	echo'>'.$v.'</option>';
		}
		echo '</select></td></tr>';

	echo '</table>';
	echo '<input type="hidden" name="clickguardar" id="clickguardar" value="no">';
		echo '<table id="tablaproductos"><tr>';

		echo '<th>Codigo</th><th>Descripcion</th><th>Cantidad</th>';
		echo '<th>Precio Unitario</th><th>Importe</th></tr>';
		$i=0;
		if($_POST['reg']>0){
			$res1 = mysql_query("SELECT a.*, b.nombre, b.id_producto FROM salidas_productos_detalle a INNER JOIN productos b ON b.cve = a.producto WHERE a.cvesalida='{$_POST['reg']}'");
			while($row1 = mysql_fetch_array($res1)){
				echo '<tr>';
				echo '<td align="center"><input type="text" class="textField" id="clavesp_'.$i.'" size="15" value="'.$row1['id_producto'].'"><input type="hidden" name="producto['.$i.']" id="producto'.$i.'" class="cproductos" value="'.$row1['producto'].'"></td>';
				echo '<td align="center"><input type="text" class="readOnly" id="nomproducto'.$i.'" size="30" value="'.$row1['nombre'].'"></td>';
				echo '<td align="center"><input type="text" class="textField" size="10" name="cant['.$i.']" id="cant'.$i.'" value="'.$row1['cantidad'].'"  onKeyUp="sumarproductos()"></td>';
				echo '<td align="center"><input type="text" class="readOnly" size="10" name="precio['.$i.']" id="precio'.$i.'" value="'.$row1['precio'].'" readOnly></td>';
				echo '<td align="center"><input type="text" class="readOnly" size="10" name="importe['.$i.']" id="importe'.$i.'" value="'.$row1['importe'].'" readOnly></td>';
				echo '</tr>';
				$i++;
			}
		}
		if($i==0){
			echo '<tr>';
			echo '<td align="center"><input type="text" class="textField" id="clavesp_'.$i.'" size="15" value=""><input type="hidden" name="producto['.$i.']" id="producto'.$i.'" class="cproductos" value=""></td>';
			echo '<td align="center"><input type="text" class="readOnly" id="nomproducto'.$i.'" size="30" value=""></td>';
			echo '<td align="center"><input type="text" class="textField" size="10" name="cant['.$i.']" id="cant'.$i.'" value=""  onKeyUp="sumarproductos()"></td>';
			echo '<td align="center"><input type="text" class="readOnly" size="10" name="precio['.$i.']" id="precio'.$i.'" value="" readOnly></td>';
			echo '<td align="center"><input type="text" class="readOnly" size="10" name="importe['.$i.']" id="importe'.$i.'" value="" readOnly></td>';
			echo '</tr>';
			$i++;
		}

		echo '<tr id="idtotal"><th align="right" colspan="3">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total" id="total" value="" readOnly></td></tr>';
		echo '</table>';	
		if($_POST['reg']==0)	
			echo '<input type="button" value="Agregar" onClick="agregarproducto()" class="textField">';
		echo '<input type="hidden" name="cantprod" value="'.$i.'">';
		echo '<script>

			function generarbuscador(i){
				$( "#clavesp_"+i ).autocomplete({
			      source: "ventas.php?ajax=3",
			      minLength: 2,
			      select: function( event, ui ) {
			      	document.getElementById("clavesp_"+i).value=ui.item.clave;
			        document.getElementById("producto"+i).value=ui.item.producto_id;
			        document.getElementById("nomproducto"+i).value=ui.item.nombre;
			        document.getElementById("precio"+i).value = ui.item.precio_venta;
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
				<td><input type="text" class="readOnly" id="nomproducto\'+num+\'" size="30" value=""></td>\
				<td align="center"><input type="text" class="textField" size="10" name="cant[\'+num+\']" id="cant\'+num+\'" value=""  onKeyUp="sumarproductos()"></td>\
				<td align="center"><input type="text" class="readOnly" size="10" name="precio[\'+num+\']" id="precio\'+num+\'" value="" readOnly></td>\
				<td align="center"><input type="text" class="readOnly" size="10" name="importe[\'+num+\']" id="importe\'+num+\'" value="" readOnly></td>\
				</tr>\
				<tr id="idtotal"><th align="right" colspan="3">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total" id="total" value="\'+tot+\'" readOnly></td></tr>\');
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

			function traerCosto(ren){
				precio = $("#producto"+ren).find("option:selected").attr("precio");
				$("#precio"+ren).val(precio);
				sumarproductos();
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
				<td><a href="#" onClick="atcr(\'ventas.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
				<!--<td><a href="#" onClick="atcr(\'ventas.php\',\'_blank\',\'101\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>-->
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
			objeto.open("POST","ventas.php",true);
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
