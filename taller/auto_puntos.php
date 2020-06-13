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
$rsMotivo=mysql_query("SELECT * FROM cat_rutas WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_rutas[$Motivo['cve']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM cat_orden WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_orden[$Motivo['cve']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM cat_sentido WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_sentido[$Motivo['cve']]=$Motivo['nombre'];
}
$array_estatus=array(1=>'Alta',2=>'Baja');
if($_POST['ajax']==1){
//	$base='gps_skymedia';

	//	$select= " SELECT * FROM sms_comandos WHERE plaza = '{$_SESSION['plaza_seleccionada']}'";
	$select= " SELECT * FROM puntos WHERE 1";
	if($_POST['usuario']!="") $select .= " AND usuario like '%".$_POST['usuario']."'%";
//	else if($_POST['apodo']!=0) $select .= " AND id = '".$_POST['apodo']."'";
	if($_POST['ruta']!="") $select .= " AND ruta= '".$_POST['ruta']."'";
	$select .= " ORDER BY cve asc";
	$res=mysql_db_query($base,$select) or die(mysql_error());
	//echo''.$select.'';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8">
		  <th width="50"></th><th>Clave</th><th>Coordenada</th><th>Descripcion</th><th>Ruta</th><th>Orden</th><th>Sentido</th><th>Usuario</th>
		  </tr>';
	while($row=mysql_fetch_array($res)){
		rowb();
	echo'<td align="center"><a href="#" onClick="atcr(\'auto_puntos.php\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
//	echo'<td align="left"></td>';

		echo'<td align="center">'.$row['clave'].'</td>';
		echo'<td align="center">'.$row['coordenada'].'</td>';
		echo'<td align="center">'.$row['des'].'</td>';
		echo'<td align="center">'.$array_rutas[$row['ruta']].'</td>';
		echo'<td align="center">'.$row['orden'].'</td>';
		echo'<td align="center">'.$array_sentido[$row['sentido']].'</td>';
		echo'<td align="center">'.$array_personal[$row['usu']].'</td>';
		echo'</tr>';
	}
	echo'<tr bgcolor="#E9F2F8"><td align="left" colspan="8">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table>';
exit();
}

 top($_SESSION);
  if($_POST['cmd']==2){
	  $str_error = '';
	if ($_POST['reg']>0){
        $select=" SELECT * FROM puntos WHERE cve='".$_POST['reg']."' ";
       $rsprovedor=mysql_db_query($base,$select);
       $Usuario=mysql_fetch_array($rsprovedor);
	   if($Usuario['coordenada']!=$_POST['coordenada']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Coordenada',nuevo='".$_POST['coordenada']."',anterior='".$Usuario['coordenada']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['des']!=$_POST['des']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Descripcion',nuevo='".$_POST['des']."',anterior='".$Usuario['des']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['clave']!=$_POST['clave']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Clave',nuevo='".$_POST['clave']."',anterior='".$Usuario['clave']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['ruta']!=$_POST['ruta']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Ruta',nuevo='".$_POST['ruta']."',anterior='".$Usuario['ruta']."',arreglo='array_rutas',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['orden']!=$_POST['orden']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Orden',nuevo='".$_POST['orden']."',anterior='".$Usuario['orden']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['sentido']!=$_POST['sentido']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Sentido',nuevo='".$_POST['sentido']."',anterior='".$Usuario['sentido']."',arreglo='array_sentido',usuario='".$_POST['cveusuario']."'");
		}
		$sSQL="update puntos
				SET usu='" . $_POST['cveusuario'] . "', des='".$_POST['des']."',coordenada='".$_POST['coordenada']."',clave='".$_POST['clave']."',ruta='".$_POST['ruta']."',
				orden='".$_POST['orden']."',sentido='".$_POST['sentido']."'	where cve='".$_POST['reg']."'";
		mysql_query($sSQL);
	}else{
			$sSQL="INSERT puntos
					SET usu='" . $_POST['cveusuario'] . "',plaza='".$_POST['plazausuario']."', des='".$_POST['des']."',coordenada='".$_POST['coordenada']."',clave='".$_POST['clave']."',ruta='".$_POST['ruta']."',orden='".$_POST['orden']."',sentido='".$_POST['sentido']."'";
			mysql_query($sSQL);
			$cveusu=mysql_insert_id();
	}

	if($str_error != '')
		$_POST['cmd'] = 1;
	else
		$_POST['cmd']=0;
}

if($_POST['cmd']==1){

	    $select=" SELECT * FROM puntos WHERE cve='".$_POST['reg']."' ";
	    $rsprovedor=mysql_db_query($base,$select);
	    $provedor=mysql_fetch_array($rsprovedor);
	

    echo'
	    <a href="#" onClick="atcr(\'auto_puntos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver </a>
		<a href="#" onClick="atcr(\'auto_puntos.php\',\'\',\'2\',\''.$provedor['cve'].'\');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br>
		<!--<a href="#" onClick="validar('.$provedor['cve'].');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br>-->
		<table>
		<tr>';
		echo'<tr><td>Clave:</td><td><input type="text" name="clave" id="clave" value="' . $provedor['clave'] . '"/></td></tr>';
		echo'<tr><td>Coordenada:</td><td><input type="text" size="35" name="coordenada" id="coordenada" value="' . $provedor['coordenada'] . '"/></td></tr>';
		echo'<tr><td>Descripcion:</td><td><input type="text" name="des" id="des" value="' . $provedor['des'] . '"/></td></tr>';
		echo '<tr><td>Ruta</td><td><select name="ruta" id="ruta" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_rutas as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($provedor['ruta']==$k){echo'selected';}echo'>'.$v.'</option>';
			}
		echo '</select></td></tr>';
		echo '<tr><td>Orden</td><td><input type="text" size="10" name="orden" id="orden" value="' . $provedor['orden'] . '"/></td></tr>';
		echo '<tr><td>Sentido</td><td><select name="sentido" id="sentido" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_sentido as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($provedor['sentido']==$k){echo'selected';}echo'>'.$v.'</option>';
			}
		echo '</select></td></tr>';
		
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
		   atcr("auto_puntos.php","",2,reg);
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
                       objeto.open("POST","auto_puntos.php",true);
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
                                               atcr("auto_puntos.php","",2,reg);
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
				<a href="#" onClick="atcr(\'auto_puntos.php\',\'\',\'1\',\'0\');" id="nuevo" name="nuevo" ><img src="images/nuevo.gif"></a>Nuevo</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Ruta</td><td><select name="ruta" id="ruta" class="textField"><option value="">Todas</option>';
		foreach ($array_rutas as $k=>$v) { 
	    echo '<option value="'.$k.'">'.$v.'</option>';
			}
		echo '</select></td></tr>';
//		echo '<tr><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
//		echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
//		echo '<tr><td>Comando:</td><td><input type="text" class="textField" size="" name="comando" id="comando"></td>';
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
			objeto.open("POST","auto_puntos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&ruta="+document.getElementById("ruta").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
