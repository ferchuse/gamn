<?php 

include ("main.php"); 
if($_POST['ajax']==99){
	mysql_db_query($base,"INSERT ".$pre."registros_sistemamov SET cveacceso='".$_POST['cvereg']."',usuario='".$_POST['usuario']."',menu='".$_POST['idmenu']."',fechahora='".fechaLocal()." ".horaLocal()."'");
	exit();
}
top($_SESSION);


if($_POST['cmd']==0){
//	echo '<h1 aling="center" style="font-size:20px"><font color="RED">No se Ha Registrado su Pago </font></h1></br></br>';
//	echo '<h1 aling="center" style="font-size:20px"><font color="RED">El Sistema se Cerrara a las 14:00 pm</font></h1></br></br>';
	echo '<h1><font color="BLACK">Bienvenido</font></h1>';

}
bottom(); 

echo '
<Script language="javascript">

	function aler()
	{
		alert("EL SERVIDOR SE SUSPENDERA EL 9 DE MARZOP DE 2017 A LAS 10:00 AM");
	}
	window.onload = function () {

	}
	</Script>
';		
?>

