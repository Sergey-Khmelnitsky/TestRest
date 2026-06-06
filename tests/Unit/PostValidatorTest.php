<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PostValidatorTest extends TestCase
{
    #[DataProvider('validPostsProvider')]
    public function test_valid_posts(string $post): void
    {
        $this->assertTrue(validatePost($post));
    }

    #[DataProvider('invalidPostsProvider')]
    public function test_invalid_posts(string $post): void
    {
        $this->assertFalse(validatePost($post));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validPostsProvider(): array
    {
        return [
            'plain text' => ['plain text without tags'],
            'whitespace only' => ['   '],
            'text with ampersand' => ['Tom & Jerry'],
            'strong tag' => ['<strong>bold</strong>'],
            'italic tag' => ['<i>italic</i>'],
            'strike tag' => ['<strike>crossed</strike>'],
            'code tag' => ['<code>var</code>'],
            'empty tag pair' => ['<strong></strong>'],
            'anchor with attributes' => ['<a href="https://x.com" title="link">go</a>'],
            'anchor with empty attributes' => ['<a href="" title="">text</a>'],
            'anchor with href only' => ['<a href="https://example.com">link</a>'],
            'anchor with title only' => ['<a title="read more">link</a>'],
            'anchor without attributes' => ['<a>link</a>'],
            'anchor attributes reversed order' => ['<a title="link" href="https://x.com">go</a>'],
            'nested tags' => ['<i><code>x</code></i>'],
            'deep nesting' => ['<strong><i><code>snippet</code></i></strong>'],
            'multiple sibling tags' => ['<strong>hello</strong> <i>world</i>'],
            'mixed content' => ['Hello <strong>world</strong>!'],
            'multiline content' => ["line1\n<strong>line2</strong>"],
            'unicode text' => ['<strong>привет</strong>'],
            'special chars except angle bracket' => ['price: 100€, ratio: 3>2'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidPostsProvider(): array
    {
        return [
            'empty string' => [''],
            'unclosed tag' => ['<strong>'],
            'extra closing tag' => ['</strong>'],
            'double closing tag' => ['<strong>text</strong></strong>'],
            'cross nested tags' => ['<i><strong></i></strong>'],
            'wrong closing tag name' => ['<i></strong>'],
            'mismatched closing tag' => ['<strike>x</strike></strong>'],
            'disallowed div tag' => ['<div>x</div>'],
            'disallowed script tag' => ['<script>alert(1)</script>'],
            'disallowed b tag' => ['<b>bold</b>'],
            'disallowed br tag' => ['<br>'],
            'disallowed img tag' => ['<img src="x.png">'],
            'hidden disallowed tag after text' => ['hello<div>x</div>'],
            'uppercase opening tag' => ['<A href="">text</A>'],
            'uppercase closing tag' => ['<a href="">text</A>'],
            'mixed case tag name' => ['<Strong>text</Strong>'],
            'uppercase attribute name' => ['<a HREF="https://x.com">link</a>'],
            'single quoted href' => ['<a href=\'x\'>link</a>'],
            'unquoted href' => ['<a href=https://x.com>link</a>'],
            'extra attribute on anchor' => ['<a onclick="x">link</a>'],
            'class attribute on anchor' => ['<a href="x" class="link">link</a>'],
            'style attribute on anchor' => ['<a href="x" style="color:red">link</a>'],
            'extra attribute on code' => ['<code class="x">x</code>'],
            'extra attribute on strong' => ['<strong id="x">x</strong>'],
            'self closing code' => ['<code/>'],
            'self closing strong' => ['<strong/>'],
            'self closing anchor' => ['<a href="https://x.com" />'],
            'closing tag with attributes' => ['<strong>text</strong foo>'],
            'bare less than in text' => ['5 < 10'],
            'bare less than inside tag content' => ['<strong>5 < 10</strong>'],
            'incomplete opening tag' => ['<strong'],
            'incomplete closing tag' => ['</strong'],
            'only opening tags' => ['<strong><i>'],
            'only closing tags' => ['</strong></i>'],
        ];
    }
}
