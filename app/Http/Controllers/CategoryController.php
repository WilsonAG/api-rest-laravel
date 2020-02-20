<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index()
    {
        $categories = Category::all();
        return response()->json(
            [
                'status' => 'ok',
                'code' => 200,
                'data' => $categories
            ],
            200
        );
    }

    public function show($id)
    {
        $category = Category::find($id);
        if (is_object($category)) {
            $response_data = [
                'status' => 'ok',
                'code' => 200,
                'data' => $category
            ];
        } else {
            $response_data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'No se pudo encontrar la categoria'
            ];
        }

        return response()->json($response_data, $response_data['code']);
    }

    public function store(Request $req)
    {
        //Recoger datos post
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
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            $response_data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Error al crear la categoria'
            ];
        } else {
            // Guardar Categoria
            $category = new Category();
            $category->name = $params['name'];
            $category->save();
            $response_data = [
                'status' => 'ok',
                'code' => 201,
                'data' => $category
            ];
        }
        // Devolver resultado

        return response()->json($response_data, $response_data['code']);
    }

    public function update($id, Request $req)
    {
        //Recoger datos post
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
            'name' => 'required'
        ]);
        // Quitar campos que no voy a actualizar
        unset($params['id']);
        unset($params['created_at']);
        // Actualizar registro
        $category = Category::where('id', $id)->update($params);
        $response_data = [
            'status' => 'ok',
            'code' => 200,
            'data' => $params
        ];
        // Devolver datos

        return response()->json($response_data, $response_data['code']);
    }
}
