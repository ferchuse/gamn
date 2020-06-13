<?php
class GPSInfo{

    private $pdo;

    public function __construct(){
        $data_base = 'gamn_gps_otra_plataforma';
        $dsn = 'mysql:host=localhost;dbname=' . $data_base;
        $user = 'gamn';
        $pass = 'rXj8nBpwFu2tDwe2';
        $this->pdo = new PDO($dsn, $user, $pass);
    }

     public function getPuntosPorFecha($fecha_inicial, $fecha_final, $imei, $plaza){

        $fecha_ini = $fecha_inicial . ' 00:00:00';
        $fecha_fin = $fecha_final . ' 23:59:59';
//		$disp=$array_unidad_[$imei];
        $query = "select * from posiciones where dispositivo = :imei and fecha between :fecha_ini and :fecha_fin and latitud <> '0.0' and longitud <> '0.0' order by fecha asc";
        $pdo_statement = $this->pdo->prepare($query);
//        $pdo_statement->bindParam(':plaza', $plaza, PDO::PARAM_INT);
        $pdo_statement->bindParam(':imei', $imei, PDO::PARAM_STR);
        $pdo_statement->bindParam(':fecha_ini', $fecha_ini, PDO::PARAM_STR);
        $pdo_statement->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
        $pdo_statement->execute();
        return $pdo_statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getImeis(){
        $query = 'select uniqueid from dispositivo order by uniqueid asc';
        $pdo_statement = $this->pdo->prepare($query);
        $pdo_statement->execute();
        return $pdo_statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
