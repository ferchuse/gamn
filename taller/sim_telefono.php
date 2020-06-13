<?php
session_start();
include ("main.php");
/*$rsMotivo=mysql_query("SELECT * FROM empresas ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_empresa[$Motivo['id']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM opciones");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_opcion[$Motivo['id']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM  economicos where empresa='".$_POST['cveempresa']."' and estatus='1'");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_economico[$Motivo['id']]=$Motivo['numero'];
	$array_cveeconomico[$Motivo['numero']]=$Motivo['id'];
}
$rsMotivo=mysql_query("SELECT * FROM  operadores where empresa='".$_POST['cveempresa']."' and estatus='1'");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_operador[$Motivo['id']]=$Motivo['numero'];
	$array_cveoperador[$Motivo['numero']]=$Motivo['id'];
}*/
$rsMotivo=mysql_query("SELECT * FROM cat_marca ORDER BY cve");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_marca[$Motivo['cve']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM cat_modelo ORDER BY cve");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_modelo[$Motivo['cve']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM cat_plataforma ORDER BY cve");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_plataforma[$Motivo['cve']]=$Motivo['nombre'];
}


mysql_select_db('gamn_gps_skymedia');
$rsMotivo=mysql_query("SELECT * FROM gps_objects ORDER BY id");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_dispositivo[$Motivo['id']]=$Motivo['dispositivo'].' - '.$Motivo['imei'];
}
mysql_select_db('gamn_gps');
$rsMotivo=mysql_query("SELECT * FROM usuarios ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_personal[$Motivo['cve']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM cat_empresa ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_empresa[$Motivo['cve']]=$Motivo['nombre'];
}
$array_estatus=array(1=>'Alta',2=>'Baja');
if($_POST['ajax']==1){


	//	$select= " SELECT * FROM sms_comandos WHERE plaza = '{$_SESSION['plaza_seleccionada']}'";
	$select= " SELECT * FROM sim_telefono WHERE 1";
	if($_POST['numero']!="") $select .= " AND cve like '%".$_POST['numero']."'%";
//	else if($_POST['apodo']!=0) $select .= " AND id = '".$_POST['apodo']."'";
	//if($_POST['empresa']!="") $select .= " AND empresa= '".$_POST['empresa']."'";
	$select .= " ORDER BY cve asc";
	$res=mysql_db_query($base,$select) or die(mysql_error());
	//echo''.$select.'';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8">
		  <th width="50"></th><th>N°</th><th>Imei</th><th>Empresa</th><th>Codigo Bueno</th><th>Serie</th><th>Telefono</th>
		  </tr>';
	while($row=mysql_fetch_array($res)){
		rowb();
		echo'<td align="center"><a href="#" onClick="atcr(\'sim_telefono.php\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
//	echo'<td align="left"></td>';
		echo'<td align="left">'.$row['cve'].'</td>';
		echo'<td align="left">'.$row['imei'].'</td>';
		echo'<td align="left">'.$array_empresa[$row['empresa']].'</td>';
		echo'<td align="left">'.$row['cod_bueno'].'</td>';
		echo'<td align="left">'.$row['serie'].'</td>';
		echo'<td align="left">'.$row['telefono'].'</td>';

		echo'</tr>';
	}
	echo'<tr bgcolor="#E9F2F8"><td align="left" colspan="7">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table>';
exit();
}

 top($_SESSION);
  if($_POST['cmd']==2){
	  $str_error = '';
	if ($_POST['reg']>0){
        $select=" SELECT * FROM sim_telefono WHERE cve='".$_POST['reg']."' ";
       $rsprovedor=mysql_db_query($base,$select);
       $Usuario=mysql_fetch_array($rsprovedor);
	   if($Usuario['empresa']!=$_POST['empresa']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Empresa',nuevo='".$_POST['empresa']."',anterior='".$Usuario['empresa']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['cod_bueno']!=$_POST['cod_bueno']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Codigo Bueno',nuevo='".$_POST['cod_bueno']."',anterior='".$Usuario['cod_bueno']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['serie']!=$_POST['serie']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Serie',nuevo='".$_POST['serie']."',anterior='".$Usuario['serie']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['telefono']!=$_POST['telefono']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Telefono',nuevo='".$_POST['telefono']."',anterior='".$Usuario['telefono']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['dispositivo']!=$_POST['dispositivo']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Dispositivo',nuevo='".$_POST['dispositivo']."',anterior='".$Usuario['dispositivo']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['marca']!=$_POST['marca']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Marca',nuevo='".$_POST['marca']."',anterior='".$Usuario['marca']."',arreglo='array_marca',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['imei']!=$_POST['imei']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Imei',nuevo='".$_POST['imei']."',anterior='".$Usuario['imei']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['modelo']!=$_POST['modelo']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Modelo',nuevo='".$_POST['modelo']."',anterior='".$Usuario['modelo']."',arreglo='array_modelo',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['plataforma']!=$_POST['plataforma']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Plataforma',nuevo='".$_POST['plataforma']."',anterior='".$Usuario['plataforma']."',arreglo='array_plataforma',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['obs']!=$_POST['obs']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Obs',nuevo='".$_POST['obs']."',anterior='".$Usuario['obs']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}

		$sSQL="update sim_telefono
				SET empresa='" . $_POST['empresa'] . "',cod_bueno='" . $_POST['cod_bueno'] . "',serie='" . $_POST['serie'] . "',telefono='" . $_POST['telefono'] . "',
				marca='" . $_POST['marca'] . "',imei='" . $_POST['imei'] . "',modelo='" . $_POST['modelo'] . "',plataforma='" . $_POST['plataforma'] . "',obs='" . $_POST['obs'] . "',
				dispositivo='" . $_POST['dispositivo'] . "' where cve='".$_POST['reg']."'";
		mysql_query($sSQL);
	}else{
			$sSQL="INSERT sim_telefono
					SET empresa='" . $_POST['empresa'] . "',cod_bueno='" . $_POST['cod_bueno'] . "',serie='" . $_POST['serie'] . "',telefono='" . $_POST['telefono'] . "',
					marca='" . $_POST['marca'] . "',imei='" . $_POST['imei'] . "',modelo='" . $_POST['modelo'] . "',plataforma='" . $_POST['plataforma'] . "',obs='" . $_POST['obs'] . "',
				dispositivo='" . $_POST['dispositivo'] . "',plaza='".$_POST['plazausuario']."'";
			mysql_query($sSQL);
			$cveusu=mysql_insert_id();
	}

	if($str_error != '')
		$_POST['cmd'] = 1;
	else
		$_POST['cmd']=0;
}

if($_POST['cmd']==1){

	    $select=" SELECT * FROM sim_telefono WHERE cve='".$_POST['reg']."' ";
	    $rsprovedor=mysql_db_query($base,$select);
	    $provedor=mysql_fetch_array($rsprovedor);
	

    echo'
	    <a href="#" onClick="atcr(\'sim_telefono.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver </a>
		<a href="#" onClick="atcr(\'sim_telefono.php\',\'\',\'2\',\''.$provedor['cve'].'\');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br>
		<!--<a href="#" onClick="validar('.$provedor['cve'].');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br>-->
		<table>
		<tr><td>Empresa</td><td><select name="empresa" id="empresa" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_empresa as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($provedor['empresa']==$k){echo'selected';}echo'>'.$v.'</option>';
			}
		echo '</select></td></tr>
		<tr>';
		echo'<tr><td>Codigo Bueno</td><td><input type="text" name="cod_bueno" id="cod_bueno" value="' . $provedor['cod_bueno'] . '"/></td></tr>';
		echo'<tr><td>Serie</td><td><input type="text" name="serie" id="serie" value="' . $provedor['serie'] . '"/></td></tr>';
		echo'<tr><td>Telefono</td><td><input type="text" name="telefono" id="telefono" value="' . $provedor['telefono'] . '"/></td></tr>
		<tr style="display:none"><td>Dispositivo</td><td><select name="dispositivo" id="dispositivo" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_dispositivo as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($provedor['dispositivo']==$k){echo'selected';}echo'>'.$v.'</option>';
			}
		echo '</select></td></tr>';
		echo'<tr><td>Marca</td><td><select name="marca" id="marca" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_marca as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($provedor['marca']==$k){echo'selected';}echo'>'.$v.'</option>';
			}
		echo '</select></td></tr>';
		echo'<tr ><td>Modelo</td><td><select name="modelo" id="modelo" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_modelo as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($provedor['modelo']==$k){echo'selected';}echo'>'.$v.'</option>';
			}
		echo '</select></td></tr>';
		echo'<tr><td>Imei</td><td><input type="text" name="imei" id="imei" value="' . $provedor['imei'] . '"/></td></tr>';
		
		echo'<tr><td>Plataforma</td><td><select name="plataforma" id="plataforma" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_plataforma as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($provedor['plataforma']==$k){echo'selected';}echo'>'.$v.'</option>';
			}
		echo '</select></td></tr>';
		echo'<tr><td>Observaciones</td><td><textarea name="obs" id="obs">'.$provedor['obs'].'</textarea></td></tr>';
		echo'</table>';
		echo '
<Script language="javascript">
	$(document).ready(function(){';

	if(isset($str_error) && $str_error != '')
		echo 'alert("' . $str_error . '")';
	echo '
	});
	 function validar(reg)
	   {
       if(document.getElementById("usuario").value==""  )
	   {
               alert("Necesita introducir el usuario");
       }
	   else if(document.getElementById("dispositivo").value==""  ){
		   alert("Necesita introducir el dispositivo");
	   }
	   else if(document.getElementById("placa").value==""  )
	   {
               alert("Necesita introducir la placa");
       }
	   else if(document.getElementById("comando").value=="0"  )
	   {
               alert("Necesita introducir el comando");
       }
       else{
		   atcr("sim_telefono.php","",2,reg);
       }
		}
		function validar_tel(reg)
	   {

               objeto=crearObjeto();
               if (objeto.readyState != 0)
			   {
                       alert("Error: El Navegador no soporta AJAX");
               } else
			   {
                       objeto.open("POST","sim_telefono.php",true);
                       objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
                       objeto.send("ajax=3&cod="+reg+"&telefono="+document.getElementById("telefono").value+"");
                       objeto.onreadystatechange = function()
					   {
                               if (objeto.readyState==4)
							   {
                                       if(objeto.responseText=="si")
									   {
                                               alert("El Telefono ya existe");
                                       }
                                       else{
                                               atcr("sim_telefono.php","",2,reg);
                                       }
                               }
                       }
               }

		}
	</Script>';
		}

 	if ($_POST['cmd']<1) {
//		$query = "select id, apodo from movil_dispositivos where plaza = '{$_SESSION['plaza_seleccionada']}'";
//		$result = mysql_query($query);
//		$apodos = '<option value="0">(Seleccionar un apodo)</option>';

//		while($row = mysql_fetch_assoc($result))
//			$apodos .= '<option value="' . $row['id'] . '">' . $row['apodo'] . '</option>';
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
				<a href="#" onClick="atcr(\'sim_telefono.php\',\'\',\'1\',\'0\');" id="nuevo" name="nuevo" ><img src="images/nuevo.gif"></a>Nuevo</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
//		echo '<tr><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
//		echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>SIM</td><td><input type="text" class="textField" size="" name="numero" id="numero"></td>';
//		echo '<td>Apodo:</td><td><select name="apodo" id="apodo">' . $apodos . '</select></td></tr>';
		//echo '<tr style="display:none;"><td>Id Tipo Evento</td><td><input type="text" class="textField" size="5" name="idtipo" id="idtipo"></td></tr>';
//	echo '<tr style="display:none;"><td>Empresa</td><td><select name="empresa" id="empresa"><option value="0">--Seleccione--</option>';
//		foreach($array_empresa as $k=>$v){
//			echo '<option value="'.$k.'">'.$v.'</option>';
//		}
//		echo '</select></td>
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
			objeto.open("POST","sim_telefono.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&numero="+document.getElementById("numero").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
	</Script>';
	}
 ?>
<?
bottom();
?>
