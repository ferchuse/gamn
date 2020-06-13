<?php 
include ("main.php");  
/*** ELIMINAR REGISTRO  **************************************************/
/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	if($_POST['reg']>0) {
		/*$res=mysql_db_query($base,"SELECT * FROM provedores WHERE cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		if($row['nombre']!=$_POST['nombre']){
			$rsfolio=mysql_db_query($base,"SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_provedores WHERE dato='Nombre'") or die(mysql_error());
			$Folio=mysql_fetch_array($rsfolio);
			$insert_estatus="	INSERT cambios_datos_provedores
						SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Nombre',fecha_reg='".fechaLocal()."',
						valor_anterior='".$row['nombre']."',valor_nuevo='".$_POST['nombre']."',
						cve_provedor='".$_POST['reg']."',usuario='".$_SESSION['CveUsuario']."'";
			$ejecutar_estatus=mysql_db_query($base,$insert_estatus);	

		}
		if($row['rfc']!=$_POST['rfc']){
			$rsfolio=mysql_db_query($base,"SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_provedores WHERE dato='Rfc'") or die(mysql_error());
			$Folio=mysql_fetch_array($rsfolio);
			$insert_estatus="	INSERT cambios_datos_provedores
						SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Rfc',fecha_reg='".fechaLocal()."',
						valor_anterior='".$row['rfc']."',valor_nuevo='".$_POST['rfc']."',
						cve_provedor='".$_POST['reg']."',usuario='".$_SESSION['CveUsuario']."'";
			$ejecutar_estatus=mysql_db_query($base,$insert_estatus);	

		}
		if($row['telefono']!=$_POST['telefono']){
			$rsfolio=mysql_db_query($base,"SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_provedores WHERE dato='Telefono'") or die(mysql_error());
			$Folio=mysql_fetch_array($rsfolio);
			$insert_estatus="	INSERT cambios_datos_provedores
						SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Telefono',fecha_reg='".fechaLocal()."',
						valor_anterior='".$row['telefono']."',valor_nuevo='".$_POST['telefono']."',
						cve_provedor='".$_POST['reg']."',usuario='".$_SESSION['CveUsuario']."'";
			$ejecutar_estatus=mysql_db_query($base,$insert_estatus);	

		}
		if($row['contacto']!=$_POST['contacto']){
			$rsfolio=mysql_db_query($base,"SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_provedores WHERE dato='Contacto'") or die(mysql_error());
			$Folio=mysql_fetch_array($rsfolio);
			$insert_estatus="	INSERT cambios_datos_provedores
						SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Contacto',fecha_reg='".fechaLocal()."',
						valor_anterior='".$row['contacto']."',valor_nuevo='".$_POST['contacto']."',
						cve_provedor='".$_POST['reg']."',usuario='".$_SESSION['CveUsuario']."'";
			$ejecutar_estatus=mysql_db_query($base,$insert_estatus);	

		}
		if($row['direccion']!=$_POST['direccion']){
			$rsfolio=mysql_db_query($base,"SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_provedores WHERE dato='Direccion'") or die(mysql_error());
			$Folio=mysql_fetch_array($rsfolio);
			$insert_estatus="	INSERT cambios_datos_provedores
						SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Direccion',fecha_reg='".fechaLocal()."',
						valor_anterior='".$row['direccion']."',valor_nuevo='".$_POST['direccion']."',
						cve_provedor='".$_POST['reg']."',usuario='".$_SESSION['CveUsuario']."'";
			$ejecutar_estatus=mysql_db_query($base,$insert_estatus);	

		}*/
			//Actualizar el Registro
			$update = " UPDATE provedores 
						SET nombre='".$_POST['nombre']."',rfc='".$_POST['rfc']."',contacto='".$_POST['contacto']."',telefono='".$_POST['telefono']."',direccion='".$_POST['direccion']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_db_query($base,$update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO provedores 
						(nombre,rfc,contacto,telefono,direccion)
						VALUES 
						('".$_POST['nombre']."','".$_POST['rfc']."','".$_POST['contacto']."','".$_POST['telefono']."','".$_POST['direccion']."')";
			$ejecutar = mysql_db_query($base,$insert) or die(mysql_error());
	}
	header("Location: provedores.php");
	
}


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM provedores WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$rsplaza=mysql_db_query($base,$select);
		$totalRegistros = mysql_num_rows($rsplaza);
		if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		$select .= " ORDER BY nombre LIMIT ".$primerRegistro.",".$eRegistrosPagina;
		$rsplaza=mysql_db_query($base,$select);
		
		if(mysql_num_rows($rsplaza)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="7">'.mysql_num_rows($rsplaza).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th></th><th>Nombre</th><th>RFC</th><!--<th>Contacto</th>--><th>Telefono</th><th>Direccion</th></tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($Plaza=mysql_fetch_array($rsplaza)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($_SESSION['CveUsuario']==1){
			echo'<a href="#" onClick="atcr(\'\',\'\',\'1\','.$Plaza['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Plaza['nombre'].'"></a>';}
				echo'</td>';
				echo '<td>'.htmlentities($Plaza['nombre']).'</td>';
				echo '<td align="center">'.htmlentities($Plaza['rfc']).'</td>';
//				echo '<td>'.htmlentities($Plaza['contacto']).'</td>';
				echo '<td align="center">'.htmlentities($Plaza['telefono']).'</td>';
				echo '<td>'.htmlentities($Plaza['direccion']).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="7" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
if($_POST["ajax"]==2)
   {
	
	if($_POST['cod']>0){
     $rs="select * from provedores where cve='".$_POST['cod']."' and rfc='".$_POST['rfc']."'";
	 $rsrfc=mysql_db_query($base,$rs);
	 $row=mysql_fetch_array($rsrfc);
	if($row['rfc']==$_POST['rfc']){
		 echo "no";
	 }else{
		 $rs="select * from provedores where rfc='".$_POST['rfc']."'";
			$rsrfc=mysql_db_query($base,$rs);
       if(mysql_num_rows($rsrfc)>0)
	    {
			
           echo "si";
		   
		}else
		   {
              echo "no";
		   }
	 }
	 exit();
	}else{
			$rs="select * from provedores where rfc='".$_POST['rfc']."'";
			$rsrfc=mysql_db_query($base,$rs);
       if(mysql_num_rows($rsrfc)>0)
	    {
			
           echo "si";
		   
		}else
		   {
              echo "no";
		   }
		  exit();
	}


	 
          exit();
   }

top($_SESSION);

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM provedores WHERE cve='".$_POST['reg']."' ";
		$rsplaza=mysql_db_query($base,$select);
		$Plaza=mysql_fetch_array($rsplaza);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
//			if($_SESSION[$archivo[(count($archivo)-1)]]>1)
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="validar('.$Plaza['cve'].');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="atcr(\'provedores.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Proveedores</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="50" value="'.$Plaza['nombre'].'"></td></tr>';
		echo '<tr><th>Rfc</th><td><input type="text" name="rfc" id="rfc" class="textField" size="50" value="'.$Plaza['rfc'].'"></td></tr>';
		echo '<tr><th>Telefono</th><td><input type="text" name="telefono" id="telefono" class="textField" size="50" value="'.$Plaza['telefono'].'"></td></tr>';
		echo '<tr style="display:none"><th>Contacto</th><td><input type="text" name="contacto" id="contacto" class="textField" size="50" value="'.$Plaza['contacto'].'"></td></tr>';
		echo '<tr><th>Direccion</th><td><textarea name="direccion" id="direccion" rows="5" cols="50" >'.$Plaza['direccion'].'</textarea></td></tr>';
		
		echo '</table>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td><td>&nbsp;</td><td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'provedores.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
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
			objeto.open("POST","provedores.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	function validar(reg)
	   {
       if(document.getElementById("rfc").value==""  )
	   {
               alert("Necesita introducir rfc");
       }
       else{
               objeto=crearObjeto();
               if (objeto.readyState != 0) 
			   {
                       alert("Error: El Navegador no soporta AJAX");
               } else 
			   {
                       objeto.open("POST","provedores.php",true);
                       objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
                       objeto.send("ajax=2&cod="+reg+"&rfc="+document.getElementById("rfc").value+"");
                       objeto.onreadystatechange = function()
					   {
                               if (objeto.readyState==4)
							   {
                                       if(objeto.responseText=="si")
									   {
                                               alert("El RFC ya existe");									   
                                       }
                                       else{
                                               atcr("provedores.php","",2,reg);
                                       }
                               }
                       }
               }
       }
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

