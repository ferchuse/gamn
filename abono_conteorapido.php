<?php 
	
	include ("main.php"); 
	$rsUnidad=mysql_db_query($base,"SELECT * FROM parque where estatus= 1 order by no_eco");
	while($Unidad=mysql_fetch_array($rsUnidad)){
		$array_unidad[$Unidad['cve']]=$Unidad['no_eco'];
		$array_unidadd[$Unidad['no_eco']]=$Unidad['cve'];
		$array_tipo_unidad[$Unidad['cve']]=$array_tipo_vehiculo[$Unidad['tipo_vehiculo']];
		$array_mandarmail_unidad[$Unidad['cve']]=$Unidad['mandarmail'];
		$array_email_unidad[$Unidad['cve']]=$Unidad['email'];
	}
	$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
	while($Usuario=mysql_fetch_array($rsUsuario)){
		$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
		$array_nomusuario[$Usuario['cve']]=$Usuario['nombre'];
	}
	$array_estatus=array('Activo','Inactivo');
	/*** CONSULTA AJAX  **************************************************/
	
	
	
	if($_POST['ajax']==1) {
		
		//Listado de tecnicos y administradores
		$select= " SELECT * FROM abono_conteorapido WHERE 1 ";
		if ($_POST['nombre']!="") { $select.=" AND nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['fecha_ini']!=""){$select.=" AND fecha >= '".$_POST['fecha_ini']."'";}
		if($_POST['fecha_fin']!="") { $select.=" AND fecha <= '".$_POST['fecha_fin']."'"; }
		if(trim($_POST['no_eco'])!="")$select.=" AND eco='".strtoupper($array_unidadd[$_POST['no_eco']])."'";
		$res=mysql_db_query($base,$select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY cve desc";
		$res=mysql_db_query($base,$select);
		//		echo $select;
		$nivelUsuario = nivelUsuario();
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="7">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Economico</th><th>Monto</th><th>Observaciones</th><th>Usuario</th></tr>';
			$tot=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo'<td>';
				if($row['estatus']!='C'){
					echo '<a href="#" onClick="atcr(\'abono_conteorapido.php\',\'\',\'201\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$cve['folio'].'"></a>';
					if($nivelUsuario >2){
						echo '&nbsp;<a href="#" onClick="if(cancelarRegistro('.$row['cve'].', \'abono_conteorapido.php\', 3)) buscarRegistros();/*if(confirm(\'Esta seguro de cancelar?\')){ obs=prompt(\'Motivo:\'); atcr(\'abono_conteorapido.php?obs=\'+obs,\'\',3,\''.$row['cve'].'\');}*/"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
					}
				}
				else{
					$row['monto']=0;
					
					echo 'CANCELADO<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
				}
				echo '<td align="center">'.$row['cve'].'</td>';
				echo '</td><td align="center">'.$row['fecha'].'</br>'.$row['hora'].'</td>';
				echo '<td align="center">'.$array_unidad[$row['eco']].'</td>';
				echo '<td align="right">'.number_format($row['monto'],2).'</td>';
				echo '<td align="left">'.$row['obs'].'</td>';
				echo '<td align="center">'.$array_usuario[$row['usuario']].'</td>';
				echo '</tr>';
				$tot+=$row['monto'];
			}
			echo '	
			<tr>
			<td colspan="3" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
			<td colspan="" bgcolor="#E9F2F8" align="right">Total</td>
			<td colspan="" bgcolor="#E9F2F8" align="right">'.number_format($tot,2).'</td>
			<td colspan="2" bgcolor="#E9F2F8" ></td>
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
	if($_POST['ajax']==3){
		//	if(nivelUsuario() > 2){
		if($_POST['clavecancelacion'] == $clavecancelacion){
			mysql_query("UPDATE abono_conteorapido SET estatus='C',obscan='".$_POST['obs']."',usucan='".$_POST['usuario']."',fechacan='".fechaLocal()."', horacan='".horaLocal()."' WHERE cve='".$_POST['id']."'") or die(mysql_error());
			$_POST['cmd']=0;
		}
		else{
			echo '1';
		}
		exit();
		//	}
	}
	
	top($_SESSION);
	
	if($_POST['cmd']==201){
		$res=mysql_db_query($base,"SELECT * FROM abono_conteorapido WHERE cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		$varimp=chr(27);//."@";
		$varimp.="|Folio: ".$row['cve']."|";
		$varimp.="Usuario: ".$array_usuario[$row['usuario']].'|';
		$varimp.=$row['fecha']." ".$row['hora']."||";
		$varimp.="Unidad: ".$array_unidad[$row['eco']]."|";
		$varimp.="Abono: $ ".number_format($row['monto'],2)."|";
		$varimp.="Observaciones: ".$row['obs']."|";
		$varimp.='|';
		$varimp.=chr(29)."h".chr(80).chr(29)."H".chr(2).chr(29)."k".chr(2)."1".sprintf("%011s",(intval($row['cve'])))." |";
		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$varimp.'&logo=GAMN" width=200 height=200></iframe>';
		
		
		$_POST['cmd']=0;
		
	}
	/*** ACTUALIZAR REGISTRO  **************************************************/
	
	if ($_POST['cmd']==2) {
		if($_POST['reg']>0) {
			/*	$res = mysql_db_query($base,"SELECT * FROM abono_conteorapido WHERE cve='".$_POST['reg']."'");
				$row = mysql_fetch_array($res);
				$cveusu=$_POST['reg'];
				if($row['nombre']!=$_POST['nombre']){
				mysql_db_query($base,"INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
				dato='Nombre',nuevo='".$_POST['nombre']."',anterior='".$row['nombre']."',arreglo='',usuario='".$_POST['cveusuario']."'");
				}
				if($row['costo']!=floatval($_POST['costo'])){
				mysql_db_query($base,"INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
				dato='Costo',nuevo='".$_POST['costo']."',anterior='".$row['costo']."',arreglo='',usuario='".$_POST['cveusuario']."'");
				}
				if($row['estatus']!=intval($_POST['estatus'])){
				mysql_db_query($base,"INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
				dato='Estatus',nuevo='".$_POST['estatus']."',anterior='".$row['estatus']."',arreglo='array_estatus',usuario='".$_POST['cveusuario']."'");
				}
				//Actualizar el abono_conteorapido
				$update = " UPDATE abono_conteorapido 
				SET nombre='".$_POST['nombre']."',costo='".$_POST['costo']."',estatus='".$_POST['estatus']."'
				WHERE cve='".$_POST['reg']."' " ;
				$ejecutar = mysql_db_query($base,$update);			
			$id=$_POST['reg'];*/
			} else {
			//Insertar el Registro
			if($_POST['eco']>0){
				$insert = " INSERT abono_conteorapido 
				SET fecha='".fechaLocal()."',hora='".horaLocal()."',estatus='A',eco='".$_POST['eco']."',monto='".$_POST['monto']."',obs='".$_POST['obs']."',
				usuario='".$_POST['cveusuario']."'";
				$ejecutar = mysql_db_query($base,$insert);
				$id = mysql_insert_id();
				$res=mysql_db_query($base,"SELECT * FROM abono_conteorapido WHERE cve='".$id."'");
				$row=mysql_fetch_array($res);
				$varimp=chr(27);//."@";
				$varimp.="|Folio: ".$id."|";
				$varimp.="Usuario: ".$array_usuario[$row['usuario']].'|';
				$varimp.=$row['fecha']." ".$row['hora']."||";
				$varimp.="Unidad: ".$array_unidad[$row['eco']]."|";
				$varimp.="Abono: $ ".number_format($row['monto'],2)."|";
				$varimp.="Observaciones: ".$row['obs']."|";
				$varimp.='|';
				$varimp.=chr(29)."h".chr(80).chr(29)."H".chr(2).chr(29)."k".chr(2)."1".sprintf("%011s",(intval($row['cve'])))." |";
				$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$varimp.'&copia=1&logo=GAMN" width=200 height=200></iframe>';
			}
		}
		
		
		
		
		$_POST['cmd']=0;
		
	}
	
	/*** EDICION  **************************************************/
	
	if ($_POST['cmd']==1) {
		$select=" SELECT * FROM abono_conteorapido WHERE cve='".$_POST['reg']."' ";
		$res=mysql_db_query($base,$select);
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
		<tr>';
		if(nivelUsuario()>1)
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'abono_conteorapido.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'abono_conteorapido.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Abono Conteo Rapido</td></tr>';
		echo '</table>';
		
		//Formulario 
		//echo '<table width="100%"><tr><td>';
		echo '<table>';
		echo '<tr><td>Economico</td><td><select name="eco" id="eco" class="textField" ><option value="0">---Seleccione---</option>';
		foreach($array_unidad as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Monto</td><td><input type="text" name="monto" id="monto" class="textField" size="15" value="'.$row['monto'].'"></td></tr>';
		echo '<tr><td>Observaciones</td><td><textarea name="obs" id="obs" rows="5" cols="25"></textarea></td></tr>';
		echo '</table>';
		
	}
	
	/*** PAGINA PRINCIPAL **************************************************/
	
	if ($_POST['cmd']<1) {
		if($impresion != ""){
			echo '<div style="visibility:hidden;position:absolute;">'.$impresion.'</div>';
		}
		//Busqueda
		echo '<table>';
		echo '<tr>
		<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
		<td><a href="#" onClick="atcr(\'abono_conteorapido.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
		</tr>';
		echo '</table>';
		
		echo '<table>';
		if($_POST['fecha_ini'] == ''){
			
			$fecha_inicial = date("Y-m-d");
		}
		else{
			$fecha_inicial = $_POST['fecha_ini'];
		}
		echo '<tr ><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="'.$fecha_inicial.'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr ><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="'.$_POST['fecha_fin'].'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>No. Economico</td><td><input type="text" size="5" class="textField" name="no_eco" id="no_eco"></td></tr>';
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
		objeto.open("POST","abono_conteorapido.php",true);
		objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&no_eco="+document.getElementById("no_eco").value+"&cvemenu="+document.getElementById("cvemenu").value+"&cveusuario="+document.getElementById("cveusuario").value);
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
		</Script>
		';
	}
	
	bottom();
?>

