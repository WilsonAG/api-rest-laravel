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
Route::get('/user/pruebas', 'UserController@pruebas');
Route::get('/category/pruebas', 'CategoryController@pruebas');
Route::get('/post/pruebas', 'PostController@pruebas');

//Rutas del controlador de ususario
Route::post('/api/v1/register', 'UserController@register');
Route::post('/api/v1/login', 'UserController@login');
Route::post('/api/v1/user/update', 'UserController@update');
