<?php 

include ("main.php"); 

$array_usuario=array();
$res=mysql_db_query($base,"SELECT * FROM ".$pre."usuarios ORDER BY usuario");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}
$array_tipo_licencia=array();
$res=mysql_db_query($base,"SELECT * FROM ".$pre."tipo_licencia ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_licencia[$row['cve']]=$row['nombre'];
}


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		//Listado de tecnicos y administradores
		$select= " SELECT * FROM ".$pre."conductores WHERE 1 ";
		if ($_POST['credencial']!="") { $select.=" AND credencial='".$_POST['credencial']."' "; }
		if ($_POST['nombre']!="") { $select.=" AND nombre LIKE '%".$_POST['nombre']."%' "; }
		if ($_POST['estatus']!="all") { $select.=" AND estatus='".$_POST['estatus']."' "; }
		$res=mysql_db_query($base,$select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY credencial";
		$res=mysql_db_query($base,$select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Credencial</th><th>Nombre</th><th>Estatus</th><th>Fecha Estatus</th><th>Licencia</th><th>Vigencia</th></tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Usuario['nombre'].'"></a></td>';
				echo '<td align="center">'.htmlentities($row['credencial']).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($row['nombre'])).'</td>';
				echo '<td align="center">'.htmlentities($array_estatus_parque[$row['estatus']]).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha_sta']).'</td>';
				echo '<td align="center">'.htmlentities($row['no_licencia']).'</td>';
				echo '<td align="center">'.htmlentities($row['vigencia_licencia']).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="8" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
	$res=mysql_db_query($base,"SELECT cve FROM ".$pre."conductores WHERE credencial='".$_POST['credencial']."' AND cve!='".$_POST['cvecon']."'");
	if(mysql_num_rows($res)>0)
		echo "1";
	else 
		echo "0";
	exit();
}

if($_POST['ajax']==3){
	$res=mysql_db_query($base,"SELECT cve FROM ".$pre."conductores WHERE rfc='".$_POST['rfc']."' AND cve!='".$_POST['cvecon']."'");
	if(mysql_num_rows($res)>0)
		echo "1";
	else 
		echo "0";
	exit();
}


if($_POST['ajax']==4) {
//////////////////////////////////////
	$select= " SELECT * FROM ".$pre."conductores_historial_obs WHERE cve_aux='".$_POST['conductor']."'";
		$rscambios=mysql_db_query($base,$select);
		$totalRegistros = mysql_num_rows($rscambios);
		if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		$select .= " ORDER BY cve DESC";
		$rscambios=mysql_db_query($base,$select);
		
		if(mysql_num_rows($rscambios)>0) 
		{
		
			echo '<h3 align="center"> Observaciones </h3>';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8">';
			echo '<th>Fecha</th><th>Observaciones</th><th>Usuario</th>';
			echo '</tr>';
			$i=0;
			while($Cambios=mysql_fetch_array($rscambios)) {
				rowb();
				echo '<td align="center">'.$Cambios['fecha'].'- '.$Cambios['hora'].'</td>';
				echo '<td align="left">'.htmlentities($Cambios['obs']).'</td>';	
				echo '<td align="left">'.$array_usuario[$Cambios['usu']].'';
				$i++;
				echo '</tr>';
			}
			
			echo '	
				<tr>
				<td colspan="9" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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
///////////////////////////7

		//Listado de Historial
		$select= " SELECT * FROM ".$pre."historial WHERE cveaux='".$_POST['conductor']."' and menu='".$_POST['idmenu']."'";
		$rscambios=mysql_db_query($base,$select);
		$totalRegistros = mysql_num_rows($rscambios);
		if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		$select .= " ORDER BY cve DESC  LIMIT ".$primerRegistro.",".$eRegistrosPagina;
		$rscambios=mysql_db_query($base,$select);
		
		if(mysql_num_rows($rscambios)>0) 
		{
		
			echo '<h3 align="center"> Historial de Cambios </h3>';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8">';
			echo '<th>Fecha</th><th>Dato</th><th>Valor Nuevo</th><th>Valor Anterior</th><th>Usuario</th>';
			echo '</tr>';
			$i=0;
			while($Cambios=mysql_fetch_array($rscambios)) {
				rowb();
				echo '<td align="center">'.($Cambios['fecha']).'</td>';
				echo '<td align="left">'.htmlentities($Cambios['dato']).'</td>';
				if($Cambios['arreglo']!=""){
					$arreglo=$Cambios['arreglo'];
					$arreglo=$$arreglo;
					echo '<td align="left">'.$arreglo[$Cambios['nuevo']].'</td>';
					echo '<td align="left">'.$arreglo[$Cambios['anterior']].'</td>';
				}else{
					echo '<td align="left">'.$Cambios['nuevo'].'</td>';
					echo '<td align="left">'.$Cambios['anterior'].'</td>';
				}	
				echo '<td align="left">'.$array_usuario[$Cambios['usuario']].'';
				$i++;
				echo '</tr>';
			}
			
			echo '	
				<tr>
				<td colspan="9" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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
		$cambios="";
		$res=mysql_db_query($base,"SELECT * FROM ".$pre."conductores WHERE cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		if($row['credencial']!=$_POST['credencial']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Credencial',nuevo='".$_POST['credencial']."',anterior='".$row['credencial']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['t_licencia']!=$_POST['t_licencia']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Tipo Licencia',nuevo='".$_POST['t_licencia']."',anterior='".$row['t_licencia']."',arreglo='array_tipo_licencia',usuario='".$_POST['cveusuario']."'");
		}
		if($row['estatus']!=$_POST['estatus']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Estatus',nuevo='".$_POST['estatus']."',anterior='".$row['estatus']."',arreglo='array_estatus_parque',usuario='".$_POST['cveusuario']."'");
			$_POST['fecha_sta'] = fechaLocal();
		}
		if($row['nombre']!=$_POST['nombre']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Nombre',nuevo='".$_POST['nombre']."',anterior='".$row['nombre']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['no_licencia']!=$_POST['no_licencia']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='No Licencia',nuevo='".$_POST['no_licencia']."',anterior='".$row['no_licencia']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['rfc']!=$_POST['rfc']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='RFC',nuevo='".$_POST['rfc']."',anterior='".$row['rfc']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($_POST['vigencia_licencia'] == "") $_POST['vigencia_licencia'] = "0000-00-00";
		if($row['vigencia_licencia']!=$_POST['vigencia_licencia']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Vigencia Licencia',nuevo='".$_POST['vigencia_licencia']."',anterior='".$row['vigencia_licencia']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
			//Actualizar el Registro
			$update = " UPDATE ".$pre."conductores 
						SET 
						  credencial='".$_POST['credencial']."',
						  estatus='".$_POST['estatus']."',
						  nombre='".$_POST['nombre']."',
						  rfc='".$_POST['rfc']."',
						  no_licencia='".$_POST['no_licencia']."',
						  t_licencia='".$_POST['t_licencia']."',
						  vigencia_licencia='".$_POST['vigencia_licencia']."',
						  fecha_sta='".$_POST['fecha_sta']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_db_query($base,$update);
			$id=$_POST['reg'];
	} else {
			//Insertar el Registro
			$insert = " INSERT ".$pre."conductores 
						SET 
						  credencial='".$_POST['credencial']."',
						  estatus='1',
						  nombre='".$_POST['nombre']."',
						  t_licencia='".$_POST['t_licencia']."',
						  rfc='".$_POST['rfc']."',
						  no_licencia='".$_POST['no_licencia']."',
						  vigencia_licencia='".$_POST['vigencia_licencia']."',
						  fecha_ini='".fechaLocal()."',
						  fecha_sta='".fechaLocal()."'
						";
			$ejecutar = mysql_db_query($base,$insert) or die(mysql_error());
			$id=mysql_insert_id();
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$id."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Estatus',nuevo='1',anterior='0',arreglo='array_estatus_parque',usuario='".$_POST['cveusuario']."'");
	}
	
	mysql_db_query($base,"INSERT ".$pre."conductores_historial_obs SET cve_aux='".$id."',fecha='".fechaLocal()."',hora='".horaLocal()."',
			obs='".$_POST['obs']."',usu='".$_POST['cveusuario']."'") or die(mysql_error());
	
	if($_POST['borrar_foto']=="S")
		unlink("fotos_condu/foto".$id.".jpg");
	if(is_uploaded_file ($_FILES['foto']['tmp_name'])){
		/*if(file_exists("fotos/foto".$_POST['reg'].".jpg")){
			unlink("fotos/foto".$id.".jpg");
		}*/
		$arch = $_FILES['foto']['tmp_name'];
		copy($arch,"fotos_condu/foto".$id.".jpg");
		chmod("fotos_condu/foto".$id.".jpg", 0777);
	}

	if($_POST['borrar_foto1']=="S")
		unlink("fotos_lice/foto".$id.".jpg");
	if(is_uploaded_file ($_FILES['foto1']['tmp_name'])){
		/*if(file_exists("fotos/foto".$_POST['reg'].".jpg")){
			unlink("fotos/foto".$id.".jpg");
		}*/
		$arch = $_FILES['foto1']['tmp_name'];
		copy($arch,"fotos_lice/foto".$id.".jpg");
		chmod("fotos_lice/foto".$id.".jpg", 0777);
	}


	$data = array(
		'function' => 'actualizar_operadores',
        'parametros' => array(
        	'empresa' => $empresagcompufax,
        	'clave' => $_POST['credencial'],
        	'cve' => $id,
        	'subempresa' => 0,
        	'estatus' => ($_POST['estatus'] > 0) ? $_POST['estatus'] : 1
        )
     );
 
	
	$options = array('http' => array(
		'method'  => 'POST',
		'content' => http_build_query($data)
	));
	$context  = stream_context_create($options);


	$page = file_get_contents($urlgcompufax, false, $context);
	$_POST['cmd']=0;
	
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM ".$pre."conductores WHERE cve='".$_POST['reg']."' ";
		$res=mysql_db_query($base,$select);
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();validar();"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '
			<td><a href="#" onClick="$(\'#panel\').show();atcr(\'conductores.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Conductores</td></tr>';
		echo '</table>';

		//Formulario 
		echo '<table><tr><td><table>';
		if($_POST['reg']==0 || nivelUsuario()>2)
			echo '<tr><th align="left">Credencial</th><td><input type="text" name="credencial" id="credencial" value="'.$row['credencial'].'" size="10" class="textField"></td></tr>';
		else
			echo '<tr><th align="left">Credencial</th><td><input type="text" name="credencial" id="credencial" value="'.$row['credencial'].'" size="10" class="readOnly" readOnly></td></tr>';
		echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" value="'.$row['nombre'].'" size="50" class="textField"></td></tr>';
		echo '<tr><th align="left">RFC</th><td><input type="text" name="rfc" id="rfc" value="'.$row['rfc'].'" size="20" class="textField"></td></tr>';
		echo '<tr><th align="left">Tipo Licencia</th><td><select name="t_licencia" id="t_licencia"><option value="">--Seleccione--</option>';
			foreach($array_tipo_licencia as $k=>$v){
				echo '<option value="'.$k.'"';
				if($k==$row['t_licencia']) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select></td></tr>';
		if($_POST['reg']>0){
			echo '<tr><th align="left">Fecha Ingreso</th><td><input type="text" name="fecha_ini" id="fecha_ini" size="15" class="readOnly" value="'.$row['fecha_ini'].'" readOnly></td></tr>';
			echo '<tr><th align="left">Fecha Estatus</th><td><input type="text" name="fecha_sta" id="fecha_sta" size="15" class="readOnly" value="'.$row['fecha_sta'].'" readOnly></td></tr>';
			echo '<tr><th align="left">Estatus</th><td><select name="estatus" id="estatus">';
			foreach($array_estatus_parque as $k=>$v){
				echo '<option value="'.$k.'"';
				if($k==$row['estatus']) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		echo '<tr><th align="left">No Licencia</th><td><input type="text" name="no_licencia" id="no_licencia" value="'.$row['no_licencia'].'" size="15" class="textField"></td></tr>';
		if($row['vigencia_licencia']=="0000-00-00") $row['vigencia_licencia']="";
		echo '<tr><th align="left">Vigencia Licencia</th><td><input type="text" name="vigencia_licencia" id="vigencia_licencia" value="'.$row['vigencia_licencia'].'" size="15" class="readOnly" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].vigencia_licencia,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Observaciones</th><td><textarea name="obs" id="obs" rows="10" cols="40"></textarea></td></tr>';
		echo '</table></td><td>';
		echo '<table align="right"><tr><td colspan="2" align="center"><img width="200" height="250" src="fotos_condu/foto'.$_POST['reg'].'.jpg?'.date('h:i:s').'" border="1"></td></tr>';
		echo '<tr><th>Nueva Foto Conductor</th><td><input type="file" name="foto" id="foto"></td></tr>';
		echo '<tr><th>Borrar Foto</th><td><input type="checkbox" name="borrar_foto" id="borrar_foto" value="S"></td></tr></table>';
		echo'</td></tr>
		<tr><td></td><td>';
		echo '<table align="right"><tr><td colspan="2" align="center"><img width="200" height="250" src="fotos_lice/foto'.$_POST['reg'].'.jpg?'.date('h:i:s').'" border="1"></td></tr>';
		echo '<tr><th>Nueva Foto Licencia</th><td><input type="file" name="foto1" id="foto1"></td></tr>';
		echo '<tr><th>Borrar Foto</th><td><input type="checkbox" name="borrar_foto1" id="borrar_foto1" value="S"></td></tr></table></td></tr></table>';

		echo '<BR>';
		echo '<div id="Cambios">';
		echo '</div>';
		
		echo '<script>
				function cambiosConductores()
				{
					document.getElementById("Cambios").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","conductores.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=4&conductor='.$_POST['reg'].'&idmenu='.$_POST['cvemenu'].'&numeroPagina="+document.getElementById("numeroPagina").value);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4)
								{document.getElementById("Cambios").innerHTML = objeto.responseText;}
						}
					}
					document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
				}
				
				
		
				function validar(){
					if(document.forma.credencial.value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar la credencial");
					}
					else if(document.forma.nombre.value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el nombre");
					}
					else if(document.forma.rfc.value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el RFC");
					}
					else{
						validarCredencial();
					}
				}
				
				function validarCredencial()
				{
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","conductores.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=2&cvecon='.$row['cve'].'&credencial="+document.getElementById("credencial").value);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4)
							{
								if(objeto.responseText=="1"){
									$("#panel").hide();
									alert("La credencial ya esta registrada");
								}
								else{
									//atcr("conductores.php","",2,\''.$row['cve'].'\');
									validarRfc()
								}
							}
						}
					}
				}
				
				function validarRfc()
				{
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","conductores.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=3&cvecon='.$row['cve'].'&rfc="+document.getElementById("rfc").value);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4)
							{
								if(objeto.responseText=="1"){
									$("#panel").hide();
									alert("El rfc ya esta registrado");
								}
								else{
									atcr("conductores.php","",2,\''.$row['cve'].'\');
								}
							}
						}
					}
				}
				
				
				
				cambiosConductores();
				';
				echo '
			  </script>';
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'conductores.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><td>Credencial</td><td><input type="text" name="credencial" id="credencial" size="10" class="textField"></td></tr>';	
		echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" size="50" class="textField"></td></tr>';	
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="all">Todos</option>';
		foreach($array_estatus_parque as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==1) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
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
			objeto.open("POST","conductores.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nombre="+document.getElementById("nombre").value+"&estatus="+document.getElementById("estatus").value+"&credencial="+document.getElementById("credencial").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	
	window.onload = function () {
	    buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}
	</Script>
';

?>

