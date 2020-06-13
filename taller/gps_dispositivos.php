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

	$select= " SELECT * FROM movil_dispositivos WHERE plaza = '{$_SESSION['plaza_seleccionada']}' and  empresa='".$_POST['cveempresa']."'";
	if($_POST['nombre']!="") $select .= " AND nombre like '%".$_POST['usuario']."'%";
	else if($_POST['apodo']!=0) $select .= " AND id = '".$_POST['apodo']."'";
	//if($_POST['empresa']!="") $select .= " AND empresa= '".$_POST['empresa']."'";
	$select .= " ORDER BY id asc";
	$res=mysql_query($select) or die(mysql_error());
	//echo''.$select.'';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8">
		  <th width="50"></th><th>Usuario</th><th>Apodo</th><th>Telefono</th><th>IMEI</th><th>Estatus</th>
		  </tr>';
	while($row=mysql_fetch_array($res)){
		rowb();
		echo'<td align="center"><a href="#" onClick="atcr(\'gps_dispositivos.php\',\'\',\'1\','.$row['id'].')"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
//	echo'<td align="left"></td>';
	echo'<td align="left">'.iconv("ISO-8859-1", "UTF-8", $row['nombre']).'</td>';
	echo'<td align="left">'.iconv("ISO-8859-1", "UTF-8", $row['apodo']).'</td>';
		echo'<td align="left">'.$row['telefono'].'</td>';
		echo'<td align="left">'.$row['imei'].'</td>';
		echo'<td align="left">';if ($row['estatus']=="A"){echo'Alta';} if ($row['estatus']=="B"){echo'Baja';}echo'</td>';
		echo'</tr>';
	}
	echo'<tr bgcolor="#E9F2F8"><td align="left" colspan="6">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table>';
exit();
}
if($_POST["ajax"]==2)
   {
	 if($_POST['cod']>0){
		  echo "no";
		  exit();
	 }else{
		$rs="select * from movil_dispositivos where imei='".$_POST['imei']."' and estatus == 'A'";
	   $rsrfc=mysql_db_query($base,$rs);
       if(mysql_num_rows($rsrfc)>0)
	    {
           echo "si";
		}else
		   {
              echo "no";
		   }
          exit();
	 }
   }
if($_POST["ajax"]==3)
   {
	 if($_POST['cod']>0){
		  echo "no";
		  exit();
	 }else{
		$rs="select * from movil_dispositivos where telefono='".$_POST['telefono']."'";
	   $rsrfc=mysql_db_query($base,$rs);
       if(mysql_num_rows($rsrfc)>0)
	    {
           echo "si";
		}else
		   {
              echo "no";
		   }
          exit();
	 }
   }
 top($_SESSION);
  if($_POST['cmd']==2){
	  $str_error = '';
	if ($_POST['reg']>0){
        $select=" SELECT * FROM movil_dispositivos WHERE id='".$_POST['reg']."' ";
       $rsprovedor=mysql_db_query($base,$select);
       $Usuario=mysql_fetch_array($rsprovedor);
	   if($Usuario['estatus']!=$_POST['estatus']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Estatus',nuevo='".$_POST['estatus']."',anterior='".$Usuario['estatus']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['nombre']!=$_POST['usuariod']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Usuario',nuevo='".$_POST['usuariod']."',anterior='".$Usuario['nombre']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['apodo']!=$_POST['apodod']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Apodo',nuevo='".$_POST['apodod']."',anterior='".$Usuario['apodo']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['telefono']!=$_POST['telefono']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Telefono',nuevo='".$_POST['telefono']."',anterior='".$Usuario['telefono']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['imei']!=$_POST['imei']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='IMEI',nuevo='".$_POST['imei']."',anterior='".$Usuario['imei']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		$sSQL="update movil_dispositivos
				SET nombre='".$_POST['usuariod']."', apodo='" . $_POST['apodod'] . "', estatus='".$_POST['estatus']."',telefono='".$_POST['telefono']."',imei='".$_POST['imei']."' where id='".$_POST['reg']."'";
		mysql_query($sSQL);
	}else{
		$query = "select * from movil_dispositivos where imei = '{$_POST['imei']}' and estatus = 'A'";
		$dispositivo = mysql_query($query);

		if($dispositivo = mysql_fetch_assoc($dispositivo))
			$str_error .= 'El imei ya esta registrado';
		else{
			$sSQL="INSERT movil_dispositivos
					SET nombre='".$_POST['usuariod']."', apodo='" . $_POST['apodod'] . "',imei='".$_POST['imei']."', empresa='".$_POST['cveempresa']."',telefono='".$_POST['telefono']."', estatus='A',
					fecha='".fechaLocal()."',hora='".horaLocal()."',usuario='".$_POST['cveusuario']."', plaza = '{$_SESSION['plaza_seleccionada']}'";
			mysql_query($sSQL);
			$cveusu=mysql_insert_id();
		}
	}

	if($str_error != '')
		$_POST['cmd'] = 1;
	else
		$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	if($_POST['reg'] > 0){
	    $select=" SELECT * FROM movil_dispositivos WHERE id='".$_POST['reg']."' ";
	    $rsprovedor=mysql_db_query($base,$select);
	    $provedor=mysql_fetch_array($rsprovedor);
	}else{
		$provedor['nombre'] = $_POST['usuariod'];
		$provedor['apodo'] = $_POST['apodod'];
		$provedor['telefono'] = $_POST['telefono'];
		$provedor['imei'] = $_POST['imei'];
		$provedor['estatus'] = $_POST['estatus'];
	}

    echo'
	    <a href="#" onClick="atcr(\'gps_dispositivos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver </a>
		<!--<a href="#" onClick="atcr(\'gps_dispositivos.php\',\'\',\'2\',\''.$provedor['id'].'\');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br> -->
		<a href="#" onClick="validar('.$provedor['id'].');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br>
		<table>
		<tr>';
		if($_POST['reg']){
		$block="class='readOnly' readonly";}

		echo'<tr><td>Usuario:</td><td><input type="text" name="usuariod" id="usuariod" value="' . $provedor['nombre'] . '"/></td></tr>';
		echo'<tr><td>Apodo:</td><td><input type="text" name="apodod" id="apodod" value="' . $provedor['apodo'] . '"/></td></tr>';
		echo'<tr><td><span>Telefono:</span></td>
				<td><input  size ="40" type="text" name="telefono" id="telefono" value="'.$provedor['telefono'].'"></br></td>
				</tr>
		<tr><td><span>IMEI</span></td>
        <td><input  size ="40" type="text" name="imei" id="imei" value="'.$provedor['imei'].'" ></br></td>
		</tr>';
		//if($_POST['reg']){
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"> <option value="0">Seleccione</option>';
		foreach($array_estatus as $k=>$v){
			$rest = substr($v, -4, 1);
			echo '<option value="'.$rest.'"';

			if($rest==$provedor['estatus']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		//}

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
       if(document.getElementById("usuariod").value==""  )
	   {
               alert("Necesita introducir el usuario");
       }
	   else if(document.getElementById("apodod").value==""  ){
		   alert("Necesita introducir el apodo");
	   }
	   else if(document.getElementById("imei").value==""  )
	   {
               alert("Necesita introducir el imei");
       }
	   else if(document.getElementById("estatus").value=="0"  )
	   {
               alert("Necesita seleccionar un estatus");
       }
       else{
		   atcr("gps_dispositivos.php","",2,reg);
               /*objeto=crearObjeto();
               if (objeto.readyState != 0)
			   {
                       alert("Error: El Navegador no soporta AJAX");
               } else
			   {
                       objeto.open("POST","gps_dispositivos.php",true);
                       objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
                       objeto.send("ajax=2&cod="+reg+"&imei="+document.getElementById("imei").value+"");
                       objeto.onreadystatechange = function()
					   {
                               if (objeto.readyState==4)
							   {
                                       if(objeto.responseText=="si")
									   {
                                               alert("El imei ya existe");
                                       }
                                       else{

												validar_tel(reg);
                                       }
                               }
                       }
               }*/
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
                       objeto.open("POST","gps_dispositivos.php",true);
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
                                               atcr("gps_dispositivos.php","",2,reg);
                                       }
                               }
                       }
               }

		}
	</Script>';
		}

 	if ($_POST['cmd']<1) {
		$query = "select id, apodo from movil_dispositivos where plaza = '{$_SESSION['plaza_seleccionada']}'";
		$result = mysql_query($query);
		$apodos = '<option value="0">(Seleccionar un apodo)</option>';

		while($row = mysql_fetch_assoc($result))
			$apodos .= '<option value="' . $row['id'] . '">' . $row['apodo'] . '</option>';
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
				<a href="#" onClick="atcr(\'gps_dispositivos.php\',\'\',\'1\',\'0\');" id="nuevo" name="nuevo" ><img src="images/nuevo.gif"></a>Nuevo</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
//		echo '<tr><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
//		echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Usuario:</td><td><input type="text" class="textField" size="" name="usuario" id="usuario"></td>';
		echo '<td>Apodo:</td><td><select name="apodo" id="apodo">' . $apodos . '</select></td></tr>';
		//echo '<tr style="display:none;"><td>Id Tipo Evento</td><td><input type="text" class="textField" size="5" name="idtipo" id="idtipo"></td></tr>';
		echo '<tr style="display:none;"><td>Empresa</td><td><select name="empresa" id="empresa"><option value="0">--Seleccione--</option>';
		foreach($array_empresa as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
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
			objeto.open("POST","gps_dispositivos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&usuario="+document.getElementById("usuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveempresa="+document.getElementById("empresa").value+"&apodo="+document.getElementById("apodo").value);
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
