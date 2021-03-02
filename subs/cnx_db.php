<?php
	//Conexion con la base
	if($_SERVER["SERVER_NAME"] == "localhost" ){
		$base = "gamn";
		if (!$MySQL=@mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2')) {
			$t=time();
			while (time()<$t+5) {}
			if (!$MySQL=@mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2')) {
				$t=time();
				while (time()<$t+10) {}
				if (!$MySQL=@mysql_connect('localhost', 'gamn', 'rXj8nBpwFu2tDwe2')) {
					echo '<br><br><br><h3 align=center">Hay problemas de comunicaci&oacute;n con la Base de datos.</h3>';
					echo '<h4>Por favor intente mas tarde.-</h4>';
					exit;
				}
			}
		}
	}
	else{
		$base = "syncsis_gamn";
		if (!$MySQL=@mysql_connect('localhost', 'syncsis_gamn', 'rXj8nBpwFu2tDwe2')) {
			$t=time();
			while (time()<$t+5) {}
			if (!$MySQL=@mysql_connect('localhost', 'syncsis_gamn', 'rXj8nBpwFu2tDwe2')) {
				$t=time();
				while (time()<$t+10) {}
				if (!$MySQL=@mysql_connect('localhost', 'syncsis_gamn', 'rXj8nBpwFu2tDwe2')) {
					echo '<br><br><br><h3 align=center">Hay problemas de comunicaci&oacute;n con la Base de datos.</h3>';
					echo '<h4>Por favor intente mas tarde.-</h4>';
					exit;
				}
			}
		}
		
	}
	
	
	// mysql_query("SET CHARACTER SET utf8") or die("Error en charset UTF8".mysql_error());
	
	// $base = "gamn";
	$pre = "";
	
	function getRealIP()
	{
		global $_SERVER;
		if( $_SERVER['HTTP_X_FORWARDED_FOR'] != '' )
		{
			$client_ip =
			( !empty($_SERVER['REMOTE_ADDR']) ) ?
            $_SERVER['REMOTE_ADDR']
            :
            ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
			$_ENV['REMOTE_ADDR']
			:
			"unknown" );
			
			// los proxys van añadiendo al final de esta cabecera
			// las direcciones ip que van "ocultando". Para localizar la ip real
			// del usuario se comienza a mirar por el principio hasta encontrar
			// una dirección ip que no sea del rango privado. En caso de no
			// encontrarse ninguna se toma como valor el REMOTE_ADDR
			
			$entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);
			
			reset($entries);
			while (list(, $entry) = each($entries))
			{
				$entry = trim($entry);
				if ( preg_match("/^([0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+)/", $entry, $ip_list) )
				{
					// http://www.faqs.org/rfcs/rfc1918.html
					$private_ip = array(
					'/^0\\./',
					'/^127\\.0\\.0\\.1/',
					'/^192\\.168\\..*/',
					'/^172\\.((1[6-9])|(2[0-9])|(3[0-1]))\\..*/',
					'/^10\\..*/');
					
					$found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);
					
					if ($client_ip != $found_ip)
					{
						$client_ip = $found_ip;
						break;
					}
				}
			}
		}
		else
		{
			$client_ip =
			( !empty($_SERVER['REMOTE_ADDR']) ) ?
            $_SERVER['REMOTE_ADDR']
            :
            ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
			$_ENV['REMOTE_ADDR']
			:
			"unknown" );
		}
		
		return $client_ip;
		
	}
	
?>