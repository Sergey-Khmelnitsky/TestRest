<?php

function validatePost(string $post): bool
{
    if ($post === '') {
        return false;
    }

    $allowedTags = ['a', 'code', 'i', 'strike', 'strong'];

    if (! preg_match_all('/<\/?([a-zA-Z][a-zA-Z0-9]*)\b([^>]*)>/', $post, $matches, PREG_SET_ORDER)) {
        return ! str_contains($post, '<');
    }

    $stack = [];

    foreach ($matches as $match) {
        $full = $match[0];
        $name = $match[1];
        $attrs = $match[2];
        $isClosing = str_starts_with($full, '</');

        if (! in_array($name, $allowedTags, true)) {
            return false;
        }

        if ($name !== strtolower($name)) {
            return false;
        }

        if (preg_match('/\/>$/', $full)) {
            return false;
        }

        if ($isClosing) {
            if (trim($attrs) !== '') {
                return false;
            }
        } elseif ($name === 'a') {
            $remaining = preg_replace('/\s*(href="[^"]*"|title="[^"]*")/', '', $attrs);

            if (trim($remaining) !== '') {
                return false;
            }
        } elseif (trim($attrs) !== '') {
            return false;
        }

        if ($isClosing) {
            if ($stack === [] || array_pop($stack) !== $name) {
                return false;
            }
        } else {
            $stack[] = $name;
        }
    }

    if ($stack !== []) {
        return false;
    }

    $textOnly = preg_replace('/<\/?(?:a|code|i|strike|strong)\b[^>]*>/', '', $post);

    return ! str_contains($textOnly, '<');
}
