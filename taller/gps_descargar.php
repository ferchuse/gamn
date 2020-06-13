<?php
$apk = 'GPSLocalizacion.apk';
header ("Content-Disposition: attachment; filename=" . $apk);
header ("Content-Type: application/force-download");
header ("Content-Length: " . filesize($apk));
readfile($apk);
exit();
?>
