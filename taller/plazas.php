<?php 

include ("main2.php"); 

$rsDepto=mysql_query("SELECT * FROM areas");
while($Depto=mysql_fetch_array($rsDepto)){
	$array_localidad[$Depto['cve']]=$Depto['nombre'];
}

$array_tipo_plaza=array(1=>"Capital",2=>"Municipio",3=>"Movil");
/*** ELIMINAR REGISTRO  **************************************************/



/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	$campos="";
	foreach($_POST['camposi'] as $k=>$v){
		$campos.=",".$k."='".$v."'";
	}	
	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE plazas 
						SET nombre='".$_POST['nombre']."',usuario='".$_POST['usuario']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);	
			
			$id=$_POST['reg'];
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO plazas 
						(nombre,usuario)
						VALUES 
						('".$_POST['nombre']."','".$_POST['usuario']."')";
			$ejecutar = mysql_query($insert);
			$id = mysql_insert_id();
	}
	
	$_POST['cmd']=0;
	
}


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM plazas WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$rsplaza=mysql_query($select);
		$totalRegistros = mysql_num_rows($rsplaza);
		if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		//$select .= " ORDER BY nombre LIMIT ".$primerRegistro.",".$eRegistrosPagina;
		$rsplaza=mysql_query($select);
		
		if(mysql_num_rows($rsplaza)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr class="grid_header"><th>Editar</th>';
			if($_POST['cveusuario']==1) echo '<th>Cve</th>';
			echo '<th>Nombre</th><th>usuario GPS</th></tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($Plaza=mysql_fetch_array($rsplaza)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$Plaza['cve'].')">'.$imgeditar.'</a></td>';
				if($_POST['cveusuario']==1) echo '<td align="center">'.utf8_encode($Plaza['cve']).'</td>';
				echo '<td>'.utf8_encode($Plaza['nombre']).'</td>';
				echo '<td>'.utf8_encode($Plaza['usuario']).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="8" class="grid_header">';menunavegacion();echo '</td>
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

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM plazas WHERE cve='".$_POST['reg']."' ";
		$rsplaza=mysql_query($select);
		$Plaza=mysql_fetch_array($rsplaza);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="atcr(\'plazas.php\',\'\',\'2\',\''.$Plaza['cve'].'\');">'.$imgguardar.'&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="atcr(\'plazas.php\',\'\',\'0\',\'0\');">'.$imgvolver.'&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		//Formulario 
		echo '<table>';
		echo '<tr><td class="texto_titulo_ventanas">Edicion Plazas</td></tr>';
		echo '</table>';
		echo '<table>';

		echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$Plaza['nombre'].'"></td></tr>';
		echo '<tr><th>Usuario GPS</th><td><input type="text" name="usuario" id="usuario" class="textField" size="100" value="'.$Plaza['usuario'].'"></td></tr>';
		echo '</table>';
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td><td>&nbsp;</td><td><a href="#" onclick="buscarRegistros();">'.$imgbuscar.'</a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'plazas.php\',\'\',\'1\',\'0\');">'.$imgnuevo.'</a>&nbsp;Nuevo</td><td>&nbsp;</td>
				</tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
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
			objeto.open("POST","plazas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value);
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

?>

