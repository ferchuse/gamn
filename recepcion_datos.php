<?php
require_once('subs/cnx_db.php');
mysql_select_db($base);
function actualizar_abono_unidad($datos){
	$resultado = '';
	foreach($datos as $row){
		$fecha = substr($row['fecha'],0,10);
		$hora = substr($row['fecha'],11,8);
		$insert = "INSERT abono_unidad_taquillamovil SET folio = '{$row['folio']}', fecha = '{$fecha}', hora = '{$hora}', 
		idterminal = '{$row['idterminal']}', terminal = '{$row['terminal']}', idusuario = '{$row['idusuario']}',
		usuario='{$row['usuario']}', unidad = '{$row['cve']}', monto = '{$row['importe']}', estatus='A'";
		/*
		$insert = "INSERT abono_unidad_taquillamovil SET cve = '{$row['folio']}', fecha = '{$fecha}', hora = '{$hora}', 
		idterminal = '{$row['idterminal']}', terminal = '{$row['terminal']}', idusuario = '{$row['idusuario']}',
		usuario='{$row['usuario']}', unidad = '{$row['cve']}', monto = '{$row['importe']}', estatus='A'";
		*/
		if($res = mysql_query($insert)){
			$resultado.=','.$row['id'];
		}
	}
	return substr($resultado, 1);
}

function actualizar_abono_operador($datos){
	$resultado = '';
	foreach($datos as $row){
		$fecha = substr($row['fecha'],0,10);
		$hora = substr($row['fecha'],11,8);
		$insert = "INSERT abono_operador_taquillamovil SET folio = '{$row['folio']}', fecha = '{$fecha}', hora = '{$hora}', 
		idterminal = '{$row['idterminal']}', terminal = '{$row['terminal']}', idusuario = '{$row['idusuario']}',
		usuario='{$row['usuario']}', operador = '{$row['cve']}', monto = '{$row['importe']}', estatus='A'";
		if($res = mysql_query($insert)){
			$resultado.=','.$row['id'];
		}
	}
	return substr($resultado, 1);
}

function actualizar_boletos_taquillamovil($datos){
	$resultado = '';
	foreach($datos as $row){
		$fecha = substr($row['fecha'],0,10);
		$hora = substr($row['fecha'],11,8);
		$monto = substr($row['codigo'],9,3)/10;
		$insert = "INSERT boletos_taquillamovil SET folio = '{$row['id']}', fecha = '{$fecha}', hora = '{$hora}', 
		id_terminal = '{$row['idterminal']}', terminal = '{$row['terminal']}', id_usuario = '{$row['id_usuario']}',
		usuario='{$row['usuario']}', unidad = '{$row['cve_economico']}', codigo = '{$row['codigo']}', estatus='A',
		monto='{$monto}'";
		//mysql_query($insert) or die(mysql_error());
		//echo $insert;
		if($res = mysql_query($insert)){
			$resultado.=','.$row['id'];
		}
	}
	return substr($resultado, 1);
}

$function = $_POST['function'];

echo $function($_POST['datos']);

?>