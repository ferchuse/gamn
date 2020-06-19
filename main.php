<?php
	error_reporting(0);
	require_once('subs/cnx_db.php');
	include("parametros-navegacion.php");
	global $base,$PHP_SELF,$pre;
	/*Validamos solicitud de login a este sitio*/
	if (!isset($_SESSION)) {
		session_start();
	}
	
	$clavecancelacion='Dnbi3z7T';
	
	if(!$_SESSION['CveUsuario'] && !$_SESSION['NomUsuario'] && !isset($_POST['loginUser']) && !isset($_POST['loginPassword'])) {
		header("Location: index2.php");
	}
	if($_SESSION['CveUsuario']!=1 && $_POST['loginUser']!="root"){
		$rsCerrado=mysql_db_query($base,"SELECT * FROM ".$pre."usuarios WHERE cve='1'") or die(mysql_error());
		$Cerrado=mysql_fetch_array($rsCerrado);
		if($Cerrado['cerrar_sistema']=='S'){
			echo '<script>window.location="index2.php";</script>';
		}
	}
	
	$rsCerrado2=mysql_db_query("enero_aaz","SELECT estatus FROM coor_cerrar_sistema WHERE dominio='3' ORDER BY cve DESC LIMIT 1");
	$Cerrado2=mysql_fetch_array($rsCerrado2);
	if($Cerrado2['estatus']=='C'){
		echo '<script>window.location="index2.php";</script>';
	}
	
	$empresagcompufax=3;
	$urlgcompufax = 'http://gcompufax.com/recepcion_datos.php';
	
	$archivo=explode("/",$_SERVER["PHP_SELF"]);
	global $archivo,$reg_sistema,$pre;
	
	$array_meses=array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
	$array_dias=array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sabado");
	//7=>"Manejo de Tickets",
	//,20=>"Banco"
	$array_modulos=array(
	1=>"Catalogos",
	3=>"Movimientos",
	4=>"Unidades",
	5=>"Conductores",
	6=>"Recaudacion",
	7=>"Taquilla",
	10=>"Saldos",
	11=>"Gps",
	12=>"Refaccionaria",
	99=>"Administracion"
	);
	$array_diasmes=array(0,31,28,31,30,31,30,31,31,30,31,30,31);
	/*Arreglos parque*/
	$array_estatus_parque=array(1=>"Alta",2=>"Baja",3=>"Inactivo");
	$array_estatus_parque_condonacion=array(11=>"Sin Conductor",12=>"Hojalateria y Pintura",13=>"Ajuste de Maquina",14=>"Detencion por Operativo Transporte Terrestre",17=>"Detencion por P.F.P.",18=>"Detencion Federal",15=>"Por Consejo",16=>"Encontrarse en Taller por Reparacion");
	$array_tipo_vehiculo=array(1=>"Autobus");
	$array_tipo_placa=array("Sin Asignar","Federal","Estatal","Tramite","Permiso");
	$array_tipo_vigencia=array(1=>"Anual",2=>"Semestral",3=>"Bimestral");
	$array_tipo_propietario=array(1=>"Socio",2=>"Arrendatario");
	$array_tipogas=array(1=>"Gasolina",2=>"Diesel");
	/*Arreglos conductores*/
	$array_estatus_conductores=array(1=>"Alta",2=>"Baja");
	$array_estatus_conductores_condonacion=array(11=>"Enfermedad",12=>"Permiso",13=>"Suspension",14=>"Por Consejo");
	$array_tipo_conductor=array(1=>"Sin Asignar",2=>"Planta",3=>"Posturero");
	$array_tipo_licencia=array(0=>"Sin Asignar",1=>"Licencia Federal A",2=>"Licencia Federal B",3=>"Licencia Estatal");
	/*Arreglos Accidentes*/
	$array_estatus_accidentes=array("Pendiente","Liquidado","Sin costo para la empresa","Recuperacion de Terceros");
	$array_tipo_accidente=array("Accidente","Especial","Robo, Conflicto y Dentencion","Liberacion","Sin registro","Mixto");
	/*Arreglos Personal*/
	$array_estatus_personal=array(1=>"Alta",2=>"Baja",3=>"Inactivo");
	$array_requisitos=array("Incompletos","Completos");
	$array_estatus_personal_condonacion=array(11=>"Enfermedad",12=>"Permiso",13=>"Suspension");
	//Arreglos  Mantenimiento
	$array_tipo_mantenimiento=array("Mantenimiento","Servicio de Grua","Diesel y Lubricante");
	//Arreglos Cheques
	$array_estatus_chequera=array("Pagado","Cancelado","En Proceso");
	//Arreglos Catemaco
	$array_rutas_catemaco=array(1=>"Mexico-Catemaco",2=>"Catemaco-Mexico");
	$array_estatus_vales=array('A'=>"Proceso",'P'=>"Pagado",'C'=>"Cancelado");
	$array_estatus_traspaso=array(0=>"Pagado",2=>"Cancelado");
	$array_tipo_cargo=array(1=>"Derrotero",2=>"Variable");
	$array_estatus_variable=array(1=>"Activo",2=>"Parado",3=>"Terminado",4=>"Cancelado");
	
	$array_empresas_sitio=array(1=>"7Enero",2=>"Zitlaltepec Autobuses",3=>"Zitlaltepec Camioneras",4=>"AETT",5=>"Paseos de San Juan");
	
	$array_cargos_unidades=array(1=>"Cargo Administrativo",2=>"Seguro Interno");
	
	//Si existen las variables POST  usuario y password viene de login
	if (isset($_POST['loginUser']) && isset($_POST['loginPassword'])) {
		//Como se supone venimos de ventana de login o sesion expirada, eliminamos cualquier rastro de sesion anterior
		// Unset all of the session variables.
		$_SESSION = array();
		// Finally, destroy the session.
		session_destroy();
		$loginUsername=$_POST['loginUser'];
		$password=$_POST['loginPassword'];
		$redirectLoginSuccess = "inicio.php";
		$redirectLoginFailed = "index2.php?ErrLogUs=true";
		//Hacemos uso de la funcion GetSQLValueString para evitar la inyeccion de SQL
		$LoginRS_query = sprintf("SELECT * FROM ".$pre."usuarios WHERE usuario = %s AND password = %s AND estatus='A'",
		GetSQLValueString($loginUsername, "text"), GetSQLValueString($password, "text")); 
		
		
		$LoginRS = mysql_db_query($base,$LoginRS_query);
		
		$loginFoundUser = mysql_num_rows($LoginRS);
		
		if ($loginFoundUser) {
			
			$Usuario=mysql_fetch_array($LoginRS);
			
			if($Usuario['cve']!=1){
				$rsCerrado=mysql_db_query($base,"SELECT * FROM ".$pre."usuarios WHERE cve='1'");
				$Cerrado=mysql_fetch_array($rsCerrado);
				if($Cerrado['cerrar_sistema']=='S'){
					echo '<script>window.location="index2.php";</script>';
				}
			}
			$ip=getRealIP();
			$fechahora=date( "Y-m-d H:i:s" , strtotime ( "0 hour" , strtotime(date("Y-m-d H:i:s")) ) );
			mysql_db_query($base,"INSERT ".$pre."registros_sistema SET usuario='".$Usuario['cve']."',entrada='".$fechahora."',ip='$ip'");
			$reg_sistema=mysql_insert_id();
			
			//Creamos la sesion
			
			session_start();		
			
			
			
			//Creamos las variables de sesion del usuario en cuestion
			
			$_SESSION['CveUsuario'] = $Usuario['cve'];
			
			$_SESSION['NomUsuario'] = $Usuario['nombre'];
			
			$_SESSION['PlazaUsuario'] = $Usuario['plaza'];
			
			$_SESSION['NickUsuario'] = $Usuario['usuario'];
			
			$_SESSION['reg_sistema'] = $reg_sistema;
			
			
			header("Location: " . $redirectLoginSuccess );
			
			} else {
			$res=mysql_db_query($base,"SELECT * FROM ".$pre."propietarios WHERE usuario='$loginUsername' AND pass='$password'");
			if($row=mysql_fetch_array($res)){
				session_start();		
				$_SESSION['CveUsuario'] = $row['cve'];
				
				$_SESSION['NomUsuario'] = $row['nombre'];
				
				$_SESSION['PlazaUsuario'] = 1;
				
				$_SESSION['NickUsuario'] = $row['usuario'];
				header("Location: edo_cuenta_parque_propietario.php");
			}
			else{
				header("Location: " . $redirectLoginFailed);
			}
			
		}
		
	}
	
	if(intval($_POST['cveusuario'])==0){
		$_POST['cveusuario']=$_SESSION['CveUsuario'];
		$_POST['cvemenu']=1;
		$_POST['cveregistro']=$_SESSION['reg_sistema'];
	}
	
	function top($SESSION,$enter=0) {
		
		
		
		global $base,$PHP_SELF,$array_modulos,$_POST,$pre;
		
		
		
		//$url=split("/",$PHP_SELF);
		$url=split("/",$_SERVER["PHP_SELF"]);
		$url=array_reverse($url);
		
		
		
		$menuRS=mysql_db_query($base,"SELECT * FROM ".$pre."menu WHERE cve='".$_POST['cvemenu']."'");
		
		while($Menu=mysql_fetch_array($menuRS)) {
			
			$menuEncabezado=$Menu['nombre'];
			
		}
		
		
		echo '
		
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		
		
		
		<html xmlns="http://www.w3.org/1999/xhtml">
		
		<head>
		
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		
		<title>:: AUTOBUSES MEXICO NEXTLALPAN SA ::</title>
		
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		
		<link rel="stylesheet" type="text/css" href="calendar/dhtmlgoodies_calendar.css" />
		<style>
		.colorrojo { color: #FF0000 } 
		.panel {
		background:#DFE6EF;
		top:0px;
		left:0px;
		display:none;
		position:absolute;
		filter:alpha(opacity=40);
		opacity:.4;
        }
		</style>
		<script src="js/rutinas.js"></script>
		
		<link rel="stylesheet" type="text/css" href="css/ui.css" />
		<script src="js/jquery-1.8.0.min.js" type="text/javascript"></script>
		<script src="js/jquery-ui-1.8.23.custom.min.js" type="text/javascript"></script>
		<script src="js/serializeform.js" type="text/javascript"></script>
		
		<script src="calendar/dhtmlgoodies_calendar.js"></script>
		
		<script>
		
		function pulsar(e) {
		tecla=(document.all) ? e.keyCode : e.which;
		if(tecla==13) return false;
		}';
		foreach($array_modulos as $k=>$v){
			echo 'var menu'.$k.'=0;';
		}
		echo '
		function mueveReloj(){
		cadena=document.getElementById("idreloj").innerHTML;
		if(cadena.substr(11,1)=="0")
		var	horas = parseInt(cadena.substr(12,1));
		else
		var	horas = parseInt(cadena.substr(11,2));
		if(cadena.substr(14,1)=="0")
		var	minuto = parseInt(cadena.substr(15,1));
		else
		var	minuto = parseInt(cadena.substr(14,2));
		if(cadena.substr(17,1)=="0")
		var	segundo = parseInt(cadena.substr(18,1));
		else
		var	segundo = parseInt(cadena.substr(17,2));
		var	anio = parseInt(cadena.substr(0,4));
		if(cadena.substr(5,1)=="0")
		var	mes = parseInt(cadena.substr(6,1));
		else
		var	mes = parseInt(cadena.substr(5,2));
		if(cadena.substr(8,1)=="0")
		var	dia = parseInt(cadena.substr(9,1));
		else
		var	dia = parseInt(cadena.substr(8,2));
		segundo++;
		if (segundo==60) {
		segundo=0;
		minuto++;
		if (minuto==60) {
		minuto=0;
		horas++;
		if (horas==24) {
		horas=0;
		dia++;
		if((dia==31 && (mes==4 || mes==6 || mes==9 || mes==11)) || (dia==32 && (mes==1 || mes==3 || mes==5 || mes==7 || mes==8 || mes==10 || mes==12)) || (dia==29 && mes==2 && (anio%4)!=0) || (dia==30 && mes==2 && (anio%4)==0)){
		dia=1;
		mes++;
		}
		if(mes==13){
		mes=1;
		anio++;
		}
		}
		}
		}
		if(horas<10) horas="0"+parseInt(horas);
		if(minuto<10) minuto="0"+parseInt(minuto);
		if(segundo<10) segundo="0"+parseInt(segundo);
		if(dia<10) dia="0"+parseInt(dia);
		if(mes<10) mes="0"+parseInt(mes);
		horaImprimible = anio+"-"+mes+"-"+dia+" "+horas+":"+minuto+ ":"+segundo;
		
		document.getElementById("idreloj").innerHTML = horaImprimible;
		
		setTimeout("mueveReloj()",1000)
		}
		
		function cancelarRegistro(id, archivo, ajax){
		var regreso = false;
		if(confirm("¿Esta seguro de cancelar este folio?")){
		obs=prompt("Observaciones:");
		clavecancelacion = prompt("Clave de cancelación:");
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
		alert("Error: El Navegador no soporta AJAX");
		} else {
		objeto.open("POST",archivo,true);
		objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		objeto.send("ajax="+ajax+"&clavecancelacion="+clavecancelacion+"&id="+id+"&obs="+obs+"&usuario='.$_POST['cveusuario'].'");
		objeto.onreadystatechange = function()
		{
		if (objeto.readyState==4)
		{
		if(objeto.responseText=="1"){
		alert("La clave es invalida");
		}
		else{
		regreso = true;
		}
		}
		}
		}
		}
		return regreso;
		}
		</script>
		
		</head>
		
		
		
		<form name="forma" id="forma" method="POST" enctype="multipart/form-data">
		
		
		
		<!-- Definicion de variables ocultas -->
		
		<input type="hidden" name="cmd" id="cmd">
		
		<input type="hidden" name="cmdreferer" id="cmdreferer">
		
		<input type="hidden" name="reg" id="reg">
		
		<input type="hidden" name="cveusuario" id="cveusuario" value="'.$_POST['cveusuario'].'">
		
		<input type="hidden" name="cvemenu" id="cvemenu" value="'.$_POST['cvemenu'].'">
		
		<input type="hidden" name="cveregistro" id="cveregistro" value="'.$_POST['cveregistro'].'">
		
		<input type="hidden" name="numeroPagina" id="numeroPagina" value="0">
		
		
		
		<body'; if($enter==1) echo ' onkeypress="return pulsar(event)"'; echo '>
		<div id="panel" class="panel"></div>
		<table width="100%" height="50" border="0" cellpadding="0" cellspacing="0">
		
		<tr>
		
	    <td background="images/bannertop-bg.png"><span class="whiteText17">AUTOBUSES MEXICO NEXTLALPAN SA SERVIDOR 2020</span></td>
		
		</tr>
		
		</table>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		
		<tr>
		
		<td width="170" valign="top" bgcolor="#FFFFFF">
		
		';	
		if(nivelUsuario()==0){
			echo '<script>alert("No tiene acceso al menu");document.forma.cvemenu.value=1;atcr("inicio.php","",0,0);</script>';
		}
		
		menuppal2($SESSION);
		
		
		
		echo '
		
		
		
		</td>
		
		<td width="6" valign="top" background="images/collapse_side_bg.png"><img src="images/collapse_side_bg.png" width="6" height="1" /></td>
		
		<td valign="top" bgcolor="#FFFFFF"><table width="100%" border="0" cellspacing="0" cellpadding="0">
		
		<tr>
		
		<td width="30%" height="24" nowrap background="images/optionHeader.png"><b>:: '.$menuEncabezado.' ::</b></td>
		
		<td background="images/optionHeader.png"><div align="right">'.$SESSION['NomUsuario'].'</div></td>
		
		<td background="images/optionHeader.png"><div align="center" id="idreloj">'.fechaLocal().' '.horaLocal().'</div></td>
		
		<td width="15%" background="images/optionHeader.png" align="center" nowrap><a href="logout.php">Cerrar Sesion</a></td>
		
		</tr>
		
		</table>
		
		<br />
		
		<table width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
		
		<tr><td>
		
		<!-- INICIO REGION EDITABLE -->
		
		';
		
	}
	
	function topsocio($SESSION,$enter=0) {
		
		
		
		global $base,$PHP_SELF,$array_modulos,$_POST,$pre;
		
		
		
		//$url=split("/",$PHP_SELF);
		$url=split("/",$_SERVER["PHP_SELF"]);
		$url=array_reverse($url);
		
		
		
		$menuRS=mysql_db_query($base,"SELECT * FROM ".$pre."menu WHERE cve='".$_POST['cvemenu']."'");
		
		while($Menu=mysql_fetch_array($menuRS)) {
			
			$menuEncabezado=$Menu['nombre'];
			
		}
		
		$menuEncabezado="Estado cuenta unidad";
		
		
		echo '
		
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		
		
		
		<html xmlns="http://www.w3.org/1999/xhtml">
		
		<head>
		
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		
		<title>:: AUTOBUSES MEXICO NEXTLALPAN SA ::</title>
		
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		
		<link rel="stylesheet" type="text/css" href="calendar/dhtmlgoodies_calendar.css" />
		<style>
		.colorrojo { color: #FF0000 } 
		.panel {
		background:#DFE6EF;
		top:0px;
		left:0px;
		display:none;
		position:absolute;
		filter:alpha(opacity=40);
		opacity:.4;
        }
		</style>
		<script src="js/rutinas.js"></script>
		
		<link rel="stylesheet" type="text/css" href="css/ui.css" />
		<script src="js/jquery-1.8.0.min.js" type="text/javascript"></script>
		<script src="js/jquery-ui-1.8.23.custom.min.js" type="text/javascript"></script>
		<script src="js/serializeform.js" type="text/javascript"></script>
		
		<script src="calendar/dhtmlgoodies_calendar.js"></script>
		
		<script>
		
		function pulsar(e) {
		tecla=(document.all) ? e.keyCode : e.which;
		if(tecla==13) return false;
		}';
		foreach($array_modulos as $k=>$v){
			echo 'var menu'.$k.'=0;';
		}
		echo '
		function mueveReloj(){
		cadena=document.getElementById("idreloj").innerHTML;
		if(cadena.substr(11,1)=="0")
		var	horas = parseInt(cadena.substr(12,1));
		else
		var	horas = parseInt(cadena.substr(11,2));
		if(cadena.substr(14,1)=="0")
		var	minuto = parseInt(cadena.substr(15,1));
		else
		var	minuto = parseInt(cadena.substr(14,2));
		if(cadena.substr(17,1)=="0")
		var	segundo = parseInt(cadena.substr(18,1));
		else
		var	segundo = parseInt(cadena.substr(17,2));
		var	anio = parseInt(cadena.substr(0,4));
		if(cadena.substr(5,1)=="0")
		var	mes = parseInt(cadena.substr(6,1));
		else
		var	mes = parseInt(cadena.substr(5,2));
		if(cadena.substr(8,1)=="0")
		var	dia = parseInt(cadena.substr(9,1));
		else
		var	dia = parseInt(cadena.substr(8,2));
		segundo++;
		if (segundo==60) {
		segundo=0;
		minuto++;
		if (minuto==60) {
		minuto=0;
		horas++;
		if (horas==24) {
		horas=0;
		dia++;
		if((dia==31 && (mes==4 || mes==6 || mes==9 || mes==11)) || (dia==32 && (mes==1 || mes==3 || mes==5 || mes==7 || mes==8 || mes==10 || mes==12)) || (dia==29 && mes==2 && (anio%4)!=0) || (dia==30 && mes==2 && (anio%4)==0)){
		dia=1;
		mes++;
		}
		if(mes==13){
		mes=1;
		anio++;
		}
		}
		}
		}
		if(horas<10) horas="0"+parseInt(horas);
		if(minuto<10) minuto="0"+parseInt(minuto);
		if(segundo<10) segundo="0"+parseInt(segundo);
		if(dia<10) dia="0"+parseInt(dia);
		if(mes<10) mes="0"+parseInt(mes);
		horaImprimible = anio+"-"+mes+"-"+dia+" "+horas+":"+minuto+ ":"+segundo;
		
		document.getElementById("idreloj").innerHTML = horaImprimible;
		
		setTimeout("mueveReloj()",1000)
		}
		</script>
		
		</head>
		
		
		
		<form name="forma" id="forma" method="POST" enctype="multipart/form-data">
		
		
		
		<!-- Definicion de variables ocultas -->
		
		<input type="hidden" name="cmd" id="cmd">
		
		<input type="hidden" name="cmdreferer" id="cmdreferer">
		
		<input type="hidden" name="reg" id="reg">
		
		<input type="hidden" name="cveusuario" id="cveusuario" value="'.$_POST['cveusuario'].'">
		
		<input type="hidden" name="cvemenu" id="cvemenu" value="'.$_POST['cvemenu'].'">
		
		<input type="hidden" name="cveregistro" id="cveregistro" value="'.$_POST['cveregistro'].'">
		
		<input type="hidden" name="numeroPagina" id="numeroPagina" value="0">
		
		
		
		<body'; if($enter==1) echo ' onkeypress="return pulsar(event)"'; echo '>
		<div id="panel" class="panel"></div>
		<table width="100%" height="50" border="0" cellpadding="0" cellspacing="0">
		
		<tr>
		
	    <td background="images/bannertop-bg.png"><span class="whiteText17">AUTOBUSES MEXICO NEXTLALPAN SA SERVIDOR 2020</span></td>
		
		</tr>
		
		</table>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		
		<tr>
		
		<td width="170" valign="top" bgcolor="#FFFFFF">
		
		';	
		/*if(nivelUsuario()==0){
			echo '<script>alert("No tiene acceso al menu");document.forma.cvemenu.value=1;atcr("inicio.php","",0,0);</script>';
			}
			
		menuppal2($_SESSION);*/
		
		echo '
		<table width="100%" border="0" cellspacing="0" cellpadding="3">
		<tr><td height="20" bgcolor="#CC9933"><span class="style1">Menu</span></td></tr>
		<tr><td><a href="descargas/GpsTotal.apk">-App (GPS)</a></td></tr>
		<tr><td><a href="descargas/app-google-debug.apk">-Plataforma</a></td></tr>
		</table>';
		
		echo '
		
		
		
		</td>
		
		<td width="6" valign="top" background="images/collapse_side_bg.png"><img src="images/collapse_side_bg.png" width="6" height="1" /></td>
		
		<td valign="top" bgcolor="#FFFFFF"><table width="100%" border="0" cellspacing="0" cellpadding="0">
		
		<tr>
		
		<td width="30%" height="24" nowrap background="images/optionHeader.png"><b>:: '.$menuEncabezado.' ::</b></td>
		
		<td background="images/optionHeader.png"><div align="right">'.$SESSION['NomUsuario'].'</div></td>
		
		<td background="images/optionHeader.png"><div align="center" id="idreloj">'.fechaLocal().' '.horaLocal().'</div></td>
		
		<td width="15%" background="images/optionHeader.png" align="center" nowrap><a href="logout.php">Cerrar Sesion</a></td>
		
		</tr>
		
		</table>
		
		<br />
		
		<table width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
		
		<tr><td>
		
		<!-- INICIO REGION EDITABLE -->
		
		';
		
	}
	
	
	function top2($SESSION) {
		
		
		
		global $base,$PHP_SELF,$array_modulos,$nombrelink,$_POST,$pre;
		
		
		
		//$url=split("/",$PHP_SELF);
		$url=split("/",$_SERVER["PHP_SELF"]);
		$url=array_reverse($url);
		
		
		
		$menuRS=mysql_db_query($base,"SELECT * FROM ".$pre."menu");
		
		while($Menu=mysql_fetch_array($menuRS)) {
			
			if($url[0]==$Menu['link'])
			
			$menuEncabezado=$Menu['nombre'];
			
		}
		
		
		
		echo '
		
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		
		
		
		<html xmlns="http://www.w3.org/1999/xhtml">
		
		<head>
		
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		
		<title>:: AUTOBUSES MEXICO NEXTLALPAN SA ::</title>
		
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		
		<link rel="stylesheet" type="text/css" href="calendar/dhtmlgoodies_calendar.css" />
		<style>
		.colorrojo { color: #FF0000 } 
		.panel {
		background:#DFE6EF;
		top:0px;
		left:0px;
		display:none;
		position:absolute;
		filter:alpha(opacity=40);
		opacity:.4;
        }
		</style>
		<script src="js/rutinas.js"></script>
		
		<link rel="stylesheet" type="text/css" href="css/ui.css" />
		<script src="js/jquery-1.8.0.min.js" type="text/javascript"></script>
		<script src="js/jquery-ui-1.8.23.custom.min.js" type="text/javascript"></script>
		<script src="js/serializeform.js" type="text/javascript"></script>
		
		<script src="calendar/dhtmlgoodies_calendar.js"></script>
		
		<script>
		/*var fecha = "'.fechaLocal().' '.horaLocal().'";
		var	momentoActual = new Date(fecha);
		var	hora = momentoActual.getHours();
		var	minuto = momentoActual.getMinutes();
		var	segundo = momentoActual.getSeconds();
		var	dia = momentoActual.getDate();
		var	mes = momentoActual.getMonth()+1;
		var	anio = momentoActual.getFullYear();*/
		/*var	horas = parseInt("'.substr(horaLocal(),0,2).'");
		var	minuto = parseInt("'.substr(horaLocal(),3,2).'");
		var	segundo = parseInt("'.substr(horaLocal(),6,2).'");
		var	anio = parseInt("'.substr(fechaLocal(),0,4).'");
		var	mes = parseInt("'.substr(fechaLocal(),5,2).'");
		var	dia = parseInt("'.substr(fechaLocal(),8,2).'");*/
		';
		foreach($array_modulos as $k=>$v){
			echo 'var menu'.$k.'=0;';
		}
		echo '
		function mueveReloj(){
		cadena=document.getElementById("idreloj").innerHTML;
		if(cadena.substr(11,1)=="0")
		var	horas = parseInt(cadena.substr(12,1));
		else
		var	horas = parseInt(cadena.substr(11,2));
		if(cadena.substr(14,1)=="0")
		var	minuto = parseInt(cadena.substr(15,1));
		else
		var	minuto = parseInt(cadena.substr(14,2));
		if(cadena.substr(17,1)=="0")
		var	segundo = parseInt(cadena.substr(18,1));
		else
		var	segundo = parseInt(cadena.substr(17,2));
		var	anio = parseInt(cadena.substr(0,4));
		if(cadena.substr(5,1)=="0")
		var	mes = parseInt(cadena.substr(6,1));
		else
		var	mes = parseInt(cadena.substr(5,2));
		if(cadena.substr(8,1)=="0")
		var	dia = parseInt(cadena.substr(9,1));
		else
		var	dia = parseInt(cadena.substr(8,2));
		segundo++;
		if (segundo==60) {
		segundo=0;
		minuto++;
		if (minuto==60) {
		minuto=0;
		horas++;
		if (horas==24) {
		horas=0;
		dia++;
		if((dia==31 && (mes==4 || mes==6 || mes==9 || mes==11)) || (dia==32 && (mes==1 || mes==3 || mes==5 || mes==7 || mes==8 || mes==10 || mes==12)) || (dia==29 && mes==2 && (anio%4)!=0) || (dia==30 && mes==2 && (anio%4)==0)){
		dia=1;
		mes++;
		}
		if(mes==13){
		mes=1;
		anio++;
		}
		}
		}
		}
		if(horas<10) horas="0"+parseInt(horas);
		if(minuto<10) minuto="0"+parseInt(minuto);
		if(segundo<10) segundo="0"+parseInt(segundo);
		if(dia<10) dia="0"+parseInt(dia);
		if(mes<10) mes="0"+parseInt(mes);
		horaImprimible = anio+"-"+mes+"-"+dia+" "+horas+":"+minuto+ ":"+segundo;
		
		document.getElementById("idreloj").innerHTML = horaImprimible;
		
		setTimeout("mueveReloj()",1000)
		}
		</script>
		
		</head>
		
		
		
		<form name="forma" id="forma" method="POST" enctype="multipart/form-data">
		
		
		
		<!-- Definicion de variables ocultas -->
		
		<input type="hidden" name="cmd" id="cmd">
		
		<input type="hidden" name="cmdreferer" id="cmdreferer">
		
		<input type="hidden" name="reg" id="reg">
		
		<input type="hidden" name="numeroPagina" id="numeroPagina" value="0">
		
		<body>
		<div id="panel" class="panel"></div>
		<table width="100%" height="50" border="0" cellpadding="0" cellspacing="0">
		
		<tr>
		
	    <td background="images/bannertop-bg.png"><span class="whiteText17">AUTOBUSES MEXICO NEXTLALPAN SA SERVIDOR 2020</span></td>
		
		</tr>
		
		</table>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		
		<tr>
		
		<td width="6" valign="top" background="images/collapse_side_bg.png"><img src="images/collapse_side_bg.png" width="6" height="1" /></td>
		
		<td valign="top" bgcolor="#FFFFFF"><table width="100%" border="0" cellspacing="0" cellpadding="0">
		
		<tr>
		
		<td width="30%" height="24" nowrap background="images/optionHeader.png"><b>:: '.$nombrelink.' ::</b></td>
		
		<td background="images/optionHeader.png"><div align="right">'.$SESSION['NomUsuario'].'</div></td>
		
		<td background="images/optionHeader.png"><div align="center" id="idreloj">'.fechaLocal().' '.horaLocal().'</div></td>
		
		<td width="15%" background="images/optionHeader.png" align="center" nowrap>&nbsp;</td>
		
		</tr>
		
		</table>
		
		<br />
		
		<table width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
		
		<tr><td>
		
		<!-- INICIO REGION EDITABLE -->
		
		';
		
	}
	
	
	function bottom() {
		
		
		
		echo '
		
		<!-- FIN REGION EDITABLE -->		
		
		</td></tr>
		
		</table>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		</td>
		
		</tr>
		
		<tr>
		
	    <td colspan="3" valign="top" bgcolor="#CC9933">&nbsp;</td>
		
		</tr>
		
		</table>
		
		</body>
		<script>
		mueveReloj();
		window.onload=function(){
		if (self.screen.availWidth) {
		$("#panel").css("width",parseFloat(self.screen.availWidth)+50);
		}
		if (self.screen.availHeight) {
		$("#panel").css("height",self.screen.availHeight);
		}
        }  
		</script>
		</form>
		
		</html>
		
		';
		
	}
	
	function nivelUsuario(){
		global $_POST,$base,$pre;
		if($_POST['cveusuario']==1 || $_POST['cvemenu']==1){
			return 3;
		}
		else{
			$res=mysql_db_query($base,"SELECT * FROM ".$pre."usuario_accesos WHERE usuario='".$_POST['cveusuario']."' AND menu='".$_POST['cvemenu']."'");
			if($row=mysql_fetch_array($res)){
				return $row['acceso'];
			}
			else{
				return 0;
			}
		}
	}
	
	
	function menuppal2($SESSION) {
		global $base,$array_modulos,$PHP_SELF,$_POST,$pre;
		$url=split("/",$_SERVER["PHP_SELF"]);
		$url=array_reverse($url);
		echo '
		<table width="100%" border="0" cellspacing="0" cellpadding="3">
		<tr><td height="20" bgcolor="#CC9933"><span class="style1">Menu</span></td></tr>
		<tr><td><a href="#" onClick="document.forma.cvemenu.value=1;atcr(\'inicio.php\',\'\',\'\',\'\')">-P&aacute;gina de Inicio</a></td></tr>
		<tr><td><a href="descargas/GpsTotal.apk">-App (GPS)</a></td></tr>
		<tr><td><a href="descargas/app-google-debug.apk">-Plataforma</a></td></tr>';
		//if($_SESSION['CveUsuario']==1){
		//echo '<tr><td><a href="#" onClick="atcr(\'inicio.php\',\'\',\'10\',\'\')">-Borrar Tablas Recaudaion</a></td></tr>';
		//}
		$mostrar="";
		foreach($array_modulos as $k=>$v){ 
			if($_POST['cveusuario']==1){
				$rs=mysql_db_query($base,"SELECT * FROM ".$pre."menu WHERE modulo='$k' ORDER BY orden");
			}
			else{
				
				$rs=mysql_db_query($base,"SELECT a.* FROM ".$pre."menu as a INNER JOIN ".$pre."usuario_accesos as b ON (b.menu=a.cve AND b.usuario='".$_POST['cveusuario']."' AND b.acceso>0) WHERE a.modulo='$k' ORDER BY a.orden");
				
			}
			if(mysql_num_rows($rs)>0){
				
				echo '
				<tr>	  
				<td height="20" bgcolor="#CC9933">
				<span id="tmenu1" class="style1" onClick="if((menu'.$k.'%2)==0) $(\'.cmenu'.$k.'\').show(\'slow\'); else $(\'.cmenu'.$k.'\').hide(\'slow\'); menu'.$k.'++;">
				'.$v.'
				</span>
				</td>
				</tr>
				<tr><td><table class="cmenu'.$k.'" style="display:none">';
				while($ro=mysql_fetch_array($rs)) {
					if(($_POST['cveusuario']>1) and ($ro['cve']==35 or $ro['cve']==36)){
						/*echo '
							<tr><td><a href="#" onClick="
							objeto=crearObjeto();
							if (objeto.readyState != 0) {
							alert(\'Error: El Navegador no soporta AJAX\');
							} else {
							objeto.open(\'POST\',\'inicio.php\',true);
							objeto.setRequestHeader(\'Content-Type\',\'application/x-www-form-urlencoded\');
							objeto.send(\'ajax=99&usuario='.$_POST['cveusuario'].'&cvereg='.$_POST['cveregistro'].'&idmenu='.$ro['cve'].'\');
							objeto.onreadystatechange = function()
							{
							if (objeto.readyState==4)
							{document.forma.cvemenu.value='.$ro['cve'].';atcr(\''.$ro['link'].'\',\'\',\'0\',\'\');}
							}
							}
							">-*'.$ro['nombre'].'</a></td></tr>';
							if($_POST['cvemenu']==$ro['cve'])
						$mostrar='cmenu'.$k;*/
						}else{
						echo '
						<tr><td><a href="#" onClick="
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
						alert(\'Error: El Navegador no soporta AJAX\');
						} else {
						objeto.open(\'POST\',\'inicio.php\',true);
						objeto.setRequestHeader(\'Content-Type\',\'application/x-www-form-urlencoded\');
						objeto.send(\'ajax=99&usuario='.$_POST['cveusuario'].'&cvereg='.$_POST['cveregistro'].'&idmenu='.$ro['cve'].'\');
						objeto.onreadystatechange = function()
						{
						if (objeto.readyState==4)
						{document.forma.cvemenu.value='.$ro['cve'].';atcr(\''.$ro['link'].'\',\'\',\'0\',\'\');}
						}
						}
						">-'.$ro['nombre'].'</a></td></tr>';
						if($_POST['cvemenu']==$ro['cve'])
						$mostrar='cmenu'.$k;
					}
				}
				if($k==99){
					//			echo '<tr><td><a href="http://gamn.mx/taller/" target="_blank" >GAMN (Taller)</a></td></tr>';
					//			echo '<tr><td><a href="http://104.237.136.236:8082" target="_blank" >GAMN (Plataforma)</a></td></tr>';
				}
				echo '</table></td></tr>';
			}
		}
		echo '</table>';
		if($mostrar!='') {
			echo '<script language="javascript">$(\'.'.$mostrar.'\').show();'.substr($mostrar,1).'++;</script>';
		}
	}
	
	function menunavegacion() {
		
		
		
		global $totalRegistros, $eTotalPaginas, $eNumeroPagina, $primerRegistro, $eAnteriorPagina, $eSiguientePagina, $eNumeroPagina;
		
		
		
		echo '
		
		
		
		<table width="100%" height="20" border="0" cellpadding="0" cellspacing="0">
		
		<tr>
		
		<td width="20%" class="">'.$totalRegistros.'</font> Registro(s)</td>';
		
		if ($eTotalPaginas>0) {
			
			echo '
			
			<td width="60%" class="" align="right">P&aacute;gina <font class="fntN10B">';print $eNumeroPagina+1; echo'</font> de <font class="fntN10B">'; print $eTotalPaginas+1; echo'</font> </td>';
			
			if ($primerRegistro>0) {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"><a href="JavaScript:moverPagina(0);"><img src="images/mover-primero.gif" width="10" height="12" border="0" align="absmiddle" title="Inicio"></a> </td>';
				
				} else {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"><img src="images/mover-primero-d.gif" width="10" height="12" border="0" align="absmiddle"></td>';
				
			}
			
			
			
			if ($eAnteriorPagina>=0) {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"><a href="JavaScript:moverPagina('.$eAnteriorPagina.');"><img src="images/mover-anterior.gif" width="7" height="12" border="0" align="absmiddle" title="Anterior"></a></td>';
				
				} else {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"><img src="images/mover-anterior-d.gif" width="7" height="12" border="0" align="absmiddle"></td>';
				
			}
			
			
			
			if ($eSiguientePagina<=$eTotalPaginas) {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"><a href="JavaScript:moverPagina('.$eSiguientePagina.');"><img src="images/mover-siguiente.gif" width="7" height="12" border="0" align="absmiddle" title="Siguiente"></a></td>';
				
				} else {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"><img src="images/mover-siguiente-d.gif" width="7" height="12" border="0" align="absmiddle"></td>';
				
			}
			
			
			
			if ($eNumeroPagina<$eTotalPaginas) {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"> <a href="JavaScript:moverPagina('.$eTotalPaginas.');"><img src="images/mover-ultimo.gif" width="10" height="12" border="0" align="absmiddle" title="Fin"></a></td>';
				
				} else {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"><img src="images/mover-ultimo-d.gif" width="10" height="12" border="0" align="absmiddle"></td>';
				
			}
			
			
			
		}
		
		echo '
		
		</tr>
		
		</table>';
		
		
		
	}
	
	
	
	
	
	function menu() {
		
		echo '';
		
	}
	
	
	
	// Renglon en fondo Blanco
	
	function rowc() {
		
		echo '<tr bgcolor="#ffffff" onmouseover="sc(this, 1, 0);" onmouseout="sc(this, 0, 0);" onmousedown="sc(this, 2, 0);">';
		
	}
	
	
	
	// Renglones que cambian el color de fondo
	
	function rowb() {
		
		static $rc;
		
		if ($rc) {
			
			echo '<tr bgcolor="#d5d5d5" onmouseover="sc(this, 1, 1);" onmouseout="sc(this, 0, 1);" onmousedown="sc(this, 2, 1);">';
			
			$rc=FALSE;
			
		}
		
		else {
			
			echo '<tr bgcolor="#e5e5e5" onmouseover="sc(this, 1, 2);" onmouseout="sc(this, 0, 2);" onmousedown="sc(this, 2, 2);">';
			
			$rc=TRUE;
			
		}
		
	}
	
	
	
	
	
	function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
	
	{
		
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
		
		
		
		$theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);
		
		
		
		switch ($theType) {
			
			case "text":
			
			$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
			
			break;    
			
			case "long":
			
			case "int":
			
			$theValue = ($theValue != "") ? intval($theValue) : "NULL";
			
			break;
			
			case "double":
			
			$theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
			
			break;
			
			case "date":
			
			$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
			
			break;
			
			case "defined":
			
			$theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
			
			break;
			
		}
		
		return $theValue;
		
	}
	
	
	
	
	
	function diaSemana($fecha) {
		
		$weekDay=array('DOMINGO','LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO');
		
		$ano=substr($fecha,0,4);
		
		$mes=substr($fecha,5,2);
		
		$dia=substr($fecha,8,2);
		
		$numDia=jddayofweek ( cal_to_jd(CAL_GREGORIAN, date($mes),date($dia), date($ano)) , 0 );
		
		$result=$weekDay[$numDia];
		
		return $result;
		
	}
	
	
	
	function horaLocal() {
		
		$differencetolocaltime=1;
		
		$new_U=date("U")+$differencetolocaltime*3600;
		
		//$fulllocaldatetime= date("d-m-Y h:i:s A", $new_U);
		
		$hora= date("H:i:s", $new_U);
		
		$hora=date( "Y-m-d H:i:s" , strtotime ( "0 hour" , strtotime(date("Y-m-d H:i:s")) ) );
		
		$hora=date( "H:i:s" , strtotime ( "0 minute" , strtotime($hora) ) );
		
		return $hora;
		
		//Regards. Mohammed Ahmad. MSN: m@maaking.com
		
	}
	
	function fechaLocal(){
		$differencetolocaltime=1;
		
		$new_U=date("U")+$differencetolocaltime*3600;
		
		//$fulllocaldatetime= date("d-m-Y h:i:s A", $new_U);
		
		//$fecha= date("Y-m-d", $new_U);
		
		$fecha=date( "Y-m-d H:i:s" , strtotime ( "0 hour" , strtotime(date("Y-m-d H:i:s")) ) );
		
		$fecha=date( "Y-m-d" , strtotime ( "0 minute" , strtotime($fecha) ) );
		
		return $fecha;
	}
	
	function fechahoraLocal(){
		$differencetolocaltime=1;
		
		$new_U=date("U")+$differencetolocaltime*3600;
		
		//$fulllocaldatetime= date("d-m-Y h:i:s A", $new_U);
		
		$//fechahora= date("Y-m-d H:i:s", $new_U);
		
		$fechahora=date( "Y-m-d H:i:s" , strtotime ( "0 hour" , strtotime(date("Y-m-d H:i:s")) ) );
		
		$fechahora=date( "Y-m-d H:i:s" , strtotime ( "0 minute" , strtotime($fechahora) ) );
		
		return $fechahora;
	}
	
	function fecha_letra($fecha){
		$fecven=split("-",$fecha);
		$fecha_letra=$fecven[2]." de ";;
		switch($fecven[1]){
			case "01":$fecha_letra.="Enero";break;
			case "02":$fecha_letra.="Febrero";break;
			case "03":$fecha_letra.="Marzo";break;
			case "04":$fecha_letra.="Abril";break;
			case "05":$fecha_letra.="Mayo";break;
			case "06":$fecha_letra.="Junio";break;
			case "07":$fecha_letra.="Julio";break;
			case "08":$fecha_letra.="Agosto";break;
			case "09":$fecha_letra.="Septiembre";break;
			case "10":$fecha_letra.="Octubre";break;
			case "11":$fecha_letra.="Noviembre";break;
			case "12":$fecha_letra.="Diciembre";break;
		}
		$fecha_letra.=" del ".$fecven[0]."";
		return $fecha_letra;
	}
	
	function fechaNormal($fecha){
		$arrFecha=explode("-",$fecha);
		return $arrFecha[2].'/'.$arrFecha[1].'/'.$arrFecha[0];
	}
	
	function traer_numero_semana($fechasem){
		global $base,$pre;
		$anio=substr($fechasem,0,4);
		$fecha=$anio.'-01-01';
		$arfecha=explode("-",$fecha);
		$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
		if($dia!=1){
			$dias=8-$dia;
			$fecha=date( "Y-m-d" , strtotime ( "+".$dias." day" , strtotime($fecha) ) );
		}
		$fecha=date( "Y-m-d" , strtotime ( "+2 day" , strtotime($fecha) ) );
		if($fechasem<$fecha){
			$anio--;
			$fecha=$anio.'-01-01';
			$arfecha=explode("-",$fecha);
			$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
			if($dia!=1){
				$dias=8-$dia;
				$fecha=date( "Y-m-d" , strtotime ( "+".$dias." day" , strtotime($fecha) ) );
			}
			$fecha=date( "Y-m-d" , strtotime ( "+2 day" , strtotime($fecha) ) );
		}
		$res=mysql_db_query($base,"SELECT TO_DAYS('$fechasem')-TO_DAYS('$fecha')");
		$row=mysql_fetch_array($res);
		$semana=intval($row[0]/7)+1;
		return $semana;
	}
	
	function traer_fechas_semana($semana,$anio){
		$fecha=$anio.'-01-01';
		$arfecha=explode("-",$fecha);
		$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
		if($dia!=1){
			$dias=8-$dia;
			$fecha=date( "Y-m-d" , strtotime ( "+".$dias." day" , strtotime($fecha) ) );
		}
		$fecha=date( "Y-m-d" , strtotime ( "+2 day" , strtotime($fecha) ) );
		$fecha_ini=date( "Y-m-d" , strtotime ( "+".(($semana-1)*7)." day" , strtotime($fecha) ) );
		$fecha_fin=date( "Y-m-d" , strtotime ( "+6 day" , strtotime($fecha_ini) ) );
		return $fecha_ini.' - '.$fecha_fin;
	}
	
	function saldo_unidad($unidad, $tipo = 0, $dato = 0, $fecha_ini = "", $fecha_fin = "", $detallado = false){
		global $base;
		$base2 = "enero_aaz";
		$abono=0;
		$cargo=0;
		$res=mysql_db_query($base,"SELECT cve_ori FROM parque WHERE cve=".$unidad);
		$row=mysql_fetch_array($res);
		$cveori=$row['cve_ori'];
		$respuesta = array();
		if($tipo==0){
			if($dato==0 || $dato==1){
				$res=mysql_db_query($base,"SELECT sum(b.monto) FROM traspaso as a INNER JOIN traspasomov as b ON (b.traspaso=a.cve AND b.unidad='".$unidad."') WHERE a.estatus!='C' AND a.fechaapl>='2016-11-30' GROUP BY b.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$respuesta['traspaso'] += $row[0];
				$res=mysql_db_query($base,"SELECT sum(a.monto) FROM cargos_parque as a WHERE a.unidad='".$unidad."' AND a.fecha>='2016-11-30' GROUP BY a.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$respuesta['cargo_admon'] += $row[0];
				$res=mysql_db_query($base,"SELECT sum(a.total) FROM cargos_variables_unidades as a WHERE a.unidad='".$unidad."' AND a.sta!='C' AND a.fecha_ini>='2016-11-30' GROUP BY a.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$respuesta['cargo_admon'] += $row[0];
			}
			if($dato==0 || $dato==2){
				$res=mysql_db_query($base,"SELECT SUM(monto) FROM parque_abono WHERE unidad='".$unidad."' AND estatus!='C' AND fecha>='2016-11-30'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
				$respuesta['recaudacion'] += $row[0];
				$res=mysql_db_query($base,"SELECT SUM(monto) FROM recibos_entrada WHERE unidad='".$unidad."' AND estatus!='C' AND fecha>='2016-11-30'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
				$respuesta['entradas'] += $row[0];
			}
		}
		elseif($tipo==1){
			if($dato==0 || $dato==1){
				
				$res=mysql_db_query($base,"SELECT sum(b.monto) FROM traspaso as a INNER JOIN traspasomov as b ON (b.traspaso=a.cve AND b.unidad='".$unidad."') WHERE a.estatus!='C' AND a.fechaapl>='2016-11-30' AND a.fechaapl<'$fecha_ini' GROUP BY b.unidad") or die(mysql_error());
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$respuesta['traspaso'] += $row[0];
				$res=mysql_db_query($base,"SELECT sum(a.monto) FROM cargos_parque as a WHERE a.unidad='".$unidad."' AND a.fecha>='2016-11-30' AND a.fecha<'".$fecha_ini."' GROUP BY a.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$respuesta['cargo_admon'] += $row[0];
				$res=mysql_db_query($base,"SELECT sum(a.total) FROM cargos_variables_unidades as a WHERE a.unidad='".$unidad."' AND a.fecha_ini>='2016-11-30' AND a.fecha_ini<'".$fecha_ini."' AND a.sta!='C' GROUP BY a.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$respuesta['cargo_admon'] += $row[0];
				
			}
			if($dato==0 || $dato==2){
				
				$res=mysql_db_query($base,"SELECT SUM(monto) FROM parque_abono WHERE unidad='".$unidad."' AND estatus!='C' AND fecha>='2016-11-30' AND fecha<'".$fecha_ini."'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
				$respuesta['recaudacion'] += $row[0];
				$res=mysql_db_query($base,"SELECT SUM(monto) FROM recibos_entrada WHERE unidad='".$unidad."' AND estatus!='C' AND fecha>='2016-11-30' AND fecha<'".$fecha_ini."'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
				$respuesta['entradas'] += $row[0];
			}
		}
		elseif($tipo==2){
			if($dato==0 || $dato==1){
				$res=mysql_db_query($base,"SELECT sum(b.monto) FROM traspaso as a INNER JOIN traspasomov as b ON (b.traspaso=a.cve AND b.unidad='".$unidad."') WHERE a.estatus!='C' AND a.fechaapl>='2016-11-30' AND a.fechaapl BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' GROUP BY b.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$respuesta['traspaso'] += $row[0];
				$res=mysql_db_query($base,"SELECT sum(a.monto) FROM cargos_parque as a WHERE a.unidad='".$unidad."' AND a.fecha>='2016-11-30' AND a.fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' GROUP BY a.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$respuesta['cargo_admon'] += $row[0];
				$res=mysql_db_query($base,"SELECT sum(a.total) FROM cargos_variables_unidades as a WHERE a.unidad='".$unidad."' AND a.fecha_ini>='2016-11-30' AND a.fecha_ini BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND a.sta!='C' GROUP BY a.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$respuesta['cargo_admon'] += $row[0];
			}
			if($dato==0 || $dato==2){
				$res=mysql_db_query($base,"SELECT SUM(monto) FROM parque_abono WHERE unidad='".$unidad."' AND estatus!='C' AND fecha>='2016-11-30' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
				$respuesta['recaudacion'] += $row[0];
				$res=mysql_db_query($base,"SELECT SUM(monto) FROM recibos_entrada WHERE unidad='".$unidad."' AND estatus!='C' AND fecha>='2016-11-30' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
				$respuesta['entradas'] += $row[0];
			}
		}
		if($detallado){
			$respuesta['cargo'] = $cargo;
			$respuesta['abono'] = $abono;
			return $respuesta;
		}
		else{
			if($dato==0) return $abono-$cargo;
			elseif($dato==1) return $cargo;
			elseif($dato==2) return $abono;
		}
	}
	
	function saldo_unidad_r2($unidad, $tipo = 0, $dato = 0, $fecha_ini = "", $fecha_fin = ""){
		global $base;
		$base2 = "enero_aaz";
		$abono=0;
		$cargo=0;
		$res=mysql_db_query($base,"SELECT cve_ori FROM parque WHERE cve=".$unidad);
		$row=mysql_fetch_array($res);
		$cveori=$row['cve_ori'];
		if($tipo==0){
			if($dato==0 || $dato==1){
				$res=mysql_db_query($base,"SELECT sum(b.monto) FROM traspaso as a INNER JOIN traspasomov as b ON (b.traspaso=a.cve AND b.unidad='".$unidad."') WHERE a.estatus!='C' GROUP BY b.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$res=mysql_db_query($base,"SELECT sum(a.monto) FROM cargos_parque as a WHERE a.unidad='".$unidad."' GROUP BY a.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$res=mysql_db_query($base,"SELECT sum(a.total) FROM cargos_variables_unidades as a WHERE a.unidad='".$unidad."' AND a.sta!='C' GROUP BY a.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
			}
			if($dato==0 || $dato==2){
				$res=mysql_db_query($base,"SELECT SUM(monto) FROM parque_abono WHERE unidad='".$unidad."' AND estatus!='C'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
				$res=mysql_db_query($base,"SELECT SUM(monto) FROM recibos_entrada WHERE unidad='".$unidad."' AND estatus!='C'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
			}
		}
		elseif($tipo==1){
			if($dato==0 || $dato==1){
				
				$res=mysql_db_query($base,"SELECT sum(b.monto) FROM traspaso as a INNER JOIN traspasomov as b ON (b.traspaso=a.cve AND b.unidad='".$unidad."') WHERE a.estatus!='C' AND a.fechaapl>='2016-11-30' AND a.fechaapl<'$fecha_ini' GROUP BY b.unidad") or die(mysql_error());
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$res=mysql_db_query($base,"SELECT sum(a.monto) FROM cargos_parque as a WHERE a.unidad='".$unidad."' AND a.fecha>='2016-11-30' AND a.fecha<'".$fecha_ini."' GROUP BY a.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$res=mysql_db_query($base,"SELECT sum(a.total) FROM cargos_variables_unidades as a WHERE a.unidad='".$unidad."' AND a.fecha_ini>='2016-11-30' AND a.fecha_ini<'".$fecha_ini."' AND a.sta!='C' GROUP BY a.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				
			}
			if($dato==0 || $dato==2){
				
				$res=mysql_db_query($base,"SELECT SUM(monto) FROM parque_abono WHERE unidad='".$unidad."' AND estatus!='C' AND fecha>='2016-11-30' AND fecha<'".$fecha_ini."'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
				$res=mysql_db_query($base,"SELECT SUM(monto) FROM recibos_entrada WHERE unidad='".$unidad."' AND estatus!='C' AND fecha>='2016-11-30' AND fecha<'".$fecha_ini."'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
			}
		}
		elseif($tipo==2){
			if($dato==0 || $dato==1){
				$res=mysql_db_query($base,"SELECT sum(b.monto) FROM traspaso as a INNER JOIN traspasomov as b ON (b.traspaso=a.cve AND b.unidad='".$unidad."') WHERE a.estatus!='C' AND a.fechaapl>='2016-11-30' AND a.fechaapl BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' GROUP BY b.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$res=mysql_db_query($base,"SELECT sum(a.monto) FROM cargos_parque as a WHERE a.unidad='".$unidad."' AND a.fecha>='2016-11-30' AND a.fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' GROUP BY a.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				$res=mysql_db_query($base,"SELECT sum(a.total) FROM cargos_variables_unidades as a WHERE a.unidad='".$unidad."' AND a.fecha_ini>='2016-11-30' AND a.fecha_ini BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND a.sta!='C' GROUP BY a.unidad");
				$row=mysql_fetch_array($res);
				$cargo+=$row[0];
				
			}
			if($dato==0 || $dato==2){
				$res=mysql_db_query($base,"SELECT SUM(monto) FROM parque_abono WHERE unidad='".$unidad."' AND estatus!='C' AND fecha>='2016-11-30' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
				$res=mysql_db_query($base,"SELECT SUM(monto) FROM recibos_entrada WHERE unidad='".$unidad."' AND estatus!='C' AND fecha>='2016-11-30' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
			}
		}
		if($dato==0) return $abono-$cargo;
		elseif($dato==1) return $cargo;
		elseif($dato==2) return $abono;
	}
	function saldo_unidadm($unidad, $tipo = 0, $dato = 0, $fecha_ini = "", $fecha_fin = ""){
		global $base;
		$base2 = "enero_aaz";
		$abono=0;
		$cargo=0;
		$res=mysql_db_query($base,"SELECT cve_ori FROM parque WHERE cve=".$unidad);
		$row=mysql_fetch_array($res);
		$cveori=$row['cve_ori'];
		if($tipo==0){
			if($dato==0 || $dato==1){
			}
			if($dato==0 || $dato==2){
				$res=mysql_db_query($base,"SELECT SUM(mutualidad) FROM parque_abono WHERE unidad='".$unidad."' AND estatus!='C' AND fecha>='2016-11-30'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
			}
		}
		elseif($tipo==1){
			if($dato==0 || $dato==1){
			}
			if($dato==0 || $dato==2){
				$res=mysql_db_query($base,"SELECT SUM(mutualidad) FROM parque_abono WHERE unidad='".$unidad."' AND estatus!='C' AND fecha>='2016-11-30' AND fecha < '".$fecha_ini."'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
			}
		}
		elseif($tipo==2){
			if($dato==0 || $dato==1){
			}
			if($dato==0 || $dato==2){
				$res=mysql_db_query($base,"SELECT SUM(mutualidad) FROM parque_abono WHERE unidad='".$unidad."' AND estatus!='C' AND fecha>='2016-11-30' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."'");
				$row=mysql_fetch_array($res);
				$abono+=$row[0];
			}
		}
		if($dato==0) return $abono-$cargo;
		elseif($dato==1) return $cargo;
		elseif($dato==2) return $abono;
	}
	
	function diferenciapunto($anterior, $nuevo){
		$res2 = mysql_query("SELECT TIMEDIFF('$nuevo','$anterior')");
		$row2 = mysql_fetch_array($res2);
		return $row2[0];
	}
?>