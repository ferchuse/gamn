<?php
session_start();
include ("main.php");

$id1 = $_GET['entrada'];
$id2 = $_GET['salida'];
$entrada = '';
$salida = '';
if(isset($id1) && !empty($id1)){
    $query = "select a.imei, a.latitud, a.longitud, a.fecha, a.estatus, d.nombre, d.apodo from movil_asistencias as a inner join movil_dispositivos as d on d.id = a.id_dispositivo where a.id = '$id1'";
    $result = mysql_query($query);

    if($entrada = mysql_fetch_assoc($result))
        $entrada = 'var entrada = ' . json_encode($entrada) . ';';
}

if(isset($id2) && !empty($id2)){
    $query = "select a.imei, a.latitud, a.longitud, a.fecha, a.estatus, d.nombre, d.apodo from movil_asistencias as a inner join movil_dispositivos as d on d.id = a.id_dispositivo where a.id = '$id2'";
    $result = mysql_query($query);

    if($salida = mysql_fetch_assoc($result))
        $salida = 'var salida = ' . json_encode($salida) . ';';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ubicacion Cliente <?php echo($cliente); ?></title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <!--<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>-->
</head>
<body>
    <div id="mapa" style="height:100%; width: 100%; position: absolute;">
       <div style="height: 100%; width: 100%;" id="map-canvas"></div>
     </div>

<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCkz855QnPp8U5ayVATsNQ96EDtLhwWBAY&v=3&libraries=geometry"></script>
<script type="text/javascript">
    var map;
    <?= $entrada ?>
    <?= $salida ?>

    function initialize() {
      var marcadores = [entrada, salida];
      var map = new google.maps.Map(document.getElementById('map-canvas'), {
        zoom: 15,
        center: new google.maps.LatLng(entrada.latitud, entrada.longitud),
        mapTypeId: google.maps.MapTypeId.ROADMAP
      });
      var infowindow = new google.maps.InfoWindow();
      var marker, i;
      for (i = 0; i < marcadores.length; i++) {
        marker = new google.maps.Marker({
          position: new google.maps.LatLng(marcadores[i].latitud, marcadores[i].longitud),
          map: map
        });
        google.maps.event.addListener(marker, 'click', (function(marker, i) {
          return function() {
            var tipo = marcadores[i].estatus == "E" ? 'Entrada' : 'Salida'
            var contenido = "<h3>" + tipo + "</h3>" +
                "Nombre: " + marcadores[i].nombre + "<br/>" +
                "Apodo: " + marcadores[i].apodo + "<br/>" +
                "Fecha: " + marcadores[i].fecha + "<br/>" +
                "Latitud: " + marcadores[i].latitud + "<br/>" +
                "Longitud: " + marcadores[i].longitud + "<br/>";
            infowindow.setContent(contenido);
            infowindow.open(map, marker);
          }
        })(marker, i));
      }
    }
    google.maps.event.addDomListener(window, 'load', initialize);
</script>
</body>
</html>
