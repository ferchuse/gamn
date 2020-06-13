<?php

class Localizacion{

    private $pdo;
    private $latitud;
    private $longitud;
    private $fecha;
    private $imei;
    private $id_geocerca;
    private $respuesta;
    private $num_mails;
    private $MAX_MAILS;

    public function __construct($params){
        $data_base = 'ncargo';
        $dsn = 'mysql:host=localhost;dbname=' . $data_base;
        $user = 'ncargo';
        $pass = 'bAllenA6##6';
        $this->pdo = new PDO($dsn, $user, $pass);

        $this->latitud = $params['latitud'];
        $this->longitud = $params['longitud'];
        $this->fecha = $params['fecha'];
        $this->imei = $params['imei'];
        $this->respuesta = array();
        $this->num_mails = 0;
        $this->MAX_MAILS = 3;
        $this->id_geocerca = 0;
    }

    public function procesarPeticion(){
        if($this->estaImeiRegistrado())
            $this->guardarUbicacion();

         return $this->regresarRespuestaJSON();
    }

    public function estaImeiRegistrado(){
        $query = 'select estatus from movil_dispositivos where imei = :imei';
        $pdo_statement = $this->pdo->prepare($query);
        $pdo_statement->bindParam(':imei', $this->imei, PDO::PARAM_STR);

        if ($pdo_statement->execute()){
            if($result = $pdo_statement->fetch(PDO::FETCH_ASSOC)){
                if(strcmp($result['estatus'], 'A') == 0)
                    return true;
                else{
                    $respuesta['validado'] = false;
                    $respuesta['mensaje'] = 'El dispositivo con el imei: "' . $this->imei . '" esta desactivado';
                }
            }else{
                $this->respuesta['validado'] = false;
                $this->respuesta['mensaje'] = 'No encontrado: ' . json_encode($pdo_statement->errorInfo());
                $this->respuesta['correo'] = 'Se esta intentando recibir informacion de un dispositivo con el siguiente imei: "' . $this->imei . '"<br/><br/>Fecha: ' . $this->fecha . '<br/>Ubicacion<br/>Latitud: ' . $this->latitud . '<br/>Longitud: ' . $this->longitud;
                $this->enviarMail();
            }
        }else{
            $this->respuesta['validado'] = false;
            $this->respuesta['mensaje'] = 'Algo ocurrio: ' . json_encode($pdo_statement->errorInfo());
        }

        return false;
    }

    function getIdGeocercaActual(){
        $query = 'select * from movil_geocercas';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $geocercas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($geocercas as $geocerca){
            $distancia = $this->harvestine($geocerca['latitud'], $geocerca['longitud'], $this->latitud, $this->longitud);

            if($distancia <= ($geocerca['radio'] / 1000)){
                $this->id_geocerca = $geocerca['id'];
                break;
            }
        }
    }

    function harvestine($lat1, $long1, $lat2, $long2){
        //Distancia en kilometros en 1 grado distancia.
        //Distancia en millas nauticas en 1 grado distancia: $mn = 60.098;
        //Distancia en millas en 1 grado distancia: 69.174;
        //Solo aplicable a la tierra, es decir es una constante que cambiaria en la luna, marte... etc.
        $km = 111.302;

        //1 Grado = 0.01745329 Radianes
        $degtorad = 0.01745329;

        //1 Radian = 57.29577951 Grados
        $radtodeg = 57.29577951;
        //La formula que calcula la distancia en grados en una esfera, llamada formula de Harvestine. Para mas informacion hay que mirar en Wikipedia
        //http://es.wikipedia.org/wiki/F%C3%B3rmula_del_Haversine
        $dlong = ($long1 - $long2);
        $dvalue = (sin($lat1 * $degtorad) * sin($lat2 * $degtorad)) + (cos($lat1 * $degtorad) * cos($lat2 * $degtorad) * cos($dlong * $degtorad));
        $dd = acos($dvalue) * $radtodeg;
        return round(($dd * $km), 2);
    }

    function guardarUbicacion(){
        $this->getIdGeocercaActual();
        $query = 'insert into movil_localizacion(latitud, longitud, fecha, imei, id_geocerca) values(:latitud, :longitud, :fecha, :imei, :id_geocerca)';
        $pdo_statement = $this->pdo->prepare($query);
        $pdo_statement->bindParam(':latitud', $this->latitud, PDO::PARAM_STR);
        $pdo_statement->bindParam(':longitud', $this->longitud, PDO::PARAM_STR);
        $pdo_statement->bindParam(':fecha', $this->fecha, PDO::PARAM_STR);
        $pdo_statement->bindParam(':imei', $this->imei, PDO::PARAM_STR);
        $pdo_statement->bindParam(':id_geocerca', $this->id_geocerca, PDO::PARAM_INT);

        if($pdo_statement->execute())
            $this->respuesta['validado'] = true;
        else{
            $this->respuesta['validado'] = false;
            $this->respuesta['mensaje'] = json_encode($pdo_statement->errorInfo());
        }
    }

    public function enviarMail(){
        if(isset($this->respuesta['correo']) && strcmp($this->respuesta['correo'], '') != 0){
            if($this->validarEnvio()){
                require_once("../phpmailer/class.phpmailer.php");
                $mail = new PHPMailer();
                $mail->Host = "ssl://smtp.mailgun.org";
                $mail->IsSMTP();
                $mail->Port = 465;
                $mail->SMTPAuth = true;
                $mail->Username ='postmaster@sandbox41deea0ea6eb42be851dd31543f3a267.mailgun.org';
                $mail->Password = 'c33cf079c2869674f31afd9594f3ebb9';
                $mail->From = 'hgaribay@gmail.com'; //$emailfrom; 'hgaribay@gmail.com'
                $mail->FromName = 'Localizador Ncargo';
                $mail->AddAddress('hgaribay@gmail.com');
                $mail->Subject = 'Intento de Acceso a Localizador Ncargo';
                $mail->IsHTML(true);
                $mail->Body = 'Se esta intentando recibir informacion de un dispositivo con el siguiente imei: "' . $this->imei . '"<br/><br/>Fecha: ' . $this->fecha . '<br/>Ubicacion<br/>Latitud: ' . $this->latitud . '<br/>Longitud: ' . $this->longitud;

                if($mail->Send()){
                    $this->respuesta['enviado'] = 'el mensaje ha sido enviado';

                    if($this->num_mails < $this->MAX_MAILS)
                        $this->incrementarMail();
                }else{
                    $this->respuesta['enviado'] = $this->email . ' ' . $mail->ErrorInfo;
                }
            }
        }
    }

    public function validarEnvio(){
        $this->num_mails = $this->getMailsEnviadosDelImei();

        if($this->num_mails < $this->MAX_MAILS)
            return true;

        return false;
    }

    public function getMailsEnviadosDelImei(){
        $query = 'select mails from movil_dispositivos_invalidos where imei = :imei';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':imei', $this->imei, PDO::PARAM_STR);
        $stmt->execute();

        if($row = $stmt->fetch(PDO::FETCH_ASSOC))
            return $row['mails'];

        $fecha = date('Y-m-d H:i:s');
        $query = 'insert into movil_dispositivos_invalidos (imei, mails, fecha) values(:imei, 0, :fecha)';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':imei', $this->imei, PDO::PARAM_STR);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();
        return 0;
    }

    public function incrementarMail(){
        $query = 'update movil_dispositivos_invalidos set mails = mails + 1 where imei = :imei';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':imei', $this->imei, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function regresarRespuestaJSON(){
        return json_encode($this->respuesta);
    }
}

$localizacion = new Localizacion($_POST);
echo($localizacion->procesarPeticion());
?>
