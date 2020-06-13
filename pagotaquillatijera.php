<?php
include("main.php");
$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

if($_POST['ajax']==1){
	$nivelUsuario = nivelUsuario();
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	echo '<th>Folio</th><th>Fecha Captura</th>';
	echo '<th>Fecha Movimiento</th><th>Usuario</th><th>Importe</th><th>Beneficiario</th>
	</tr>'; 
	$x=0;
	$filtroruta="";
	$select="SELECT a.* FROM pagotaquillatijera a ";
	$select.=" WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."'";
	$select.=" ORDER BY a.cve DESC";
	$res=mysql_db_query($base,$select) or die(mysql_error());
	$totales=array();
	while($row=mysql_fetch_array($res)){
		rowb();
		$factor=1;
		echo '<td align="center">';
		if($row['estatus']!='C'){
			echo '<a href="#" onClick="atcr(\'pagotaquillatijera.php\',\'_blank\',10,\''.$row['cve'].'\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>';
			if($nivelUsuario > 2)
				echo '&nbsp;&nbsp;<a href="#" onClick="if(cancelarRegistro('.$row['cve'].', \'pagotaquillatijera.php\', 3)) buscarRegistros();/*if(confirm(\'Esta seguro de cancelar el registro?\')) atcr(\'pagotaquillatijera.php\',\'\',3,\''.$row['cve'].'\')*/"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
		}
		elseif($row['estatus']=='C'){
			echo 'CANCELADO';
			$factor=0;
		}
		echo '</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['fecha'].' '.$row['hora'].'</td>';
		echo '<td align="center">'.$row['fecha_aplicacion'].'</td>';
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		$c=0;
		echo '<td align="right">'.number_format($row['monto']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto']*$factor,2);$c++;
		echo '<td align="center">'.utf8_encode($row['beneficiario']).'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="5" align="left">'.$x.' Registro(s)</th>';
	foreach($totales as $total)
		echo '<th align="right">'.number_format($total,2).'</th>';
	echo '<th>&nbsp;</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['cmd']==10){
	$res = mysql_db_query($base,"SELECT * FROM pagotaquillatijera WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	$texto =chr(27)."@".'|';
	$texto.=chr(27).'!'.chr(10)."PAGO TAQUILLA TIJERA";
	$texto.='||';
	$texto.=chr(27).'!'.chr(40)."FOLIO: ".$_POST['reg'];
	$texto.='|';
	$texto.=fechaLocal()." ".horaLocal();
	$texto.='|';
	$texto.="FECHA:    ".$row['fecha_viaje'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."USUARIO: ".$array_usuario[$row['usuario']];
	$texto.='|';
	$texto.=sprintf("%-10s","FOLIO");
	$texto.=sprintf("%-10s","FOLIO");
	$texto.=sprintf("%-10s","TOTAL");
	$texto.=sprintf("%-10s","IMPORTE");
	$texto.='|';
	$texto.=sprintf("%-10s","INICIAL");
	$texto.=sprintf("%-10s","FINAL");
	$texto.=sprintf("%-10s","BOLETOS");
	$texto.=sprintf("%-10s","");
	$texto.='|';
	$res1=mysql_db_query($base,"SELECT * FROM pagotaquillatijeradetalle WHERE pago='".$_POST['reg']."'");
	while($row1=mysql_fetch_array($res1)){
		$texto.=sprintf("%-10s",$row1['folioini']);
		$texto.=sprintf("%-10s",$row1['foliofin']);
		$texto.=sprintf("%-10s",$row1['cantidad']);
		$texto.=sprintf("%10s",number_format($row1['monto'],2));
		$texto.='|';
	}
	$texto.=chr(27).'!'.chr(10)."TOTAL: ".number_format($row['monto'],2);
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."BENEFICIARIO:| ".$row['beneficiario'];
	$texto.='|';
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&copia=1&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$_POST['reg']).'" width=200 height=200></iframe>';
	
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",4000);</script>';
	exit();
}

if($_POST['ajax']==2){
	$resultado = 0;
	$datos = json_decode($_POST['folios'], true);
	foreach($datos['cantidad'] as $k=>$v){
		if($v>0){
			foreach($datos['cantidad'] as $k1=>$v1){
				if($v1>0){
					if( $k!=$k1 && 
						(($datos['folioini'][$k]>=$datos['folioini'][$k1] && $datos['folioini'][$k]<=$datos['foliofin'][$k1]) || 
						($datos['foliofin'][$k]>=$datos['folioini'][$k1] && $datos['foliofin'][$k]<=$datos['foliofin'][$k1]))
					){
						echo "1";
						exit();
					}
				}
			}
		}
	}
	if($resultado==0){
		foreach($datos['cantidad'] as $k=>$v){
			if($v>0){
				$res = mysql_db_query($base,"SELECT a.cve FROM pagotaquillatijera a INNER JOIN pagotaquillatijeradetalle b ON a.cve = b.pago WHERE a.estatus!='C' AND (b.folioini BETWEEN '".$datos['folioini'][$k]."' AND '".$datos['foliofin'][$k]."' OR b.foliofin BETWEEN '".$datos['folioini'][$k]."' AND '".$datos['foliofin'][$k]."'");
				if($row = mysql_fetch_array($res)){
					echo "1";
					exit();
				}
			}
		}
	}

	exit();
}
if($_POST['ajax']==3){
	if($_POST['clavecancelacion'] == $clavecancelacion){
		mysql_db_query($base,"UPDATE pagotaquillatijera SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['cveusuario']."' WHERE cve='".$_POST['reg']."'");
	}
	else{
		echo '1';
	}
	exit();
}

top($_SESSION);

if($_POST['cmd']==3){
	mysql_db_query($base,"UPDATE pagotaquillatijera SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['cveusuario']."' WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
	mysql_db_query($base,"INSERT pagotaquillatijera SET fecha_aplicacion='".$_POST['fecha_aplicacion']."',monto='".$_POST['monto']."',
		fecha='".fechaLocal()."',beneficiario='".$_POST['beneficiario']."',hora='".horaLocal()."',
		usuario='".$_POST['cveusuario']."',estatus='A'") or die(mysql_error());
	$cverecaudacion=mysql_insert_id();
	foreach($_POST['cantidad'] as $k=>$v){
		if($v>0){
			mysql_db_query($base,"INSERT pagotaquillatijeradetalle SET pago='$cverecaudacion',folioini='".$_POST['folioini'][$k]."',foliofin='".$_POST['foliofin'][$k]."',cantidad='$v',monto='".$_POST['montob'][$k]."'");
		}
	}
	$res = mysql_db_query($base,"SELECT * FROM pagotaquillatijera WHERE cve='".$cverecaudacion."'");
	$row = mysql_fetch_array($res);
	$texto =chr(27)."@".'|';
	$texto.=chr(27).'!'.chr(10)."PAGO TAQUILLA TIJERA";
	$texto.='||';
	$texto.=chr(27).'!'.chr(40)."FOLIO: ".$cverecaudacion;
	$texto.='|';
	$texto.=fechaLocal()." ".horaLocal();
	$texto.='|';
	$texto.="FECHA:    ".$row['fecha_viaje'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."USUARIO: ".$array_usuario[$row['usuario']];
	$texto.='|';
	$texto.=sprintf("%-10s","FOLIO");
	$texto.=sprintf("%-10s","FOLIO");
	$texto.=sprintf("%-10s","TOTAL");
	$texto.=sprintf("%-10s","IMPORTE");
	$texto.='|';
	$texto.=sprintf("%-10s","INICIAL");
	$texto.=sprintf("%-10s","FINAL");
	$texto.=sprintf("%-10s","BOLETOS");
	$texto.=sprintf("%-10s","");
	$texto.='|';
	$res1=mysql_db_query($base,"SELECT * FROM pagotaquillatijeradetalle WHERE pago='".$cverecaudacion."'");
	while($row1=mysql_fetch_array($res1)){
		$texto.=sprintf("%-10s",$row1['folioini']);
		$texto.=sprintf("%-10s",$row1['foliofin']);
		$texto.=sprintf("%-10s",$row1['cantidad']);
		$texto.=sprintf("%10s",number_format($row1['monto'],2));
		$texto.='|';
	}
	$texto.=chr(27).'!'.chr(10)."TOTAL: ".number_format($row['monto'],2);
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."BENEFICIARIO:| ".$row['beneficiario'];
	$texto.='|';
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$_POST['empresa']].'&copia=1&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$_POST['reg']).'" width=200 height=200></iframe>';
	$_POST['cmd']=0;
}


if($_POST['cmd']==1){
	echo '<table>';
	echo '<tr>';
	if(nivelUsuario()>1 && $_POST['reg']==0)
		echo '<td><a href="#" onClick="
				if(!validarfolios()){
					alert(\'Hay folios ya capturados\');
				}
				else{
					atcr(\'pagotaquillatijera.php\',\'\',\'2\',\''.$_POST['reg'].'\');
				}
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="atcr(\'pagotaquillatijera.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	if(nivelUsuario()>2){echo '<tr><th align="left">Fecha</th><td><input type="text" class="readOnly" name="fecha_aplicacion" id="fecha_aplicacion" value="'.fechaLocal().'" size="15" readOnly>';
		echo'&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_aplicacion,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
		echo'</td></tr>';
	}else{
		echo '<input type="hidden" class="readOnly" name="fecha_aplicacion" id="fecha_aplicacion" value="'.fechaLocal().'" size="15" readOnly>';
	}
	echo '<tr><td colspan="2"><table id="folios"><tr><th>Folio Inicial</th><th>Folio Final</th><th>Cantidad</th><th>Importe</th></tr>';
	echo '<tr ren="0">
	<td align="center"><input type="text" class="textField" size="10" name="folioini[]" id="folioini_0" value="" onKeyUp="calcular_cantidad(0)"></td>
	<td align="center"><input type="text" class="textField" size="10" name="foliofin[]" id="foliofin_0" value="" onKeyUp="calcular_cantidad(0)"></td>
	<td align="center"><input type="text" class="readOnly" size="10" name="cantidad[]" id="cantidad_0" value="" readOnly></td>
	<td align="center"><input type="text" class="readOnly montos" size="10" name="montob[]" id="montob_0" value="" onKeyUp="calcular()" readOnly></td>
	</tr>';

	echo '</table><br><input type="button" value="Agregar" onClick="agregar()"><input type="hidden" id="ren" value="1"></td></tr>';
	echo '<tr><th align="left">Total</td><td><input type="text" class="readOnly" size="10" name="monto" id="monto" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Beneficiario</td><td><input type="text" class="textField" size="50" name="beneficiario" id="beneficiario" value=""></td></tr>';

	echo '</table>';
	echo '<script>
			function validarfolios(){
				regresar = true;
				$.ajax({
					url: "pagotaquillatijera.php",
				  	type: "POST",
				  	async: false,
				  	data: {
						folios: JSON.stringify($("#folios").serializeForm()),
						ajax: 2
				  	},
				  	success: function(data) {
				  		if(data=="1")
				  			regresar = false;
				  	}
				});
				return regresar;
			}

			function agregar(){
				reng = document.getElementById("ren").value;
				$("#folios").append(\'<tr ren="\'+reng+\'">\
					<td align="center"><input type="text" class="textField" size="10" name="folioini[]" id="folioini_\'+reng+\'" value="" onKeyUp="calcular_cantidad(\'+reng+\')"></td>\
					<td align="center"><input type="text" class="textField" size="10" name="foliofin[]" id="foliofin_\'+reng+\'" value="" onKeyUp="calcular_cantidad(\'+reng+\')"></td>\
					<td align="center"><input type="text" class="readOnly" size="10" name="cantidad[]" id="cantidad_\'+reng+\'" value="" readOnly></td>\
					<td align="center"><input type="text" class="readOnly montos" size="10" name="montob[]" id="montob_\'+reng+\'" value="" onKeyUp="calcular()" readOnly></td>\
					</tr>\');
				reng++;
				document.getElementById("ren").value=reng;
			}

			function calcular_cantidad(reng){
				if((document.getElementById("folioini_"+reng).value/1)>0 && (document.getElementById("foliofin_"+reng).value/1)>0 && (document.getElementById("folioini_"+reng).value/1)<=(document.getElementById("foliofin_"+reng).value/1))
				{
					cantidad = (document.getElementById("foliofin_"+reng).value/1)+1-(document.getElementById("folioini_"+reng).value/1);
					document.getElementById("cantidad_"+reng).value=cantidad;
					$("#montob_"+reng).removeClass("readOnly").removeAttr("readOnly").addClass("textField");
				}
				else{
					document.getElementById("cantidad_"+reng).value="";
					$("#montob_"+reng).removeClass("textField").attr("readOnly","readOnly").addClass("readOnly").val("");	
				}
				calcular();
			}

			function calcular(){
				var total=0;
				$(".montos").each(function(){
					total+=this.value/1;
				});
				document.forma.monto.value=total.toFixed(2);
			}
		</script>';
}

if($_POST['cmd']==0){


	if($impresion!=""){
		echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
	}
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'pagotaquillatijera.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	
	echo '</table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';

echo '
<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","pagotaquillatijera.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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
}

bottom();

?>