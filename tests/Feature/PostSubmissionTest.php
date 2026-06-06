<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_post_form(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Отправка поста');
        $response->assertSee('name="post"', false);
    }

    public function test_valid_html_post_is_saved(): void
    {
        $response = $this->post(route('posts.store'), [
            'post' => '<strong>Hello</strong>',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('valid', true);
        $response->assertSessionHas('message', 'Пост валиден и сохранён');

        $this->assertDatabaseCount('posts', 1);
        $this->assertDatabaseHas('posts', [
            'content' => '<strong>Hello</strong>',
        ]);
    }

    public function test_valid_plain_text_post_is_saved(): void
    {
        $response = $this->post(route('posts.store'), [
            'post' => 'Hello world',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('valid', true);

        $this->assertDatabaseHas('posts', [
            'content' => 'Hello world',
        ]);
    }

    public function test_valid_anchor_post_is_saved(): void
    {
        $post = '<a href="https://example.com" title="Example">link</a>';

        $response = $this->post(route('posts.store'), [
            'post' => $post,
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('valid', true);

        $this->assertDatabaseHas('posts', [
            'content' => $post,
        ]);
    }

    public function test_valid_nested_post_is_saved(): void
    {
        $post = '<strong><i><code>x</code></i></strong>';

        $response = $this->post(route('posts.store'), [
            'post' => $post,
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('valid', true);

        $this->assertDatabaseHas('posts', [
            'content' => $post,
        ]);
    }

    public function test_unclosed_tag_is_not_saved(): void
    {
        $response = $this->post(route('posts.store'), [
            'post' => '<strong>',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('valid', false);
        $response->assertSessionHas('message', 'Пост невалиден');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_disallowed_tag_is_not_saved(): void
    {
        $response = $this->post(route('posts.store'), [
            'post' => '<div>text</div>',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('valid', false);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_cross_nested_tags_are_not_saved(): void
    {
        $response = $this->post(route('posts.store'), [
            'post' => '<i><strong></i></strong>',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('valid', false);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_bare_less_than_is_not_saved(): void
    {
        $response = $this->post(route('posts.store'), [
            'post' => '5 < 10',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('valid', false);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_empty_post_is_not_saved(): void
    {
        $response = $this->post(route('posts.store'), [
            'post' => '',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('valid', false);
        $response->assertSessionHas('message', 'Пост невалиден');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_invalid_post_preserves_input(): void
    {
        $post = '<strong>';

        $response = $this->post(route('posts.store'), [
            'post' => $post,
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('_old_input.post', $post);
    }
}
