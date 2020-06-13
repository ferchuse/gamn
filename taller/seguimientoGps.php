<?php
function getConexion(){
  $data_base = 'gamn_gps_skymedia';
  $dsn = 'mysql:host=localhost;dbname=' . $data_base;
  $user = 'gamn';
  $pass = 'rXj8nBpwFu2tDwe2';
  return new PDO($dsn, $user, $pass);
}

function harvestine($lat1, $long1, $lat2, $long2){
	$degtorad = 0.01745329;
	$radtodeg = 57.29577951;

	$dlong = ($long1 - $long2);
	$dvalue = (sin($lat1 * $degtorad) * sin($lat2 * $degtorad))
	+ (cos($lat1 * $degtorad) * cos($lat2 * $degtorad)
	* cos($dlong * $degtorad));
	$dd = acos($dvalue) * $radtodeg;
	$miles = ($dd * 69.16);
	$km = ($dd * 111.302);

	return round($km, 2);
}

function getPuntos($pdo){
  $query = "select fecha from gpsAproximacionPunto order by fecha desc, hora desc limit 1";
  $stmt = $pdo->prepare($query);
  $stmt->execute();
  
  if($result = $stmt->fetch(PDO::FETCH_ASSOC)){
    $fecha = $result['fecha'];
  }else{
    $fecha = date('Y-m-d H:i:s');
  }

  $query = "select * from gps_objects_history where dt_tracker > '$fecha' order by imei";
  echo($query . '<br/>');
  $stmt = $pdo->prepare($query);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function seguimientoRutas(){
  $pdo = getConexion();
  $query = 'select cve, latitud, longitud from marcadores';
  echo($query . '<br/>');
  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $fecha = date('Y-m-d');

  foreach($result as $row){
    echo('MARCADOR ' . $row['cve'] . '<br/><br/>');
    $punto = null;
    $imeiAnt = '';

    foreach(getPuntos($pdo) as $rowPunto){
      $distancia = 0;
      $imei = $rowPunto['imei'];

      if($punto == null){
        $imeiAnt = $imei;
        $punto = $rowPunto;
        $distancia = harvestine($punto['latitud'], $punto['longitud'], $row['latitud'], $row['longitud']);
      }else{
        $distancia = harvestine($punto['latitud'], $punto['longitud'], $row['latitud'], $row['longitud']);
        $distanciaAux = harvestine($rowPunto['latitud'], $rowPunto['longitud'], $row['latitud'], $row['longitud']);

        if($distanciaAux < $distancia){
          $distancia = $distanciaAux;
          $punto = $rowPunto;
        }
      }

      if($imei != $imeiAnt){
        $query = "insert into gpsAproximacionPunto 
        set imei = '{$punto['imei']}', idPuntoMarcador = '{$row['cve']}', 
        latitud = '{$punto['latitud']}', longitud = '{$punto['longitud']}',
        fecha = '{$punto['dt_tracker']}', distancia = '$distancia', idPuntoGps = '{$punto['cve']}'";
        echo($query . '<br/>');
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $imeiAnt = $imei;
        $distancia = 0;
        $punto = null;
      }
    }

    echo('<br/><br/><br/>');
  }
}

seguimientoRutas();
echo('Termino...')
?>