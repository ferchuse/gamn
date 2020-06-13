<?php

include ("main2.php");
if($_POST['ajax']==99){
	mysql_query("INSERT registros_sistemamov SET cveacceso='".$_POST['cvereg']."',usuario='".$_POST['usuario']."',menu='".$_POST['idmenu']."',fechahora='".fechaLocal()." ".horaLocal()."'");
	exit();
}
if(!$_POST['plazausuario'] && count($array_plaza) == 1){
	foreach($array_plaza as $k=>$v)
		$_POST['plazausuario']=$k;
}
if($_POST['cmd']=='cambiarplaza'){
	$_POST['plazausuario']=0;
}
$_SESSION['plaza_seleccionada'] = $_POST['plazausuario'];
top($_SESSION);
	if($_POST['plazausuario']){
		echo '<h1><font color="BLACK">Bienvenidos a la plaza '.$array_plaza[$_POST['plazausuario']].'</font></h1>';
		if($row['mensajeinicio']!=''){
			echo '<p style="color:#FF0000;font-size:16px;">'.$row['mensajeinicio'].'</p>';
		}
	}
	else{

		echo '<h1><font color="BLACK">Seleccionar Plaza</font></h1><br><ul>';
		if($_POST['cveusuario']==1 || $_SESSION['TipoUsuario']==1)
			$res = mysql_query("SELECT a.cve,a.nombre FROM plazas a WHERE 1 ORDER BY a.nombre");
		else
			$res = mysql_query("SELECT a.cve,a.nombre FROM plazas a INNER JOIN usuario_accesos b ON a.cve=b.plaza AND b.usuario='".$_POST['cveusuario']."' AND b.acceso>0  WHERE 1 GROUP BY a.cve ORDER BY a.nombre");
		$localidad = "";
		while($row = mysql_fetch_array($res)){
			echo '<li><a href="#" onClick="document.forma.plazausuario.value='.$row['cve'].';atcr(\'inicio2.php\',\'\',\'\',\'\');">'.$row['nombre'].'</li>';
		}
		echo '</ul>';
	}
bottom();

?>
