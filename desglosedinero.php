<?php
include("main.php");
$array_empresa=array();
$res=mysql_db_query($base,"SELECT * FROM empresas ORDER BY nombre");
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
//19-01-2017
$rsBenef=mysql_query("SELECT * FROM beneficiarios ORDER BY nombre");//soloesta comobeneficiarios
while($Benef=mysql_fetch_array($rsBenef)){
	$array_beneficiario[$Benef['cve']]=$Benef['nombre'];
}
$res=mysql_db_query($base,"SELECT * FROM taquillas ORDER BY nombre");

//$denominaciones=array(1000,500,200,100,50,20,10,5,2,1,0.50,0.20,0.10,0.05,"Documentos","Boletos","Cheques");
$denominaciones=array(1000,500,200,100,50,20,10,5,2,1,0.50,0.20,0.10,0.05);

if($_POST['cmd']==100){
	$datos=explode("|",$_POST['reg']);
	$con="select * from desglosedinero where cve='".$_POST['reg']."'";
		$res1=mysql_db_query($base,$con);
		$row11=mysql_fetch_array($res1);
	$varimp="Desglose de Dinero|";
	$varimp.="Fecha: ".$_POST['reg']."|";
	$varimp.="Fecha: ".$row11['fecha']."|";
	$varimp.="Recaudacion: ".$array_recaudacion[$row11['recaudacion']]."|";
	$varimp.="Beneficiario: ".$array_beneficiario[$row11['permisionario']]."|";
	$varimp.="Empresa: ".$array_empresa[$row11['empresa']]."|";
	$varimp.="Usuario: ".$array_nomusuario[$_SESSION['CveUsuario']]."|";
	$varimp.="Efectivo|";
	$varimp.=sprintf("%10s","Denomin").sprintf("%10s","Cant").sprintf("%' 10s","Importe")."|";
	$subtot=0;
	$total=0;
/*	foreach($denominaciones as $k=>$v){
		$res1=mysql_db_query("$base","select b.denomin,sum(b.denomin*b.cant) as importe,sum(b.cant) as cant from desglosedinero as a inner join desglosedineromov as b on (b.cvedesg=a.cve and b.tipo='0' and denomin='$v') where a.fecha='".$datos[0]."' AND a.usu='".$datos[1]."' $filtro group by a.fecha,a.usu,b.denomin") or die(mysql_error());
		//$res1=mysql_db_query($base,"select * from desglosedineromov where cvedesg='".$row['cve']."' and denomin='$v' and tipo='0'");
		$row1=mysql_fetch_array($res1);
		
		$varimp.=sprintf("%10s",$v).sprintf("%10s",$row1['cant']).sprintf("%' 10s",number_format($row1['importe'],2))."|";
		
		$subtot+=round($row1['importe'],2);
	}*/
	$n=0;
//	$total=0;
	$des="Pesos";
	foreach($denominaciones as $k=>$v){
		$t=$v;
		$t1=$v;
//		$res1=mysql_db_query($base,"select * from desglosedineromov where cvedesg='".$row['cve']."' and denomin='$v' and tipo='0'");
//		$row1=mysql_fetch_array($res1);
		if($k>13){$des="";}
	//	echo '<tr><th>'.$v.' '.$des.'</th>';
		if($k>13){$t=1; $t1=$k;}
		$con="select * from desglosedineromov where cvedesg='".$_POST['reg']."' and denomin='".$t1."' and tipo='0'";
		$res1=mysql_db_query($base,$con);
		$row1=mysql_fetch_array($res1);
		$varimp.=sprintf("%10s",$v).sprintf("%10s",$row1['cant']).sprintf("%' 10s",number_format($row1['cant']*$t,2))."|";
		/*echo '<td><input type="text" size="5" name="cant[]" class="textField" id="cant'.$n.'" value="'.$row1['cant'].'" onKeyUp="calcular()">
			<input type="hidden" name="denomin[]" id="denomin'.$n.'" value="'.$t1.'"></td>
			<td><input type="text" size="15" name="imp[]" id="imp'.$n.'" value="'.($row1['cant']*$t).'" class="readOnly" readOnly></td></tr>';*/
	//	$total+=($row1['cant']*$t);
    	$subtot+=round($row1['cant']*$t,2);
		$n++;
	}
	$total+=$subtot;
	$varimp.=sprintf("%' 20s","Subtotal:").sprintf("%' 10s",number_format($subtot,2))."|";
	$varimp.="Boletos|";
	$varimp.=sprintf("%10s","Denomin").sprintf("%10s","Cant").sprintf("%' 10s","Importe")."|";
	$res1=mysql_db_query($base,"select * from desglosedineromov where cvedesg='".$row['cve']."' and tipo='1'");
	$row1=mysql_fetch_array($res1);
//$res2=mysql_db_query("$base","select b.denomin,sum(b.denomin*b.cant) as importe,sum(b.cant) as cant from desglosedinero as a inner join desglosedineromov as b on (b.cvedesg=a.cve and b.tipo='20' and denomin='$v') where a.fecha='".$datos[0]."' AND a.usu='".$datos[1]."' $filtro group by a.fecha,a.usu,b.denomin") or die(mysql_error());
	$res2=mysql_db_query("$base","select b.denomin,(b.denomin*b.cant) as importe,b.cant as cant from desglosedinero as a inner join desglosedineromov as b on (b.cvedesg=a.cve) where b.cvedesg='".$_POST['reg']."' and b.tipo='20'") or die(mysql_error());
	$cantbol=0;
	$subtotal_bole=0;
	while($row2=mysql_fetch_array($res2)){
		//echo '<tr><td><input type="text" class="textField" name="cant_'.$cantbol.'" value="'.$row2['cant'].'" size="10" onKeyUp="sumabol();"></td>';
		//echo '<td><input type="text" class="textField" name="denomin_'.$cantbol.'" value="'.$row2['denomin'].'" size="10" onKeyUp="sumabol();"></td>';
		//echo '<td><input type="text" class="readOnly" name="imp_'.$cantbol.'" value="'.($row2['denomin']*$row2['cant']).'" size="10" readOnly></td></tr>';
		$cantbol++;
		$varimp.=sprintf("%10s",$row2['denomin']).sprintf("%10s",$row2['cant']).sprintf("%' 10s",number_format($row2['importe'],2))."|";
		$subtotal_bole= $subtotal_bole + $row2['importe'];
	}
	$varimp.=sprintf("%' 20s","Subtotal:").sprintf("%' 10s",number_format($subtotal_bole,2))."|";
	$total+=$subtotal_bole;
//$total+=$row1['denomin'];
	$res1=mysql_db_query($base,"select * from desglosedineromov where cvedesg='".$_POST['reg']."' and tipo='2'");
	$row1=mysql_fetch_array($res1);
	$varimp.=sprintf("%' 20s","Cheques:").sprintf("%' 10s",number_format($row1['denomin'],2))."|";
	$total+=$row1['denomin'];
//$total+=$row1['denomin'];
		$varimp.=sprintf("%' 20s","|");
	$varimp.=sprintf("%' 20s","Total:").sprintf("%' 10s",number_format($total,2));
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row11['empresa']].'&textoimp='.$varimp.'" width=200 height=200></iframe>';
//	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$row['cve']).'" width=200 height=200></iframe>';
	/*echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",100);</script>';
	exit();*/
		$_POST['cmd']=0;
}

if ($_POST['cmd']==2) {
	if($_POST['reg']>0){
		$sSQL="update desglosedinero set fecham='".fechaLocal." ".horaLocal()."',permisionario='".$_POST['permisionario']."',recaudacion='".$_POST['recaudacion']."',usum='".$_SESSION['CveUsuario']."'
			,sector='".$_POST['sector']."' where cve='".$_POST['reg']."'";
		mysql_db_query("$base",$sSQL) or die(mysql_error());
		mysql_db_query($base,"DELETE FROM desglosedineromov WHERE cvedesg='".$_POST['reg']."'");
	}
	else{
		$sSQL="insert desglosedinero set fecha='".$_POST['fechahoy']."',hora='".horaLocal()."',permisionario='".$_POST['permisionario']."',recaudacion='".$_POST['recaudacion']."',usu='".$_SESSION['CveUsuario']."',fecham='".fechaLocal()." ".horaLocal()."',usum='".$_SESSION['CveUsuario']."',empresa='".$_POST['empresa']."',sector='".$_POST['sector']."'";
		mysql_db_query("$base",$sSQL) or die(mysql_error());
		$_POST['reg']=mysql_insert_id();
	}
	$varimp="Desglose de Dinero|";
	$varimp.="Folio: ".$_POST['reg']."|";
	$varimp.="Fecha: ".$_POST['fechahoy']." ".horaLocal()."|";
	$varimp.="Recaudacion: ".$array_recaudacion[$_POST['recaudacion']]."|";
	$varimp.="Beneficiario: ".$array_beneficiario[$_POST['permisionario']]."|";
	$varimp.="Usuario: ".$array_nomusuario[$_SESSION['CveUsuario']]."|";
	$varimp.="Sector: ".$array_tipo_sector[$_POST['sector']]."|";
	$varimp.="Empresa: ".$array_empresa[$_POST['empresa']]."|";
	$varimp.="Efectivo|";
	$varimp.=sprintf("%10s","Denomin").sprintf("%10s","Cant").sprintf("%' 10s","Importe")."|";
	$subtot=0;
	$total=0;
	for($i=0;$i<count($_POST['cant']);$i++){
		$sSQL="insert desglosedineromov set cvedesg='".$_POST['reg']."',denomin='".$_POST['denomin'][$i]."',cant='".$_POST['cant'][$i]."',tipo='0'";
		mysql_db_query("$base",$sSQL) or die(mysql_error());
		if($_POST['denomin'][$i]==14 or $_POST['denomin'][$i]==15 or $_POST['denomin'][$i]==16){
$varimp.=sprintf("%10s",$denominaciones[$_POST['denomin'][$i]]).sprintf("%10s",$_POST['cant'][$i]).sprintf("%' 10s",number_format($_POST['cant'][$i]*1,2))."|";
		$subtot+=round($_POST['cant'][$i]*1,2);
		}else{
     		     $varimp.=sprintf("%10s",$_POST['denomin'][$i]).sprintf("%10s",$_POST['cant'][$i]).sprintf("%' 10s",number_format($_POST['cant'][$i]*$_POST['denomin'][$i],2))."|";
				 $subtot+=round($_POST['cant'][$i]*$_POST['denomin'][$i],2);
			}
		//$subtot+=round($_POST['cant'][$i]*$_POST['denomin'][$i],2);
	}	
	$total+=$subtot;
	$varimp.=sprintf("%' 20s","Subtotal:").sprintf("%' 10s",number_format($subtot,2))."|";
	$sSQL="insert desglosedineromov set cvedesg='".$_POST['reg']."',denomin='".$_POST['denominE']."',cant='".$_POST['cantE']."',tipo='0'";
	mysql_db_query("$base",$sSQL) or die(mysql_error());
	$varimp.="Boletos|";
	$varimp.=sprintf("%10s","Denomin").sprintf("%10s","Cant").sprintf("%' 10s","Importe")."|";
	for($i=0;$i<$_POST['cantbol'];$i++){
		$sSQL="insert desglosedineromov set cvedesg='".$_POST['reg']."',denomin='".$_POST['denomin_'.$i]."',cant='".$_POST['cant_'.$i]."',tipo='20'";
		mysql_db_query("$base",$sSQL) or die(mysql_error());
		$varimp.=sprintf("%10s",$_POST['denomin_'.$i]).sprintf("%10s",$_POST['cant_'.$i]).sprintf("%' 10s",number_format($_POST['denomin_'.$i]*$_POST['cant_'.$i],2))."|";
	}
	$sSQL="insert desglosedineromov set cvedesg='".$_POST['reg']."',denomin='".$_POST['denominD']."',cant='".$_POST['cantD']."',tipo='1'";
	mysql_db_query("$base",$sSQL) or die(mysql_error());
	$varimp.=sprintf("%' 20s","Subtotal:").sprintf("%' 10s",number_format($_POST['denominD'],2))."|";
	$total+=$_POST['denominD'];
	$sSQL="insert desglosedineromov set cvedesg='".$_POST['reg']."',denomin='".$_POST['denominC']."',cant='".$_POST['cantC']."',tipo='2'";
	mysql_db_query("$base",$sSQL) or die(mysql_error());
	$varimp.=sprintf("%' 20s","Cheques:").sprintf("%' 10s",number_format($_POST['denominC'],2))."|";
	$total+=$_POST['denominC'];
	$varimp.=sprintf("%' 20s","|");
	$varimp.=sprintf("%' 20s","Total:").sprintf("%' 10s",number_format($total,2));
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$_POST['empresa']].'&textoimp='.$varimp.'" width=200 height=200></iframe>';
	$sSQL="insert desglosedineromov set cvedesg='".$_POST['reg']."',denomin='".$_POST['denominT']."',cant='".$_POST['cantT']."',tipo='3'";
	mysql_db_query("$base",$sSQL) or die(mysql_error());
	$_POST['cmd']=0;
}
if ($_POST['cmd']==3) {
	$delete= "UPDATE desglosedinero SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()."',horacan='".horaLocal()."' WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_query($delete);
	$_POST['cmd']=0;
}
if($_POST['ajax']==1)
{
	if($_POST['agrupar']==0){
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th width="10%">Folio</th><th width="10%">Fecha</th><th>Empresa</th><th>Recaudacion</th><th>Usuario</th><th>Importe</th>';
		echo '</tr>';
		$n=0;
		$filtro="";
		$fil_rec="";
		$fil_per="";
		//if($_SESSION[$archivo[(count($archivo)-1)]]<=2)
		if(nivelUsuario()>1){
//			$filtro=" and a.usu='".$_SESSION['CveUsuario']."'";
		}
/*		if ($_POST['recaudacion']!="") { $fil_rec=" AND a.recaudacion = ".$_POST['recaudacion'].""; }
		$ss="select a.*,sum(b.denomin*b.cant) as importe from desglosedinero as a inner join 
		desglosedineromov as b on (b.cvedesg=a.cve and b.tipo<20) where a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
		$filtro".$fil_rec." order by a.cve desc";*/
	///	$result=mysql_db_query("$base","select a.*,sum(b.denomin*b.cant) as importe from desglosedinero as a inner join desglosedineromov as b on (b.cvedesg=a.cve and b.tipo<20) where a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' $filtro".$fil_rec." group by a.cve order by a.cve desc") or die(mysql_error());
		//$result=mysql_db_query("$base","select * from recibos where 1 and usuario='$emite' order by cve");
		if ($_POST['recaudacion']!="") { $fil_rec=" AND recaudacion = ".$_POST['recaudacion'].""; }
		if ($_POST['permisionario']!="") { $fil_per=" AND permisionario = ".$_POST['permisionario'].""; }
		$ss="select * from desglosedinero where fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
		$filtro".$fil_rec."".$fil_rec." order by cve desc";
		$suma=0;
//		echo''.$ss.'';
		$result=mysql_db_query($base,$ss)or die(mysql_error());
		while ($row=mysql_fetch_array($result))
		{
			$total=0;
			$ss1="select if(tipo not in(1,2,20),sum(cant*if(tipo not in(1,2,20),denomin,0)),0) as efectivo,sum(cant*if(tipo in(2,20),denomin,0)) as descuento from desglosedineromov where cvedesg='".$row['cve']."' and tipo not in(1)";
		//$ss1="select sum(cant*if(denomin in(14.00,15.00,16.00),1,denomin)) as subtotal from desglosedineromov where cvedesg='".$row['cve']."'";
			
			$result1=mysql_db_query($base,$ss1)or die(mysql_error());
			$row1=mysql_fetch_array($result1);
			rowc();
			//echo '<td align=center><a href="javascript:ventanaSecundaria(\'derecho_piso.php?reg='.$row["cve"].'&cmd=4\')"><img src="imagenes/b_print.png" border=0></a></td>';
			if($row["estatus"]=="C"){
				echo'<td align=center>Cancelado</td>';$row1["subtotal"]=0;
				echo '<td align=center>'.$row["cve"].'</td>';
			}else{
				echo '<td align=center><a href="#" onClick="atcr(\'desglosedinero.php\',\'\',100,'.$row['cve'].')"><img src="images/b_print.png" border="0"></a>
									   <a href="#" onClick="atcr(\'desglosedinero.php\',\'\',3,'.$row['cve'].')"><img src="images/validono.gif" border="0"></a></td>';
									   echo '<td align=center><a href="#" onClick="atcr(\'desglosedinero.php\',\'\',1,'.$row['cve'].')">'.$row["cve"].'</a></td>';
		}
//			echo '<td align=center><a href="#" onClick="atcr(\'desglosedinero.php\',\'\',1,'.$row['cve'].')">'.$row["cve"].'</a></td>';
			echo '<td align="center">'.$row["fecha"].' '.$row['hora'].'</td>';
			echo '<td align="center">'.$array_empresa[$row['empresa']].'</td>';
			echo '<td align="center">'.$array_recaudacion[$row["recaudacion"]].'</td>';
			echo '<td align="center">'.$array_usuario[$row["usu"]].'</td>';
			$total=$row1['efectivo'] + $row1['descuento'];
			if($row["estatus"]=="C"){
			$total=0;
			}
			echo '<td align="right">'.number_format($total,2).'</td>';
			echo '</tr>';
			$suma+=round($total,2);
			//$suma+=round($row1['subtotal'],2);
			$n++;
		}
		echo '<tr bgcolor="#E9F2F8"><th colspan=5 align="left">'.$n.' Registros</th><th align="right">Total&nbsp;</th><th align="right">'.number_format($suma,2).'</th>';
		echo '</table>';
	}
	/*else{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>Imprimit</th><th width="10%">Fecha</th><th>Recaudacions</th><th>Usuario</th><th>Importe</th>';
		echo '</tr>';
		$n=0;
		$filtro="";
		$fil_rec="";
		//if($_SESSION[$archivo[(count($archivo)-1)]]<=2)
		if(nivelUsuario()>1){
	//		$filtro=" and a.usu='".$_SESSION['CveUsuario']."'";
		}
				if ($_POST['recaudacion']!="") { $fil_rec=" AND a.recaudacion = ".$_POST['recaudacion'].""; }
				$ss="select a.*,sum(b.denomin*b.cant) as importe from desglosedinero as a inner join desglosedineromov as b on (b.cvedesg=a.cve and b.tipo<20) where a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' $filtro".$fil_rec." group by a.fecha,a.usu order by a.cve desc";
//		$result=mysql_db_query("$base","select a.*,sum(b.denomin*b.cant) as importe from desglosedineroa as a inner join desglosedineromov as b on (b.cvedesg=a.cve and b.tipo<20) where a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' $filtro".$fil_rec." group by a.fecha,a.usu order by a.cve desc") or die(mysql_error());
		//$result=mysql_db_query("$base","select * from recibos where 1 and usuario='$emite' order by cve");
		echo''.$ss.'';
		$result=mysql_db_query($ss)or die(mysql_error());
		$suma=0;
		while ($row=mysql_fetch_array($result))
		{
			rowc();
			echo '<td align=center><a href="#" onClick="atcr(\'desglosedinero.php\',\'_blank\',100,\''.$row['fecha'].'|'.$row['usu'].'\')"><img src="images/b_print.png" border="0"></a></td>';
			echo '<td align="center">'.$row["fecha"].'</td>';
			echo '<td align="center">'.$array_recaudacion[$row["recaudacion"]].'</td>';
			echo '<td align="center">'.$array_usuario[$row["usu"]].'</td>';
			echo '<td align="right">'.number_format($row["importe"],2).'</td>';
			echo '</tr>';
			$suma+=round($row['importe'],2);
			$n++;
		}
		echo '<tr bgcolor="#E9F2F8"><th colspan="3" align="left">'.$n.' Registros</th><th align="right">Total&nbsp;</th><th align="right">'.number_format($suma,2).'</th>';
		echo '</table>';
	}*/
	exit();
}


top($_SESSION);
if($_POST['cmd']==1)
{	
	$result=mysql_db_query("$base","select * from desglosedinero where cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($result);
	echo '<table><tr>';
//	if($_SESSION[$archivo[(count($archivo)-1)]]>1)
	if(nivelUsuario()>1){
		
		if($_POST['reg']){echo'<td>&nbsp;</td>';}else{echo '<td><a href="#" onClick="if(document.forma.recaudacion.value==\'\')
					alert(\'Necesita Introdicir el Tipo de Recauacion\');
				if(document.forma.empresa.value==\'\')
					alert(\'Necesita Introdicir la Empresa\');
						else{atcr(\'desglosedinero.php\',\'\',2,\''.$row['cve'].'\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';}
	}
	echo '<td><a href="#" onclick="atcr(\'desglosedinero.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
	echo '</tr></table>';
	if($row['fecha']=="") $row['fecha']=fechaLocal();
	echo '<table>';
	$n=0;
	$total=0;
	echo '<tr><th align="left">Fecha</th><td><input type="text" name="fechahoy" id="fechahoy" class="readOnly" size="15" value="'.$row['fecha'].'" readOnly>';
	if($_SESSION[$archivo[(count($archivo)-1)]]>2)
		echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fechahoy,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
	echo '</td></tr>';
	echo '<tr><td>Recaudacion</td><td><select name="recaudacion" id="recaudacion" class="textField"><option value="">---Seleccione---</option>';
		$res1=mysql_query("SELECT * FROM recaudaciones ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'"';if($row['recaudacion']==$row1['cve']){echo'selected';} echo'>'.$row1['nombre'].'</option>';
		}
		echo '</select></td></tr>';
	echo '<tr><td>Beneficiario</td><td><select name="permisionario" id="permisionario" class="textField"><option value="">---Seleccione---</option>';
		$res1=mysql_query("SELECT * FROM beneficiarios ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'"';if($row['permisionario']==$row1['cve']){echo'selected';} echo'>'.$row1['nombre'].'</option>';
		}
		echo '</select></td></tr>';
	echo '<tr><td>Empresa</td><td><select name="empresa" id="empresa" class="textField"><option value="">---Seleccione---</option>';
		$res1=mysql_query("SELECT * FROM empresas ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'"';if($row['empresa']==$row1['cve']){echo'selected';} echo'>'.$row1['nombre'].'</option>';
		}
		echo '</select></td></tr>
			<tr><td>Sector</td><td><select name="sector" id="sector" class="textField"><option value="">---Seleccione---</option>';
		$res1=mysql_query("SELECT * FROM tipo_sector ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'"';if($row['sector']==$row1['cve']){echo'selected';} echo'>'.$row1['nombre'].'</option>';
		}
		echo '</select></td></tr></table>';
	echo '<table><tr><th align="left">Usuario</th><td><b>'.$array_nomusuario[$_SESSION['CveUsuario']].'</b><input type="hidden" name="taquillero" id="taquillero" value="'.$_SESSION['CveUsuario'].'"></td></tr>';
	$n=0;
	$total=0;
	$des="Pesos";
	foreach($denominaciones as $k=>$v){
		$t=$v;
		$t1=$v;
//		$res1=mysql_db_query($base,"select * from desglosedineromov where cvedesg='".$row['cve']."' and denomin='$v' and tipo='0'");
//		$row1=mysql_fetch_array($res1);
		if($k>13){$des="";}
		echo '<tr><th>'.$v.' '.$des.'</th>';
		if($k>13){$t=1; $t1=$k;}
		$con="select * from desglosedineromov where cvedesg='".$row['cve']."' and denomin='".$t1."' and tipo='0'";
		$res1=mysql_db_query($base,$con);
		$row1=mysql_fetch_array($res1);
		echo '<td><input type="text" size="5" name="cant[]" class="textField" id="cant'.$n.'" value="'.$row1['cant'].'" onKeyUp="calcular()">
			<input type="hidden" name="denomin[]" id="denomin'.$n.'" value="'.$t1.'"></td>
			<td><input type="text" size="15" name="imp[]" id="imp'.$n.'" value="'.($row1['cant']*$t).'" class="readOnly" readOnly></td></tr>';
		$total+=($row1['cant']*$t);
		$n++;
	}
	echo '<tr><th colspan="2" align="right">Subtotal:&nbsp;</th><td><input type="text" size="15" class="readOnly" name="subtotal" id="subtotal" value="'.($total).'" readOnly></td></tr>';
	$res1=mysql_db_query($base,"select * from desglosedineromov where cvedesg='".$row['cve']."' and tipo='1'");
	$row1=mysql_fetch_array($res1);
	echo '<tr><th align="right" colspan="2" valign="top">Documento Boletos:&nbsp;</th>
		<td><input type="text" class="readOnly" size="15" name="denominD" value="'.$row1['denomin'].'" onKeyUp="calcular()" readOnly>
		<input type="hidden" name="cantD" value="1">
		<input type="hidden" size="5" name="impD" value="'.($row1['denomin']*1).'" readOnly>&nbsp;<input type="button" value="Detalles" class="textField" onClick="mostrarbol();"><input type="hidden" name="bandera" value="0">
		<div id="capabol'.$row1['usuario'].'" style="display:none;"><table id="tablabol'.$row1['usuario'].'">
		<tr><th>Cantidad</th><th>Denominacion</th><th>Importe</th>';
		$res2=mysql_db_query($base,"select * from desglosedineromov where cvedesg='".$row['cve']."' and tipo='20'");
		$cantbol=0;
		while($row2=mysql_fetch_array($res2)){
			echo '<tr><td><input type="text" class="textField" name="cant_'.$cantbol.'" value="'.$row2['cant'].'" size="10" onKeyUp="sumabol();"></td>';
			echo '<td><input type="text" class="textField" name="denomin_'.$cantbol.'" value="'.$row2['denomin'].'" size="10" onKeyUp="sumabol();"></td>';
			echo '<td><input type="text" class="readOnly" name="imp_'.$cantbol.'" value="'.($row2['denomin']*$row2['cant']).'" size="10" readOnly></td></tr>';
			$cantbol++;
		}
		echo '<tr><td><input type="text" class="textField" name="cant_'.$cantbol.'" value="" size="10" onKeyUp="sumabol();"></td>';
		echo '<td><input type="text" class="textField" name="denomin_'.$cantbol.'" value="" size="10" onKeyUp="sumabol();"></td>';
		echo '<td><input type="text" class="readOnly" name="imp_'.$cantbol.'" value="" size="10" readOnly></td></tr>';
		$cantbol++;
		echo '</table>
		<input type="hidden" name="cantbol" value="'.$cantbol.'">
		<br><input type="button" class="textField" value="Agregar" onClick="agregar()"></div></td></tr>';
	$total+=($row1['denomin']*1);
	$res1=mysql_db_query($base,"select * from desglosedineromov where cvedesg='".$row['cve']."' and tipo='2'");
	$row1=mysql_fetch_array($res1);
	echo '<tr><th align="right" colspan="2">Documento Cheques:&nbsp;</th>
		<td><input type="text" class="textField" size="15" name="denominC" value="'.$row1['denomin'].'" onKeyUp="calcular()">
		<input type="hidden" name="cantC" value="1">
		<input type="hidden" size="5" name="impC" value="'.($row1['denomin']*1).'" readOnly></td></tr>';
	$total+=($row1['denomin']*1);
	echo '<tr><td>&nbsp;</td></tr>
	<tr><th align="right" colspan="2">Total:&nbsp;</th><td><input class="readOnly" type="text" size="15" name="total" id="total" value="'.($total).'" readOnly></td></tr>';
	echo '</table>';
	echo '<script language="javascript">
			function calcular(){
				tot=0;
				for(i=0;i<'.$n.';i++){
				
					
						imp=document.getElementById("cant"+i).value*document.getElementById("denomin"+i).value;
						
					document.getElementById("imp"+i).value=imp.toFixed(2);
					tot+=(imp/1);
					//if(i=='.($n-3).')
						document.getElementById("subtotal").value=tot.toFixed(2);
				}
				tot+=document.forma.denominD.value/1;
				tot+=document.forma.denominC.value/1;
				//totefectivo=(document.forma.totalrec.value/1)-(tot/1);
				//document.forma.denominE.value=totefectivo.toFixed(2);
				document.getElementById("total").value=tot.toFixed(2);
			}
			
			function sumabol(){
				totalo=0;
				for(i=0;i<(document.forma.cantbol.value/1);i++){
					impo=(document.forma["cant_"+i].value/1)*(document.forma["denomin_"+i].value/1);
					document.forma["imp_"+i].value=impo.toFixed(2);
					totalo+=(impo/1);
				}
				document.forma.denominD.value=totalo.toFixed(2);
				calcular();
			}
			
			function mostrarbol(){
				if(document.forma["bandera"].value=="0"){
					$("#capabol").show();
					document.forma["bandera"].value="1";
				}
				else{
					$("#capabol").hide();
					document.forma["bandera"].value="0";
				}
			}
			
			function agregar(){
				var tblBody = document.getElementById("tablabol").getElementsByTagName("TBODY")[0];
				var lastRow = tblBody.rows.length;
				var iteration = document.forma["cantbol"].value;
				var newRow = tblBody.insertRow(lastRow);
				var newCell0 = newRow.insertCell(0);
				newCell0.innerHTML = \'<input type="text" class="textField" name="cant_\'+iteration+\'" value="" size="10" onKeyUp="sumabol();">\';
				var newCell1 = newRow.insertCell(1);
				newCell1.innerHTML = \'<input type="text" class="textField" name="denomin_\'+iteration+\'" value="" size="10" onKeyUp="sumabol();">\';
				var newCell2 = newRow.insertCell(2);
				newCell2.innerHTML = \'<input type="text" class="readOnly" name="imp_\'+iteration+\'" value="" size="10" readOnly>\';
				iteration++;
				document.forma["cantbol"].value=iteration;
			}
			
			function traeMontoRecaudado(){
				objeto1=crearObjeto();
				if (objeto1.readyState != 0) {
					alert("Error: El Navegador no soporta AJAX");
				} else {
					objeto1.open("POST","desglosedinero.php",true);
					objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto1.send("ajax=2&taquillero="+document.forma.taquillero.value+"&taquilla="+document.forma.taquilla.value+"&fecha="+document.forma.fechahoy.value);
					objeto1.onreadystatechange = function(){
						if (objeto1.readyState==4){
							//alert(objeto1.responseText);
							document.getElementById("datostaq").innerHTML=objeto1.responseText
							//document.forma.denominE.value=document.forma.totalrec.value;
						}
					}
				}
			}
		  </script>';
}


	if ($_POST['cmd']<1) {
		if(trim($impresion)!="") echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>';
			echo '<td><a href="#" onclick="atcr(\'desglosedinero.php\',\'\',1,0);"><img src="images/nuevo.gif" border="0">&nbsp;&nbsp;Ingresar Dinero del Dia</a>&nbsp;&nbsp;</td>';
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
		echo '<tr><td>Recaudacion</td><td><select name="recaudacion" id="recaudacion" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_recaudacion as $k=>$v) { 
	    echo '<option value="'.$k.'"';echo'>'.$v.'</option>';
}
		echo '</tr><tr><td>Beneficiario</td><td><select name="permisionario" id="permisionario" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_beneficiario as $k=>$v) { 
	    echo '<option value="'.$k.'"';echo'>'.$v.'</option>';
}
		echo '</tr><!--<tr><td>Empresa</td><td><select name="empresa" id="empresa" class="textField"><option value="">---Seleccione---</option>';
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
						objeto.open("POST","desglosedinero.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=1&permisionario="+document.getElementById("permisionario").value+"&plaza="+document.getElementById("searchplaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&recaudacion="+document.getElementById("recaudacion").value);
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
