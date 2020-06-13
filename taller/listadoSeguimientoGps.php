<?php
/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/

session_start();
include ("main.php");

function getConexion(){
  $data_base = 'gamn_gps_skymedia';
  $dsn = 'mysql:host=localhost;dbname=' . $data_base;
  $user = 'gamn';
  $pass = 'rXj8nBpwFu2tDwe2';
  return new PDO($dsn, $user, $pass);
}

$pdo = getConexion();

top($_SESSION);

if($_POST['cmd'] == 0){
  $query = 'select * from gps_objects';
  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $gpsArray = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $query = 'select * from marcadores order by cve';
  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $marcadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $thMarcadores = '';
  $arrayCveMarcadores = array();

  foreach($marcadores as $marcador){
    $arrayCveMarcadores[] = $marcador['cve'];
    $thMarcadores .= "<th bgcolor=\"#E9F2F8\">{$marcador['nombre']}</th>";
  }
  
  $fecha = date('Y-m-d');
  echo('<div style="height: 450px; overflow: auto;">');
  echo('<table  width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
          <thead>
            <tr>
              <th bgcolor="#E9F2F8">Dispositivo</th>
              ' . $thMarcadores . '
            </tr>
          </thead>
          <tbody>');

  $query = 'select min(distancia) as distancia, fecha, latitud, longitud 
  from gpsAproximacionPunto 
  where left(fecha, 10) = :fecha and imei = :imei and idPuntoMarcador = :idPunto order by fecha desc limit 1';
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
  $size = count($arrayCveMarcadores);

  foreach($gpsArray as $gps){
    rowb();
    echo("
      <td align='center'>{$gps['dispositivo']}</td>");

    for($i = 0; $i < $size; $i++){
      $stmt->bindParam(':imei', $gps['imei'], PDO::PARAM_STR);
      $stmt->bindParam(':idPunto', $arrayCveMarcadores[$i], PDO::PARAM_STR);
      $stmt->execute();
      $punto = $stmt->fetch(PDO::FETCH_ASSOC);

      if($punto['distancia']){
        echo("
        <td align='left'>
          <ul>
            <li>Distancia: {$punto['distancia']}</li>
            <li>Hora: " . substr($punto['fecha'], 11) . "</li>
            <li>Latitud: {$punto['latitud']}</li>
            <li>Longitud: {$punto['longitud']}</li>
          </ul>
        </td>");
      }else
        echo('<td></td>');
    }

    echo("
    </tr>");
  }

  echo('  </tbody>
        </table>
      </div>');
}

bottom();
?>