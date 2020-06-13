<?php 
include ("main.php");
$res=mysql_db_query($base,"SELECT a.cve,a.nombre FROM ".$pre."propietarios a left join parque b on a.cve=b.propietario where  b.propietario>='1' and b.estatus='1' ORDER BY a.nombre") or die(mysql_error());
while($row=mysql_fetch_array($res)){
	$array_propietario[$row['cve']]=$row['nombre'];
}
$res=mysql_db_query($base," SELECT * FROM ".$pre."propietarios_operadores WHERE 1 group by cve_propietario ORDER BY cve_propietario");
while($row=mysql_fetch_array($res)){
	$array_propietario_opera[$row['cve_propietario']]=$array_propietario[$row['cve_propietario']];
}

$res=mysql_db_query($base,"SELECT * FROM ".$pre."conductores where estatus='1'ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_conductor[$row['cve']]=$row['nombre'];
}
$rsUsuario=mysql_db_query($base,"SELECT * FROM ".$pre."usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de derroteros
		$select= " SELECT * FROM ".$pre."propietarios_operadores WHERE 1 ";
		if ($_POST['propietario']!="") { $select.=" AND cve_propietario= '".$_POST['propietario']."' "; }
		$select .= " group by cve_propietario ORDER BY cve ";
		$res=mysql_db_query($base,$select);
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="3">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Nombre</th></tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				

				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'propietarios_operadores.php\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$row['nombre'].'"></a></td>';
				echo '<td>'.htmlentities($array_propietario[$row['cve_propietario']]).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																					   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																					   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																					   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																					   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$selec= " SELECT * FROM ".$pre."parque WHERE propietario='".$row['cve_propietario']."' and estatus='1' ";
		$selec .= " ORDER BY cve desc";
		$ress=mysql_db_query($base,$selec);
		while($row1=mysql_fetch_array($ress)){echo'-'.$row1['no_eco'].' ';}
				
				echo'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="3" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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

if($_POST['ajax']==2){
          
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
if ($_POST['ajax']==3) {
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
          
		
		echo'<td id="Resultados2">';
		$select= " SELECT * FROM ".$pre."parque WHERE propietario='".$_POST['propietario']."' and estatus='1' ";
		$select .= " ORDER BY cve desc";
		$res=mysql_db_query($base,$select);
		
		echo '<textarea rows="4" cols="50">';
//			echo '<tr><td bgcolor="#E9F2F8" colspan="3">'.mysql_num_rows($res).' Registro(s)</td></tr>';
//			echo '<tr bgcolor="#E9F2F8"><th>Eliminar</th><th>Nombre</th></tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
//				rowb();
				echo ' -'.htmlentities($row['no_eco']).'';

			}
			echo '	
				</textarea></td>';

	exit();
}
top($_SESSION);
/*** ACTUALIZAR REGISTRO  **************************************************/


if ($_POST['cmd']==-2) {

	if($_POST['reg']) {
		//Actualizar el Registro
		$update = " UPDATE ".$pre."propietarios 
					SET nombre='".$_POST['nombre']."',usuario='".$_POST['usuario']."',pass='".$_POST['pass']."' WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_db_query($base,$update);			
	} else {
		//Insertar el Registro
		$insert = " INSERT INTO ".$pre."propietarios 
					(nombre,usuario,pass)
					VALUES 
					('".$_POST['nombre']."','".$_POST['usuario']."','".$_POST['pass']."')";
		$ejecutar = mysql_db_query($base,$insert);
	}
	$_POST['cmd']=0;
	
}

/*** EDICION  **************************************************/

if ($_POST['cmd']==1) {
	$cve="";
	$select=" SELECT * FROM ".$pre."propietarios_operadores WHERE cve='".$_POST['reg']."' ";
	$res=mysql_db_query($base,$select);
	$row=mysql_fetch_array($res);
	
	//Menu
	echo '<table>';
	echo '
		<tr>';
//		if(nivelUsuario()>1)
//			echo '<td><a href="#" onClick="$(\'#panel\').show();if(document.forma.nombre.value==\'\'){$(\'#panel\').hide();alert(\'Necesita introducir el propietario\');}else{atcr(\'propietarios_operadores.php\',\'\',\'2\','.$_POST['reg'].');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'propietarios_operadores.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Edicion de propietarios</td></tr>';
	echo '</table>';
	echo '<table>';
	
	if($_POST['reg']>0){
		echo'<tr><td>Propietario</td><td><input type="hidden" name="propietario" id="propietario" value="'.$row['cve_propietario'].'" class="readOnly" readonly><input type="text" size="50" name="" id="" value="'.$array_propietario[$row['cve_propietario']].'" class="readOnly" readonly></td></tr>';
		$cve=$row['cve_propietario'];
	}else{echo '<tr><td>Propietario</td><td><select name="propietario" id="propietario" class="textField" onchange="traer_uni()"><option value="">---Todos---</option>';
		foreach($array_propietario as $k=>$v){
			echo '<option value="'.$k.'"';
		//	if($k==$row['cve']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		$cve=$k;
		}
		
	echo '<tr><td>Unidades</td><td id="Resultados2"><textarea></textarea></td></tr>
	<tr><td>Operador</td><td><select name="operador" id="operador" class="textField"><option value="">---Seleccione---</option>';
		foreach($array_conductor as $k=>$v){
			echo '<option value="'.$k.'"';
	//		if($k==$row['cve']) echo ' selected';
			echo '>'.$v.'</option>';
		}
//		echo '</select>&nbsp;<input type="button" value="Agregar Conductor" class="textField" name="agregar" onClick="$(\'#panel\').show();if(document.forma.propietario.value==\'\'){$(\'#panel\').hide();alert(\'Necesita introducir el Propietario\');}elseif(document.forma.operador.value==\'\'){$(\'#panel\').hide();alert(\'Necesita introducir el Operador\');}else{agregarconductor('.$row['cve'].');}"></td></tr>';
	echo '</select>&nbsp;<input type="button" value="Agregar Conductor" class="textField" name="agregar" onClick="agregarconductor('.$row['cve'].');"></td></tr>';
	echo '</table><table><tr id="Resultados1"><td>';
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
	echo '</table>
	<table><tr><td>';
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
	echo '</table>';
	
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
				objeto.open("POST","propietarios_operadores.php",true);
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
		function traer_uni(cod)
		{
		
//			document.getElementById("Resultados2").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","propietarios_operadores.php",true);
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
			  
				objeto.open("POST","propietarios_operadores.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=3&cod="+reg+"&numeroPagina="+document.getElementById("numeroPagina").value+"&propietario="+document.getElementById("propietario").value);
				objeto.onreadystatechange = function()
				{
					if (objeto.readyState==4)
					{
					opc=objeto.responseText;
					console.log(opc);
					if(opc==1){
                                       atcr("propietarios_operadores.php","",0,reg); 
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
		traer_uni();
	</script>';
}

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onClick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>Buscar&nbsp;
				<a href="#" onClick="atcr(\'propietarios_operadores.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			</tr>';
			echo '<tr><td>Propietario</td><td><select name="propietario" id="propietario" class="textField"><option value="">---Todos---</option>';
		foreach($array_propietario_opera as $k=>$v){
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
				objeto.open("POST","propietarios_operadores.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=1&propietario="+document.getElementById("propietario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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