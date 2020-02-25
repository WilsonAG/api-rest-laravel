<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Fecades\DB;
use App\User;

class JwtAuth
{
    public $token_key;

    public function __construct()
    {
        $this->token_key = 'AQUI_HAY_QUE_GENERAR_UNA_KEY_SUPER_SEGURA';
    }

    public function signUp($email, $password, $getToken = null)
    {
        // Buscar el usuario con credenciales
        $user = User::where([
            'email' => $email,
            'password' => $password
        ])->first();
        // Comprobar si son correctas
        $signup = false;
        if (is_object($user)) {
            $signup = true;
        }
        // Generar el token con los datos del user
        if ($signup) {
            $token_options = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'lastname' => $user->lastname,
                'description' => $user->description,
                'image' => $user->image,
                'iat' => time(),
                'exp' => time() + 5
            );

            $jwt_token = JWT::encode($token_options, $this->token_key, 'HS256');

            $jwt_user = JWT::decode($jwt_token, $this->token_key, ['HS256']);

            if (is_null($getToken)) {
                $data = array(
                    'status' => 'ok',
                    'code' => 200,
                    'data' => $jwt_token
                );
            } else {
                $data = array(
                    'status' => 'ok',
                    'code' => 200,
                    'data' => $jwt_user
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 401,
                'message' => 'Credenciales no validas'
            );
        }

        // Devolver los datos decodificados o el token
        return $data;
    }

    public function checkToken($jwt_token, $getIdentity = false)
    {
        $auth = false;
        try {
            $decoded = JWT::decode($jwt_token, $this->token_key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity) {
            return $decoded;
        }

        return $auth;
    }
}
