<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UsuarioModel extends Model{

    protected $table = 'usuariogpsmovil';

    public static function getByLogin($user, $pass) {
        return DB::select(DB::raw("select cve as id, usuario as user, password as pass, dispositivos, admin, estatus from usuariogpsmovil where usuario = '$user' and password = '$pass'"));
    }
}
