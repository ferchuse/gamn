<?php
require_once('subs/cnx_db.php');
function actualizar_abono_unidad($datos){
	$resultado = '';
	foreach($datos as $row){
		$fecha = substr($row['fecha'],0,10);
		$hora = substr($row['fecha'],11,8);
		$insert = "INSERT abono_unidad_taquillamovil SET cve = '{$row['folio']}', fecha = '{$fecha}', hora = '{$hora}', 
		idterminal = '{$row['idterminal']}', terminal = '{$row['terminal']}', idusuario = '{$row['idusuario']}',
		usuario='{$row['usuario']}', unidad = '{$row['cve']}', monto = '{$row['importe']}', estatus='A'";
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
		$insert = "INSERT abono_operador_taquillamovil SET cve = '{$row['folio']}', fecha = '{$fecha}', hora = '{$hora}', 
		idterminal = '{$row['idterminal']}', terminal = '{$row['terminal']}', idusuario = '{$row['idusuario']}',
		usuario='{$row['usuario']}', operador = '{$row['cve']}', monto = '{$row['importe']}', estatus='A'";
		if($res = mysql_query($insert)){
			$resultado.=','.$row['id'];
		}
	}
	return substr($resultado, 1);
}

$function = $_POST['function'];

echo $function($_POST['datos']);

?>