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
$rsMotivo=mysql_query("SELECT * FROM usuarios ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_personal[$Motivo['cve']]=$Motivo['nombre'];
}
$array_estatus=array(1=>'Alta',2=>'Baja');
if($_POST['ajax']==1){
	$base='gamn_gps_skymedia';

	//	$select= " SELECT * FROM sms_comandos WHERE plaza = '{$_SESSION['plaza_seleccionada']}'";
	$select= " SELECT * FROM gps_objects WHERE 1";
	if($_POST['usuario']!="") $select .= " AND usuario like '%".$_POST['usuario']."'%";
//	else if($_POST['apodo']!=0) $select .= " AND id = '".$_POST['apodo']."'";
	//if($_POST['empresa']!="") $select .= " AND empresa= '".$_POST['empresa']."'";
	$select .= " ORDER BY id asc";
	$res=mysql_db_query($base,$select) or die(mysql_error());
	//echo''.$select.'';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8">
		  <th width="50"></th><th>Usuario</th><th>Dispositivo</th><th>Placa</th><th>Comando</th>
		  </tr>';
	while($row=mysql_fetch_array($res)){
		rowb();
		echo'<td align="center"><a href="#" onClick="atcr(\'auto_sms_comandos.php\',\'\',\'1\','.$row['id'].')"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
//	echo'<td align="left"></td>';

		echo'<td align="left">'.$row['usuario'].'</td>';
		echo'<td align="left">'.$row['dispositivo'].'</td>';
		echo'<td align="left">'.$row['placa'].'</td>';
		echo'<td align="left">'.$row['comando'].'</td>';

		echo'</tr>';
	}
	echo'<tr bgcolor="#E9F2F8"><td align="left" colspan="5">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table>';
exit();
}

 top($_SESSION);
  if($_POST['cmd']==2){
	  $str_error = '';
	if ($_POST['reg']>0){
        $select=" SELECT * FROM sms_comandos WHERE cve='".$_POST['reg']."' ";
       $rsprovedor=mysql_db_query($base,$select);
       $Usuario=mysql_fetch_array($rsprovedor);
	   if($Usuario['usuario']!=$_POST['usuario']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Usuario',nuevo='".$_POST['usuario']."',anterior='".$Usuario['usuario']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['dispositivo']!=$_POST['dispositivo']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Dispositivo',nuevo='".$_POST['dispositivo']."',anterior='".$Usuario['dispositivo']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['placa']!=$_POST['placa']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Placa',nuevo='".$_POST['placa']."',anterior='".$Usuario['placa']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['comando']!=$_POST['comando']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Comando',nuevo='".$_POST['comando']."',anterior='".$Usuario['comando']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}

		$sSQL="update sms_comandos
				SET usuario='" . $_POST['usuario'] . "', dispositivo='".$_POST['dispositivo']."',placa='".$_POST['placa']."',comando='".$_POST['comando']."' where cve='".$_POST['reg']."'";
		mysql_query($sSQL);
	}else{
			$sSQL="INSERT sms_comandos
					SET usuario='" . $_POST['usuario'] . "', dispositivo='".$_POST['dispositivo']."',placa='".$_POST['placa']."',comando='".$_POST['comando']."', estatus='A',
					fecha='".fechaLocal()."',hora='".horaLocal()."',usu='".$_POST['cveusuario']."', plaza = '{$_SESSION['plaza_seleccionada']}'";
			mysql_query($sSQL);
			$cveusu=mysql_insert_id();
	}

	if($str_error != '')
		$_POST['cmd'] = 1;
	else
		$_POST['cmd']=0;
}

if($_POST['cmd']==1){

	    $select=" SELECT * FROM sms_comandos WHERE cve='".$_POST['reg']."' ";
	    $rsprovedor=mysql_db_query($base,$select);
	    $provedor=mysql_fetch_array($rsprovedor);
	

    echo'
	    <a href="#" onClick="atcr(\'auto_sms_comandos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver </a>
		<!--<a href="#" onClick="atcr(\'auto_sms_comandos.php\',\'\',\'2\',\''.$provedor['cve'].'\');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br> -->
		<a href="#" onClick="validar('.$provedor['cve'].');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br>
		<table>
		<tr>';
		echo'<tr><td>Usuario:</td><td><input type="text" name="usuario" id="usuario" value="' . $provedor['usuario'] . '"/></td></tr>';
		echo'<tr><td>Dispositivo:</td><td><input type="text" name="dispositivo" id="dispositivo" value="' . $provedor['dispositivo'] . '"/></td></tr>';
		echo'<tr><td>Placa:</td><td><input type="text" name="placa" id="placa" value="' . $provedor['placa'] . '"/></td></tr>';
		echo'<tr><td>Comando:</td><td><input type="text" name="comando" id="comando" value="' . $provedor['comando'] . '"/></td></tr>';
		
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
		   atcr("auto_sms_comandos.php","",2,reg);
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
                       objeto.open("POST","auto_sms_comandos.php",true);
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
                                               atcr("auto_sms_comandos.php","",2,reg);
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
				<a href="#" onClick="atcr(\'auto_sms_comandos.php\',\'\',\'1\',\'0\');" id="nuevo" name="nuevo" ><img src="images/nuevo.gif"></a>Nuevo</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
//		echo '<tr><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
//		echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Comando:</td><td><input type="text" class="textField" size="" name="comando" id="comando"></td>';
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
			objeto.open("POST","auto_sms_comandos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&comando="+document.getElementById("comando").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
