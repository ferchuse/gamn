<?php
session_start();
include ("main.php");


mysql_select_db('gamn_gps_otra_plataforma');
if($_POST['plazausuario'] == 1)
	$rsMotivo=mysql_query("SELECT * FROM rutas WHERE 1 ORDER BY nombre");
else
	$rsMotivo=mysql_query("SELECT * FROM rutas WHERE plaza IN (0, ".$_POST['plazausuario'].") ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_rutasgps[$Motivo['base']][$Motivo['cvebase']]=$Motivo['nombre'];
}
$array_estatus[0]="ALTA";
$rsMotivo=mysql_query("SELECT * FROM estatus WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_estatus[$Motivo['cve']]=$Motivo['nombre'];
}

$rsMotivo=mysql_query("SELECT * FROM motivo WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_motivos[$Motivo['cve']]=$Motivo['nombre'];
}

$rsMotivo=mysql_query("SELECT * FROM telefonia WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_telefonia[$Motivo['cve']]=$Motivo['nombre'];
}
mysql_select_db('gamn_gps');


if($_POST['cmd']==101){
	header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=Dispositivos.xsl"); 
	mysql_select_db('gamn_gps_otra_plataforma');
	$filtroruta='(';
	foreach($array_rutasgps as $base=>$rutas){
		if($filtroruta!='(') $filtroruta.=" OR ";
		$filtroruta .= "(base = '$base' AND ruta IN (";
		$primero = true;
		foreach($rutas as $k=>$v){
			if(!$primero) $filtroruta.=",";
			$filtroruta.="'".$k."'";
			$primero = false;
		}
		$filtroruta.="))";
	}
	$filtroruta.=")";
	$x=0;
	$listado="";
	foreach($_POST['chksdispositivo'] as $dispositivo){
		if($x=0){
			$lsitado=$dispositivo;
			$x++;
		}else{
			$lisatdo=$lisatdo.",".$dispositivo;
			$x++;
		}
	}
	$select= " SELECT * FROM dispositivos WHERE cve in (".$listado.")";
	//if($_POST['nom']!="") $select .= " AND nombre like '%".$_POST['nom']."%'";
	//if($_POST['ruta']!=""){
	//	$ruta = explode(',',$_POST['ruta']);
	//	$select .= " AND base='".$ruta[0]."' AND ruta= '".$ruta[1]."'";
	//}
	//if($_POST['estatus']!="") $select .= " AND estatus= '".$_POST['estatus']."'";
	//if($_POST['motivo']!="") $select .= " AND motivo= '".$_POST['motivo']."'";
	//if($_POST['telefonia']!="") $select .= " AND telefonia= '".$_POST['telefonia']."'";
	$select .= " ORDER BY nombre";
	$res=mysql_query($select) or die(mysql_error());
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="">
	      <tr bgcolr="#E9F2F8"><th colspan="" style="font-size:26px">Dispositivos</th><tr>
		  <tr><td>&nbsp;</td></tr>';
	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="" id="tabla1" style="font-size:14px">
	      <tr bgcolr="#E9F2F8"><th>ID</th>
		  <th>Nombre</th><th>IMEI</th><th>Ruta</th><th>Concepto</th><th>Paro</th><th>Arranque</th><th>Telefono</th><th>Modelo</th><th>Estatus</th>';

	echo '
		  </tr>';
	while($row=mysql_fetch_array($res)){
//		rowb();

		echo'<tr><td align="center">'.$row['cvebase'].'</td>';
		echo'<td align="center">'.$row['nombre'].'</td>';
		echo'<td align="center">'.$row['uniqueid'].'</td>';
		echo'<td align="center">'.$array_rutasgps[$row['base']][$row['ruta']].'</td>';

		echo'<td align="center">'.$array_motivos[$row['motivo']].'</td>';
		echo'<td align="center">'.$row['paro'].'</td>';
		echo'<td align="center">'.$row['arranque'].'</td>';
		echo'<td align="center">'.$row['telefono'].'</td>';
		echo'<td align="center">'.$row['modelo'].'</td>';
		echo'<td align="center">'.$array_estatus[$row['estatus']].'</td>';

		echo'</tr>';
	}
	echo'<tr bgcolo="#E9F2F8"><td align="left" colspan="13">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table>';

exit();
}

if($_POST['ajax']==1){
	mysql_select_db('gamn_gps_otra_plataforma');
	$filtroruta='(';
	foreach($array_rutasgps as $base=>$rutas){
		if($filtroruta!='(') $filtroruta.=" OR ";
		$filtroruta .= "(base = '$base' AND ruta IN (";
		$primero = true;
		foreach($rutas as $k=>$v){
			if(!$primero) $filtroruta.=",";
			$filtroruta.="'".$k."'";
			$primero = false;
		}
		$filtroruta.="))";
	}
	$filtroruta.=")";
	$select= " SELECT * FROM dispositivos WHERE $filtroruta";
	if($_POST['nom']!="") $select .= " AND nombre like '%".$_POST['nom']."%'";
	if($_POST['ruta']!=""){
		$ruta = explode(',',$_POST['ruta']);
		$select .= " AND base='".$ruta[0]."' AND ruta= '".$ruta[1]."'";
	}
	if($_POST['estatus']!="") $select .= " AND estatus= '".$_POST['estatus']."'";
	if($_POST['motivo']!="") $select .= " AND motivo= '".$_POST['motivo']."'";
	if($_POST['telefonia']!="") $select .= " AND telefonia= '".$_POST['telefonia']."'";
	$select .= " ORDER BY nombre";
	$res=mysql_query($select) or die(mysql_error());
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8"><th>&nbsp;</th><th><input type="checkbox" id="selall" onClick="if(this.checked) $(\'.chks\').attr(\'checked\',\'checked\'); else $(\'.chks\').removeAttr(\'checked\');"></th><th>ID</th>
		  <th>Nombre</th><th>IMEI</th><th>Ruta</th><th>Concepto</th><th>Paro</th><th>Arranque</th><th>Telefono</th><th>Modelo</th><th>Estatus</th>';
		 if($_POST['cveusuario']==1) echo '<th>Borrar</th>';
	echo '
		  </tr>';
	while($row=mysql_fetch_array($res)){
		rowb();
		echo '<td align="center"><a href="#" onClick="atcr(\'dispositivos.php\',\'\',1,'.$row['cve'].')">'.$imgeditar.'</a></td>';
		echo '<td align="center"><input type="checkbox" name="chksdispositivo[]" value="'.$row['cve'].'" class="chks"></td>';
		echo'<td align="center">'.$row['cvebase'].'</td>';
		echo'<td align="center">'.$row['nombre'].'</td>';
		echo'<td align="center">'.$row['uniqueid'].'</td>';
		echo'<td align="center">'.$array_rutasgps[$row['base']][$row['ruta']].'</td>';

		echo'<td align="center">'.$array_motivos[$row['motivo']].'</td>';
		echo'<td align="center">'.$row['paro'].'</td>';
		echo'<td align="center">'.$row['arranque'].'</td>';
		echo'<td align="center">'.$row['telefono'].'</td>';
		echo'<td align="center">'.$row['modelo'].'</td>';
		echo'<td align="center">'.$array_estatus[$row['estatus']].'</td>';

		if($_POST['cveusuario']==1)
			echo'<td align="center"><a href="#" onClick="if(confirm(\'Esta seguro de eliminar el registro?\'))atcr(\'dispositivos.php\',\'\',\'3\','.$row['cve'].')">'.$imgborrar.'</a></td>';
		echo'</tr>';
	}
	echo'<tr bgcolor="#E9F2F8"><td align="left" colspan="13">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table>';
	 mysql_select_db('gamn_gps');
exit();
}

 top($_SESSION);

 	if($_POST['cmd']==12){
 		$dispositivos = '';
 		mysql_select_db('gamn_gps_otra_plataforma');
 		foreach($_POST['chksdispositivo'] as $dispositivo){
 			mysql_query("UPDATE dispositivos SET estatus='".$_POST['estatuscorreo']."',motivo='".$_POST['motivocorreo']."' WHERE cve='".$dispositivo."'");
 			$res = mysql_query("SELECT nombre FROM dispositivo WHERE cve='".$dispositivo."'");
 			$row = mysql_fetch_array($res);
 			$dispositivos.=$row['nombre']."\n";
 		}
 		require_once("phpmailer/class.phpmailer.php");
 		$res1 = mysql_query("SELECT descripcion FROM motivos WHERE motivo = '".$_POST['motivocorreo']."'");
 		$row1 = mysql_fetch_array($res1);
 		$mail = new PHPMailer();
		$mail->Host = "localhost";
		$mail->From = "sutalle@sutaller.net";
		$mail->FromName = "SuTaller.Net";
		$mail->Subject = "Cambio de Estatus y Concepto a Dispositivos";
		$mail->Body = "Actualizacion de estatus y Concepto\n\nEstatus: ".$array_estatus[$_POST['estatuscorreo']]."\nConcepto: ".$array_motivos[$_POST['motivocorreo']]."\nDescripcion: ".$row1['descripcion']."\nObservaciones: ".$_POST['obscorreo']."\nDispositivos:\n".$dispositivos;
		$correos = explode(",",trim($_POST['correos_envio']));
		foreach($correos as $correo)
			$mail->AddAddress(trim($correo));
		$mail->Send();

 	}
   if($_POST['cmd']==3){
   	mysql_select_db('gamn_gps_otra_plataforma');
	$sSQL="delete from dispositivos where cve='".$_POST['reg']."'";
	mysql_query($sSQL);
	mysql_select_db('gamn_gps');
   	$_POST['cmd']=0;
   }

   if($_POST['cmd']==2){
  	mysql_select_db('gamn_gps_otra_plataforma');
	$id=$_POST['reg'];
		$sSQL="update dispositivos
				SET telefono='" . $_POST['telefono'] . "',telefonia='".$_POST['telefonia']."',numero='" . $_POST['numero'] . "',descripcion='" . $_POST['descripcion'] . "',
				grupo='" . $_POST['grupo'] . "',paro='" . $_POST['no_placas'] . "',arranque='" . $_POST['no_verificacion'] . "',modelo='" . $_POST['modelo'] . "',
				tecnologia='" . $_POST['tecnologia'] . "',anio='" . $_POST['anio'] . "',marca='" . $_POST['marca'] . "',no_serie='" . $_POST['no_serie'] . "',
				no_motor='" . $_POST['no_motor'] . "',no_poliza='" . $_POST['no_poliza'] . "',fecha_vencimiento='" . $_POST['fecha_vencimiento'] . "' where cve='".$_POST['reg']."'";
		mysql_query($sSQL)or die (mysql_error());
		

	if(is_uploaded_file ($_FILES['foto']['tmp_name'])){
		/*if(file_exists("fotos/foto".$_POST['reg'].".jpg")){
			unlink("fotos/foto".$id.".jpg");
		}*/
		$arch = $_FILES['foto']['tmp_name'];
		copy($arch,"fotos/foto".$id.".jpg");
		@chmod("fotos/foto".$id.".jpg", 0777);
	}
		$_POST['cmd']=0;
	}
  

  if($_POST['cmd']==1){
	mysql_select_db('gamn_gps_otra_plataforma');
	    $select=" SELECT * FROM dispositivos WHERE cve='".$_POST['reg']."' ";
	    $res=mysql_query($select);
	    $row=mysql_fetch_array($res);
	

    echo'
	    
		<a href="#" onClick="atcr(\'dispositivos.php\',\'\',\'2\',\''.$row['cve'].'\');">'.$imgguardar.' &nbsp;Guardar</a>&nbsp;&nbsp;
		<a href="#" onClick="atcr(\'dispositivos.php\',\'\',\'0\',\'0\');">'.$imgvolver.'&nbsp;Volver </a></br></br>
		<table width="100%" border ="0"><tr><td><table>';
		echo'<tr><td>Nombre:</td><td>' . $row['nombre'] . '</td></tr>';
		echo '<tr><td>Telefono:</td><td><input type="text" class="textField" name="telefono" id="telefono" value="'.$row['telefono'].'"></td></tr>';
		echo '<tr><td>Telefonia:</td><td><select name="telefonia" id="telefonia"><option value="">Seleccione</option>';
		foreach($array_telefonia as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['telefonia'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>No</td><td><input type="text" class="textField" name="numero" id="numero" value="'.$row['numero'].'"></td></tr>';
		echo '<tr><td>Descripcion</td><td><input type="text" class="textField" name="descripcion" id="descripcion" value="'.$row['descripcion'].'"></td></tr>';
		echo '<tr><td>Grupo</td><td><input type="text" class="textField" name="grupo" id="grupo" value="'.$row['grupo'].'"></td></tr>';
		echo '<tr><td>Paro</td><td><input type="text" class="textField" name="no_placas" id="no_placas" value="'.$row['paro'].'"></td></tr>';
		echo '<tr><td>Arranque</td><td><input type="text" class="textField" name="no_verificacion" id="no_verificacion" value="'.$row['arranque'].'"></td></tr>';
		echo '<tr><td>Modelo</td><td><input type="text" class="textField" name="modelo" id="modelo" value="'.$row['modelo'].'"></td></tr>';
		echo '<tr><td>Tecnologia</td><td><input type="text" class="textField" name="tecnologia" id="tecnologia" value="'.$row['tecnologia'].'"></td></tr>';
		echo '<tr><td>'.utf8_decode(Año).'</td><td><input type="text" class="textField" name="anio" id="anio" value="'.$row['anio'].'"></td></tr>';
		echo '<tr><td>Marca</td><td><input type="text" class="textField" name="marca" id="marca" value="'.$row['marca'].'"></td></tr>';
		echo '<tr><td>No de Serie:</td><td><input type="text" class="textField" name="no_serie" id="no_serie" value="'.$row['no_serie'].'"></td></tr>';
		echo '<tr><td>No de Motor</td><td><input type="text" class="textField" name="mo_motor" id="no_motor" value="'.$row['no_motor'].'"></td></tr>';
		echo '<tr><td>No de Poliza de Seguro</td><td><input type="text" class="textField" name="no_poliza" id="no_poliza" value="'.$row['no_poliza'].'"></td></tr>';
		echo '<tr><th align="left">Fecha Vencimiento</th><td><input type="text" name="fecha_vencimiento" id="fecha_vencimiento" class="readOnly" size="15" value="'.$row['fecha_vencimiento'].'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_vencimiento,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		
		
		echo'</table></td><td valign="top">';
		echo '<table align="right">
		<tr><td colspan="2" align="center"><img width="200" height="250" src="fotos/foto'.$_POST['reg'].'.jpg?'.date('h:i:s').'" border="1"></td></tr>';
		echo '<tr><th>Nueva Foto</th><td><input type="file" name="foto" id="foto"></td></tr>
		</table>
		
		</td></tr></table>';
		
	}


 if ($_POST['cmd']<1) {

 	echo '<div id="dialog" style="display:none">
		<table width="100%">
		<tr><th>Correo(s)<br>(separados por comas)</th><td><input type="text" id="correos_envio" class="textField" value="" size="50"></td></tr>
		<tr><th>Estatus</th><td><select id="estatuscorreo"><option value="">Seleccione</option><option value="0">Alta</option>';
		foreach($array_estatus as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
		echo '</select></td></tr>
		<tr><th>Concepto</th><td><select id="motivocorreo"><option value="">Seleccione</option>';
		foreach($array_motivos as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
		echo '</select></td></tr>
		<tr><th>Observaciones</th><td><textarea id="obscorreo" cols="40" rows="3"></textarea></td></tr>
		</table>
		</div>'; 
		echo '<input type="hidden" name="correos_envio" value="">';
		echo '<input type="hidden" name="estatuscorreo" value="">';
		echo '<input type="hidden" name="motivocorreo" value="">';
		echo '<input type="hidden" name="obscorreo" value="">';

		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();">'.$imgbuscar.'</a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
				<td><a href="#" onclick="$(\'#dialog\').dialog(\'open\');">'.$imgnuevo.'</a>&nbsp;&nbsp;Actualizar Estatus y Concepto&nbsp;&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Ruta</td><td><select name="ruta" id="ruta" class="textField"><option value="">Todas</option>';
		foreach ($array_rutasgps as $base=>$rutas) { 
			foreach ($rutas as $k=>$v) { 
	    		echo '<option value="'.$base.','.$k.'">'.$v.'</option>';
	    	}
		}
		echo '</select></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus" class="textField"><option value="">Todos</option>';
		foreach ($array_estatus as $k=>$v) { 
	    	echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Concepto</td><td><select name="motivo" id="motivo" class="textField"><option value="">Todos</option>';
		foreach ($array_motivos as $k=>$v) { 
	    	echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Telefonia</td><td><select name="telefonia" id="telefonia" class="textField"><option value="">Todas</option>';
		foreach ($array_telefonia as $k=>$v) { 
	    	echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>IMEI</td><td><input type="text" class="textField" name="imei" id="imei"></td>';
		echo '<tr><td>Nombre</td><td><input type="text" class="textField" name="nom" id="nom"></td>';
		echo'</tr>';
		echo '</table>
		';
		echo '<br>';
		//echo 'El numeo de credencial parpadeando significa que no tiene asignacion vigente';
		//Listado
		echo '<div id="Resultados">';

		echo '</div>';



/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">
	
	function buscarRegistros(){
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else{
			objeto.open("POST","dispositivos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&motivo="+document.getElementById("motivo").value+"&telefonia="+document.getElementById("telefonia").value+"&estatus="+document.getElementById("estatus").value+"&nom="+document.getElementById("nom").value+"&imei="+document.getElementById("imei").value+"&ruta="+document.getElementById("ruta").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
			objeto.onreadystatechange = function(){
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}//buscarRegistros();
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


		$("#dialog").dialog({ 
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 600,
			height: 300,
			autoResize: true,
			position: "center",
			beforeClose: function( event, ui ) {
				document.getElementById("correos_envio").value="";
				document.getElementById("estatuscorreo").value="";
				document.getElementById("motivocorreo").value="";
				document.getElementById("obscorreo").value="";
			},
			buttons: {
				"Aceptar": function(){ 
					if(document.getElementById("correos_envio").value==""){
						alert("Necesita seleccionar los correos de envio");
					}
					else if(document.getElementById("estatuscorreo").value==""){
						alert("Necesita seleccionar el estatus");
					}
					else if(document.getElementById("motivocorreo").value==""){
						alert("Necesita seleccionar el concepto");
					}
					else{
						document.forma.correos_envio.value=document.getElementById("correos_envio").value;
						document.forma.estatuscorreo.value=document.getElementById("correos_envio").value;
						document.forma.motivocorreo.value=document.getElementById("motivocorreo").value;
						document.forma.obscorreo.value=document.getElementById("obscorreo").value;
						atcr("dispositivos.php","",12);
					}
				},
				"Cerrar": function(){ 
					document.getElementById("correos_envio").value="";
					document.getElementById("estatuscorreo").value="";
					document.getElementById("motivocorreo").value="";
					document.getElementById("obscorreo").value="";
					$(this).dialog("close"); 
				}
			},
		}); 


	</Script>';
}
bottom();
?>
