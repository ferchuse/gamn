<?php 

include ("main.php"); 


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de derroteros
		$select= " SELECT * FROM ".$pre."propietarios WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$select .= " ORDER BY nombre ";
		$res=mysql_db_query($base,$select);
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="3">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Nombre</th><th>No de Cuenta</th></tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'propietarios.php\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$row['nombre'].'"></a></td>';
				echo '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
				echo '<td>'.htmlentities(utf8_encode($row['no_cuenta'])).'</td>';
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
	$res=mysql_db_query($base,"SELECT * FROM ".$pre."usuarios WHERE usuario='".$_POST['usuario']."' AND estatus!='I'");
	$res1=mysql_db_query($base,"SELECT * FROM ".$pre."propietarios WHERE usuario='".$_POST['usuario']."' AND cve!='".$_POST['cvepropietario']."'");
	if(mysql_num_rows($res)>0 || mysql_num_rows($res1)>0){
		echo "1";
	}
	else{
		echo "0";
	}
	exit();
}

top($_SESSION);
/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	if($_POST['reg']) {
		//Actualizar el Registro
		$update = " UPDATE ".$pre."propietarios 
					SET nombre='".$_POST['nombre']."',usuario='".$_POST['usuario']."',pass='".$_POST['pass']."',no_cuenta='".$_POST['no_cuenta']."' WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_db_query($base,$update);			
	} else {
		//Insertar el Registro
		$insert = " INSERT INTO ".$pre."propietarios 
					(nombre,usuario,pass,no_cuenta)
					VALUES 
					('".$_POST['nombre']."','".$_POST['usuario']."','".$_POST['pass']."','".$_POST['no_cuenta']."')";
		$ejecutar = mysql_db_query($base,$insert);
	}
	$_POST['cmd']=0;
	
}

/*** EDICION  **************************************************/

if ($_POST['cmd']==1) {
	
	$select=" SELECT * FROM ".$pre."propietarios WHERE cve='".$_POST['reg']."' ";
	$res=mysql_db_query($base,$select);
	$row=mysql_fetch_array($res);
	
	//Menu
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();if(document.forma.nombre.value==\'\'){$(\'#panel\').hide();alert(\'Necesita introducir el nombre\');}else{validarusuario(\''.$row['cve'].'\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'propietarios.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Edicion de propietarios</td></tr>';
	echo '</table>';
	$bloqueado='';
	$class='textField';
	echo '<table>';
	echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" class="'.$class.'" size="100" value="'.$row['nombre'].'"'.$bloqueado.'></td></tr>';
	if($Usuario['usuario']!=0){
		echo '<tr><th>Usuario</th><td><input autocomplete="off" type="text" name="usuario" id="usuario" value="'.$row['usuario'].'" class="readOnly" readOnly></td></tr>';
	}
	else{
		echo '<tr><th>Usuario</th><td><input autocomplete="off" type="text" name="usuario" id="usuario" value="'.$row['usuario'].'" class="textField"></td></tr>';
	}
	echo '<tr><th>Password</th><td><input autocomplete="off" type="password" name="pass" id="pass" value="'.$row['pass'].'" class="textField"></td></tr>';
	echo '<tr><th>No de Cuenta</th><td><input type="text" name="no_cuenta" id="no_cuenta" class="'.$class.'" size="30" value="'.$row['no_cuenta'].'"'.$bloqueado.'></td></tr>';
	echo '</table>';
	
	echo '<script>
		function validarusuario(reg){
			if(document.getElementById("usuario").value!=""){
				objeto=crearObjeto();
				if (objeto.readyState != 0) {
					alert("Error: El Navegador no soporta AJAX");
				} else {
					objeto.open("POST","propietarios.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=2&cvepropietario="+reg+"&usuario="+document.getElementById("usuario").value);
					objeto.onreadystatechange = function()
					{
						if (objeto.readyState==4)
						{
							if(objeto.responseText=="1"){
								$("#panel").hide();
								alert("El usuario ya esta registrado");
							}
							else{
								atcr(\'propietarios.php\',\'\',\'2\',reg);
							}
						}
					}
				}
			}
			else{
				atcr(\'propietarios.php\',\'\',\'2\',reg);
			}
		}
	</script>';
}

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td><td>&nbsp;</td><td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'propietarios.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
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
				objeto.open("POST","propietarios.php",true);
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
bottom();
?>