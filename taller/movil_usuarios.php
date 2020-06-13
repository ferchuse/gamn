<?php
session_start();
include ("main.php");
mysql_select_db('gamn_gps_otra_plataforma');
$rsMotivo=mysql_query("SELECT * FROM dispositivos WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_dispositivos[$Motivo['cve']]=$Motivo['nombre'];
}
mysql_select_db('gamn_gps');
if($_POST['ajax']==1){

	mysql_select_db('gamn_gps_otra_plataforma');
	$select= " SELECT * FROM usuariogpsmovil WHERE 1";
	if($_POST['nombre']!="") $select .= " AND usuario like '%".$_POST['nombre']."'%";
	$select .= " ORDER BY usuario ";
	$res=mysql_query($select) or die(mysql_error());
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8">
		  <th width="50">&nbsp;</th><th>Nombre</th>
		  </tr>';
	while($row=mysql_fetch_array($res)){
		rowb();
		echo'<td align="center"><a href="#" onClick="atcr(\'movil_usuarios.php\',\'\',\'1\','.$row['cve'].')">'.$imgeditar.'</a></td>';

		echo'<td align="left">'.$row['usuario'].'</td>';

		echo'</tr>';
	}
	echo'<tr bgcolor="#E9F2F8"><td align="left" colspan="5">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table>';
exit();
}
 if($_POST["ajax"]==2)
   {
	 if($_POST['cod']>0){
		  echo "no";
		  exit(); 
	 }else{
		 mysql_select_db('gamn_gps_otra_plataforma');
		$rs="select * from usuariogpsmovil where usuario='".$_POST['usuario']."'";
	   $rsrfc=mysql_query($rs);
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
   if($_POST['cmd']==-2){
	   $x=0;
	   foreach($array_dispositivos as $k=>$v){
	    if($_POST['opcion'.$k.'']!=""){$x++;}
	   }
	   $y=1;
	   $unidades="";
	   foreach($array_dispositivos as $k=>$v){
//		   echo''.$v.'';
		 if($y==$x){
			if($_POST['opcion'.$k.'']!=""){$unidades.=$_POST['opcion'.$k.''];}
		 }else{
		 if($_POST['opcion'.$k.'']!=""){$unidades.=$_POST['opcion'.$k.''].",";$y++;}
		 
		 }
		 
		}
	   
//		echo''.$unidades.'';
//	   print_r($_POST);
   $_POST['cmd']=0;
   }
  if($_POST['cmd']==2){
	  mysql_select_db('gamn_gps_otra_plataforma');
	if ($_POST['reg']>0){
        $select=" SELECT * FROM usuariogpsmovil WHERE cve='".$_POST['reg']."' ";
       $rsprovedor=mysql_query($select);
       $Usuario=mysql_fetch_array($rsprovedor);
		
		$x=0;
	   foreach($array_dispositivos as $k=>$v){
	    if($_POST['opcion'.$k.'']!=""){$x++;}
	   }
	   $y=1;
	   $unidades="";
	   foreach($array_dispositivos as $k=>$v){
//		   echo''.$v.'';
		 if($y==$x){
			if($_POST['opcion'.$k.'']!=""){$unidades.=$_POST['opcion'.$k.''];}
		 }else{
		 if($_POST['opcion'.$k.'']!=""){$unidades.=$_POST['opcion'.$k.''].",";$y++;}
		 
		 }
		 
		}
	   
	   if($Usuario['password']!=$_POST['pass']){
		   mysql_select_db('gamn_gps');
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Password',nuevo='".$_POST['pass']."',anterior='".$Usuario['password']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['dispositivos']!=$unidades){
			mysql_select_db('gamn_gps');
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Dispositivos',nuevo='".$unidades."',anterior='".$Usuario['dispositivos']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		
		
		mysql_select_db('gamn_gps_otra_plataforma');
		$sSQL="update usuariogpsmovil
				SET password='".$_POST['pass']."',dispositivos='".$unidades."' where cve='".$_POST['reg']."'";
		mysql_query($sSQL);
		


		
		
		
	}
	else{
		
		/*$x=0;
	   foreach($array_dispositivos as $k=>$v){
	    if($_POST['opcion'.$k.'']!=""){$x++;}
	   }
	   $y=1;
	   $unidades="";
	   foreach($array_dispositivos as $k=>$v){
//		   echo''.$v.'';
		 if($y==$x){
			if($_POST['opcion'.$k.'']!=""){$unidades.=$_POST['opcion'.$k.''];}
		 }else{
		 if($_POST['opcion'.$k.'']!=""){$unidades.=$_POST['opcion'.$k.''].",";$y++;}
		 
		 }
		 
		}*/
		
		$sSQL="INSERT usuariogpsmovil
				SET usuario='".$_POST['usu']."',password='".$_POST['pass']."', estatus='A'";
		mysql_query($sSQL);
		$cveusu=mysql_insert_id() or die(mysql_error());
	}
	
	$_POST['cmd']=0;
}

if($_POST['cmd']==1){ 
		if($_POST['reg']){
			$block="class='readOnly' readonly"; mysql_select_db('gamn_gps_otra_plataforma');
		$select=" SELECT * FROM usuariogpsmovil WHERE cve='".$_POST['reg']."' ";
       $rsprovedor=mysql_query($select);
       $provedor=mysql_fetch_array($rsprovedor) or die(mysql_error());
	   }

     echo'
	    <a href="#" onClick="atcr(\'movil_usuarios.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver </a>
		<a href="#" onClick="validar_usu('.$_POST['reg'].');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></br></br>
		<table>
		<tr>';

		echo'<tr><td><span>Usuario</span></td>
        <td><input  size ="40" type="text" name="usu" id="usu" value="'.$provedor['usuario'].'" '.$block.'></br></td>
		</tr>
	    <tr><td><span>Pasword</span></td>
        <td><input  size ="40" type="text" name="pass" id="pass" value="'.$provedor['password'].'"></br></td>
		</tr></table>';
		if($_POST['reg']){
			

			echo'<table>';
			$selec=" SELECT dispositivos FROM usuariogpsmovil WHERE cve='".$_POST['reg']."' ";
			$rsprovedo=mysql_query($selec);
			$provedo=mysql_fetch_array($rsprovedo);
			$roww=explode(",",$provedo['dispositivos']);
//			print_r($roww);
			echo'<tr><th>Dispositivo</th><th>Activo</th></tr>';
			foreach($array_dispositivos as $k=>$v){
			rowb();
			echo'
				 <th align="left">'.$v.'</th>';
				 //foreach($roww as $k1=>$v1){if($v1==$k){echo'checked';} }
			echo'<td>&nbsp<input type="checkbox" name="opcion'.$k.'" id="opcion[]" value="'.$k.'" ';
			foreach($roww as $k1=>$v1){if($v1==$k){echo'checked';} }
			echo'></td>';
			echo'<tr>';
			}
			echo'</table>';
			
		}
		echo'</table>';
		echo'
		<Script language="javascript">
			function validar_usu(reg)
	   {
       if(document.getElementById("usu").value==""  )
	   {
               alert("Necesita introducir el Usuario");
       }
	   if(document.getElementById("pass").value==""  )
	   {
               alert("Necesita introducir el Pasword");
       }
       else{
               objeto=crearObjeto();
               if (objeto.readyState != 0) 
			   {
                       alert("Error: El Navegador no soporta AJAX");
               } else 
			   {
			   
                       objeto.open("POST","movil_usuarios.php",true);
                       objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
                       objeto.send("ajax=2&cod="+reg+"&usuario="+document.getElementById("usu").value+"");
                       objeto.onreadystatechange = function()
					   {
                               if (objeto.readyState==4)
							   {
                                     
                                       if(objeto.responseText=="si")
									   {
                                               alert("El usuario ya existe");
											   
                                       }
                                       else{
                                               atcr("movil_usuarios.php","",2,reg);
                                       }
                               }
                       }
               }
       }

		}
	   
	   </script>';
		}

 if ($_POST['cmd']<1) {
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
				<a href="#" onClick="atcr(\'movil_usuarios.php\',\'\',\'1\',\'0\');" id="nuevo" name="nuevo" ><img src="images/nuevo.gif"></a>Nuevo</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Nombre:</td><td><input type="text" class="textField" size="" name="nombre" id="nombre"></td>';
		echo'</tr>';
		echo '</table>
		';
		echo '<br>';
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
			objeto.open("POST","movil_usuarios.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nombre="+document.getElementById("nombre").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function(){
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}buscarRegistros();
	
			  

	
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
