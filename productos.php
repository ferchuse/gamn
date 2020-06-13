<?php 

include ("main.php"); 


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de derroteros
		$select= " SELECT * FROM ".$pre."productos WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' OR id_producto = '{$_POST['nom']}'"; }
		$select .= " ORDER BY nombre ";
		$res=mysql_db_query($base,$select);
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="4">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Nombre</th><th>ID Producto</th><!--<th>Costo</th>--></tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'productos.php\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$row['nombre'].'"></a></td>';
				echo '<td>'.htmlentities($row['nombre']).'</td>';
				echo '<td>'.htmlentities($row['id_producto']).'</td>';
				//echo '<td>'.htmlentities(number_format($row['costo'],2)).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="4" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	if($_POST['reg']) {
		$select="SELECT * FROM productos WHERE cve='".$_POST['reg']."' ";
			$rsparque=mysql_db_query($base,$select);
			$Parque=mysql_fetch_array($rsparque);
			if($Parque['costo']!=$_POST['costo']){
				$rsfolio=mysql_db_query($base,"SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_productos WHERE dato='costo'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_propietario="	INSERT cambios_datos_productos
							SET folio='".$Folio[0]."',dato='costo',
							valor_anterior='".$Parque['costo']."',valor_nuevo='".$_POST['costo']."',
							cve_aux='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['usuario']."' ";
				$ejecutar_propietario=mysql_db_query($base,$insert_propietario) or die(mysql_error());			
			}
		//Actualizar el Registro
		$update = " UPDATE ".$pre."productos 
					SET nombre='".$_POST['nombre']."',id_producto='".$_POST['id_producto']."',costo='".$_POST['costo']."' WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_db_query($base,$update);			
	} else {
		//Insertar el Registro
		$insert = " INSERT INTO ".$pre."productos 
					(nombre,id_producto,costo)
					VALUES 
					('".$_POST['nombre']."','".$_POST['id_producto']."','".$_POST['costo']."')";
		$ejecutar = mysql_db_query($base,$insert);
	}
	$_POST['cmd']=0;
	
}

/*** EDICION  **************************************************/

if ($_POST['cmd']==1) {
	
	$select=" SELECT * FROM ".$pre."productos WHERE cve='".$_POST['reg']."' ";
	$res=mysql_db_query($base,$select);
	$row=mysql_fetch_array($res);
	
	//Menu
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="if(document.forma.nombre.value==\'\'){alert(\'Necesita introducir el nombre\');}else{atcr(\'productos.php\',\'\',\'2\',\''.$row['cve'].'\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'productos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Edicion de productos</td></tr>';
	echo '</table>';
	$bloqueado='';
	$class='textField';
	echo '<table>';
	echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" class="'.$class.'" size="100" value="'.$row['nombre'].'"'.$bloqueado.'></td></tr>';
	echo '<tr><th>ID del Producto</th><td><input type="text" name="id_producto" id="id_producto" class="'.$class.'" size="20" value="'.$row['id_producto'].'"'.$bloqueado.'></td></tr>';
	echo '<tr style="display:none;"><th>Costo</th><td><input type="text" name="costo" id="costo" class="'.$class.'" size="10" value="'.$row['costo'].'"'.$bloqueado.'></td></tr>';
	echo '</table>';
	
}

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td><td>&nbsp;</td><td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'productos.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			</tr>';
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
				objeto.open("POST","productos.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
echo '<input type="hidden" name="usuario" value="'.$_SESSION['CveUsuario'].'">';
bottom();
?>