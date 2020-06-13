<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DispositivoModel extends Model{
    
    protected $table = 'dispositivos';

    public static function getDispositivosPorUsuario($usuario) {
        $idDispositivos = explode(',', $usuario->dispositivos);
        return self::select('cve as id','nombre as economico', 'telefono', 'paro', 'arranque as arrancar')
            ->whereIn('cve', $idDispositivos)
            ->get();
    }
}
