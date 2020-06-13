<?php
include("main.php");
function lastday() { 
      $month = date('m');
      $year = date('Y');
      $day = date("d", mktime(0,0,0, $month+1, 0, $year));
 
      return date('Y-m-d', mktime(0,0,0, $month, $day, $year));
  };

  function firstday() {
      $month = date('m');
      $year = date('Y');
      return date('Y-m-d', mktime(0,0,0, $month, 1, $year));
  }
$res=mysql_db_query($base,"SELECT * FROM usuarios ORDER BY cve");
while($row = mysql_fetch_array($res)){
	$array_usuario[$row['cve']] = $row['usuario'];
}

$res=mysql_db_query($base,"SELECT * FROM productos where 1");
while($row = mysql_fetch_array($res)){
	$array_personal[$row['cve']] = $row['id_producto']." - (".$row['nombre'].")";
}

if($_POST["ajax"]==1)
   {
	   if ($_POST["cliente"] != ""){$fil=" and cve='".$_POST['cliente']."'";}
	   
//		 $base="bancotepe";
//$pre="";
//$base="tepe_tepe";
//$pre="tepe2_";
$res=mysql_db_query($base,"SELECT * FROM productos where 1 ".$fil."");
while($row = mysql_fetch_array($res)){
	$array_personals[$row['cve']] = $row['nombre'];
}
//$base="bancotepe";
//mysql_select_db($base);
//$pre="";
     echo ' 
	    <table width="100%" border="0" cellpadding="" cellspacing="">
		 <tr bgcolor="#3399FF">
		  <td colspan="4">'.$registros.' Registro(s)</td>
		 </tr>
	     <tr bgcolor="#E9F2F8">
		  
		  <th>Nombre</th>
		  <th>Entradas</th>
		  <th>Salidas</th>
		  <th>Existencia</th>
		  <input type="hidden" id="fecha_ini" name="fecha_ini" value="'.$_POST['fecha_ini'].'">
		  <input type="hidden" id="fecha_ini" name="fecha_fin" value="'.$_POST['fecha_fin'].'">
		 </tr>';
		 $abonos=0;
		 $retiros=0;
		 $saldos=0;
		foreach($array_personals as $k=>$v)
		 {
			$saldo=0;
		  /*<a href="#" onClick="atcr(\'cat_motivos.php\',\'_blank\',\'101\','.$reg1['id'].')"><img src="images/b_print.png"></a></spam></td>*/
		   rowb();
		$con= "SELECT sum(b.cantidad) as abonos
		FROM entradas_productos a 
		left JOIN entradas_productos_detalle b ON a.cve = b.cveentrada 
		WHERE  a.estatus!='C' and b.producto ='".$k."' ";
		$con1 =mysql_db_query($base,$con) or die(mysql_error());
		$row1=mysql_fetch_array($con1);
		
		$conn= "SELECT sum(b.cantidad) as retiros
		FROM salidas_productos a 
		left JOIN salidas_productos_detalle b ON a.cve = b.cvesalida 
		WHERE a.estatus!='C' and b.producto ='".$k."' ";
		$conn1 =mysql_db_query($base,$conn) or die(mysql_error());
		$roww=mysql_fetch_array($conn1);
		$saldo=$row1['abonos'] - $roww['retiros'];
//		   echo $con;
		   echo'
		   <!--<td align="center">-->';
           //if($_POST['nivelUsuario']>1)
           if(nivelUsuario()>1)
		  // if($_SESSION[$archivo[(count($archivo)-1)]]>1)
		   {
//             echo'<a href="#" onClick="atcr(\'refaccionaria_edo_cuenta.php\',\'\',\'1\',\''.$reg1['cve'].'\')"><img src="images/modificar.gif"></a>';
           }
           echo'<!--</td>-->		       
		   <td align="">'.$v.'</td> 
		   <td align="center">'.number_format($row1['abonos'],0).'</td>
		   <td align="center">'.number_format($roww['retiros'],0).'</td>
		   <td align="center"><a href="#" onClick="atcr(\'refaccionaria_edo_cuenta.php\',\'\',\'105\',\''.$k.'\')">'.number_format($saldo,0).'</a></td>
		   </tr>';
		   $abonos=$abonos + $row1['abonos'];
		   $retiros=$retiros + $roww['retiros'];
		   $saldos=$saldos + $saldo;
         }
			echo'<tr bgcolor="#3399FF">
		  <td colspan="">'.$registros.' Registro(s)</td>
		  <td colspan="" align="center">'.number_format($abonos,0).'</td>
		  <td colspan="" align="center">'.number_format($retiros,0).'</td>
		  <td colspan="" align="center">'.number_format($saldos,0).'</td>
		 </tr>';
		echo'</table>';
   exit();
   }

   if($_POST["ajax"]==-106)
   {
    $con= "SELECT * FROM ahorro_personal_retiro where fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
//     if ($_POST["cliente"] != ""){$con.=" and cliente='".$_POST['cliente']."'"; }
//	 if ($_POST["folio"] != ""){$con.=" and cve='%".$_POST['folio']."'"; }
      $con1 =mysql_db_query($base,$con);
     echo ' 
	    <table width="100%" border="0" cellpadding="" cellspacing="">
		 <tr bgcolor="#3399FF">
		  <td colspan="6">'.mysql_num_rows($con1).' Registro(s)</td>
		 </tr>
	     <tr bgcolor="#E9F2F8">
		  <th width="40"></th>
		  <th>Folio</th>
		  <th>Conductor</th>
		  <th>Fecha</th>
		  <th>Monto</th>
		  <th>Usuario</th>
		 </tr>';
		while($reg1= mysql_fetch_array($con1))
		 {
		  /*<a href="#" onClick="atcr(\'cat_motivos.php\',\'_blank\',\'101\','.$reg1['id'].')"><img src="images/b_print.png"></a></spam></td>*/
		   rowb();
		   echo'
		   <td align="center">';
           //if($_POST['nivelUsuario']>1)
           if(nivelUsuario()>1)
		   {
             //echo'<a href="#" onClick="atcr(\'ahorro_personal_abono.php\',\'\',\'1\',\''.$reg1['cve'].'\')"><img src="images/modificar.gif"></a>';
			 if($reg1['estatus']=="C"){echo'Cancelado';$reg1[monto]=0;}
			 //else{echo '&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){ atcr(\'ahorro_personal_abono.php\',\'\',3,\''.$reg1['cve'].'\');}"><img src="images/validono.gif" border="0" title="Cancelar"></a>';}
           }
           echo'</td>		       
		   <td align="center">'.$reg1[cve].'</td>
		   <td align="center">'.$array_personal[$reg1[cliente]].'</td>
		   <td align="center">'.$reg1[fecha].'</td>
		   <td align="center">'.number_format($reg1[monto],2).'</td>
		   <td align="center">'.$array_usuario[$reg1[usuario]].'</td>
		   </tr>';
         }
			echo'<tr bgcolor="#3399FF">
		  <td colspan="6">'.mysql_num_rows($con1).' Registro(s)</td>
		 </tr>';
		echo'</table>';
		exit();
   }

   if($_POST["ajax"]==105)
   {
//	   print_r ($array_usuario);
     echo ' 
	    <table width="100%" border="0" cellpadding="" cellspacing="">
		 <tr bgcolor="#3399FF">
		  <td colspan="6"></td>
		 </tr>
	     <tr bgcolor="#E9F2F8">
		  <th>Fecha</th>
		  <th>Entradas</th>
		  <th>Salidas</th>
		  <th>Existencia</th>
		  <!--<th>Observaciones</th>-->
		  <th>Usuario</th>
		  
		 </tr>';

		 if($_POST['fecha_ini']=="" or $_POST['fecha_fin']==""){
		 $con= "SELECT max(fecha) as fecha_fin FROM entradas_productos where 1";
		 $con1 =mysql_db_query($base,$con);
		 $fe_fin=mysql_fetch_array($con1);
		 $conn= "SELECT min(fecha) as fecha_ini FROM salidas_productos where 1";
		 $conn1 =mysql_db_query($base,$conn);
		 $fe_ini=mysql_fetch_array($conn1);
		 $fecha=$fe_ini['fecha_ini'];
		 }else{
			$fecha=$_POST['fecha_ini'];
			$fe_fin['fecha_fin']=$_POST['fecha_fin'];
			$fe_ini['fecha_ini']=$_POST['fecha_ini'];
		 }
		 
		 
		 $fecha=$fe_ini['fecha_ini'];
		 $x=0;
		 $abonos=0;
		 $retiros=0;
		 for($i=1;$fecha<=$fe_fin['fecha_fin'];$i++){
		///abonos    
				$con= "SELECT a.cve,a.fecha_creacion,b.producto,b.cantidad,a.usuario,a.hora,a.obs
		FROM entradas_productos a 
		left JOIN entradas_productos_detalle b ON a.cve = b.cveentrada 
		WHERE a.estatus!='C' and b.producto ='".$_POST['cliente']."' and a.fecha_creacion = '".$fecha."'";
				$con1 =mysql_db_query($base,$con);
//				echo $con;
				while($reg1= mysql_fetch_array($con1))
				{
				rowb();
				$abonos=$abonos + $reg1['cantidad'];
				echo'
				<!--<td align="">'.$reg1[cve].'</td>-->
				<!--<td align="">'.$array_personal[$reg1[cliente]].'</td>-->
				<td align="">'.$reg1[fecha_creacion].'  '.$reg1[hor].'  -->  (folio '.$reg1['cve'].' Compras)</td>
				<td align="center">'.number_format($reg1[cantidad],0).'</td>
				<td align="center">'.number_format(0,0).'</td>
				<td align="center">'.number_format(($abonos - $retiros),0).'</td>
				<!--<td align="center">'.$reg1[obs].'</td>-->
				<td align="center">'.$array_usuario[$reg1[usuario]].'</td>

				</tr>';
				$x++;

				}
		///retiros
				 $conn= "SELECT a.cve,a.fecha_creacion,b.producto,b.cantidad,a.usuario,a.hora,a.obs
		FROM salidas_productos a 
		left JOIN salidas_productos_detalle b ON a.cve = b.cvesalida 
		WHERE a.estatus!='C' and b.producto ='".$_POST['cliente']."' and a.fecha_creacion = '".$fecha."'";
				 $conn1 =mysql_db_query($base,$conn);
				 while($reg1= mysql_fetch_array($conn1))
				{
				rowb();
				$retiros=$retiros + $reg1['cantidad'];
				echo'</td>		       
				<!--<td align="">'.$reg1[folio].'</td>-->
				<!--<td align="">'.$array_personal[$reg1[cliente]].'</td>-->
				<td align="">'.$reg1[fecha_creacion].'   '.$reg1[hoa].'  -->  (folio '.$reg1['cve'].' Ventas)</td>
				<td align="center">'.number_format(0,0).'</td>
				<td align="center">'.number_format($reg1[cantidad],0).'</td>
				<td align="center">'.number_format(($abonos - $retiros),0).'</td>
				<!--<td align="center">'.$reg1[obs].'</td>-->
				<td align="center">'.$array_usuario[$reg1[usuario]].'</td>
				</tr>';
				$x++;
				
				}
			$fecha=date( "Y-m-d" , strtotime ( "+ ".$i." day" , strtotime($fe_ini['fecha_ini']) ) );
			}
		
			echo'<tr bgcolor="#3399FF">
		  <td colspan="">'.$x.' Registro(s)</td>
		  <td colspan="" align="center">'.number_format($abonos,0).'</td>
		  <td colspan="" align="center">'.number_format($retiros,0).'</td>
		  <td colspan="" align="center">'.number_format(($abonos-$retiros),0).'</td>
		  <td colspan="" align="center"></td>
		 </tr>';
		echo'</table>';
		exit();
   }

        if($_POST["cmd"]==101)
   {
require_once('dompdf/dompdf_config.inc.php');
		$html='<html><head>
      <style type="text/css">
	                    top  lado      ladoiz
		 @page{ margin: 5in 0.5in 1px 0.5in;}
		</style>
		 </head><body>';
		 if($_POST['fecha_ini']=="" or $_POST['fecha_fin']==""){
		 $con= "SELECT max(fecha) as fecha_fin FROM ahorro_personal_abono where 1";
		 $con1 =mysql_db_query($base,$con);
		 $fe_fin=mysql_fetch_array($con1);
		 $conn= "SELECT min(fecha) as fecha_ini FROM ahorro_personal_abono where 1";
		 $conn1 =mysql_db_query($base,$conn);
		 $fe_ini=mysql_fetch_array($conn1);
		 $fecha=$fe_ini['fecha_ini'];
		 }else{
			$fecha=$_POST['fecha_ini'];
			$fe_fin['fecha_fin']=$_POST['fecha_fin'];
			$fe_ini['fecha_ini']=$_POST['fecha_ini'];
		 }
		 
		 
		 $fecha=$fe_ini['fecha_ini'];
		 $x=0;
		 $abonos=0;
		 $retiros=0;
     $html.= '<h2>Estado de Cuenta del Conductor '.$array_personal[$_POST[cliente]].'</h2></br>
			<h2>Periodo de '.$fecha.' al '.$fe_fin['fecha_fin'].'</h2>
	    <table width="100%" border="1" cellpadding="" cellspacing=""  style="font-size:13px">
		 <tr bgcolr="#3399FF">
		  <!--<td colspan="6">'.mysql_num_rows($con1).' Registro(s)</td>
		 </tr>
	     <tr bgcoor="#E9F2F8">-->
		  <th width="50px">Folio</th>
		  <th>Fecha</th>
		  <th>Abono</th>
		  <th>Retiro</th>
		  <th>Saldo</th>
		  <th>Observaciones</th>
		  <th>Usuario</th>
		  
		 </tr>';

		 for($i=1;$fecha<=$fe_fin['fecha_fin'];$i++){
		///abonos    
				$con= "SELECT * FROM ahorro_personal_abono where fecha = '".$fecha."' and cliente='".$_POST['cliente']."' and estatus!='C'";
				$con1 =mysql_db_query($base,$con);
				while($reg1= mysql_fetch_array($con1))
				{
				//rowb();
				$abonos=$abonos + $reg1['monto'];
				$html.='<tr>
				<td align="center">'.$reg1[cve].'</td>
				<!--<td align="center">'.$array_personal[$reg1[cliente]].'</td>-->
				<td align="center">'.$reg1[fecha].' '.$reg1[hora].'</td>
				<td align="center">'.number_format($reg1[monto],2).'</td>
				<td align="center">'.number_format(0,2).'</td>
				<td align="center">'.number_format(($abonos - $retiros),2).'</td>
				<td align="center">'.$reg1[obs].'</td>
				<td align="center">'.$array_usuario[$reg1[usuario]].'</td>
				</tr>';
				$x++;
				
				}
		///retiros
				 $conn= "SELECT * FROM ahorro_personal_retiro where fecha = '".$fecha."' and cliente='".$_POST['cliente']."' and estatus!='C'";
				 $conn1 =mysql_db_query($base,$conn);
				 while($reg1= mysql_fetch_array($conn1))
				{
//				rowb();
				$retiros=$retiros + $reg1['monto'];
				$html.='<tr></td>		       
				<td align="center">'.$reg1[cve].'</td>
				<!--<td align="center">'.$array_personal[$reg1[cliente]].'</td>-->
				<td align="center">'.$reg1[fecha].'   '.$reg1[hora].'</td>
				<td align="center">'.number_format(0,2).'</td>
				<td align="center">'.number_format($reg1[monto],2).'</td>
				<td align="center">'.number_format(($abonos - $retiros),2).'</td>
				<td align="center">'.$reg1[obs].'</td>
				<td align="center">'.$array_usuario[$reg1[usuario]].'</td>

				</tr>';
				$x++;

				}
			
			$fecha=date( "Y-m-d" , strtotime ( "+ ".$i." day" , strtotime($fe_ini['fecha_ini']) ) );
			}
		
			$html.='<tr bgcolr="#3399FF">
		  <td colspan="2">'.$x.' Registro(s)</td>
		  <td colspan="" align="center">'.number_format($abonos,2).'</td>
		  <td colspan="" align="center">'.number_format($retiros,2).'</td>
		  <td align="center">'.number_format(($abonos - $retiros),2).'</td>
		  <td colspan="" align="center"></td>
		  <td colspan="" align="center"></td>
		 </tr>';
		$html.='</table>
			 </body></html>';
		$mipdf= new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
	$mipdf->set_paper("A4", "portrait");
    
//    $mipdf->set_margin("Legal", "landscape");
//	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($html);
	$mipdf->render();
	$mipdf ->stream();
		exit();
   }

     if($_POST["cmd"]==-101)
   {
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=salidaas de accidentes.xls");
header("Pragma: no-cache");
header("Expires: 0");
		 if($_POST['fecha_ini']=="" or $_POST['fecha_fin']==""){
		 $con= "SELECT max(fecha) as fecha_fin FROM ahorro_personal_abono where 1";
		 $con1 =mysql_db_query($base,$con);
		 $fe_fin=mysql_fetch_array($con1);
		 $conn= "SELECT min(fecha) as fecha_ini FROM ahorro_personal_abono where 1";
		 $conn1 =mysql_db_query($base,$conn);
		 $fe_ini=mysql_fetch_array($conn1);
		 $fecha=$fe_ini['fecha_ini'];
		 }else{
			$fecha=$_POST['fecha_ini'];
			$fe_fin['fecha_fin']=$_POST['fecha_fin'];
			$fe_ini['fecha_ini']=$_POST['fecha_ini'];
		 }
		 
		 
		 $fecha=$fe_ini['fecha_ini'];
		 $x=0;
		 $abonos=0;
		 $retiros=0;
     echo '<h2>Estado de Cuenta del Conductor '.$array_personal[$_POST[cliente]].'</h2></br>
			<h2>Periodo de '.$fecha.' al '.$fe_fin['fecha_fin'].'</h2>
	    <table width="100%" border="1" cellpadding="" cellspacing="">
		 <tr bgcolr="#3399FF">
		  <!--<td colspan="6">'.mysql_num_rows($con1).' Registro(s)</td>
		 </tr>
	     <tr bgcoor="#E9F2F8">-->
		  <th width="50px">Folio</th>
		  <th>Fecha</th>
		  <th>Abono</th>
		  <th>Retiro</th>
		  <th>Saldo</th>
		  <th>Observaciones</th>
		  <th>Usuario</th>
		  
		 </tr>';

		 for($i=1;$fecha<=$fe_fin['fecha_fin'];$i++){
		///abonos    
				$con= "SELECT * FROM ahorro_personal_abono where fecha = '".$fecha."' and cliente='".$_POST['cliente']."' and estatus!='C'";
				$con1 =mysql_db_query($base,$con);
				while($reg1= mysql_fetch_array($con1))
				{
				//rowb();
				$abonos=$abonos + $reg1['monto'];
				echo'<tr>
				<td align="center">'.$reg1[cve].'</td>
				<!--<td align="center">'.$array_personal[$reg1[cliente]].'</td>-->
				<td align="center">'.$reg1[fecha].' '.$reg1[hora].'</td>
				<td align="center">'.number_format($reg1[monto],2).'</td>
				<td align="center">'.number_format(0,2).'</td>
				<td align="center">'.number_format(($abonos - $retiros),2).'</td>
				<td align="center">'.$reg1[obs].'</td>
				<td align="center">'.$array_usuario[$reg1[usuario]].'</td>
				</tr>';
				$x++;
				
				}
		///retiros
				 $conn= "SELECT * FROM ahorro_personal_retiro where fecha = '".$fecha."' and cliente='".$_POST['cliente']."' and estatus!='C'";
				 $conn1 =mysql_db_query($base,$conn);
				 while($reg1= mysql_fetch_array($conn1))
				{
//				rowb();
				$retiros=$retiros + $reg1['monto'];
				echo'<tr></td>		       
				<td align="center">'.$reg1[cve].'</td>
				<!--<td align="center">'.$array_personal[$reg1[cliente]].'</td>-->
				<td align="center">'.$reg1[fecha].'   '.$reg1[hora].'</td>
				<td align="center">'.number_format(0,2).'</td>
				<td align="center">'.number_format($reg1[monto],2).'</td>
				<td align="center">'.number_format(($abonos - $retiros),2).'</td>
				<td align="center">'.$reg1[obs].'</td>
				<td align="center">'.$array_usuario[$reg1[usuario]].'</td>

				</tr>';
				$x++;

				}
			
			$fecha=date( "Y-m-d" , strtotime ( "+ ".$i." day" , strtotime($fe_ini['fecha_ini']) ) );
			}
		
			echo'<tr bgcolr="#3399FF">
		  <td colspan="2">'.$x.' Registro(s)</td>
		  <td colspan="" align="center">'.number_format($abonos,2).'</td>
		  <td colspan="" align="center">'.number_format($retiros,2).'</td>
		  <td align="center">'.number_format(($abonos - $retiros),2).'</td>
		  <td colspan="" align="center"></td>
		  <td colspan="" align="center"></td>
		 </tr>';
		echo'</table>';
		exit();
   }

top($_SESSION);
 

if ($_POST['cmd']==105)
   {    
     echo'<a href="#" onclick="buscarRegistros1();" id="buscar"><img border="0" src="images/buscar.gif">Buscar&nbsp</a>&nbsp;
		 <a href="#" onClick="$(\'#panel\').show();atcr(\'refaccionaria_edo_cuenta.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a>&nbsp;
		 <!--<a href="#" onClick="atcr(\'\',\'_blank\',\'101\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir">Imprimir</a>-->';
          if(nivelUsuario()>1)
		//if($_SESSION[$archivo[(count($archivo)-1)]]>1)  
          {
  //          echo'<a href="#" onClick="atcr(\'refaccionaria_edo_cuenta.php\',\'\',\'1\',\'0\');" id="nuevo" name="nuevo" ><img src="images/nuevo.gif">Nuevo</a>';
          }
     echo'<table>';
		echo '<tr><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.firstday().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo'<input type="hidden" name="cliente" id="cliente" value="'.$_POST['reg'].'">
		</table>
		  </br>
		  </br>
		  <h2>Estado de Cuenta de '.$array_personal[$_POST['reg']].'</h2>
		  <div id="Resultados1" ></div>';
	echo '<script language="javascript">
   function buscarRegistros1()
	    {
          document.getElementById("Resultados1").innerHTML =" <img src=\'/home/conta/images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'>Espere un momento, buscando registros..";	   
          objeto=crearObjeto();
          if(objeto.readyState!=0)
		   {
		    alert("Error: El Navegador no soporta AJAX");
		   } else
		      {
		       objeto.open("POST","refaccionaria_edo_cuenta.php",true);
			   objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			   objeto.send("ajax=105&cliente="+document.getElementById("cliente").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value);
               objeto.onreadystatechange = function()
                {
                  if (objeto.readyState==4)
                   {document.getElementById("Resultados1").innerHTML = objeto.responseText;
				   }
                }
			  }
		}
			   buscarRegistros1(1,1);
		</script>';
   }

   

		if ($_POST['cmd']<1)
   {    
     echo'<a href="#" onclick="buscarRegistros();" id="buscar"><img border="0" src="images/buscar.gif">Buscar&nbsp</a>';
         if(nivelUsuario()>1)
          //if($_SESSION[$archivo[(count($archivo)-1)]]>1)
		  {
//            echo'<a href="#" onClick="atcr(\'refaccionaria_edo_cuenta.php\',\'\',\'1\',\'0\');" id="nuevo" name="nuevo" ><img src="images/nuevo.gif">Nuevo</a>';
          }
		  echo'<table>';
		echo '<tr style="display:none;"><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr style="display:none;"><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo'<tr><th>Producto</th><td><select name="cliente" id="cliente"><option value="">--Todos--</option>';
		foreach($array_personal as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['cliene']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>
		</table>
		  </br>
		  </br>
		  <div id="Resultados" ></div>';
   }
 echo '<script language="javascript">
   function buscarRegistros()
	    {
          document.getElementById("Resultados").innerHTML =" <img src=\'/home/conta/images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'>Espere un momento, buscando registros..";	   
          objeto=crearObjeto();
          if(objeto.readyState!=0)
		   {
		    alert("Error: El Navegador no soporta AJAX");
		   } else
		      {
		       objeto.open("POST","refaccionaria_edo_cuenta.php",true);
			   objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			   objeto.send("ajax=1&cliente="+document.getElementById("cliente").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value);
               objeto.onreadystatechange = function()
                {
                  if (objeto.readyState==4)
                   {document.getElementById("Resultados").innerHTML = objeto.responseText;
				   }
                }
			  }
		}
			   buscarRegistros(1,1);
		</script>';
bottom();
?>