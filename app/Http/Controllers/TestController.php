<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;

class TestController extends Controller
{
    public function index()
    {
        $animals = ['perro', 'gato', 'tigre'];
        $title = 'Mis Animales';
        return view('test.index', array(
            'title' => $title,
            'animals' => $animals
        ));
    }

    public function testOrm()
    {
        // $posts = Post::all();
        // foreach ($posts as $post) {
        //     echo "<h1>$post->title</h1>";
        //     echo "<span>By: {$post->user->name}</span>";
        //     echo "<span>Category: {$post->Category->name}</span>";
        //     echo "<p>$post->content</p>";
        //     echo "<hr>";
        // }

        $categories = Category::all();
        foreach ($categories as $category) {
            echo "<h1> {$category->name}</h1>";

            foreach ($category->posts as $post) {
                echo "<h2>{$post->title}</h2>";
                echo "<span>By: {$post->user->name} - {$category->name}</span>";
                echo "<p>{$post->content}</p>";
            }
            echo "<hr>";
        }
        die();
    }
}
