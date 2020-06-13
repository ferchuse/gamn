<?php 
include ("main2.php"); 
	$fechahora=fechahoraLocal();
	mysql_query("UPDATE registros_sistema SET salida='".$fechahora."' WHERE cve='".$_SESSION['reg_sistema']."'");
	// Unset all of the session variables.
	$_SESSION = array();
	
	// Finally, destroy the session.
	session_destroy();
	
	header("Location: login.php");
	
?>
