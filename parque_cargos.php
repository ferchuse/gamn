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

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		//Listado de tecnicos y administradores
		$select= " SELECT * FROM ".$pre."parque WHERE 1 ";
		if ($_POST['no_eco']!="") { $select.=" AND no_eco='".$_POST['no_eco']."' "; }
		if ($_POST['serie']!="") { $select.=" AND serie='".$_POST['serie']."' "; }
		if ($_POST['propietario']!="all") { $select.=" AND propietario='".$_POST['propietario']."' "; }
		if ($_POST['estatus']!="all") { $select.=" AND estatus='".$_POST['estatus']."' "; }
		if ($_POST['derrotero']!="all") { $select.=" AND derrotero='".$_POST['derrotero']."' "; }
		$res=mysql_db_query($base,$select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY no_eco";
		$res=mysql_db_query($base,$select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>No Eco</th><th>Propietario</th><th>Estatus</th>';
			foreach($array_cargos_unidades as $cargo){
				echo '<th>'.$cargo.'</th>';
			}
			echo '</tr>';
			$i=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center">'.htmlentities($row['no_eco']).'</td>';
				echo '<td>'.htmlentities($array_propietario[$row['propietario']]).'</td>';
				echo '<td align="center">'.htmlentities($array_estatus_parque[$row['estatus']]).'</td>';
				foreach($array_cargos_unidades as $cve=>$cargo){
					echo '<td align="center"><input type="text" class="textField" size="10" id="car'.$i.'" value="'.$row['cargo_'.$cve].'" onKeyUp="if(event.keyCode==13){ cambia_cargo('.$cve.','.$row['cve'].',this.value,'.($i+1).');}"></td>';
					$i++;
				}
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
	$res=mysql_db_query($base,"SELECT cve FROM ".$pre."cargos_parque WHERE unidad='".$_POST['unidad']."' AND motivo='".$_POST['motivo']."' AND fecha='".substr(fechaLocal(),0,8)."01'");
	if($row=mysql_fetch_assoc($res)){
		if($_POST['monto']>0)
			mysql_db_query($base,"UPDATE ".$pre."cargos_parque SET monto = '".$_POST['monto']."' WHERE cve='".$row['cve']."'");
		else
			mysql_db_query($base,"DELETE FROM ".$pre."cargos_parque WHERE cve='".$row['cve']."'");
	}
	elseif($_POST['monto']>0){
		mysql_db_query($base,"INSERT ".$pre."cargos_parque SET fecha='".substr(fechaLocal(),0,8)."01', hora='".horaLocal()."', unidad='".$_POST['unidad']."', motivo='".$_POST['motivo']."', variable=0, monto = '".$_POST['monto']."'");
	}
	$res=mysql_db_query($base,"SELECT cargo_".$_POST['motivo']." FROM ".$pre."parque WHERE cve='".$_POST['unidad']."'");
	$row=mysql_fetch_assoc($res);
	if($row['cargo_'.$_POST['motivo']]!=$_POST['monto']){
		mysql_db_query($base,"INSERT ".$pre."historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['unidad']."',fecha='".fechaLocal()." ".horaLocal()."',
		dato='".$array_cargos_unidades[$_POST['motivo']]."',nuevo='".$_POST['monto']."',anterior='".$row['cargo_'.$_POST['motivo']]."',arreglo='',usuario='".$_POST['cveusuario']."'");
		mysql_db_query($base,"UPDATE ".$pre."parque SET cargo_1='".$_POST['monto']."' WHERE cve='".$_POST['unidad']."'");
	}
	exit();
}

top($_SESSION);


/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
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
			objeto.open("POST","parque_cargos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&derrotero="+document.getElementById("derrotero").value+"&serie="+document.getElementById("serie").value+"&propietario="+document.getElementById("propietario").value+"&estatus="+document.getElementById("estatus").value+"&no_eco="+document.getElementById("no_eco").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	function cambia_cargo(motivo,unidad,monto,sig)
	{
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","parque_cargos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&motivo="+motivo+"&unidad="+unidad+"&monto="+monto+"&cveusuario="+document.getElementById("cveusuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4){
					if(document.getElementById("car"+sig) != undefined)
						document.getElementById("car"+sig).focus();
				}
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

