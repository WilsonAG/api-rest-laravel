<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
    public function pruebas(Request $request)
    {
        return "pruebas con usercontroller";
    }

    public function register(Request $request)
    {
        // Obtener datos
        $userdata = $request->input('json', null);
        // $userdata_obj = json_decode($userdata); //devuelve objeto
        $userdata_array = json_decode($userdata, true); //devuelve array

        //Validacion de datos
        $data = $this->validateRegister($userdata_array);

        if ($data['status'] == 'ok') {
            // Validacion correcta
            $pass = password_hash(
                $userdata_array['password'],
                PASSWORD_BCRYPT,
                [
                    'cost' => 4
                ]
            );

            // Crear el usuario
            $user = new User();
            $user->name = $userdata_array['name'];
            $user->lastname = $userdata_array['lastname'];
            $user->email = $userdata_array['email'];
            $user->password = $pass;
            $user->role = 'ROLE_USER';
            //Guardar el usuario en BD
            $user->save();

            //Agregar datos del usuario a la respuesta json
            $data['user'] = $user;
        }

        return response()->json($data, $data['code']);
    }

    public function login(Request $request)
    {
        return "Aqui se va a logear usuario";
    }

    // Funciones auixiliares
    private function validateRegister($userdata_array)
    {
        if (!empty($userdata_array)) {
            // Limpiar datos
            $userdata_array = array_map('trim', $userdata_array);

            // Validar datos
            $validate = \Validator::make($userdata_array, [
                'name' => 'required|alpha',
                'lastname' => 'required|alpha',
                'email' => 'required|email|unique:users', //la opcion unique:users valida q el email sea unico en la tabla users
                'password' => 'required'
            ]);

            if ($validate->fails()) {
                //Validacion fallo
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' =>
                        'Error al crear el usuario, verifique que los datos enviados sean correctos.',
                    'errors' => $validate->errors()
                );
            } else {
                // Se va a crear el usuario
                $data = array(
                    'status' => 'ok',
                    'code' => 201,
                    'message' => 'El usuario ha sido creado correctamente.'
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No se pudo procesar los datos enviados.'
            );
        }

        return $data;
    }
}
