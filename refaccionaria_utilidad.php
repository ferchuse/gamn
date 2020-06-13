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

if($_POST["ajax"]==1){
	if ($_POST["nom"] != ""){$fil=" and nombre like '%".$_POST['cliente']."%'";}
	   

	$res=mysql_db_query($base,"SELECT * FROM productos where 1 ".$fil."");


     echo ' 
	    <table width="100%" border="0" cellpadding="" cellspacing="">
	     <tr bgcolor="#E9F2F8">
		  
		  <th>Nombre</th>
		  <th>Costo Venta</th>
		  <th>% Ganancia</th>
		  <th>Precio Venta</th>
		  <th>Ganancia</th>
		  <input type="hidden" id="fecha_ini" name="fecha_ini" value="'.$_POST['fecha_ini'].'">
		  <input type="hidden" id="fecha_ini" name="fecha_fin" value="'.$_POST['fecha_fin'].'">
		 </tr>';
		 $costos=0;
		 $precios=0;
		 $utilidades=0;
		 $filtros="";
		 $registros=0;
		 if($_POST['fecha_ini']>'0000-00-00') $filtros .= " AND a.fecha_creacion >= '{$_POST['fecha_ini']}'";
		 if($_POST['fecha_fin']>'0000-00-00') $filtros .= " AND a.fecha_creacion <= '{$_POST['fecha_fin']}'";
		while($row = mysql_fetch_array($res)){
			$saldo=0;
		   rowb();

			
			$conn= "SELECT sum(b.cantidad*b.precio) as precios, SUM(b.cantidad*b.costo) as costos
			FROM salidas_productos a 
			left JOIN salidas_productos_detalle b ON a.cve = b.cvesalida 
			WHERE a.estatus!='C' and b.producto ='".$row['cve']."' {$filtros}";
			$conn1 =mysql_db_query($base,$conn) or die(mysql_error());
			$roww=mysql_fetch_array($conn1);
			$ganancia = $roww['precios']-$roww['costos'];
			$porcentaje = round($roww['precios']*100/$roww['costos']-100,1);
			if($roww['costos']==0 && $roww['precios']==0) $porcentaje=0;
           echo'	       
		   <td align="">'.$row['nombre'].'</td> 
		   <td align="center">'.number_format($roww['costos'],2).'</td>
		   <td align="center">'.number_format($porcentaje,1).'</td>
		   <td align="center">'.number_format($roww['precios'],2).'</td>
		   <td align="center">'.number_format($ganancia,2).'</td>
		   </tr>';
		   $costos+=$roww['costos'];
		   $precios+=$roww['precios'];
		   $utilidades+=$ganancia;
		   $registros++;
         }
         $porcentaje = round($precios*100/$costos-100,1);
         if($costos==0 && $precios==0) $porcentaje=0;
			echo'<tr bgcolor="#E9F2F8">
		  <td colspan="">'.$registros.' Registro(s)</td>
		  <td colspan="" align="center">'.number_format($costos,2).'</td>
		  <td colspan="" align="center">'.number_format($porcentaje,1).'</td>
		  <td colspan="" align="center">'.number_format($precios,2).'</td>
		  <td colspan="" align="center">'.number_format($utilidades,2).'</td>
		 </tr>';
		echo'</table>';
   		exit();
   }

  

top($_SESSION);
 


   
if ($_POST['cmd']<1){    
     echo'<a href="#" onclick="buscarRegistros();" id="buscar"><img border="0" src="images/buscar.gif">Buscar&nbsp</a>';

		  echo'<table>';
		echo '<tr><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo'<tr><td>Producto</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>
		</table>
		  </br>
		  </br>
		  <div id="Resultados" ></div>';
 
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
		       objeto.open("POST","refaccionaria_utilidad.php",true);
			   objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			   objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value);
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
  }
bottom();
?>