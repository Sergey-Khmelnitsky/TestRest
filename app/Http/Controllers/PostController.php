<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\CommentLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(
        private readonly CommentLimiter $commentLimiter,
    ) {}

    public function index(): View
    {
        return view('home');
    }

    public function store(Request $request): RedirectResponse
    {
        $post = (string) ($request->input('post') ?? '');
        $userId = (int) $request->input('user_id', 0);

        if (! validatePost($post)) {
            return $this->redirectBack($userId, false, 'Пост невалиден');
        }

        if ($userId <= 0) {
            return back()
                ->withInput()
                ->with('valid', false)
                ->with('message', 'Укажите корректный ID пользователя');
        }

        if (! $this->commentLimiter->canPost($userId)) {
            return $this->redirectBack(
                $userId,
                false,
                'Слишком много комментариев, подождите 10 секунд'
            );
        }

        Post::create(['content' => $post]);

        return $this->redirectBack($userId, true, 'Пост валиден и сохранён');
    }

    private function redirectBack(int $userId, bool $valid, string $message): RedirectResponse
    {
        $redirect = back()
            ->withInput()
            ->with('valid', $valid)
            ->with('message', $message);

        if ($userId > 0) {
            $redirect->with('recent_post_count', $this->commentLimiter->getRecentPostCount($userId));
        }

        return $redirect;
    }
}
