<?php 

include ("main.php"); 


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de cat_cargos_variables
		$select= " SELECT * FROM ".$pre."cat_cargos_variables WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$select .= " ORDER BY nombre ";
		$res=mysql_db_query($base,$select);
		/*$totalRegistros = mysql_num_rows($res);
		if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		$select .= " LIMIT ".$primerRegistro.",".$eRegistrosPagina;
		$res=mysql_db_query($base,$select);*/
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="2">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Nombre</th></tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$row['nombre'].'"></a></td>';
				echo '<td>'.htmlentities($row['nombre']).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="2" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
		$select=" SELECT * FROM ".$pre."cat_cargos_variables WHERE cve='".$_POST['reg']."' ";
		$res=mysql_db_query($base,$select);
		$row=mysql_fetch_array($res);
		if($row['nombre']!=$_POST['nombre']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Nombre',nuevo='".$_POST['nombre']."',anterior='".$row['nombre']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
			//Actualizar el Registro
			$update = " UPDATE ".$pre."cat_cargos_variables 
						SET nombre='".$_POST['nombre']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_db_query($base,$update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO ".$pre."cat_cargos_variables 
						(nombre)
						VALUES 
						('".$_POST['nombre']."')";
			$ejecutar = mysql_db_query($base,$insert);
	}
	$_POST['cmd']=0;
	
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM ".$pre."cat_cargos_variables WHERE cve='".$_POST['reg']."' ";
		$res=mysql_db_query($base,$select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cat_cargos_variables.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cat_cargos_variables.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion de Catalogo de Cargos Administrativos</td></tr>';
		echo '</table>';
		$bloqueado='';
		$class='textField';
		if($_POST['reg']>0){
			$class='readOnly';
			$bloqueado=' readOnly';
		} 
		echo '<table>';
		echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" class="'.$class.'" size="100" value="'.$row['nombre'].'"'.$bloqueado.'></td></tr>';
		echo '</table>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td><td>&nbsp;</td><td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'cat_cargos_variables.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
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
					objeto.open("POST","cat_cargos_variables.php",true);
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

