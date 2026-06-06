<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            font-family: system-ui, -apple-system, sans-serif;
            padding: 24px;
        }

        .container {
            width: 100%;
            max-width: 640px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 16px;
        }

        textarea {
            width: 100%;
            min-height: 200px;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            resize: vertical;
            font-family: inherit;
        }

        textarea:focus {
            outline: none;
            border-color: #666;
        }

        button {
            margin-top: 12px;
            padding: 10px 16px;
            font-size: 16px;
            border: 1px solid #333;
            border-radius: 6px;
            background: #333;
            color: #fff;
            cursor: pointer;
        }

        button:hover {
            background: #111;
        }

        .message {
            margin-top: 16px;
            padding: 12px;
            border-radius: 6px;
            font-size: 15px;
        }

        .message--success {
            background: #ecfdf3;
            border: 1px solid #86efac;
            color: #166534;
        }

        .message--error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Отправка поста</h1>

        <form method="POST" action="{{ route('posts.store') }}">
            @csrf
            <textarea name="post" placeholder="Введите текст поста...">{{ old('post') }}</textarea>
            <button type="submit">Отправить</button>
        </form>

        @if (session()->has('message'))
            <div class="message {{ session('valid') ? 'message--success' : 'message--error' }}">
                {{ session('message') }}
            </div>
        @endif
    </div>
</body>
</html>
