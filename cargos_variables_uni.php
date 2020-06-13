<?
include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsUsuario=mysql_db_query($base,"SELECT * FROM ".$pre."usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsMotivos=mysql_db_query($base,"SELECT * FROM ".$pre."cat_cargos_variables");
while($Motivo=mysql_fetch_array($rsMotivos)){
	$array_motivo[$Motivo['cve']]=$Motivo['nombre'];
}

$rsconductor=mysql_db_query($base,"SELECT * FROM ".$pre."parque");
while($Conductor=mysql_fetch_array($rsconductor)){
	$array_parque[$Conductor['cve']]=$Conductor['no_eco'];
}

$sta_var=array('A'=>"ACTIVO",'C'=>"CANCELADO");	

if($_POST['cmd']==100){
	echo '<h2>Cargos Variables de Unidades</h2>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$col=6;
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Folio</th><th>Motivo</th><th>Fecha</th><th>Fecha Aplicacion</th><th>Unidad</th><th>Deposito Inicial</th><th>Total</th>';
	echo '</tr>';
	$i=0;
	$x=0;
	$deposito_inicial=0;
	$total=0;
	$saldototal=0;
	$abono=0;
	for($z=0;$z<count($_POST['sel']);$z++) {
		$rsCargos=mysql_db_query($base,"SELECT * FROM ".$pre."cargos_variables_unidades WHERE cve='".$_POST['sel'][$z]."'");
		$Cargos=mysql_fetch_array($rsCargos);
		$estatus="";
		if($Cargos['sta']=="D") $estatus="&nbsp;(Devuelto)";
		elseif($Cargos['sta']=="C") $estatus="&nbsp;(Cancelado)";
		rowb();
		echo '<td align="center">'.$Cargos['cve'].$estatus.'</td>';
		echo '<td align="left">'.$array_motivo[$Cargos['motivo']].'</td>';
		echo '<td align="center">'.$Cargos['fecha'].'</td>';
		echo '<td align="center">'.$Cargos['fecha_ini'].'</td>';
		echo '<td align="left">'.$array_parque[$Cargos['unidad']].'</td>';
		if($Cargos['sta']=="C"){
			$Cargos['deposito_inicial']=0;
			$Cargos['total']=0;
		}
		echo '<td align="right">'.number_format($Cargos['deposito_inicial'],2).'</td>';
		echo '<td align="right">'.number_format(($Cargos['total']),2).'</td>';
		echo '</tr>';
		$i++;
		$deposito_inicial+=$Cargos['deposito_inicial'];
		$total+=$Cargos['total'];
		$abono+=$Cargo['abonos'];
		$saldototal+=($Cargos['total']-$Cargo['abonos']);
	}
	echo '	
		<tr>
		<td colspan="5" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
		<td align="right" bgcolor="#E9F2F8"><span id="dep2">'.number_format($deposito_inicial,2).'</span></td>
		<td align="right" bgcolor="#E9F2F8"><span id="tot2">'.number_format($total,2).'</span></td>
		</tr>
	</table>';

	exit();	
}

if($_POST['cmd']==101){
	$i=0;
	$x=0;
	$deposito_inicial=0;
	$total=0;
	$saldototal=0;
	$abono=0;
	for($z=0;$z<count($_POST['sel']);$z++) {
		$rsCargos=mysql_db_query($base,"SELECT * FROM ".$pre."cargos_variables_unidades WHERE cve='".$_POST['sel'][$z]."'");
		$Cargos=mysql_fetch_array($rsCargos);
		$fecha=$Cargos['fecha'];
		$fecha_ini=$Cargos['fecha_ini'];
		$montototal=$Cargos['total'];
		$fecha_fin=$Cargos['fecha_fin'];
		$motivo=$Cargos['motivo'];
		$concepto=$Cargos['concepto'];
		$deposito_inicial=$Cargos['deposito_inicial'];
		$obs=$Cargos['obs'];
		$sta=$Cargos['sta'];
		echo '<br>';
		echo '<table>';
		echo '<tr><td class="tableEnc"><h3>Cargo por Convenio Folio# '.$Cargos['cve'].' de la unidad '.$array_parque[$Cargos['unidad']].'</h3></td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><th align="left">Deposito Inicial $</th><td>'.number_format($deposito_inicial,2).'</td></tr>';
		echo '<tr><th align="left">Fecha</th><td>'.$fecha.'</td></tr>';
		echo '<tr><th align="left">Fecha Aplicacion</th><td>'.$fecha_ini.'</td></tr>';
		echo '<tr><th align="left">Monto Total de la Diferencia de la Deuda:</th><td>'.number_format($montototal,2).'</td></tr>';
		echo '<tr><th align="left">Monto Total: $</th><td>'.number_format($montototal,2).'</td></tr>';
		echo '<tr><th align="left">Motivo:</th><td>'.$array_motivo[$motivo].'</td></tr>';
		echo '<tr><th align="left">Concepto:</th><td>'.$concepto.'<br></td></tr>';
		echo '<tr><th align="left">Observaciones:</th><td>'.$obs.'<br></td></tr>';
		echo '</table><br><br>';
	}

	exit();	
}


if($_POST['ajax']==1){
	$filtro="";
	if(trim($_POST['nom'])!="") $filtro=" AND b.no_eco='".($_POST['nom'])."'";
	$select= " SELECT a.* FROM ".$pre."cargos_variables_unidades as a 
			INNER JOIN ".$pre."parque as b ON (b.cve=a.unidad $filtro)
			WHERE 1 ";
	if ($_POST['motivo']!="all") { $select.=" AND a.motivo='".$_POST['motivo']."'"; }
	if ($_POST['estatus']!="all") { $select.=" AND a.sta='".$_POST['estatus']."'"; }
	$select.=" ORDER BY a.cve DESC";
	//echo $select;
	$rsCargos=mysql_db_query($base,$select) or die(mysql_error());
	if(mysql_num_rows($rsCargos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$col=6;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.mysql_num_rows($rsCargos).' Registro(s)</td>
			<td align="right" bgcolor="#E9F2F8"><span id="dep1">'.number_format($deposito_inicial,2).'</span></td>
			<td align="right" bgcolor="#E9F2F8"><span id="tot1">'.number_format($total,2).'</span></td><td bgcolor="#E9F2F8">&nbsp;</td></tr>';
		echo '<tr bgcolor="#E9F2F8">';
		echo '<th><input type="checkbox" name="marcatodos" id="marcartodos" onclick="marcar();" value="">&nbsp;Marcar&nbsp;</th>';
		echo '<th>Folio</th><th>Motivo</th><th>Fecha</th><th>Fecha Aplicacion</th><th>Unidad</th><th>Deposito Inicial</th><th>Total</th><th>Usuario</th>';
		echo '</tr>';
		$i=0;
		$x=0;
		$deposito_inicial=0;
		$total=0;
		$saldototal=0;
		$abono=0;
		while($Cargos=mysql_fetch_array($rsCargos)) {
			rowb();
			echo '<td align="center"><input type="checkbox" name="sel[]" id="sel2'.$x.'" value="'.$Cargos['cve'].'"></td>';
			$x++;
			$estatus="";
			if($Cargos['sta']=="D") $estatus="&nbsp;(Devuelto)";
			elseif($Cargos['sta']=="C") $estatus="&nbsp;(Cancelado)";
			echo '<td align="center"><a href="#" onClick="atcr(\'cargos_variables_uni.php\',\'\',10,\''.$Cargos['cve'].'\')">'.$Cargos['cve'].$estatus.'</a></td>';
			echo '<td align="left">'.$array_motivo[$Cargos['motivo']].'</td>';
			echo '<td align="center">'.$Cargos['fecha'].'</td>';
			echo '<td align="center">'.$Cargos['fecha_ini'].'</td>';
			echo '<td align="left">'.$array_parque[$Cargos['unidad']].'</td>';
			if($Cargos['sta']=="C"){
				$Cargos['deposito_inicial']=0;
				$Cargos['total']=0;
			}
			echo '<td align="right">'.number_format($Cargos['deposito_inicial'],2).'</td>';
			echo '<td align="right">'.number_format(($Cargos['total']),2).'</td>';
			echo '<td align="left">'.$array_usuario[$Cargos['usuario']].'</td>';
			echo '</tr>';
			$i++;
			$deposito_inicial+=$Cargos['deposito_inicial'];
			$total+=$Cargos['total'];
			$abono+=$Cargo['abonos'];
			$saldototal+=($Cargos['total']-$Cargo['abonos']);
		}
		echo '	
			<tr>
			<td colspan="'.$col.'" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
			<td align="right" bgcolor="#E9F2F8"><span id="dep2">'.number_format($deposito_inicial,2).'</span></td>
			<td align="right" bgcolor="#E9F2F8"><span id="tot2">'.number_format($total,2).'</span></td>
			<td  bgcolor="#E9F2F8">&nbsp;</td>
			</tr>
		</table>';
		echo '<input type="hidden" name="numsels" id="numsels" value="'.$x.'">';
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
		$select= " SELECT * FROM ".$pre."parque WHERE 1 ";
		if ($_POST['estatus']!="all") { $select.=" AND estatus='".$_POST['estatus']."'"; }
		if ($_POST['no_eco']!="") { $select.=" AND no_eco='".$_POST['no_eco']."'"; }
		if ($_POST['fecha_ini']!="") { $select.=" AND fecha_sta>='".$_POST['fecha_ini']."'"; }
		if ($_POST['fecha_fin']!="") { $select.=" AND fecha_sta<='".$_POST['fecha_fin']."'"; }
		$select.=" ORDER BY no_eco";
		$res=mysql_db_query($base,$select);
		$totalRegistros = mysql_num_rows($res);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table border="0" cellpadding="4" cellspacing="1" class="">';
			$col=3;
			echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8">';
			echo '<th><input type="checkbox" name="marcatodos" id="marcartodos" onclick="marcar();" value="">&nbsp;Marcar&nbsp;</th>';
			echo '<th>Unidad</th><th>Estatus</th>';
			echo '</tr>';
			$i=0;
			$x=0;
			$cargos=0;
			$abonos=0;
			$saldo_favor=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
			
				if($row['estatus']==1){
					echo '<td align="center"><input type="checkbox" name="sel[]" id="sel2'.$x.'" value="'.$row['cve'].'"></td>';
					$x++;
				}
				else{
					echo '<td>&nbsp;</td>';
				}
				echo '<td align="left">'.$row['no_eco'].'</td>';
				echo '<td align="center">'.$array_estatus_parque[$row['estatus']].'</td>';
			}
			$col=3;
			echo '	
				<tr>
				<td colspan="'.$col.'" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
				</tr>
			</table>';
			echo '<input type="hidden" name="numsels" id="numsels" value="'.$x.'">';
		}
		else {
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

if($_POST['cmd']==10){
	$rsCargos=mysql_db_query($base,"SELECT * FROM ".$pre."cargos_variables_unidades WHERE cve='".$_POST['reg']."'");
	$Cargos=mysql_fetch_array($rsCargos);
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="atcr(\'cargos_variables_uni.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
	if(nivelUsuario()>1){		
		echo '<td><a href="#" onclick="
				if(document.forma.sta.value!=\'A\')
					alert(\'El Cargo ya no esta Activo\');
				else if(confirm(\'¿Esta seguro de cancelar el cargo?\')){
					resp=prompt(\'Observacion:\');
					atcr(\'cargos_variables_uni.php?obs=\'+resp,\'\',3,\''.$_POST['reg'].'\');
				}
				"><img src="images/validono.gif" border="0">&nbsp;&nbsp;Cancelar</a></td>';
	}
	echo '
		  </tr>';
	echo '</table>';

	
	if(mysql_num_rows($rsCargos)>1){
		echo '<br>';
		echo '<table>';
		echo '<tr><td class="tableEnc">Cargo Global por Convenio Folio# '.$_POST['reg'].'</td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><th colspan=2>Aplicar Cargo a Unidades</th></tr>';
		while($Cargos=mysql_fetch_array($rsCargos)){
			rowb();
			echo '<td align=center>'.$array_parque[$Cargos['unidad']].'</td>';
			echo '</tr>';
			$fecha=$Cargos['fecha'];
			$fecha_ini=$Cargos['fecha_ini'];
			$montototal=$Cargos['total'];
			$motivo=$Cargos['motivo'];
			$concepto=$Cargos['concepto'];
			$i++;
		}
		echo '<tr><th>'.$i.' Registros</th></tr>';
		echo '</table><br>';
		echo '<table>';
		echo '<tr><th align="left">Monto Total de la Deuda:</th><td>'.number_format($i*$montototal,2).'</td></tr>';
		echo '<tr><th align="left">Fecha</th><td>'.$fecha.'</td></tr>';
		echo '<tr><th align="left">Fecha Aplicacion</th><td>'.$fecha_ini.'</td></tr>';
		echo '<tr><th align="left">Monto Total: $</th><td>'.number_format($montototal,2).'</td></tr>';
		echo '<tr><th align="left">Motivo:</th><td>'.$array_motivo[$motivo].'</td></tr>';
		echo '<tr><th align="left">Concepto:</th><td>'.$concepto.'<br></td></tr>';
		echo '</table>';
	}
	else{
		
		$fecha=$Cargos['fecha'];
		$fecha_ini=$Cargos['fecha_ini'];
		$montototal=$Cargos['total'];
		$motivo=$Cargos['motivo'];
		$concepto=$Cargos['concepto'];
		$deposito_inicial=$Cargos['deposito_inicial'];
		$obs=$Cargos['obs'];
		$sta=$Cargos['sta'];
		echo '<br>';
		echo '<table>';
		echo '<tr><td class="tableEnc">Cargo por Convenio Folio# '.$Cargos['cve'].' de la unidad '.$array_parque[$Cargos['unidad']].'</td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><th align="left">Deposito Inicial $</th><td>'.number_format($deposito_inicial,2).'</td></tr>';
		echo '<tr><th align="left">Fecha</th><td>'.$fecha.'</td></tr>';
		echo '<tr><th align="left">Fecha Aplicacion</th><td>'.$fecha_ini.'</td></tr>';
		echo '<tr><th align="left">Monto Total de la Diferencia de la Deuda:</th><td>'.number_format($montototal,2).'</td></tr>';
		echo '<tr><th align="left">Monto Total: $</th><td>'.number_format($montototal,2).'</td></tr>';
		echo '<tr><th align="left">Motivo:</th><td>'.$array_motivo[$motivo].'</td></tr>';
		echo '<tr><th align="left">Concepto:</th><td>'.$concepto.'<br></td></tr>';
		echo '<tr><th align="left">Observaciones:</th><td>'.$obs.'<br></td></tr>';
		echo '</table><input type="hidden" name="sta" value="'.$sta.'">';
	}
}

if($_POST['cmd']==3){
	mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Estatus',nuevo='Cancelado',anterior='',arreglo='',usuario='".$_POST['cveusuario']."'");
	mysql_db_query($base,"UPDATE ".$pre."cargos_variables_unidades SET sta='C',obs=CONCAT(obs,', ','Cancelado ".fechaLocal()." por motivo ".$_GET['obs']."') WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
	for ($i=0;$i<count($_POST['sel']);$i++){
		$insert="INSERT INTO ".$pre."cargos_variables_unidades (fecha,fecha_ini,total,motivo,usuario,concepto,unidad,deposito_inicial) 
		values ('".fechaLocal()."','".$_POST['fecha_ini']."','".$_POST['montototal']."',
				'".$_POST['motivo']."','".$_POST['cveusuario']."','".$_POST['concepto']."','".$_POST['sel'][$i]."','".$_POST['deposito_inicial']."')";
		mysql_db_query($base,$insert);
		$variable=mysql_insert_id();
	}
	$_POST['cmd']=0;
}


if($_POST['cmd']==1){
	if($_POST['reg']==0){
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar&nbsp;&nbsp;</a></td>
				<td><a href="#" onclick="validar_seleccion();"><img src="images/nuevo.gif" border="0">&nbsp;&nbsp;Cargo Administrativo</a></td>
				<td><a href="#" onclick="atcr(\'cargos_variables_uni.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus" class="textField"><option value="all">---Todos---</option>';
		foreach($array_estatus_parque as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==1) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td><td></td><td>&nbsp;</td></tr>';
		echo '<tr><td>No Eco</td><td><input type="text" name="no_eco" id="no_eco" class="textField"></td></tr>'; 
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Unidades">';
		echo '</div>';
		
		echo '
		<Script language="javascript">

			function buscarRegistros()
			{
				document.getElementById("Unidades").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
				objeto=crearObjeto();
				if (objeto.readyState != 0) {
					alert("Error: El Navegador no soporta AJAX");
				} else {
					objeto.open("POST","cargos_variables_uni.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=3&estatus="+document.getElementById("estatus").value+"&no_eco="+document.getElementById("no_eco").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
					objeto.onreadystatechange = function()
					{
						if (objeto.readyState==4)
						{document.getElementById("Unidades").innerHTML = objeto.responseText;}
					}
				}
				document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
			}
			
			function marcar()
			{
				if(document.forma.marcatodos.checked)
			 	{
					for (i=0;i<(document.forma.numsels.value/1);i++) 
						document.getElementById(\'sel2\'+i).checked =true;
					document.forma.marcatodos.checked=true;		
					document.forma.marcatodos.value=1;	
				}		
				
				if(document.forma.marcatodos.checked==false)
			 	{
					for (i=0;i<(document.forma.numsels.value/1);i++) 
						document.getElementById(\'sel2\'+i).checked =false;
					document.forma.marcatodos.checked=false;		
					document.forma.marcatodos.value=0;	
				}		
			}
			
			function validar_seleccion()
			{
				sels=0;
				for (i=0;i<(document.forma.numsels.value/1);i++){
					if(document.getElementById(\'sel2\'+i).checked==true)
						sels++;
				}
				if(sels==0)
					alert("Necesita seleccionar un conductor");
				else
					atcr("cargos_variables_uni.php","",1,"1");
			}
		
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	
		</Script>
		';

	}
	if($_POST['reg']>0){
		echo '<table><tr>';
		if(nivelUsuario()>1){
			echo '<td><a href="#" onClick="
			if(document.forma.fecha_ini.value==\'\')
				alert(\'Necesita seleccionar una fecha aplicacion\');
			else if((document.forma.montototal.value/1)<1 && (document.forma.deposito_inicial.value/1)<1)
				alert(\'Necesita ingresar el monto total no puede ser menor a 1\');
			else if(document.forma.motivo.value==\'\')
				alert(\'Necesita seleccionar el motivo\');
			else
				atcr(\'cargos_variables_uni.php\',\'\',2,\'0\');
			"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		}
		echo '<td><a href="#" onclick="atcr(\'cargos_variables_uni.php\',\'\',1,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
		echo '</tr></table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><td class="tableEnc">Cargo Administrativo</td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><th colspan=2>Aplicar Cargo a Unidades</th></tr>';
		for ($i=0;$i<count($_POST['sel']);$i++){
			rowb();
			echo '<td align=left>'.$array_parque[$_POST['sel'][$i]].'</td>';
			echo '<input type="hidden" name="sel[]" id="sel2'.$x.'" value="'.$_POST['sel'][$i].'">';
			echo '</tr>';
		}
		$rsConductor=mysql_db_query($base,"SELECT * FROM ".$pre."parque WHERE cve='".$_POST['sel'][0]."'");
		$Conductor=mysql_fetch_array($rsConductor);
		$class="textField";
		$tipo="";
		if(count($_POST['sel'])>1){
			$class="readOnly";
			$tipo="readonly";
		}
		echo '<tr><th>'.$i.' Registros</th></tr>';
		echo '</table><br>';
		echo '<table>';
		echo '<tr><th align="left">Fecha</th><td>'.fechaLocal().'</td></tr>';
		echo '<tr><th align="left">Monto Total del Cargo:</th><td><input type="text" class="textField" name="montototaldeuda" id="montototaldeuda" value="" onblur="calcula();"></td></tr>';
		echo '<tr><th align="left">Deposito Inicial:</th><td><input type="text" class="'.$class.'" name="deposito_inicial" id="deposito_inicial" value="" onblur="calcula();" '.$tipo.'> <small>Solo se habilita cuando se genera el cargo a solo un conductor</th></td></tr>';
		echo '<tr><th align="left">Diferencia $</th><td><input  type="text" class="readOnly" name="montototal" id="montototal" value="" readonly></td></tr>';
		echo '<tr><th align="left">Fecha Aplicacion</th><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" value="'.fechaLocal().'" class="readOnly" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Motivo</th><td><select name="motivo" id="motivo" class="textField"><option value="">--Seleccione--</option>';
		$result=mysql_db_query("$base","SELECT * FROM ".$pre."cat_cargos_variables WHERE 1 ORDER BY nombre");
		while($rowx=mysql_fetch_array($result)){
			echo '<option value="'.$rowx['cve'].'">'.$rowx['nombre'].'</option>';
		}	
		echo '</select></td></tr>';
		echo '<tr><th align="left">Concepto:</th><td><textarea cols=60 rows=4 name="concepto" class="textField"></textarea><br></td></tr>';
		echo '</table>';
		echo '<script>
				function calcula(){
					var t4=0;
					t4=(document.forma.montototaldeuda.value/1)-(document.forma.deposito_inicial.value/1);
					document.forma.montototal.value=t4;
				}
				
			 </script>';
	}

}


if($_POST['cmd']<1){
	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a></td><td>&nbsp;</td>
				<td><a href="#" onclick="atcr(\'cargos_variables_uni.php\',\'\',1,\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;&nbsp;Nuevo Cargo Administrativo</a></td>
				<td><a href="#" onclick="atcr(\'cargos_variables_uni.php\',\'_blank\',\'100\',\'\');"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir Listado</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'cargos_variables_uni.php\',\'_blank\',\'101\',\'\');"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir Detalle</a>&nbsp;&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>No Eco</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
		echo '<tr><td align="left">Motivo</td><td><select name="motivo" id="motivo" class="textField"><option value="all">--Todos--</option>';
		$result=mysql_db_query("$base","SELECT * FROM ".$pre."cat_cargos_variables WHERE 1 ORDER BY nombre");
		while($rowx=mysql_fetch_array($result)){
			echo '<option value="'.$rowx['cve'].'">'.$rowx['nombre'].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus" class="textField"><option value="all">---Todos---</option>';
		foreach($sta_var as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}


echo '
<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","cargos_variables_uni.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nombre").value+"&motivo="+document.getElementById("motivo").value+"&estatus="+document.getElementById("estatus").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					document.getElementById("Resultados").innerHTML = objeto.responseText;
					document.getElementById("dep1").innerHTML = document.getElementById("dep2").innerHTML;
					document.getElementById("tot1").innerHTML = document.getElementById("tot2").innerHTML;
				}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	
	</Script>
';
}
bottom();

?>