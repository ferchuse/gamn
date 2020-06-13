<?php
class GPSInfo{

    private $pdo;

    public function __construct(){
        $data_base = 'gps';
        $dsn = 'mysql:host=localhost;dbname=' . $data_base;
        $user = 'gamn';
        $pass = 'gamn';
        $this->pdo = new PDO($dsn, $user, $pass);
    }

    public function getPuntosPorFecha($fecha_inicial, $fecha_final, $imei, $plaza){
        $fecha_ini = $fecha_inicial . ' 00:00:00';
        $fecha_fin = $fecha_final . ' 23:59:59';
        $query = "select * from movil_localizacion where plaza = :plaza and imei = :imei and fecha between :fecha_ini and :fecha_fin and latitud <> '0.0' and longitud <> '0.0' order by fecha asc";
        $pdo_statement = $this->pdo->prepare($query);
        $pdo_statement->bindParam(':plaza', $plaza, PDO::PARAM_INT);
        $pdo_statement->bindParam(':imei', $imei, PDO::PARAM_STR);
        $pdo_statement->bindParam(':fecha_ini', $fecha_ini, PDO::PARAM_STR);
        $pdo_statement->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
        $pdo_statement->execute();
        return $pdo_statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getImeis(){
        $query = 'select imei from movil_dispositivos order by imei asc';
        $pdo_statement = $this->pdo->prepare($query);
        $pdo_statement->execute();
        return $pdo_statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
