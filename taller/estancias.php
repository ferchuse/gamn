<?php 

include ("main2.php"); 

mysql_select_db('gamn_gps_otra_plataforma');
//if($_POST['plazausuario'] == 1)
	$select = "SELECT a.cve,  a.codigo, CONCAT(a.nombre, ', RUTA: ',b.nombre) as nombre FROM geocercas a INNER JOIN rutas b ON a.base = b.base AND a.ruta = b.cvebase WHERE 1 ORDER BY a.codigo, a.nombre";
//else
//	$select= " SELECT * FROM geocercas a LEFT JOIN rutas b ON a.base = b.base AND a.ruta = b.cvebase WHERE (b.plaza IN (0,'".$_POST['plazausuario']."') OR ISNULL(b.cve)) ORDER BY a.codigo, a.nombre";
$array_geocercas = array();
$res = mysql_query($select);
while($row = mysql_fetch_array($res)){
	$array_geocercas[$row['cve']] = $row['codigo'].' '.$row['nombre'];
}
		
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	mysql_select_db('gamn_gps_otra_plataforma');
		//Listado de tecnicos y administradores
		$select= " SELECT * FROM estancias WHERE 1";
	
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY nombre";
		$res=mysql_query($select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="tablas">';
			echo '<tr class="grid_header"><th>&nbsp;</th><th>Nombre</th><th>Geocerca Llegada</th><th>Geocerca Salida</th><th>Geocerca Anden</th></tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')">'.$imgeditar.'</a></td>';
				echo '<td>'.htmlentities($row['nombre']).'</td>';
				echo '<td>'.htmlentities($array_geocercas[$row['geocerca_llegada']]).'</td>';
				echo '<td>'.htmlentities($array_geocercas[$row['geocerca_salida']]).'</td>';
				echo '<td>'.htmlentities($array_geocercas[$row['geocerca_anden']]).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="7" class="grid_header">';menunavegacion();echo '</td>
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

mysql_select_db('gamn_gps');
top($_SESSION);

/*** ELIMINAR REGISTRO  **************************************************/



/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	mysql_select_db('gamn_gps_otra_plataforma');
	if($_POST['reg']) {
		
		
		//Actualizar el Registro
			$update = " UPDATE estancias 
					SET nombre='".$_POST['nombre']."',geocerca_llegada='".$_POST['geocerca_llegada']."',geocerca_salida='".$_POST['geocerca_salida']."',geocerca_anden='".$_POST['geocerca_anden']."'
					WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_query($update);
		$cveusu=$_POST['reg'];
	} else {
		//Insertar el Registro
		$insert = " INSERT estancias 
					SET nombre='".$_POST['nombre']."',geocerca_llegada='".$_POST['geocerca_llegada']."',geocerca_salida='".$_POST['geocerca_salida']."',geocerca_anden='".$_POST['geocerca_anden']."'";
		$ejecutar = mysql_query($insert) or die(mysql_error());
		$cveusu=mysql_insert_id();
	}
	
	
	$_POST['cmd']=0;
	mysql_select_db('gamn_gps');
}


/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		mysql_select_db('gamn_gps_otra_plataforma');
		$select=" SELECT * FROM estancias WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
		if(nivelUsuario()>1)
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'estancias.php\',\'\',\'2\',\''.$_POST['reg'].'\');">'.$imgguardar.'&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '
			<td><a href="#" onClick="$(\'#panel\').show();atcr(\'estancias.php\',\'\',\'0\',\'0\');">'.$imgvolver.'&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="texto_titulo_ventanas">Edicion Estancias</td></tr>';
		echo '</table>';

		//Formulario 
		echo '<table>';
		
		echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" value="'.$row['nombre'].'" size="40" class="textField"></td></tr>';
		
		echo '<tr><th>Geocerca Llegada</th><td><select name="geocerca_llegada" id="geocerca_llegada"><option value="0">Seleccione</option>';
		foreach($array_geocercas as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$row['geocerca_llegada']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>Geocerca Salida</th><td><select name="geocerca_salida" id="geocerca_salida"><option value="0">Seleccione</option>';
		foreach($array_geocercas as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$row['geocerca_salida']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>Geocerca Anden</th><td><select name="geocerca_anden" id="geocerca_anden"><option value="0">Seleccione</option>';
		foreach($array_geocercas as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$row['geocerca_anden']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		
		echo '</table>';
		
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();">'.$imgbuscar.'</a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'estancias.php\',\'\',\'1\',\'0\');">'.$imgnuevo.'</a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="30" class="textField"></td></tr>';	
		
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
					objeto.open("POST","estancias.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
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

