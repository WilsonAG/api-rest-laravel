<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            $pass = hash('sha256', $userdata_array['password']);

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
        // Recibir data por post
        $userdata = $request->input('json', null);
        $logindata = json_decode($userdata, true);

        // Validar datos
        $response_data = $this->validateLogin($logindata);

        return response()->json($response_data, $response_data['code']);
    }

    public function update(Request $request)
    {
        //Comprobar si esta autenticado
        $token = $request->header('Authorization');
        $token = preg_replace('/([\'"])/', '', $token);
        $jwtAuth = new \JwtAuth();

        $checkToken = $jwtAuth->checkToken($token);
        // Recoger datos por post
        $json = $request->input('json', null);
        $userdata = json_decode($json, true);

        if ($checkToken && !empty($userdata)) {
            //Actualizar Usuario

            //Sacar id del usuario identificado
            $user = $jwtAuth->checkToken($token, true);

            // Validacion de datos
            $validator = \Validator::make($userdata, [
                'name' => 'required|alpha',
                'lastname' => 'required|alpha',
                'email' => "required|email|unique:users, $user->sub"
            ]);

            // Eliminar campos que no voy a actualizar
            unset($userdata['id']);
            unset($userdata['role']);
            unset($userdata['password']);
            unset($userdata['created_at']);
            unset($userdata['remember_token']);

            // Actualizar user en BD
            $user_update = User::where('id', $user->sub)->update($userdata);
            $response_data = array(
                'status' => 'ok',
                'code' => 200,
                'updated' => $user_update,
                'data' => array(
                    'user' => $user,
                    'changes' => $userdata
                )
            );
        } else {
            //Mandar error
            $response_data = array(
                'code' => 401,
                'status' => 'error',
                'message' => 'El usuario no esta autenticado.'
            );
        }

        return response()->json($response_data, $response_data['code']);
    }

    public function upload(Request $request)
    {
        // Obtener datos
        $image = $request->file('file0');

        // Validar imagen
        $validator = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);
        // Guardar imagen
        if (!$image || $validator->fails()) {
            $responsedata = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El al subir la imagen'
            );
        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $responsedata = array(
                'status' => 'ok',
                'code' => '200',
                'image' => $image_name
            );
        }
        // Devolver resultado

        return response()->json($responsedata, $responsedata['code']);
    }

    public function getImage($filename)
    {
        $isset = \Storage::disk('users')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } else {
            $response_data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'La imagen no existe'
            );
            return response()->json($response_data, $response_data['code']);
        }
    }

    public function detail($id)
    {
        $user = User::find($id);

        if (is_object($user)) {
            $response_data = array(
                'status' => 'ok',
                'code' => 200,
                'message' => 'Usuario encontrado con exito',
                'data' => $user
            );
        } else {
            $response_data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Usuario no encontrado'
            );
        }

        return response()->json($response_data, $response_data['code']);
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

    private function validateLogin($logindata)
    {
        $jwtAuth = new \JwtAuth();
        if (!empty($logindata)) {
            $validator = \Validator::make($logindata, [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if ($validator->fails()) {
                $response_data = array(
                    'status' => 'error',
                    'code' => 401,
                    'message' => 'No se ha podido iniciar sesion.',
                    'errors' => $validator->errors()
                );
            } else {
                // Cifrar password
                $pwd = hash('sha256', $logindata['password']);

                // Devolver datos o token
                $response_data = $jwtAuth->signUp($logindata['email'], $pwd);
                if (!empty($logindata['getToken'])) {
                    $response_data = $jwtAuth->signUp(
                        $logindata['email'],
                        $pwd,
                        true
                    );
                }
            }
        } else {
            $response_data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No se pudo procesar los datos enviados.'
            );
        }

        return $response_data;
    }
}
