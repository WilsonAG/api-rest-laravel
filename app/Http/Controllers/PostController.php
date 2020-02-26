<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', [
            'except' => [
                'index',
                'show',
                'getImage',
                'getPostsByCategory',
                'getPostsByUser'
            ]
        ]);
    }

    public function index()
    {
        $posts = Post::all()->load('category');
        $response_data = [
            'status' => 'ok',
            'code' => 200,
            'data' => $posts
        ];

        return response()->json($response_data, $response_data['code']);
    }

    public function show($id)
    {
        $post = Post::find($id)
            ->load('category')
            ->load('user');

        if (is_object($post)) {
            $response_data = [
                'status' => 'ok',
                'code' => 200,
                'data' => $post
            ];
        } else {
            $response_data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'La entrada no existe.'
            ];
        }

        return response()->json($response_data, $response_data['code']);
    }

    public function store(Request $req)
    {
        // Recoger datos post
        $userdata = $req->input('json', null);
        $params = json_decode($userdata, true);

        if (empty($params)) {
            $response_data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Error al procesar la solicitud'
            ];
            return response()->json($response_data, $response_data['code']);
        }
        // obtener ususario identificado
        $user = $this->getIdentity($req);

        // Validar datos
        $validator = \Validator::make($params, [
            'title' => 'required',
            'content' => 'required',
            'category_id' => 'required',
            'image' => 'required'
        ]);

        if ($validator->fails()) {
            $response_data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Datos insuficientes para guardar el post.'
            ];
            return response()->json($response_data, $response_data['code']);
        }

        // Guardar el post
        $post = new Post();
        $post->user_id = $user->sub;
        $post->category_id = $params['category_id'];
        $post->title = $params['title'];
        $post->content = $params['content'];
        $post->image = $params['image'];

        $post->save();
        // Devolver la respuesta

        $response_data = [
            'status' => 'ok',
            'code' => 200,
            'message' => 'Se ha creado el post sin problemas.',
            'data' => $post
        ];
        return response()->json($response_data, $response_data['code']);
    }

    public function update(Request $req, $id)
    {
        // Recoger datos
        $userdata = $req->input('json', null);
        $params = json_decode($userdata, true);

        if (empty($params)) {
            $response_data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Error al procesar la solicitud'
            ];

            return response()->json($response_data, $response_data['code']);
        }

        // Validar datos
        $validator = \Validator::make($params, [
            'title' => 'required',
            'content' => 'required',
            'category_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response_data = [
                'status' => 'error',
                'code' => 400,
                'message' =>
                    'Los datos enviados no tienen el formato correcto.',
                'errors' => $validator->errors()
            ];

            return response()->json($response_data, $response_data['code']);
        }

        // eliminar lo q no vamos a actualizar
        unset($params['id']);
        unset($params['user_id']);
        unset($params['created_at']);
        unset($params['user']);

        // obtener usuario identificado
        $user = $this->getIdentity($req);
        // buscar registro a actualizar
        $post = Post::where('id', $id)
            ->where('user_id', $user->sub)
            ->first();

        if (!empty($post) && is_object($post)) {
            $post->update($params);
            // Devolver data
            $response_data = [
                'status' => 'ok',
                'code' => 200,
                'data' => [
                    'post' => $post,
                    'changes' => $params
                ]
            ];
        } else {
            $response_data = [
                'status' => 'error',
                'code' => 401,
                'message' => 'No tiene permiso para modificar este post.'
            ];
        }

        return response()->json($response_data, $response_data['code']);
    }

    public function destroy($id, Request $req)
    {
        // Conseguir usuario identificado
        $user = $this->getIdentity($req);
        // Comprobar si existe el post
        $post = Post::where('id', $id)
            ->where('user_id', $user->sub)
            ->first();
        if (empty($post)) {
            $response_data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'No se pudo localizar el post.'
            ];
            return response()->json($response_data, $response_data['code']);
        }
        // Borrar si existe
        $post->delete();
        // Devolver data

        $response_data = [
            'status' => 'ok',
            'code' => 200,
            'data' => $post
        ];

        return response()->json($response_data, $response_data['code']);
    }

    public function upload(Request $req)
    {
        // Recoger datos
        $image = $req->file('file0');
        // Validar imagen
        $validator = \Validator::make($req->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);
        // Guardar la imagen
        if (!$image || $validator->fails()) {
            $response_data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Error al subir la imagen.'
            ];
        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));
            $response_data = [
                'status' => 'ok',
                'code' => 201,
                'image' => $image_name
            ];
        }
        // Devolver datos
        return response()->json($response_data, $response_data['code']);
    }

    public function getImage($filename)
    {
        // Comprobar si existe el archivo
        $isset = \Storage::disk('images')->exists($filename);
        if ($isset) {
            // Conseguir la imagen
            $file = \Storage::disk('images')->get($filename);
            // Devolver imagen
            return new Response($file, 200);
        } else {
            // error
            $response_data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'No se encontro la imagen.'
            ];
        }

        return response()->json($response_data, $response_data['code']);
    }

    public function getPostsByCategory($id)
    {
        $posts = Post::where('category_id', $id)->get();
        $response_data = [
            'status' => 'ok',
            'code' => 200,
            'data' => $posts
        ];
        return response()->json($response_data, $response_data['code']);
    }

    public function getPostsByUser($id)
    {
        $posts = Post::where('user_id', $id)->get();
        $response_data = [
            'status' => 'ok',
            'code' => 200,
            'data' => $posts
        ];
        return response()->json($response_data, $response_data['code']);
    }

    private function getIdentity($request)
    {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization');
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }
}
