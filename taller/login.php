<?php
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>GPS</title>
<link rel="stylesheet" href="css/estilos.css" type="text/css" />
</head>
<body>
<form name="forma" action="inicio2.php" method="post">
<div align="center">
<table width="467" class="login" cellspacing="8">
  <tr>
    <td width="46%" align="left"><div align="center"><img src="images/logueo2.gif"></div>
	  Bienvenidos<br>Sistema de GPS <br>
	  <br>
	  Ingrese un usuario y contraseña<br>correctos para ingresar al sistema
	</td>
    <td width="54%" align="left" valign="top"><br><br>
      <img src="images/login2.gif"><br>
	  <table width="100%" height="152"  border="0" cellpadding="0" cellspacing="0" class="tablas">
        <tr align="left">
          <td class="inputlabel">';
		    if($_GET['ErrLogUs'] == 'true'){ 
			    echo '<span class="letras_error">
				  &nbsp;&nbsp;&nbsp;Su usuario o contraseña son 
				  &nbsp;&nbsp;&nbsp;incorectos escribalas de nuevo o
				  &nbsp;&nbsp;&nbsp;pida ser registrada si no lo ha hecho
	            </span>'; 
        	} 

            echo '<br>
            &nbsp;&nbsp;&nbsp;Usuario:</td>
        </tr>
        <tr align="left">
          <td>&nbsp;&nbsp;&nbsp;<input name="loginUser" type="text" class="inputbox" size="25"></td>
        </tr>
        <tr align="left">
          <td class="inputlabel">&nbsp;&nbsp;&nbsp;Contraseña:</td>
        </tr>
        <tr align="left">
          <td>&nbsp;&nbsp;&nbsp;<input name="loginPassword" type="password" class="inputbox" size="25"></td>
        </tr>
        <tr>
		  <td align="right"><input name="Login" type="submit" class="button" value="Login">&nbsp;&nbsp;<br>&nbsp;&nbsp;</td>
        </tr>
		<script>document.forma.loginUser.focus();</script>
      </table>	
	</td>
  </tr>
</table>
</div>
 </form>
</body>
</html>
';

?>