<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>:: AUTOBUSES MEXICO NEXTLALPAN SA ::</title>
<link rel="stylesheet" type="text/css" href="css/style.css" />


<!--<table width="90%" border="0" class="loginInnerTable">
<script>
  if(top.window.location.href!="http://gamn.mx" && top.window.location.href!="http://gamn.agaribay.net/")
    top.window.location.href="http://gamn.mx";
</script>	
	
-->

</head>

<body>
<p>&nbsp;</p>
<form id="forma" name="forma" method="POST" action="inicio.php">
  <table width="530" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" class="loginTable">
    <tr>
      <td height="50" colspan="2" background="images/bannertop-bg.png">&nbsp;</td>
    </tr>
    <tr>
      <td width="50%"><div align="center">
          <p>&nbsp;</p>
        <table width="70%" border="0">
            <tr>
              <td><div align="center"><img src="images/nextlalpan_logo.jpg?<?php echo date("Y-m-d");?>" width="130" height="69" /></div></td>
            </tr>
            <tr>
              <td class="bodyText"><div align="center">&iexcl; AUTOBUSES MEXICO NEXTLALPAN SA ! </div></td>
            </tr>
            <tr>
              <td class="bodyText"><div align="center"></div></td>
            </tr>
            <tr>
              <td class="bodyText"><div align="center">Introduzca su usuario y password </div></td>
            </tr>
          </table>
        <p>&nbsp;</p>
      </div></td>
      <td width="50%"><div align="center">
			<!--<table width="90%" border="0" class="loginInnerTable">
				<tr><td><font color="RED" size="5">Respaldando base de datos a nivel general... disculpe las molestias</font></td></tr>
			</table>-->
          <table width="90%" border="0" class="loginInnerTable">
            <tr>
              <td colspan="5"><img src="images/login.gif" width="80" height="36" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="bodyTextBold">Usuario</td>
              <td><input name="loginUser" type="text" class="textField" id="loginUser" /></td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="bodyTextBold">Password</td>
              <td><input name="loginPassword" type="password" class="textField" id="loginPassword" /></td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td><div align="center">
                  <input name="Submit" type="submit" class="appDefButton" value="Login" />
              </div></td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
<?php
		if($_GET['ErrLogUs']) {
			echo '<tr><th colspan="5"><font color="RED">Usuario y/o Password Incorrectos !</font></th></tr>';
		}

?>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
          </table>
	  </div></td>
    </tr>
<?php
/*require_once('subs/cnx_db.php');
$rsCerrado2=mysql_db_query("enero_aaz","SELECT estatus FROM coor_cerrar_sistema WHERE dominio='3' ORDER BY cve DESC LIMIT 1");
$Cerrado2=mysql_fetch_array($rsCerrado2);
if($Cerrado2['estatus']=='C'){
	echo '<tr><td colspan="2" align="center"><h1><font color="RED">Sistema cerrado por falta de pago de administraci&oacute;n<br><br>&nbsp;</font></h1></td></tr>';
}
elseif(date("d")=="08" || date("d")=="09" || date("d")=="23" || date("d")=="24")
{
	echo '<tr><td colspan="2" align="center"><h1><font color="RED">El sistema se cerrar&aacute; por falta de pago de administraci&oacute;n<br><br>&nbsp;</font></h1></td></tr>';
}*/

?>
  </table>
</form>
</body>
</html>