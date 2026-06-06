<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('home');
    }

    public function store(Request $request): RedirectResponse
    {
        $post = (string) ($request->input('post') ?? '');

        if (validatePost($post)) {
            Post::create(['content' => $post]);

            return back()
                ->with('valid', true)
                ->with('message', 'Пост валиден и сохранён');
        }

        return back()
            ->withInput()
            ->with('valid', false)
            ->with('message', 'Пост невалиден');
    }
}
