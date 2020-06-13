<?php 

include ("main.php"); 

$array_usuario=array();
$res=mysql_db_query($base,"SELECT * FROM ".$pre."usuarios ORDER BY usuario");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$array_propietario=array();
$res=mysql_db_query($base,"SELECT * FROM ".$pre."propietarios ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_propietario[$row['cve']]=$row['nombre'];
}

$array_derrotero=array();
$res=mysql_db_query($base,"SELECT * FROM ".$pre."derroteros ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_derrotero[$row['cve']]=$row['nombre'];
}

$array_uni7=array();
$res=mysql_db_query("enero_aaz","SELECT * FROM parque WHERE tipo_vehiculo=3 ORDER BY no_eco");
while($row=mysql_fetch_array($res)){
	$array_uni7[$row['cve']]=$row['no_eco'];
}

$array_tipo_unidad=array();
$res=mysql_db_query($base,"SELECT * FROM tipos_unidad ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_unidad[$row['cve']]=$row['nombre'];
}

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		//Listado de tecnicos y administradores
		$select= " SELECT * FROM ".$pre."parque WHERE 1 ";
		if ($_POST['no_eco']!="") { $select.=" AND no_eco='".$_POST['no_eco']."' "; }
		if ($_POST['serie']!="") { $select.=" AND serie='".$_POST['serie']."' "; }
		if ($_POST['propietario']!="all") { $select.=" AND propietario='".$_POST['propietario']."' "; }
		if ($_POST['estatus']!="all") { $select.=" AND estatus='".$_POST['estatus']."' "; }
		if ($_POST['derrotero']!="all") { $select.=" AND derrotero='".$_POST['derrotero']."' "; }
		if ($_POST['tipo']!="all") { $select.=" AND tipo='".$_POST['tipo']."' "; }
		$res=mysql_db_query($base,$select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY no_eco";
		$res=mysql_db_query($base,$select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="12">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>No Eco</th><th>Propietario</th><th>Estatus</th><th>Fecha Estatus</th><th>Derrotero</th><th>Serie</th><th>Tipo</th><th>Modelo</th><th>Unidad 7Enero</th><th>IMEI</th><th>Vencimiento Poliza</th></tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Usuario['nombre'].'"></a></td>';
				echo '<td align="center">'.htmlentities($row['no_eco']).'</td>';
				echo '<td>'.htmlentities(utf8_encode($array_propietario[$row['propietario']])).'</td>';
				echo '<td align="center">'.htmlentities($array_estatus_parque[$row['estatus']]).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha_sta']).'</td>';
				echo '<td align="left">'.htmlentities($array_derrotero[$row['derrotero']]).'</td>';
				echo '<td align="center">'.htmlentities($row['serie']).'</td>';
				echo '<td align="left">'.htmlentities($array_tipo_unidad[$row['tipo']]).'</td>';
				echo '<td align="center">'.htmlentities($row['modelo']).'</td>';
				echo '<td align="center">'.htmlentities($array_uni7[$row['cve_ori']]).'</td>';
				echo '<td align="center">'.htmlentities($row['imei']).'</td>';
				if($row['vigencia_poliza'] > '0000-00-00' && $row['vigencia_poliza'] < date('Y-m-d'))
					echo '<td align="center"><font color="RED">'.htmlentities($row['vigencia_poliza']).'</font></td>';
				else
					echo '<td align="center">'.htmlentities($row['vigencia_poliza']).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="12" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
	$res=mysql_db_query($base,"SELECT cve FROM ".$pre."parque WHERE no_eco='".$_POST['no_eco']."' AND cve!='".$_POST['cveuni']."'");
	if(mysql_num_rows($res)>0)
		echo "1";
	else 
		echo "0";
	exit();
}

if($_POST['ajax']==3){
	$res=mysql_db_query($base,"SELECT cve FROM ".$pre."parque WHERE serie='".$_POST['serie']."' AND cve!='".$_POST['cveuni']."'");
	if(mysql_num_rows($res)>0)
		echo "1";
	else 
		echo "0";
	exit();
}


if($_POST['ajax']==4) {
		//Listado de Historial
		$select= " SELECT * FROM ".$pre."historial WHERE cveaux='".$_POST['unidad']."' and menu='".$_POST['idmenu']."'";
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
		$res=mysql_db_query($base,"SELECT * FROM ".$pre."parque WHERE cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		if($row['no_eco']!=$_POST['no_eco']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='No Eco',nuevo='".$_POST['no_eco']."',anterior='".$row['no_eco']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['estatus']!=$_POST['estatus']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Estatus',nuevo='".$_POST['estatus']."',anterior='".$row['estatus']."',arreglo='array_estatus_parque',usuario='".$_POST['cveusuario']."'");
			$_POST['fecha_sta'] = fechaLocal();
		}
		if($row['propietario']!=$_POST['propietario']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Propietario',nuevo='".$_POST['propietario']."',anterior='".$row['propietario']."',arreglo='array_propietario',usuario='".$_POST['cveusuario']."'");
		}
		if($row['serie']!=$_POST['serie']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Serie',nuevo='".$_POST['serie']."',anterior='".$row['serie']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['derrotero']!=$_POST['derrotero']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Derrotero',nuevo='".$_POST['derrotero']."',anterior='".$row['derrotero']."',arreglo='array_derrotero',usuario='".$_POST['cveusuario']."'");
		}
		if($row['modelo']!=$_POST['modelo']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Modelo',nuevo='".$_POST['modelo']."',anterior='".$row['modelo']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['cobertura']!=$_POST['cobertura']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Cobertura',nuevo='".$_POST['cobertura']."',anterior='".$row['cobertura']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['vigencia_poliza']=='0000-00-00') $row['vigencia_poliza'] = '';
		if($row['vigencia_poliza']!=$_POST['vigencia_poliza']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Vigencia Poliza',nuevo='".$_POST['vigencia_poliza']."',anterior='".$row['vigencia_poliza']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['aseguradora']!=$_POST['aseguradora']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Aseguradora',nuevo='".$_POST['aseguradora']."',anterior='".$row['aseguradora']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['placa']!=$_POST['placa']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Placa',nuevo='".$_POST['placa']."',anterior='".$row['placa']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['imei']!=$_POST['imei']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='IMEI',nuevo='".$_POST['imei']."',anterior='".$row['imei']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['tipo']!=$_POST['tipo']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Tipo',nuevo='".$_POST['tipo']."',anterior='".$row['tipo']."',arreglo='array_derrotero',usuario='".$_POST['cveusuario']."'");
		}
		if($row['cve_ori']!=$_POST['cve_ori']){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Unidad 7Enero',nuevo='".$_POST['cve_ori']."',anterior='".$row['cve_ori']."',arreglo='array_uni7',usuario='".$_POST['cveusuario']."'");
		}
		if(intval($row['recaudacion_local'])!=intval($_POST['recaudacion_local'])){
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Recaudacion Local',nuevo='".intval($_POST['recaudacion_local'])."',anterior='".intval($row['recaudacion_local'])."',arreglo='array_nosi',usuario='".$_POST['cveusuario']."'");
		}
			//Actualizar el Registro
			$update = " UPDATE ".$pre."parque 
						SET 
						  no_eco='".$_POST['no_eco']."',
						  estatus='".$_POST['estatus']."',
						  propietario='".$_POST['propietario']."',
						  serie='".$_POST['serie']."',
						  derrotero='".$_POST['derrotero']."',
						  tipo='".$_POST['tipo']."',
						  modelo='".$_POST['modelo']."',
						  cobertura='".$_POST['cobertura']."',
						  vigencia_poliza='".$_POST['vigencia_poliza']."',
						  aseguradora='".$_POST['aseguradora']."',
						  placa='".$_POST['placa']."',
						  fecha_sta='".$_POST['fecha_sta']."',
						  cve_ori='".$_POST['cve_ori']."',
						  imei='".$_POST['imei']."',
						  fecha_vencimiento_poliza='".$_POST['fecha_vencimiento_poliza']."',
						  recaudacion_local='".$_POST['recaudacion_local']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_db_query($base,$update);
			$id=$_POST['reg'];
	} else {
			//Insertar el Registro
			$insert = " INSERT ".$pre."parque 
						SET 
						  no_eco='".$_POST['no_eco']."',
						  estatus='1',
						  propietario='".$_POST['propietario']."',
						  serie='".$_POST['serie']."',
						  derrotero='".$_POST['derrotero']."',
						  tipo='".$_POST['tipo']."',
						  cobertura='".$_POST['cobertura']."',
						  vigencia_poliza='".$_POST['vigencia_poliza']."',
						  aseguradora='".$_POST['aseguradora']."',
						  placa='".$_POST['placa']."',
						  modelo='".$_POST['modelo']."',
						  fecha_ini='".fechaLocal()."',
						  fecha_sta='".fechaLocal()."',
						  cve_ori='".$_POST['cve_ori']."',
						  imei='".$_POST['imei']."',
						  fecha_vencimiento_poliza='".$_POST['fecha_vencimiento_poliza']."',
						  recaudacion_local='".$_POST['recaudacion_local']."'
						";
			$ejecutar = mysql_db_query($base,$insert) or die(mysql_error());
			$id=mysql_insert_id();
			mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$id."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Estatus',nuevo='1',anterior='0',arreglo='array_estatus_parque',usuario='".$_POST['cveusuario']."'");
	}
		if($_POST['borrar_foto']=="S")
		unlink("fotos_uni/foto".$id.".jpg");
	if(is_uploaded_file ($_FILES['foto']['tmp_name'])){
		/*if(file_exists("fotos/foto".$_POST['reg'].".jpg")){
			unlink("fotos/foto".$id.".jpg");
		}*/
		$arch = $_FILES['foto']['tmp_name'];
		copy($arch,"fotos_uni/foto".$id.".jpg");
		chmod("fotos_uni/foto".$id.".jpg", 0777);
	}

	$data = array(
		'function' => 'actualizar_unidades',
        'parametros' => array(
        	'empresa' => $empresagcompufax,
        	'no_eco' => $_POST['no_eco'],
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
		
		$select=" SELECT * FROM ".$pre."parque WHERE cve='".$_POST['reg']."' ";
		$res=mysql_db_query($base,$select);
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();validar();"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '
			<td><a href="#" onClick="$(\'#panel\').show();atcr(\'parque.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Unidades</td></tr>';
		echo '</table>';

		//Formulario 
		echo '<table><tr><td><table>';
		if($_POST['reg']==0 || nivelUsuario()>2)
			echo '<tr><th align="left">No Eco</th><td><input type="text" name="no_eco" id="no_eco" value="'.$row['no_eco'].'" size="5" class="textField"></td></tr>';
		else
			echo '<tr><th align="left">No Eco</th><td><input type="text" name="no_eco" id="no_eco" value="'.$row['no_eco'].'" size="5" class="readOnly" readOnly></td></tr>';
		echo '<tr><th align="left">Propietario</th><td><select name="propietario" id="propietario"><option value="0">Seleccione</option>';
		foreach($array_propietario as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['propietario']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Recaudacion Local</th><td><input type="hidden" name="recaudacion_local" id="recaudacion_local" value="1"';
		if($row['recaudacion_local']==1) echo ' checked';
		echo '></td></tr>';
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
		echo '<tr><th align="left">Derrotero</th><td><select name="derrotero" id="derrotero"><option value="0">Seleccione</option>';
		foreach($array_derrotero as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['derrotero']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Tipo</th><td><select name="tipo" id="tipo"><option value="0">Seleccione</option>';
		foreach($array_tipo_unidad as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['tipo']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Serie</th><td><input type="text" name="serie" id="serie" value="'.$row['serie'].'" size="30" class="textField"></td></tr>';
		echo '<tr><th align="left">Modelo</th><td><input type="text" name="modelo" id="modelo" value="'.$row['modelo'].'" size="10" class="textField"></td></tr>';
		echo '<tr><th align="left">IMEI</th><td><input type="text" name="imei" id="imei" value="'.$row['imei'].'" size="20" class="textField"></td></tr>';

		echo '<tr><th align="left">Cobertura de Poliza</th><td><input type="text" name="cobertura" id="cobertura" value="'.$row['cobertura'].'" size="10" class="textField"></td></tr>';
		echo '<tr><th align="left">Vigencia Poliza</th><td><input type="text" name="vigencia_poliza" id="vigencia_poliza" value="'.$row['vigencia_poliza'].'" size="15" class="readOnly" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].vigencia_poliza,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Aseguradora</th><td><input type="text" name="aseguradora" id="aseguradora" value="'.$row['aseguradora'].'" size="" class="textField"></td></tr>';
		echo '<tr><th align="left">Placa</th><td><input type="text" name="placa" id="placa" value="'.$row['placa'].'" size="10" class="textField"></td></tr>';

		
		if($row['cve_ori']>0){
			echo '<tr><th align="left">Unidad 7Enero</th><td><input type="hidden" name="cve_ori" id="cve_ori" value="'.$row['cve_ori'].'">'.$array_uni7[$row['cve_ori']].'</td></tr>';
		}
		else
		{
			echo '<tr><th align="left">Unidad 7Enero</th><td><select name="cve_ori" id="cve_ori"><option value="0">Seleccione</option>';
			$res1 = mysql_db_query("enero_aaz","SELECT * FROM parque WHERE (estatus = 1 AND tipo_vehiculo=3) OR cve='".$row['cve_ori']."' ORDER BY no_eco");
			while($row1 = mysql_fetch_array($res1)){
				echo '<option value="'.$row1['cve'].'"';
				if($row['cve_ori'] == $row1['cve']) echo ' selected>'.$row1['no_eco'].' ('.$array_estatus_parque[$row1['estatus']].')</option>';
				else echo '>'.$row1['no_eco'].'</option>';
			}
			echo '</select></td></tr>';
		}
		echo '</table></td><td>';
		echo '<table align="right"><tr><td colspan="2" align="center"><img width="200" height="250" src="fotos_uni/foto'.$_POST['reg'].'.jpg?'.date('h:i:s').'" border="1"></td></tr>';
		echo '<tr><th>Nueva Foto Poliza</th><td><input type="file" name="foto" id="foto"></td></tr>';
		echo '<tr><th>Borrar Foto</th><td><input type="checkbox" name="borrar_foto" id="borrar_foto" value="S"></td></tr></table></td></tr></table>';
		echo '<BR>';
		echo '<div id="Cambios">';
		echo '</div>';
		
		echo '<script>
				function cambiosParque()
				{
					document.getElementById("Cambios").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","parque.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=4&unidad='.$_POST['reg'].'&idmenu='.$_POST['cvemenu'].'&numeroPagina="+document.getElementById("numeroPagina").value);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4)
								{document.getElementById("Cambios").innerHTML = objeto.responseText;}
						}
					}
					document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
				}
		
				function validar(){
					if(document.forma.propietario.value=="0"){
						$(\'#panel\').hide();
						alert("Necesita seleccionar el propietario");
					}
					else if(document.forma.no_eco.value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el no economico");
					}
					else if(document.forma.serie.value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar la serie");
					}
					else if(document.forma.derrotero.value=="0"){
						$(\'#panel\').hide();
						alert("Necesita seleccionar el derrotero");
					}
					else{
						validarEco();
					}
				}
				
				function validarEco()
				{
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","parque.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=2&cveuni='.$row['cve'].'&no_eco="+document.getElementById("no_eco").value);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4)
							{
								if(objeto.responseText=="1"){
									$("#panel").hide();
									alert("El no eco ya esta registrado");
								}
								else{
									//atcr("parque.php","",2,\''.$row['cve'].'\');
									validarSerie();
								}
							}
						}
					}
				}
				
				function validarSerie()
				{
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","parque.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=3&cveuni='.$row['cve'].'&serie="+document.getElementById("serie").value);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4)
							{
								if(objeto.responseText=="1"){
									$("#panel").hide();
									alert("La serie ya esta registrada");
								}
								else{
									atcr("parque.php","",2,\''.$row['cve'].'\');
								}
							}
						}
					}
				}
				cambiosParque();';
				echo '
			  </script>';
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'parque.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><td>No Eco</td><td><input type="text" name="no_eco" id="no_eco" size="5" class="textField"></td></tr>';	
		echo '<tr><td>Serie</td><td><input type="text" name="serie" id="serie" size="15" class="textField"></td></tr>';	
		echo '<tr><td>Propietario</td><td><select name="propietario" id="propietario"><option value="all">Todas</option>';
		foreach($array_propietario as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="all">Todos</option>';
		foreach($array_estatus_parque as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==1) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Derrotero</td><td><select name="derrotero" id="derrotero"><option value="all">Todos</option>';
		foreach($array_derrotero as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Tipo</td><td><select name="tipo" id="tipo"><option value="all">Todos</option>';
		foreach($array_tipo_unidad as $k=>$v){
			echo '<option value="'.$k.'"';
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
			objeto.open("POST","parque.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&tipo="+document.getElementById("tipo").value+"&derrotero="+document.getElementById("derrotero").value+"&serie="+document.getElementById("serie").value+"&propietario="+document.getElementById("propietario").value+"&estatus="+document.getElementById("estatus").value+"&no_eco="+document.getElementById("no_eco").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

