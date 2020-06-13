<?php
session_start();
include ("main.php");

# ajax blocks

top($_SESSION);

# cmd blocks
if($_POST['cmd'] == 0){
    echo('
    <div class="height: 450px; overflow: auto">
        <table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
            <tr bgcolor="#E9F2F8">
                <th>Nombre</th>
                <th>Apodo</th>
                <th>IMEI</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Mapa</th>
            </tr>');

    $fecha = date('Y-m-d');
    $query = "select a.id, a.imei, a.latitud, a.longitud, a.fecha, a.estatus, d.nombre, d.apodo from movil_asistencias as a inner join movil_dispositivos as d on d.id = a.id_dispositivo where left(a.fecha, 10) = '{$fecha}' order by a.imei, a.fecha";
    $result = mysql_query($query);
    $asistencias = array();

    while($row = mysql_fetch_assoc($result)){
        if(!array_key_exists($row['imei'], $asistencias))
            $asistencias[$row['imei']] = array();

        $asistencias[$row['imei']][$row['estatus']]['id'] = $row['id'];
        $asistencias[$row['imei']][$row['estatus']]['nombre'] = $row['nombre'];
        $asistencias[$row['imei']][$row['estatus']]['apodo'] = $row['apodo'];
        $asistencias[$row['imei']][$row['estatus']]['fecha'] = $row['fecha'];
        $asistencias[$row['imei']][$row['estatus']]['latitud'] = $row['latitud'];
        $asistencias[$row['imei']][$row['estatus']]['longitud'] = $row['longitud'];
    }

    foreach($asistencias as $imei => $aestatus){
        rowb();
        echo('
            <td>' . iconv("ISO-8859-1", "UTF-8", $aestatus['E']['nombre']) . '</td>
            <td>' . iconv("ISO-8859-1", "UTF-8", $aestatus['E']['apodo']) . '</td>
            <td>' . $imei . '</td>
            <td>' . $aestatus['E']['fecha'] . '</td>
            <td>' . $aestatus['S']['fecha'] . '</td>
            <td><a href="#" onclick="verMapa(' . $aestatus['E']['id'] . ', ' . $aestatus['S']['id'] . ');">Ver Mapa</a></td>
        </tr>');
    }

    echo('
        </table>
    </div>
    <script type="text/javascript">
        function verMapa(id1, id2){
            var params = "entrada=" + id1 + "&salida=" + id2;
            window.open("gps_mapa.php?" + params, "_blank", "width=600,height=600")
        }
    </script>');
}

bottom()
?>
