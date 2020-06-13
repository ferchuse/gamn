<?
include("main.php");
$array_empresa=array();
$res=mysql_query("SELECT * FROM empresas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_empresa[$row['cve']]=$row['nombre'];
	$array_empresalogo[$row['cve']]=$row['logo'];
}
$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
	$array_nomusuario[$Usuario['cve']]=$Usuario['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM recaudaciones ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_recaudacion[$Motivo['cve']]=$Motivo['nombre'];
}
//
$rsMotivo=mysql_query("SELECT * FROM tipo_sector ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_tipo_sector[$Motivo['cve']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM permisionarios ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_permisionarios[$Motivo['cve']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM conductores ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_operador[$Motivo['cve']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM parque ORDER BY no_eco");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_unidad[$Motivo['cve']]=$Motivo['no_eco'];
}
$res=mysql_db_query($base,"SELECT * FROM taquillas ORDER BY nombre");

$denominaciones=array(1000,500,200,100,50,20,10,5,2,1,0.50,0.20,0.10,0.05,"Documentos","Boletos","Cheques");
$estatus=array("A","C");


if($_POST['cmd']==100){
	$datos=explode("|",$_POST['reg']);
	$con="select * from vale_dinero where cve='".$_POST['reg']."'";
		$res1=mysql_db_query($base,$con);
		$row11=mysql_fetch_array($res1);
	$varimp="Vale de Dinero|";
	$varimp.="Folio: ".$_POST['reg']."|";
	$varimp.="Fecha: ".$row11['fecha']."|";
	$varimp.="Operador: ".$array_operador[$row11['operador']]."|";
	$varimp.="Permisionario: ".$array_unidad[$row11['unidad']]."|";
	$varimp.="Usuario: ".$array_nomusuario[$row11['usuario']]."|";
	$varimp.="Monto: ".number_format($row11['monto'],2)."|";
	//$varimp.=sprintf("%' 20s","Total:").sprintf("%' 10s",number_format($total,2));
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$_POST['empresa']].'&textoimp='.$varimp.'" width=200 height=200></iframe>';
	//$sSQL="insert vale_dineroamov set cvedesg='".$_POST['reg']."',denomin='".$_POST['denominT']."',cant='".$_POST['cantT']."',tipo='3'";
	//mysql_db_query("$base",$sSQL) or die(mysql_error());
	$_POST['cmd']=0;
/*	foreach($denominaciones as $k=>$v){
		$res1=mysql_db_query("$base","select b.denomin,sum(b.denomin*b.cant) as importe,sum(b.cant) as cant from vale_dinero as a inner join vale_dineromov as b on (b.cvedesg=a.cve and b.tipo='0' and denomin='$v') where a.fecha='".$datos[0]."' AND a.usu='".$datos[1]."' $filtro group by a.fecha,a.usu,b.denomin") or die(mysql_error());
		//$res1=mysql_db_query($base,"select * from vale_dineroamov where cvedesg='".$row['cve']."' and denomin='$v' and tipo='0'");
		$row1=mysql_fetch_array($res1);
		
		$varimp.=sprintf("%10s",$v).sprintf("%10s",$row1['cant']).sprintf("%' 10s",number_format($row1['importe'],2))."|";
		
		$subtot+=round($row1['importe'],2);
	}*/
	
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row11['empresa']].'&textoimp='.$varimp.'" width=200 height=200></iframe>';
//	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$row['cve']).'" width=200 height=200></iframe>';
	/*echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",100);</script>';
	exit();*/
		$_POST['cmd']=0;
}

if ($_POST['cmd']==2) {
	if($_POST['reg']>0){
		$sSQL="update vale_din fecha='".$_POST['fecha']."',hora='".horaLocal()."',operador='".$_POST['operador']."',unidad='".$_POST['unidad']."',usuario='".$_SESSION['CveUsuario']."',monto='".$_POST['monto']."',obs='".$_SESSION['obs']."' where cve='".$_POST['reg']."'";
		mysql_db_query("$base",$sSQL) or die(mysql_error());
		mysql_db_query($base,"DELETE FROM vale_dineromov WHERE cvedesg='".$_POST['reg']."'");
	}
	else{
		$sSQL="insert vale_dinero set fecha_creacion='".fechaLocal()."',fecha='".$_POST['fecha']."',hora='".horaLocal()."',operador='".$_POST['operador']."',unidad='".$_POST['unidad']."',usuario='".$_SESSION['CveUsuario']."',monto='".$_POST['monto']."',obs='".$_SESSION['obs']."',estatus='A'";
		mysql_db_query("$base",$sSQL) or die(mysql_error());
		$_POST['reg']=mysql_insert_id();
	
	$id=mysql_insert_id();
	$varimp="Vale de Dinero|";
	$varimp.="Folio: ".$id."|";
	$varimp.="Fecha: ".$_POST['fecha']."|";
	$varimp.="Operador: ".$array_operador[$_POST['operador']]."|";
	$varimp.="Permisionario: ".$array_unidad[$_POST['unidad']]."|";
	$varimp.="Usuario: ".$array_nomusuario[$_SESSION['CveUsuario']]."|";
	$varimp.="Monto: ".number_format($_POST['monto'],2)."|";
	//$varimp.=sprintf("%' 20s","Total:").sprintf("%' 10s",number_format($total,2));
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$_POST['empresa']].'&textoimp='.$varimp.'" width=200 height=200></iframe>';
	//$sSQL="insert vale_dineroamov set cvedesg='".$_POST['reg']."',denomin='".$_POST['denominT']."',cant='".$_POST['cantT']."',tipo='3'";
	//mysql_db_query("$base",$sSQL) or die(mysql_error());
	$_POST['cmd']=0;
	}
}
if ($_POST['ajax']==3) {
	if($_POST['clavecancelacion'] == $clavecancelacion){
		$delete= "UPDATE vale_dinero SET estatus='C',usucan='".$_POST['usuario']."',fechacan='".fechaLocal()."',horacan='".horaLocal()."' WHERE cve='".$_POST['id']."' ";
		$ejecutar=mysql_query($delete);
	}
	else{
		echo '1';
	}
	exit();
}
if($_POST['ajax']==1)
{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th width="10%">Folio</th><th width="10%">Fecha</th><th>Unidad</th><th>Operador</th><th>Importe</th><th>Usuario</th>';
		echo '</tr>';
		$n=0;
		$filtro="";
		$fil_ope="";
		$fil_uni="";
		$fil_usu="";
		$fil_est="";
		//if($_SESSION[$archivo[(count($archivo)-1)]]<=2)
		if(nivelUsuario()>1){
//			$filtro=" and a.usu='".$_SESSION['CveUsuario']."'";
		}
/*		if ($_POST['recaudacion']!="") { $fil_rec=" AND a.recaudacion = ".$_POST['recaudacion'].""; }
		$ss="select a.*,sum(b.denomin*b.cant) as importe from vale_dinero as a inner join 
		vale_dineromov as b on (b.cvedesg=a.cve and b.tipo<20) where a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
		$filtro".$fil_rec." order by a.cve desc";*/
//		$result=mysql_db_query("$base","select a.*,sum(b.denomin*b.cant) as importe from vale_dinero as a inner join vale_dineromov as b on (b.cvedesg=a.cve and b.tipo<20) where a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' $filtro".$fil_rec." group by a.cve order by a.cve desc") or die(mysql_error());
		//$result=mysql_db_query("$base","select * from recibos where 1 and usuario='$emite' order by cve");
		if ($_POST['operador']!="") { $fil_ope=" AND operador = ".$_POST['operador'].""; }
		if ($_POST['unidad']!="") { $fil_uni=" AND unidad = ".$_POST['unidad'].""; }
		if ($_POST['usuario']!="") { $fil_usu=" AND usuario = ".$_POST['usuario'].""; }
		if ($_POST['estatus']!="") { $fil_est=" AND estatus = '".$_POST['estatus']."'"; }
		$ss="select * from vale_dinero where fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
		$filtro".$fil_ope."".$fil_uni."".$fil_usu."".$fil_est." order by cve desc";
		$suma=0;
	//	echo''.$ss.'';
		$result=mysql_db_query($base,$ss)or die(mysql_error());
		while ($row=mysql_fetch_array($result))
		{
		rowc();
			//echo '<td align=center><a href="javascript:ventanaSecundaria(\'derecho_piso.php?reg='.$row["cve"].'&cmd=4\')"><img src="imagenes/b_print.png" border=0></a></td>';
			if($row["estatus"]=="C"){
				echo'<td align=center>Cancelado</td>';
			}else{
				echo '<td align=center>';
				if(nivelUsuario()==3)
				{
				echo'<a href="#" onClick="/*atcr(\'vale_dinero.php\',\'\',3,'.$row['cve'].')*/if(cancelarRegistro('.$row['cve'].', \'vale_dinero.php\', 3)) buscarRegistros();"><img src="images/validono.gif" border="0"></a>';
				}
				echo'<a href="#" onClick="atcr(\'vale_dinero.php\',\'\',100,'.$row['cve'].')"><img src="images/b_print.png" border="0"></a>';
			   echo'</td>';
		}
			echo '<td align="center">'.$row["cve"].'</td>';
			echo '<td align="center">'.$row["fecha"].' '.$row['hora'].'</td>';
			echo '<td align="center">'.$array_unidad[$row["unidad"]].'</td>';
			echo '<td align="center">'.$array_operador[$row["operador"]].'</td>';
			if($row["estatus"]=="C"){
				$row["monto"]=0;
				echo '<td align="right">'.number_format($row["monto"],2).'</td>';
			}
			else{
			echo '<td align="right">'.number_format($row["monto"],2).'</td>';
			}
			echo '<td align="center">'.$array_usuario[$row["usuario"]].'</td>';
			$suma=$suma + $row['monto'];

			echo '</tr>';
			$suma+=round($row1['subtotal'],2);
			$n++;
		}
		echo '<tr bgcolor="#E9F2F8"><th colspan=4 align="left">'.$n.' Registros</th><th align="right">Total&nbsp;</th><th align="right">'.number_format($suma,2).'</th><th></th>';
		echo '</table>';
	
	
	exit();
}


top($_SESSION);
if($_POST['cmd']==1)
{	
	$result=mysql_db_query("$base","select * from vale_dinero where cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($result);
	echo '<table><tr>';
//	if($_SESSION[$archivo[(count($archivo)-1)]]>1)
	if(nivelUsuario()>1){
		
		if($_POST['reg']){echo'<td>&nbsp;</td>';}else{echo '<td><a href="#" onClick="if(document.forma.operador.value==\'\')
					alert(\'Necesita Introdicir el Operador\');
				if(document.forma.unidad.value==\'\')
					alert(\'Necesita Introdicir la Unidad\');
						else{atcr(\'vale_dinero.php\',\'\',2,\''.$row['cve'].'\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';}
	}
	echo '<td><a href="#" onclick="atcr(\'vale_dinero.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
	echo '</tr></table>';
//	if($row['fecha']=="") $row['fecha']=fechaLocal();
	echo '<table>';
	
	echo '<tr><th align="left">Fecha</th><td><input type="text" name="fecha" id="fecha" class="readOnly" size="15" value="'.fechaLocal().'" readOnly>';
//		if($_SESSION[$archivo[(count($archivo)-1)]]>2)
		if(nivelUsuario()>2)
		echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
	echo '</td></tr>';
	echo '<tr><td>Operador</td><td><select name="operador" id="operador" class="textField"><option value="">---Seleccione---</option>';
		$res1=mysql_query("SELECT * FROM conductores ORDER BY cve");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'"';if($row['operador']==$row1['cve']){echo'selected';} echo'>'.$row1['nombre'].'</option>';
		}
		echo '</select></td></tr>';
	echo '<tr><td>Unidad</td><td><select name="unidad" id="unidad" class="textField"><option value="">---Seleccione---</option>';
		$res1=mysql_query("SELECT * FROM parque ORDER BY cve");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'"';if($row['unidad']==$row1['cve']){echo'selected';} echo'>'.$row1['no_eco'].'</option>';
		}
		echo '</select></td></tr>
		<tr>
		<td>Importe</td><td><input type="text" id="monto" name="monto" value="'.$row['monto'].'"></td>
		</tr>
		<tr>
		<td>Observaciones</td><td><input type="text" id="obs" name="obs" value="'.$row['obs'].'"></td>
		</tr>
		</table>';
	
}


	if ($_POST['cmd']<1) {
		if(trim($impresion)!="") echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>';
			echo '<td><a href="#" onclick="atcr(\'vale_dinero.php\',\'\',1,0);"><img src="images/nuevo.gif" border="0">&nbsp;&nbsp;Nuevo</a>&nbsp;&nbsp;</td>';
		echo '
			  </tr>';
		echo '</table>';
		echo '<table>';
		if($_SESSION['PlazaUsuario']==0){
			echo '<tr><td>Plaza</td><td><select name="searchplaza" id="searchplaza" class="textField"><option value="all">---Todas---</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select>&nbsp;&nbsp;</tr>';
		}
		else{
			echo '<input type="hidden" name="searchplaza" id="searchplaza" value="'.$_SESSION['PlazaUsuario'].'">';
		}
		//echo '<td>No. Economico</td><td><input type="text" size="5" class="textField" name="no_eco" id="no_eco"></td></tr>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Unidad</td><td><select name="unidad" id="unidad" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_unidad as $k=>$v) { 
	    echo '<option value="'.$k.'"';echo'>'.$v.'</option>';
}
echo '</tr>';
		echo '<tr><td>Operador</td><td><select name="operador" id="operador" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_operador as $k=>$v) { 
	    echo '<option value="'.$k.'"';echo'>'.$v.'</option>';
}
echo '</tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus" class="textField"><option value="">---Seleccione---</option>
		<option value="A">Activo</option>
		<option value="C">Cancelado</option>';
		//foreach ($estatus as $k=>$v) { 
	    //echo '<option value="'.$v.'"';echo'>'.$v.'</option>';
//}
echo '</tr>';
		echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_usuario as $k=>$v) { 
	    echo '<option value="'.$k.'"';echo'>'.$v.'</option>';
}
echo '</tr><!--<tr><td>Permisionario</td><td><select name="permisionario" id="permisionario" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_permisionarios as $k=>$v) { 
	    echo '<option value="'.$k.'"';echo'>'.$v.'</option>';
}
		echo '</tr><tr><td>Empresa</td><td><select name="empresa" id="empresa" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_empresa as $k=>$v) { 
	    echo '<option value="'.$k.'"';echo'>'.$v.'</option>';
}
		echo '</select></td></tr>-->';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
		echo '<script language="javascript">
				function buscarRegistros()
				{
					document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","vale_dinero.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=1&estatus="+document.getElementById("estatus").value+"&unidad="+document.getElementById("unidad").value+"&operador="+document.getElementById("operador").value+"&usuario="+document.getElementById("usuario").value+"&plaza="+document.getElementById("searchplaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4)
							{document.getElementById("Resultados").innerHTML = objeto.responseText;}
						}
					}
					document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
				}
				
				window.onload = function () {
					buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
				}
			</script>';
	}
	
	echo '<script>
			function validanumero(campo) {
				var ValidChars = "0123456789.";
				var cadena=campo.value;
				var cadenares="";
				var digito;
				for(i=0;i<cadena.length;i++) {
					digito=cadena.charAt(i);
					if (ValidChars.indexOf(digito) != -1)
						cadenares+=""+digito;
				}
				campo.value=cadenares;
			}
		</script>';


bottom();

?>
