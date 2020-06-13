<?php
include("main.php");

$res = mysql_db_query($base,"SELECT * FROM origenes_destinos ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_orides[$row['cve']]=$row['nombre'];
	$array_abreviaturaorides[$row['cve']]=$row['nombre'];
}

if($_POST['cmd']==101){
	$res = mysql_db_query($base,"SELECT * FROM roles WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	
	$texto ='|';
	$texto.=chr(27).'!'.chr(40)."NEXTLALPAN ROLES";
	$texto.='||';
	$texto.=chr(27).'!'.chr(40)."No: ".$row['numero'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."Nombre: ".$row['nombre'];
	$texto.='|';
	$texto.="SALIDA     HORA DESTINO    HORA";
	$texto.='||';
	$res1=mysql_db_query($base,"SELECT * FROM roles_detalles WHERE rol = '".$_POST['reg']."' ORDER BY cve");
	while($row1=mysql_fetch_array($res1)){
		$texto.=sprintf("%-10s",$array_abreviaturaorides[$row1['origen']]);
		$texto.=sprintf("% 6s",$row1['horasalida']);
		$texto.=sprintf("%-10s",$array_abreviaturaorides[$row1['destino']]);
		$texto.=sprintf("% 6s",$row1['horallegada']);
		$texto.='||';
	}
	$texto.='|';
	
	$impresion='<iframe src="http://localhost/impresiongeneral.php?textoimp='.$texto.'" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",2000);</script>';
	exit();
}

if($_POST['ajax']==1){
	//Listado de derroteros
	$select= " SELECT * FROM ".$pre."roles WHERE 1 ";
	if ($_POST['numero']!="") { $select.=" AND numero='".$_POST['numero']."' "; }
	if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
	$select .= " ORDER BY numero ";
	$res=mysql_db_query($base,$select);
	if(mysql_num_rows($res)>0) 
	{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="3">'.mysql_num_rows($res).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Numero</th><th>Nombre</th></tr>';//<th>P.Costo</th><th>P.Venta</th>
		while($row=mysql_fetch_array($res)) {
			rowb();
			echo '<td align="center" width="40" nowrap>
			<a href="#" onClick="atcr(\'origenes_destinos.php\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$row['nombre'].'"></a>&nbsp;&nbsp;
			<a href="#" onClick="atcr(\'origenes_destinos.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['nombre'].'"></a></td>';
			echo '<td>'.htmlentities($row['numero']).'</td>';
			echo '<td>'.htmlentities($row['nombre']).'</td>';
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
	$res = mysql_db_query($base,"SELECT * FROM roles WHERE numero = '".$_POST['numero']."' AND cve!='".$_POST['clave']."'");
	if($row=mysql_fetch_array($res))
		echo "1";
	else
		echo "2";
	exit();
}

top($_SESSION);

if($_POST['cmd']==2){

	if($_POST['reg']) {
		//Actualizar el Registro
		$update = " UPDATE ".$pre."roles 
					SET nombre='".$_POST['nombre']."',numero='".$_POST['numero']."' WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_db_query($base,$update);			
		mysql_db_query($base,"DELETE roles_detalles WHERE rol = '".$_POST['reg']."'");
	} else {
		//Insertar el Registro
		$insert = " INSERT INTO ".$pre."roles 
					(nombre,numero)
					VALUES 
					('".$_POST['nombre']."','".$_POST['numero']."')";
		$ejecutar = mysql_db_query($base,$insert);
		$_POST['reg']=mysql_insert_id();
	}
	if(count($_POST['origen'])>0){
		foreach($_POST['origen'] as $k=>$v){
			mysql_db_query($base,"INSERT roles_detalles SET rol='".$_POST['reg']."',origen='$v',horasalida='".$_POST['horasalida'][$k]."',
			destino='".$_POST['destino'][$k]."',horallegada='".$_POST['horallegada'][$k]."'");
		}
	}
	$_POST['cmd']=0;
	
}

if ($_POST['cmd']==1) {
	
	$select=" SELECT * FROM ".$pre."roles WHERE cve='".$_POST['reg']."' ";
	$res=mysql_db_query($base,$select);
	$row=mysql_fetch_array($res);
	
	//Menu
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();
			if(document.forma.numero.value==\'\'){
				$(\'#panel\').hide();
				alert(\'Necesita introducir el numero\');
			}
			else if(IsNumeric(document.forma.numero.value)!=true){
				$(\'#panel\').hide();
				alert(\'Necesita introducir el numero\');
			}
			else if(document.forma.nombre.value==\'\'){
				$(\'#panel\').hide();
				alert(\'Necesita introducir el nombre\');
			}
			else{
				$(\'#r0\').remove();
				atcr(\'roles.php\',\'\',\'2\',\''.$row['cve'].'\');
			}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'roles.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Edicion de roles</td></tr>';
	echo '</table>';
	$bloqueado='';
	$class='textField';
	echo '<table>';
	echo '<tr><th>Numero</th><td><input type="text" name="numero" id="numero" class="'.$class.'" size="10" value="'.$row['numero'].'"'.$bloqueado.'></td></tr>';
	echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" class="'.$class.'" size="100" value="'.$row['nombre'].'"'.$bloqueado.'></td></tr>';
	echo '</table>';
	echo '<table id="tablaorigendestino" border="1" width="50%"><tr><th>Origen</th><th>Hora Salida</th><th>Destino</th><th>Hora Llegada</th><th>&nbsp;</th></tr>';	
	echo '<tr id="r0" style="display:none;">';
	echo '<td align="center"><select name="origen[]"><option value="0">Seleccione</option>';
	foreach($array_orides as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td>';
	echo '<td align="center"><input type="text" class="textField" name="horasalida[]" value="" size="10"></td>';
	echo '<td align="center"><select name="destino[]"><option value="0">Seleccione</option>';
	foreach($array_orides as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td>';
	echo '<td align="center"><input type="text" class="textField" name="horallegada[]" value="" size="10"></td>';
	echo '<td><span style="cursor:pointer;" onClick="$(this).parents(\'tr:first\').remove();"><img src="images/basura.gif"></span></td>';
	echo '</tr>';
	$res1=mysql_db_query($base,"SELECT * FROM roles_detalle WHERE rol='".$_POST['reg']."' ORDER BY cve");
	$i=1;
	while($row1=mysql_fetch_array($res1)){
		echo '<tr>';
		echo '<td align="center"><select name="origen[]"><option value="0">Seleccione</option>';
		foreach($array_orides as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row1['origen']==$k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td>';
		echo '<td align="center"><input type="text" class="textField" name="horasalida[]" value="'.$row1['horasalida'].'" size="10"></td>';
		echo '<td align="center"><select name="destino[]"><option value="0">Seleccione</option>';
		foreach($array_orides as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row1['origen']==$k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td>';
		echo '<td align="center"><input type="text" class="textField" name="horallegada[]" value="'.$row1['horallegada'].'" size="10"></td>';
		echo '<td><span style="cursor:pointer;" onClick="$(this).parents(\'tr:first\').remove();"><img src="images/basura.gif"></span></td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '<input type="button" value="Agregar" class="textField" onClick="agregar()">';
	
	echo '<script>
			function agregar(){
				$("#tablaorigendestino").append(\'<tr>\'+$("#r0").html()+\'</tr>\');
			}
		</script>';
	
}

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
			<td><a href="#" onclick="atcr(\'roles.php\',\'\',1,0);"><img src="images/nuevo.gif" border="0">&nbsp;&nbsp;Nuevo</a>&nbsp;&nbsp;</td>';
	echo '
		  </tr>';
	echo '</table>';
	//Busqueda
	echo '<table>';
	echo '<tr><td>Numero</td><td><input type="text" name="numero" id="numero" size="5" class="textField" value=""></td></tr>';
	echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>';
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
				objeto.open("POST","roles.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=1&numero="+document.getElementById("numero").value+"&nom="+document.getElementById("nom").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
