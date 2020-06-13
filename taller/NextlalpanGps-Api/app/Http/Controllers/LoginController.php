<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

use App\Http\Requests;

use App\UsuarioModel;
use App\DispositivoModel;

class LoginController extends Controller{
    
    public function login(Request $request) {
        $respuesta = array('validado' => true, 'mensaje' => '', 'data' => array());
        $user = $request->input('user');
        $pass = $request->input('pass');

        try {
            $usuarios = UsuarioModel::getByLogin($user, $pass);

            if(count($usuarios) == 0)
                throw new Exception('El usuario no existe o los datos son incorrectos');

            if($usuarios[0]->estatus != 'A')
                throw new Exception('El usuario no esta activo');

            $respuesta['data']['usuario'] = $usuarios[0];
            $respuesta['data']['economicos'] = DispositivoModel::getDispositivosPorUsuario($usuarios[0]);
        } catch(Exception $e) {
            $respuesta['validado'] = false;
            $respuesta['mensaje'] = $e->getMessage();
        }
        
        return response()->json($respuesta);
    }
}
