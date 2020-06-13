<?php 

include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$array_propietarios=array();
$res=mysql_db_query($base,"SELECT * FROM ".$pre."propietarios ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_propietarios[$row['cve']]=$row['nombre'];
}

$rsBenef=mysql_db_query($base,"SELECT * FROM parque WHERE 1");
while($Benef=mysql_fetch_array($rsBenef)){
	$array_unidad[$Benef['cve']]=$Benef['no_eco'].$Benef['letra'];
	$array_propietario[$Benef['cve']]=$array_propietarios[$Benef['propietario']];
}

$rsBenef=mysql_db_query($base,"SELECT * FROM beneficiarios");
while($Benef=mysql_fetch_array($rsBenef)){
	$array_benef[$Benef['cve']]=$Benef['nombre'];
}

$array_formapago=array("EFECTIVO");
$array_estatusvales=array('A'=>"Pagado",'C'=>"Cancelado");
/*** ELIMINAR REGISTRO  **************************************************/

if($_POST['cmd']==100){
	echo '<h1>Traspaso de Utilidades</h1>';
	$select=" SELECT * FROM traspaso WHERE cve='".$_POST['reg']."' ";
		$rssalida=mysql_db_query($base,$select);
		$Salida=mysql_fetch_array($rssalida);
		//Menu
		//Formulario 
		echo '<table>';
		echo '<tr><th align="left">Folio: </th><td>'.$Salida['cve'].'</td></tr>';
		echo '<tr><th align="left">Fecha</th><td>'.$Salida['fecha'].'</td></tr>';
		echo '<tr><th align="left">Fecha Aplicacion</th><td>'.$Salida['fechaapl'].'</td></tr>';
		echo '<tr><th align="left">Referencia Bancaria</th><td>'.$Salida['referencia'].'</td></tr>';
		echo '<tr><th align="left">Unidades</th><td><table id="tabla1"><tr><th>No. Eco.</th><th>Propietario</th><th>Monto</th></tr>';
		$cantuni=0;
			$res1=mysql_db_query($base,"SELECT * FROM traspasomov WHERE traspaso='".$_POST['reg']."' ORDER BY cve");
			while($row1=mysql_fetch_array($res1)){
				echo '<tr><td>'.$array_unidad[$row1['unidad']].'</td>
				<td>'.$array_propietario[$row1['unidad']].'</td>
				<td align="right">'.$row1['monto'].'</td></tr>';
				$cantuni++;
			}
		echo '</table></td></tr>';
		echo '<input type="hidden" name="cantuni" value="'.$cantuni.'">';
		echo '<tr><th align="left">Monto</th><td>'.$Salida['monto'].'</td></tr>';
		echo '<tr><th align="left">Tipo de Beneficiario</th><td>';
		if($Salida['tipo_beneficiario']==0) echo ' Externo';
		else echo 'Unidad';
		echo '</td></tr>';
		echo '<tr><th align="left">Beneficiario</th><td>';
			if($Salida['tipo_beneficiario']==0){
				$res1=mysql_db_query($base,"SELECT * FROM beneficiarios WHERE cve='".$Salida['beneficiario']."'");
				$row1=mysql_fetch_array($res1);
				echo $row1['nombre'].' '.$row1['apaterno'].' '.$row1['amaterno'];
				
			}
			elseif($Salida['tipo_beneficiario']==1){
				$res1=mysql_db_query($base,"SELECT * FROM parque WHERE cve='".$Salida['beneficiario']."'");
				$row1=mysql_fetch_array($res1);
				echo $row1['no_eco'].$row1['letra'].' '.$array_propietarios[$row1['propietario']];
			}
		echo '</td></tr>';
		echo '<tr><th valign="top" align="left">Concepto</th><td>'.$Salida['concepto'].'</td></tr>';
		echo '</table>';
		
		exit();
}

if ($_POST['cmd']==3) {
	$delete= "UPDATE traspaso SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['cveusuario']."',obscan='".$_GET['obs']."' WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_db_query($base,$delete);
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	//Insertar el Registro
	$insert = " INSERT traspaso 
				SET monto='".$_POST['monto']."',referencia='".$_POST['referencia']."',fechaapl='".$_POST['fechaapl']."',
				    tipo_beneficiario='".$_POST['tipo_beneficiario']."',concepto='".$_POST['concepto']."',
					usuario='".$_POST['cveusuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
					beneficiario='".$_POST['beneficiario']."',fechasal='".$_POST['fechasal']."',saldoxperiodo='".$_POST['saldoxperiodo']."'";
	$ejecutar = mysql_db_query($base,$insert);
	$salida=mysql_insert_id();
	for($i=0;$i<$_POST['cantuni'];$i++){
		if($_POST['uni'.$i]!="" && $_POST['monto'.$i]>0){
			mysql_db_query($base,"INSERT traspasomov SET traspaso='".$salida."',unidad='".$_POST['uni'.$i]."',monto='".$_POST['monto'.$i]."',
			saldoa='".$_POST['sal'.$i]."',saldon='".$_POST['saln'.$i]."'");
		}
	}
	$_POST['cmd']=0;
}

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	$filtroeco="";
	if($_POST['no_eco']!="") $filtroeco=" INNER JOIN parque AS c ON (c.cve=b.unidad AND c.no_eco='".$_POST['no_eco']."')";
	$select= " SELECT a.* FROM traspaso as a INNER JOIN traspasomov as b on (b.traspaso=a.cve) $filtroeco WHERE a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	$select .= " GROUP BY a.cve ORDER BY a.cve DESC";
	$rssalida=mysql_db_query($base,$select) or die(mysql_error());
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	echo '<th>Folio</th><th>Fecha</th><th>Fecha Aplicacion</th><th>Beneficiario</th><th>Concepto</th><th>Monto</th><th>Estatus</th><th>Usuario<br>
	<select name="usu2" id="usu2" onChange="document.forma.usu.value=this.value;buscarRegistros()">
	<option value="all">--- Todos ---</option>';
	$res=mysql_db_query($base,"SELECT usuario FROM traspaso WHERE 1 GROUP BY usuario");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['usuario'].'"';
		if($row['usuario']==$_POST['usu']) echo ' selected';
		echo '>'.$array_usuario[$row['usuario']].'</option>';
	}
	echo '</select></th>';
	echo '</tr>';
	$total=0;
	$i=0;
	while($Salida=mysql_fetch_array($rssalida)) {
		rowb();
		echo '<td align="center">';
		if($Salida['estatus']=='C'){
			echo 'CANCELADO';
			$Salida['monto']=0;
		}
		else{
			echo '<a href="#" onClick="atcr(\'traspaso.php\',\'_blank\',\'100\','.$Salida['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$Salida['cve'].'"></a>';
			echo '&nbsp;&nbsp;&nbsp;<a href="#" onClick="atcr(\'traspaso.php\',\'\',\'1\','.$Salida['cve'].')"><img src="images/modificar.gif" border="0" title="Ver '.$Salida['cve'].'"></a>';
			if(nivelUsuario()>2){
				echo '&nbsp;&nbsp;<a href="#" onClick="cancelarTraspaso('.$Salida['cve'].')"><img src="images/validono.gif" border="0"></a>';
			}
		}
		echo '</td>';
		echo '<td align="center">'.$Salida['cve'].'</td>';
		echo '<td align="center">'.$Salida['fecha'].'</td>';
		echo '<td align="center">'.$Salida['fechaapl'].'</td>';
		if($Salida['tipo_beneficiario']==0)
			echo '<td align="left">'.htmlentities($array_benef[$Salida['beneficiario']]).'</td>';
		else
			echo '<td align="left">'.htmlentities($array_propietario[$Salida['beneficiario']]).' (No Eco: '.$array_unidad[$Salida['beneficiario']].')</td>';
		echo '<td align="left">'.utf8_encode($Salida['concepto']).'</td>';
		echo '<td align="center">$ '.number_format($Salida['monto'],2).'</td>';
		echo '<td align="center">'.$array_estatusvales[$Salida['estatus']].'</td>';
		echo '<td align="left">'.htmlentities($array_usuario[$Salida['usuario']]).'</td>';
		$total+=$Salida['monto'];
		$i++;
		echo '</tr>';
	}
	echo '	
		<tr>
		<td colspan="5" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
		<td bgcolor="#E9F2F8" align="right">Total:</td>
		<td bgcolor="#E9F2F8" align="center">$ '.number_format($total,2).'</td>
		<td colspan="2" bgcolor="#E9F2F8">&nbsp;</td>
		</tr>
	</table>';
		
	exit();	
}

if($_POST['ajax']==2){
	if($_POST['clavecancelacion'] == 'Dnbi3z7T'){
	
		$delete= "UPDATE traspaso SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['usuario']."',obscan='".$_POST['obs']."' WHERE cve='".$_POST['traspaso']."' ";
		$ejecutar=mysql_db_query($base,$delete) or die (mysql_error());
	}
	else{
		echo '1';
	}
	exit();
}

if($_POST['ajax']==3){
	if($_POST['tipo_beneficiario']==0){
		$res1=mysql_db_query($base,"SELECT * FROM beneficiarios ORDER BY nombre");
		while ($row1=mysql_fetch_array($res1)) {
			echo $row1['cve'].','.$row1['nombre'].'|';
		}
	}
	elseif($_POST['tipo_beneficiario']==1){
		$res1=mysql_db_query($base,"SELECT * FROM parque ORDER BY no_eco");
		while ($row1=mysql_fetch_array($res1)) {
			echo $row1['cve'].','.$row1['no_eco'].$row1['letra'].' '.$array_propietarios[$row1['propietario']].'|';
		}
	}
	exit();
}	

if($_GET['ajax']==4){
	$resultado = array();
	if($_GET['tipo']==0){
		$res = mysql_query("SELECT * FROM beneficiarios WHERE nombre like '%".$_GET['term']."%' OR no_cuenta LIKE '%".$_GET['term']."%' ORDER BY nombre, no_cuenta LIMIT 10");
	}
	else{
		$res = mysql_query("SELECT a.cve, CONCAT(a.no_eco,' ',b.nombre) as nombre, b.no_cuenta FROM parque a LEFT JOIN propietarios b ON b.cve = a.propietario WHERE a.no_eco LIKE '{$_GET['term']}%' OR b.nombre like '%{$_GET['term']}%' OR b.no_cuenta LIKE '%{$_GET['term']}%' ORDER BY a.no_eco, b.nombre, b.no_cuenta LIMIT 10");
	}
	while($row = mysql_fetch_array($res)){
		$resultado[] = array(
			'id' => $row['cve'],
			'value' => utf8_encode($row['nombre'].' '.$row['no_cuenta']),
			'label' => utf8_encode($row['nombre'].' '.$row['no_cuenta'])
		);
	}
	echo json_encode($resultado);
	exit();
}

if($_POST['ajax']==5){
	$rsUni=mysql_db_query($base,"SELECT cve FROM parque WHERE no_eco='".strtoupper($_POST['eco'])."' AND estatus='1'");
	if($Uni=mysql_fetch_array($rsUni)){
		$_POST['unidad']=$Uni['cve'];
		$fechaaux="0000-00-00";
		if($_POST['saldoxperiodo']==1){
			$fechaaux=substr($_POST['fechasal'],0,8)."01";
			$saldo = saldo_unidad($_POST['unidad'],2,0,$fechaaux,$_POST['fechasal']);
		}
		else{
			$saldo = saldo_unidad($_POST['unidad'],1,0,date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($_POST['fechasal']) ) ));
		}
			
			
		echo $_POST['unidad'].'|'.$array_propietario[$_POST['unidad']].'|'.round($saldo,2);
	}
	else{
		echo "0";
	}
	exit();
}


top($_SESSION);

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM traspaso WHERE cve='".$_POST['reg']."' ";
		$rssalida=mysql_db_query($base,$select);
		$Salida=mysql_fetch_array($rssalida);
		if($_POST['reg']>0){
			$fecha=$Salida['fecha'];
			$Encabezado = 'Folio No.'.$_POST['reg'];
		}
		else{
			$fecha=fechaLocal();
			$Encabezado = 'Nuevo Traspaso';
		}
		$fechaperiodo=date( "Y-m-d" , strtotime ( "-1 day" , strtotime(substr(fechaLocal(),0,8).'01') ) );
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1 && $_POST['reg']==0){
				echo '<td><a href="#" onClick="$(\'#panel\').show();
				if(document.forma.beneficiario.value==\'\'){
					$(\'#panel\').hide();
					alert(\'Necesita seleccionar el beneficiario\');
				}
				else if(document.forma.monto.value==\'\'){
					$(\'#panel\').hide();
					alert(\'Necesita ingresar el monto\');
				}
				else if((document.forma.monto.value/1)<=0){
					$(\'#panel\').hide();
					alert(\'El monto debe ser mayor a 0\');
				}
				else{
					atcr(\'traspaso.php\',\'\',\'2\',\''.$Salida['cve'].'\');
				}"
				><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			}
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'traspaso.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Traspaso</td></tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><th align="left">'.$Encabezado.'</th><td>&nbsp;</td></tr>';
		echo '<tr><th align="left">Fecha Aplicacion</th><td><input type="text" name="fechaapl" id="fechaapl" class="readOnly" size="15" value="'.$fechapag.'" readOnly>';
		if(nivelUsuario()>0){
			echo '&nbsp;<a class="cfechas" href="#" onClick="displayCalendar(document.forms[0].fechaapl,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
		}
		echo '<tr><th align="left">Tipo de Saldo</th><td>
		<input type="radio" name="saldoxperiodo" value="0" onClick="
		$(\'.cfechas\').show(); 
		$(\'.cecos\').parent().parent().remove(); 
		document.forma.cantuni.value=0;
		addUni();
		document.forma.fechaapl.value=\''.fechaLocal().'\';
		document.forma.fechasal.value=\'\';"';
		if($Salida['saldoxperiodo']!=1) echo ' checked';
		echo '> corriente
		<input type="radio" name="saldoxperiodo" value="1" onClick="
		$(\'.cfechas\').hide(); 
		$(\'.cecos\').parent().parent().remove();
		document.forma.cantuni.value=0;
		addUni();
		document.forma.fechaapl.value=\''.$fechaperiodo.'\';
		document.forma.fechasal.value=\''.$fechaperiodo.'\';"';
		if($Salida['saldoxperiodo']==1) echo ' checked';
		echo '> por periodo</td></tr>';
		
		echo '</td></tr>';
		echo '<tr><th align="left">Referencia Bancaria</th><td><input type="text" name="referencia" id="referencia" class="textField" size="30" value="'.$Salida['referencia'].'"></td></tr>';
		echo '<input type="hidden" name="bandera" id="bandera" value="0">';
		echo '<tr><th class="bene1" align="left">Saldo a la fecha</th><td><input type="text" name="fechasal" id="fechasal" class="readOnly" size="15" value="" readOnly>';
		echo '&nbsp;<a class="cfechas" href="#" onClick="displayCalendar(document.forms[0].fechasal,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
		echo '</td></tr>';
		echo '<tr><th align="left">Unidades<br>
		<a href="#" onClick="addUni();">Agregar Unidad</a></th><td><table id="tabla1"><tr><th>No. Eco.</th><th>Propietario</th><th>Saldo</th><th>Monto</th></tr>';
		$cantuni=0;
		if($_POST['reg']==0){
			echo '<tr><td><input type="text" name="eco0" class="textField cecos" value="" size="15" onKeyUp="if(event.keyCode==13){ traeUni(0);}"><input type="hidden" name="uni0" value=""></td>
			<td><input type="text" name="pro0" class="readOnly" size="50" value="" readOnly></td>
			<td><input type="text" name="sal0" class="readOnly" size="20" value="" readOnly></td>
			<td><input type="text" name="monto0" value"" class="textField" size="20" onKeyUp="validamonto(0);calcula();"></td>
			<td><input type="text" name="saln0" class="readOnly" size="20" value="" readOnly></td></tr>';
			$cantuni++;
		}
		else{
			$res1=mysql_db_query($base,"SELECT * FROM traspasomov WHERE traspaso='".$_POST['reg']."' ORDER BY cve");
			while($row1=mysql_fetch_array($res1)){
				echo '<tr><td><input type="text" name="eco'.$cantuni.'" class="textField cecos" value="'.$array_unidad[$row1['unidad']].'" size="15" onKeyUp="if(event.keyCode==13){ traeUni('.$cantuni.');}"><input type="hidden" name="uni'.$cantuni.'" value="'.$row1['unidad'].'"></td>
				<td><input type="text" name="pro'.$cantuni.'" class="readOnly" size="50" value="'.$array_propietario[$row1['unidad']].'" readOnly></td>
				<td><input type="text" name="sal'.$cantuni.'" class="readOnly" size="20" value="'.$row1['saldoa'].'" readOnly></td>
				<td><input type="text" name="monto'.$cantuni.'" value="'.$row1['monto'].'" class="textField" size="20" onKeyUp="validamonto('.$cantuni.');calcula();"></td>
				<td><input type="text" name="saln'.$cantuni.'" class="readOnly" size="20" value="'.$row1['saldon'].'" readOnly></td></tr>';
				$cantuni++;
			}
		}
		echo '</table></td></tr>';
		echo '<input type="hidden" name="cantuni" value="'.$cantuni.'">';
		echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="readOnly" size="15" value="'.$Salida['monto'].'" readOnly></td></tr>';
		echo '<tr><th align="left">Tipo de Beneficiario</th><td><input type="radio" name="tipo_beneficiario" value="0" onClick="traeBenef(true);"';
		if($_POST['reg']==0 || $Salida['tipo_beneficiario']==0) echo ' checked';
		echo '>Externo&nbsp;
		<input type="radio" name="tipo_beneficiario" value="1" onClick="traeBenef(true);"';
		if($Salida['tipo_beneficiario']==1) echo ' checked';
		echo '>Unidades&nbsp;
		&nbsp;</td></tr>';
		echo '<tr><th align="left">Beneficiario</th><td>';
		if($Salida['tipo_beneficiario']==0){
			$res1=mysql_db_query($base,"SELECT * FROM beneficiarios WHERE cve = '{$Salida['beneficario']}'");
			$row1=mysql_fetch_array($res1);
			$nombeneficiario = $row1['nombre'].' '.$row1['no_cuenta'];
		}
		elseif($Salida['tipo_beneficiario']==1){
			$res1=mysql_db_query($base,"SELECT a.no_eco, a.letra, a.propietario, b.no_cuenta FROM parque a LEFT JOIN propietarios b ON b.cve = a.propietario WHERE a.cve='{$Salida['beneficiario']}'");
			$row1=mysql_fetch_array($res1);
			$nombeneficiario = $row1['no_eco'].$row1['letra'].' '.$array_propietarios[$row1['propietario']].' '.$row1['no_cuenta'];
		}
		echo '<input type="text" class="textField" id="nombeneficiario" value="'.$nombeneficiario.'" size="100"><input type="hidden" name="beneficiario" id="beneficiario" value="'.$Salida['beneficiario'].'">';
		/*echo '<select name="beneficiario" id="beneficiario" class="textField">';
		echo '<option value="0">--- Seleccione un Beneficiario ---</option>';
		if($_POST['reg']==0){
			$res1=mysql_db_query($base,"SELECT * FROM beneficiarios  ORDER BY nombre");
			while ($row1=mysql_fetch_array($res1)) {
				echo '<option value="'.$row1['cve'].'"';
				echo '>'.$row1['nombre'].'</option>';
			}
		}
		else{
			if($Salida['tipo_beneficiario']==0){
				$res1=mysql_db_query($base,"SELECT * FROM beneficiarios ORDER BY nombre");
				while ($row1=mysql_fetch_array($res1)) {
					echo '<option value="'.$row1['cve'].'"';
					if ($Salida["beneficiario"]==$row1['cve']) echo ' selected';
					echo '>'.$row1['nombre'].'</option>';
				}
			}
			elseif($Salida['tipo_beneficiario']==1){
				$res1=mysql_db_query($base,"SELECT * FROM parque ORDER BY no_eco");
				while ($row1=mysql_fetch_array($res1)) {
					echo '<option value="'.$row1['cve'].'"';
					if ($Salida["beneficiario"]==$row1['cve']) echo ' selected';
					echo '>'.$row1['no_eco'].$row1['letra'].' '.$array_propietarios[$row1['propietario']].'</option>';
				}
			}
		}
		echo '</select>';*/

		echo '</td></tr>';
		echo '<tr><th valign="top" align="left">Concepto</th><td><textarea name="concepto" id="concepto" class="textField" rows="5" cols="50">'.$Salida['concepto'].'</textarea></td></tr>';
		echo '</table>';
		echo '<script language="javascript">
				
				function traeBenef2(){
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						if(document.forma.tipo_beneficiario[0].checked==true) tipo_benef=0;
						else if(document.forma.tipo_beneficiario[1].checked==true) tipo_benef=1;
						else if(document.forma.tipo_beneficiario[2].checked==true) tipo_benef=2;
						else if(document.forma.tipo_beneficiario[3].checked==true) tipo_benef=3;
						objeto.open("POST","traspaso.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=3&tipo_beneficiario="+tipo_benef);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4){
								document.forma.beneficiario.options.length=0;
								document.forma.beneficiario.options[0]= new Option("---Seleccione un Beneficiario---","0");
								var opciones2=objeto.responseText.split("|");
								for (i = 0; i < opciones2.length-1; i++){
									datos=opciones2[i].split(",");
									document.forma.beneficiario.options[i+1]= new Option(datos[1], datos[0]);
								}
							}
						}
					}
				}

				function traeBenef(limpiar){
					if(limpiar){
						$("#nombeneficiario").val("");
						$("#beneficiario").val("");
					}
					tipo = 0;
					if(document.forma.tipo_beneficiario[1].checked) tipo=1;
					$( "#nombeneficiario" ).autocomplete({
				      source: "traspaso.php?ajax=4&tipo="+tipo,
				      minLength: 2,
				      select: function( event, ui ) {
				      	document.getElementById("beneficiario").value=ui.item.id;
				      }
				    });
				}

				traeBenef(false);
				
				function validamonto(ren){
					if(document.forma["uni"+ren].value==""){
						alert("No se ha cargado la unidad correctamente");
						document.forma["monto"+ren].value="";
					}
					else if((document.forma["monto"+ren].value/1)>(document.forma["sal"+ren].value/1) && '.intval(nivelUsuario()).'<3){
						alert(\'El saldo es menor al monto\');
						document.forma["monto"+ren].value=document.forma["sal"+ren].value;
					}
					else if((document.forma["monto"+ren].value/1)>(document.forma["sal"+ren].value/1) && '.intval(nivelUsuario()).'==3 && document.forma.bandera.value=="0"){
						if(!confirm(\'El saldo es menor al monto, desea continuar?\')){
							document.forma["monto"+ren].value=document.forma["sal"+ren].value;
						}
						else{
							document.forma.bandera.value="1";
						}
					}
					saldon=(document.forma["sal"+ren].value/1)-(document.forma["monto"+ren].value/1)
					document.forma["saln"+ren].value=saldon.toFixed(2);
				}
				
				function calcula(){
					total=0;
					for(i=0;i<(document.forma.cantuni.value/1);i++){
						total+=(document.forma["monto"+i].value/1);
					}
					document.forma.monto.value=total.toFixed(2);
				}
				
				function traeUni(ren){
					if(document.forma.fechasal.value==""){
						alert("Necesita seleccionar la fecha del saldo");
					}
					else{
						if(document.forma["eco"+ren].value==""){
							document.forma["eco"+ren].focus();
							document.forma["uni"+ren].value="";
							document.forma["pro"+ren].value="";
							document.forma["sal"+ren].value="";
							document.forma["monto"+ren].value="";
						}
						else{
							objeto2=crearObjeto();
							if (objeto2.readyState != 0) {
								alert("Error: El Navegador no soporta AJAX");
							} else {
								if(document.forma.saldoxperiodo[0].checked) sxp=0;
								else sxp=1;
								objeto2.open("POST","traspaso.php",true);
								objeto2.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
								objeto2.send("ajax=5&eco="+document.forma["eco"+ren].value+"&fechasal="+document.forma.fechasal.value+"&saldoxperiodo="+sxp);
								objeto2.onreadystatechange = function()
								{
									if (objeto2.readyState==4){
										if(objeto2.responseText=="0"){
											alert("Error en Unidad");
											document.forma["eco"+ren].focus();
											document.forma["uni"+ren].value="";
											document.forma["pro"+ren].value="";
											document.forma["sal"+ren].value="";
											document.forma["monto"+ren].value="";
										}
										else{
											datos=objeto2.responseText.split("|");
											document.forma["uni"+ren].value=datos[0];
											document.forma["pro"+ren].value=datos[1];
											document.forma["sal"+ren].value=datos[2];
											document.forma["monto"+ren].value="";
											document.forma["monto"+ren].focus();
										}
										calcula();
									}
								}
							}
						}
					}
				}
				
				function addUni(){
					var tblBody = document.getElementById("tabla1").getElementsByTagName("TBODY")[0];
					var lastRow = tblBody.rows.length;
					var iteration = document.forma["cantuni"].value;
					var newRow = tblBody.insertRow(lastRow);
					var newCell0 = newRow.insertCell(0);
					newCell0.innerHTML = \'<input type="text" name="eco\'+iteration+\'" size="15" value="" class="textField cecos" onKeyUp="if(event.keyCode==13){ traeUni(\'+iteration+\');}"><input type="hidden" name="uni\'+iteration+\'" value="">\';
					var newCell1 = newRow.insertCell(1);
					newCell1.innerHTML = \'<input type="text" name="pro\'+iteration+\'" value="" size="50" class="readOnly" readOnly>\';
					var newCell2 = newRow.insertCell(2);
					newCell2.innerHTML = \'<input type="text" name="sal\'+iteration+\'" value="" size="20" class="readOnly" readOnly>\';
					var newCell3 = newRow.insertCell(3);
					newCell3.innerHTML = \'<input type="text" name="monto\'+iteration+\'" size="20" value="" class="textField" onKeyUp="validamonto(\'+iteration+\');calcula();">\';
					var newCell4 = newRow.insertCell(4);
					newCell4.innerHTML = \'<input type="text" name="saln\'+iteration+\'" value="" size="20" class="readOnly" readOnly>\';
					document.forma["eco"+iteration].focus();
					iteration++;
					document.forma["cantuni"].value=iteration;
				}
								
			  </script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'traspaso.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>No. Eco.</td><td><input type="text" class="textField" name="no_eco" id="no_eco" value="" size="10"></td></tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
		//if($_GET['salida']!='') echo '<script>atcr(\'imp_traspaso.php\',\'_bank\',\'1\','.$_GET['salida'].');</script>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
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
			objeto.open("POST","traspaso.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&no_eco="+document.getElementById("no_eco").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}


	function cancelarTraspaso(numtraspaso){
	  if(confirm("¿Esta seguro de cancelar este folio?")){
		obs=prompt("Observaciones:");
		clavecancelacion = prompt("Clave de cancelación:");
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","traspaso.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&clavecancelacion="+clavecancelacion+"&traspaso="+numtraspaso+"&obs="+obs+"&usuario='.$_POST['cveusuario'].'");
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					if(objeto.responseText=="1"){
						alert("La clave es invalida");
					}
					else{
						buscarRegistros(0,1);
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

