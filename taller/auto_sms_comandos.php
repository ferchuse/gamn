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
	if($_POST['comando']!="") $select .= " AND dispositivo like '%".$_POST['comando']."%'";
//	else if($_POST['apodo']!=0) $select .= " AND id = '".$_POST['apodo']."'";
	//if($_POST['empresa']!="") $select .= " AND empresa= '".$_POST['empresa']."'";
	$select .= " ORDER BY id asc";
	$res=mysql_db_query($base,$select) or die(mysql_error());
	//echo''.$select.'';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8">
		  <th width="50"></th><th>Usuario</th><th>Dispositivo</th><th>Placa</th><th>Telefono</th>
		  </tr>';
	while($row=mysql_fetch_array($res)){
		rowb();
		echo'<td align="center"><a href="#" onClick="atcr(\'auto_sms_comandos.php\',\'\',\'1\','.$row['id'].')"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
//	echo'<td align="left"></td>';

		echo'<td align="left">'.$row['usuario'].'</td>';
		echo'<td align="left">'.$row['dispositivo'].'</td>';
		echo'<td align="left">'.$row['placa'].'</td>';
		echo'<td align="left">'.$row['telefono'].'</td>';

		echo'</tr>';
	}
	echo'<tr bgcolor="#E9F2F8"><td align="left" colspan="5">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table>';
exit();
}

if($_POST['ajax'] == 2){
	$base='gamn_gps_skymedia';
	$query = "select id, descripcion from comandos where 1";

	if($_POST['descripcion'] != '')
		$query .= " and descripcion like '%{$_POST['descripcion']}%'";

	$query .= ' order by descripcion';
	$result = mysql_db_query($base, $query);
	echo '
	<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
    <tr bgcolor="#E9F2F8">
		  <th width="50"></th><th>Descripcion</th>
	  </tr>';

	while($row = mysql_fetch_assoc($result)){
		rowb();
		echo '
		<tr>
			<td align="center">
				<a href="#" onClick="atcr(\'auto_sms_comandos.php\',\'\',\'4\',' . $row['id'] . ')">
					<img src="images/modificar.gif" border="0" title="Editar">
				</a>
			</td>
			<td>' . $row['descripcion'] . '</td>
		</tr>';
	}

	echo '
	<tr bgcolor="#E9F2F8">
		<td align="left" colspan="5">
			' . mysql_num_rows($result).' Registro(s)
		</td>
	</tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax'] == 3){
	$base = 'gamn_gps_skymedia';
	$respuesta = array();
	$idPlaca = $_POST['idPlaca'];

	$query = "select usuario, placa, dispositivo from gps_objects where id = '$idPlaca'";
	$result = mysql_db_query($base, $query);
	$respuesta['placa'] = mysql_fetch_assoc($result);

	$html = '
	<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tblComandos">
    <tr bgcolor="#E9F2F8">
			<th><a href="#" onclick="agregarComando();">Agregar</a></th><th>Descripcion</th><th>Comando</th><th></th>
		</tr>';

	$query = "select c.id, c.descripcion, c.comando 
		from comandos as c inner join comandosxplaca as cp on c.id = cp.idComando and cp.idPlaca = '$idPlaca'";
	$result = mysql_db_query($base, $query);

	while($row = mysql_fetch_assoc($result)){
		$html .= '
		<tr>
			<td align="center">
				<a href="#" onclick="eliminarComando(' . $idPlaca . ', ' . $row['id'] . ')">Eliminar</a>
			</td>
			<td><strong>' . $row['descripcion'] . '</strong></td>
			<td>' . $row['comando'] . '</td>
			<td align="center">
				<input type="button" name="EnviarComando" value="Enviar" onclick="enviarComando(' . $idPlaca . ', \'' . $row['comando'] . '\');"/>
			</td>
		</tr>';
	}

	$html .= '
	</table>';
	$respuesta['html'] = $html;
	echo json_encode($respuesta);
	exit();
}

if($_POST['ajax'] == 4){
	$base = 'gamn_gps_skymedia';
	$query = "select 1 from comandosxplaca where idPlaca = {$_POST['idPlaca']} and idComando = {$_POST['idComando']}";
	$result = mysql_db_query($base, $query);

	if(mysql_fetch_assoc($result))
		echo "EXISTE";
	else{
		$query = "insert into comandosxplaca (idPlaca, idComando) values({$_POST['idPlaca']}, {$_POST['idComando']})";
		mysql_db_query($base, $query);
		echo "OK";
	}

	exit();
}

if($_POST['ajax'] == 5){
	$base = 'gamn_gps_skymedia';
	$query = "select 1 from comandosxplaca where idPlaca = {$_POST['idPlaca']} and idComando = {$_POST['idComando']}";
	$result = mysql_db_query($base, $query);

	if(!mysql_fetch_assoc($result))
		echo "NO EXISTE";
	else{
		$query = "delete from comandosxplaca where idPlaca = {$_POST['idPlaca']} and idComando = {$_POST['idComando']}";
		mysql_db_query($base, $query);
		echo "OK";
	}

	exit();
}

if($_POST['ajax'] == 6){
	$base = 'gamn_gps_skymedia';
	$query = "select telefono from gps_objects where id = {$_POST['idPlaca']}";
	$result = mysql_db_query($base, $query);
	$result = mysql_fetch_assoc($result);
	$telefono = $result['telefono'];
	$query = "insert into sms (mensaje, telefono, idPlaca) values('{$_POST['comando']}', '$telefono', {$_POST['idPlaca']})";
	mysql_db_query($base, $query);
	echo "OK";
	exit();
}

 top($_SESSION);

if($_POST['cmd'] == 6){
	$base = 'gamn_gps_skymedia';
	echo '
	<table>
		<tr>
			<td>
			<a href="#" onclick="atcr(\'auto_sms_comandos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0"></a>&nbsp;&nbsp;Volver&nbsp;&nbsp;
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td>Dispositivo:</td>
			<td>
				<select name="dispositivoSelect" id="dispositivoSelect" onchange="traePlaca();">
					<option value="0">-- Seleccione un Dispositivo --</option>';

	$query = 'select * from gps_objects order by imei';
	$result = mysql_db_query($base, $query);
	while($row = mysql_fetch_assoc($result))
		echo '<option value="' . $row['id'] . '">' . $row['dispositivo'] . ' - ' . $row['imei'] . '</option>';

				echo '
				</select>
			</td>
			<td>Usuario:</td>
			<td><input type="text" name="usuario" id="usuario" value="" readonly/></td>
		</tr>
		<tr>
			<td>Placa:</td>
			<td><input type="text" name="placa" id="placa" value="" readonly/></td>
			<td>Dispositivo:</td>
			<td><input type="text" name="dispositivo" id="dispositivo" value="" readonly/></td>
		</tr>
	</table>
	<hr/>
	<div id="divComandos"></div>
	
	<div id="dialogo" style="display: none; z-index: 1; position: fixed; left: 500px; top: 250px; padding: 2em; background-color: #ccc; border: 1px solid black; width: 200px; height: 60px;">
		<table width="100%">
			<tr>
				<td align="right">Comando:</td>
				<td>
					<select id="comandosSelect">';

					$query = "select * from comandos order by descripcion";
					$result = mysql_db_query($base, $query);

					while($row = mysql_fetch_assoc($result))
						echo '<option value="' . $row['id'] . '">' . $row['descripcion'] . '</option>';

					echo '
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="right">
					<input type="button" onclick="cerrarDialogo();" value="Cerrar"/>
					&nbsp;
					<input type="button" onclick="seleccionarComando();" value="Seleccionar"/>
				</td>
			</tr>
		</table>
	</div>

	<script type="text/javascript">
		function cerrarDialogo(){
			$("#dialogo").fadeOut();
			$("#comandosSelect").val($("#comandosSelect option:first").val());
		}

		function agregarComando(){
			$("#dialogo").fadeIn();
		}

		function eliminarComando(idPlaca, idComando){
			$.post(
				"auto_sms_comandos.php",
				{
					ajax: 5,
					idPlaca: idPlaca,
					idComando: idComando
				},
				function(html){
					if(html == "OK")
						traePlaca();
				},
				"html"
			);
		}

		function enviarComando(idPlaca, comando){
			$.post(
				"auto_sms_comandos.php",
				{
					ajax: 6,
					idPlaca: idPlaca,
					comando: comando
				},
				function(html){
					if(html == "OK")
						alert("Mensaje Guardado.");
				},
				"html"
			);
		}

		function seleccionarComando(){
			$.post(
				"auto_sms_comandos.php",
				{
					ajax: 4,
					idPlaca: $("#dispositivoSelect").val(),
					idComando: $("#comandosSelect").val()
				},
				function(html){
					if(html == "EXISTE")
						alert("El comando ya fue seleccionado con anterioridad.");
					
					traePlaca();
				},
				"html"
			);
		}

		function traePlaca(){
			$.post(
				"auto_sms_comandos.php",
				{
					ajax: 3,
					idPlaca: $("#dispositivoSelect").val()
				},
				function(json){
					$("#usuario").val(json.placa.usuario);
					$("#placa").val(json.placa.placa);
					$("#dispositivo").val(json.placa.dispositivo);
					$("#divComandos").html(json.html);
				},
				"json"
			);
		}
	</script>';
}

if($_POST['cmd'] == 5){
	$str_error = '';
	if($_POST['descripcion'] == '' || $_POST['comando'] == '')
		$str_error = 'Los campos no pueden estar vacios.';
	else{
		$base = 'gamn_gps_skymedia';

		if($_POST['idComando'] == 0)
			$query = "insert into comandos (descripcion, comando)  values('{$_POST['descripcion']}', '{$_POST['comando']}')";
		else
			$query = "update comandos set descripcion = '{$_POST['descripcion']}', comando = '{$_POST['comando']}' where id = '{$_POST['idComando']}'";

		$result = mysql_db_query($base, $query);

		if(!$result)
			$str_error = 'El comando \'' . $_POST['comando'] . '\' ya esta registrado.';
	}

	$_POST['cmd'] = $str_error != '' ? 4 : 3;
}

if($_POST['cmd'] == 4){
	if($_POST['reg'] > 0){
		$query = "select * from comandos where id = '{$_POST['reg']}'";
		$base='gamn_gps_skymedia';
		$result = mysql_db_query($base, $query);
		$result = mysql_fetch_assoc($result);
		$_POST['descripcion'] = $result['descripcion'];
		$_POST['comando'] = $result['comando'];
	}

	echo '
	<input type="hidden" name="idComando" value="' . $_POST['reg'] . '"/>
	<table>
		<tr>
			<td>
			<a href="#" onclick="atcr(\'auto_sms_comandos.php\',\'\',\'3\',\'0\');"><img src="images/flecha-izquierda.gif" border="0"></a>&nbsp;&nbsp;Volver&nbsp;&nbsp;
			<a href="#" onclick="validar();"><img src="images/guardar.gif" border="0"></a>&nbsp;&nbsp;Guardar&nbsp;&nbsp;
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td>Descripcion:</td>
			<td><input type="text" name="descripcion" id="descripcion" value="' . $_POST['descripcion'] . '"/></td>
		</tr>
		<tr>
			<td>Comando:</td>
			<td><input type="text" name="comando" id="comando" value="' . $_POST['comando'] . '"/></td>
		</tr>
	</table>
	<script type="text/javascript">
		$(document).ready(function(){
			';

			if($str_error != '')
				echo "alert(\"$str_error\");";

			echo '
		});

		function validar(){
			var descripcion = $("#descripcion").val();
			var comando = $("#comando").val();

			if(descripcion == "" || comando == ""){
				alert("Los campos no pueden estar vacios.");
				return;
			}

			atcr(\'auto_sms_comandos.php\',\'\',\'5\',\'0\');
		}
	</script>';
}

if($_POST['cmd'] == 3){
	echo '<table>';
	echo '<tr>										
			<td>
				<a href="#" onclick="atcr(\'auto_sms_comandos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0"></a>&nbsp;&nbsp;Volver&nbsp;&nbsp;
				<a href="#" onclick="getDescripciones();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
				<a href="#" onClick="atcr(\'auto_sms_comandos.php\',\'\',\'4\',\'0\');" id="nuevo" name="nuevo" ><img src="images/nuevo.gif"></a>Nuevo
				</a></br></br>
				</td>
		 </tr>';
	echo '</table>';
	echo '<table>
					<tr>
						<td>Descripcion:</td>
						<td><input type="text" name="descripcion" id="descripcion"/></td>
					</tr>
				</table>
				<div id="resultados"></div>';

	echo '
	<script type="text/javascript">
		$(document).ready(function(){
			getDescripciones();
		});

		function getDescripciones(){
			$("#resultados").html("<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...");
			$.post(
				"auto_sms_comandos.php",
				{
					ajax: 2,
					descripcion: $("#descripcion").val()
				},
				function(html){
					$("#resultados").html(html);
				},
				"html"
			);
		}
	</script>';
}

 if($_POST['cmd']==2){
	$base = 'gamn_gps_skymedia';
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

		$sSQL="update gps_objects
				SET usuario='" . $_POST['usuario'] . "', dispositivo='".$_POST['dispositivo']."',placa='".$_POST['placa']."',comando='".$_POST['comando']."', telefono='{$_POST['telefono']}' where id='".$_POST['reg']."'";
		mysql_db_query($base, $sSQL);
	}else{
			$sSQL="INSERT into gps_objects
					SET usuario='" . $_POST['usuario'] . "', dispositivo='".$_POST['dispositivo']."',placa='".$_POST['placa']."',comando='".$_POST['comando']."', telefono='{$_POST['telefono']}',
					fecha='".fechaLocal()."',hora='".horaLocal()."'";
			mysql_db_query($base, $sSQL);
			$cveusu=mysql_insert_id();
	}

	if($str_error != '')
		$_POST['cmd'] = 1;
	else
		$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	$base = 'gamn_gps_skymedia';
	    $select=" SELECT * FROM gps_objects WHERE id='".$_POST['reg']."' ";
	    $result=mysql_db_query($base,$select);
	    $gpsObject=mysql_fetch_array($result);


    echo'
	    <a href="#" onClick="atcr(\'auto_sms_comandos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver </a>
		<!--<a href="#" onClick="atcr(\'auto_sms_comandos.php\',\'\',\'2\',\''.$gpsObject['id'].'\');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br> -->
		<a href="#" onClick="validar('.$gpsObject['id'].');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br>
		<table>
		<tr>';
		echo'<tr><td>Usuario:</td><td><input type="text" name="usuario" id="usuario" value="' . $gpsObject['usuario'] . '"/></td></tr>';
		echo'<tr><td>Dispositivo:</td><td><input type="text" name="dispositivo" id="dispositivo" value="' . $gpsObject['dispositivo'] . '"/></td></tr>';
		echo'<tr><td>Placa:</td><td><input type="text" name="placa" id="placa" value="' . $gpsObject['placa'] . '"/></td></tr>';
		//echo'<tr><td>Comando:</td><td><input type="text" name="comando" id="comando" value="' . $gpsObject['comando'] . '"/></td></tr>';
		echo('<tr>
			<td>Telefono:</td>
			<td><input type="text" name="telefono" id="telefono" value="' . $gpsObject['telefono'] . '"/></td>
		</tr>');

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
	   else if(document.getElementById("telefono").value=="0"  )
	   {
               alert("Necesita introducir el telefono");
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
				<a href="#" onClick="atcr(\'auto_sms_comandos.php\',\'\',\'3\',\'0\');">
					&nbsp; Agregar Comando
				</a></br></br>
				<a href="#" onClick="atcr(\'auto_sms_comandos.php\',\'\',\'6\',\'0\');">
					&nbsp; Enviar Comando
				</a></br></br>
			 </tr>';
		echo '</table>';
		echo '<table>';
//		echo '<tr><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
//		echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Dispositivo:</td><td><input type="text" class="textField" size="" name="comando" id="comando"></td>';
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
