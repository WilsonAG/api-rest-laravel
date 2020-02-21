<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Cargando clases
use App\Http\Middleware\ApiAuthMiddleware;

// Prueba

Route::get('/', function () {
    return view('welcome');
});

Route::get('/testing/{name?}', function ($name = 'Will') {
    $text = '<p>hola mundo</p>' . "<p>Nombre: $name</p>";
    return view('test', array(
        'text' => $text
    ));
});

Route::get('/pruebas/animales', 'TestController@index');
Route::get('/test-orm', 'TestController@testOrm');

//Rutas API
//Rutas de prueba
//Route::get('/user/pruebas', 'UserController@pruebas');
//Route::get('/category/pruebas', 'CategoryController@pruebas');
//Route::get('/post/pruebas', 'PostController@pruebas');

//Rutas del controlador de ususario
Route::post('/api/v1/register', 'UserController@register');
Route::post('/api/v1/login', 'UserController@login');
Route::put('/api/v1/user/update', 'UserController@update');
Route::post('/api/v1/user/upload', 'UserController@upload')->middleware(
    ApiAuthMiddleware::class
);
Route::get('/api/v1/user/avatar/{filename}', 'UserController@getImage');
Route::get('/api/v1/user/detail/{id}', 'UserController@detail');

// Rutas del controlador de categorias
Route::resource('/api/v1/category', 'CategoryController');
// Rutas del controlador de Posts
Route::resource('/api/v1/post', 'PostController');
Route::post('api/v1/post/upload', 'PostController@upload');
Route::get('api/v1/post/image/{filename}', 'PostController@getImage');
Route::get('api/v1/post/category/{id}', 'PostController@getPostsByCategory');
Route::get('api/v1/post/user/{id}', 'PostController@getPostsByUser');
