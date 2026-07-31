<?php

use App\Models\Page;
use App\Services\ContentSanitizer;

test('allowed page formatting is preserved', function (): void {
    $html = <<<'HTML'
<h2>Official heading</h2>
<p>
    This is <strong>important</strong> information.
</p>
<ul>
    <li>First item</li>
    <li>Second item</li>
</ul>
HTML;

    $cleanHtml = app(
        ContentSanitizer::class,
    )->sanitize($html);

    expect($cleanHtml)
        ->toContain('<h2>Official heading</h2>')
        ->toContain('<strong>important</strong>')
        ->toContain('<ul>')
        ->toContain('<li>First item</li>');
});

test('dangerous html and event attributes are removed', function (): void {
    $html = <<<'HTML'
<h2 onclick="alert('unsafe')">Safe heading</h2>
<script>alert('unsafe')</script>
<iframe src="https://example.com"></iframe>
<p onmouseover="alert('unsafe')">
    Safe paragraph
</p>
HTML;

    $cleanHtml = app(
        ContentSanitizer::class,
    )->sanitize($html);

    expect($cleanHtml)
        ->toContain('Safe heading')
        ->toContain('Safe paragraph')
        ->not->toContain('<script')
        ->not->toContain('<iframe')
        ->not->toContain('onclick')
        ->not->toContain('onmouseover');
});

test('javascript links are removed', function (): void {
    $html = <<<'HTML'
<p>
    <a href="javascript:alert('unsafe')">
        Unsafe link
    </a>
</p>
HTML;

    $cleanHtml = app(
        ContentSanitizer::class,
    )->sanitize($html);

    expect($cleanHtml)
        ->toContain('Unsafe link')
        ->not->toContain('javascript:');
});

test('page model sanitizes content before storage', function (): void {
    $page = Page::factory()->create([
        'excerpt' => '<strong>Plain description</strong>',

        'content' => '<h2>Allowed heading</h2>'
            .'<script>alert("unsafe")</script>',
    ]);

    $page->refresh();

    expect($page->excerpt)
        ->toBe('Plain description')
        ->and($page->content)
        ->toContain('<h2>Allowed heading</h2>')
        ->and($page->content)
        ->not->toContain('<script');
});

test('empty rich text markup is stored as null', function (): void {
    $page = Page::factory()->create([
        'content' => '<div><br></div>',
    ]);

    $page->refresh();

    expect($page->content)
        ->toBeNull();
});

test('encoded allowed html is decoded and safely sanitized', function (): void {
    $encodedHtml =
        '&lt;h2 onclick=&quot;alert(1)&quot;&gt;'
        .'About Our Unit'
        .'&lt;/h2&gt;'
        .'&lt;p&gt;'
        .'Official &lt;strong&gt;information&lt;/strong&gt;.'
        .'&lt;/p&gt;'
        .'&lt;script&gt;alert(&quot;unsafe&quot;)&lt;/script&gt;';

    $cleanHtml = app(
        ContentSanitizer::class,
    )->sanitize($encodedHtml);

    expect($cleanHtml)
        ->toContain('<h2>About Our Unit</h2>')
        ->toContain(
            '<strong>information</strong>',
        )
        ->not->toContain('onclick')
        ->not->toContain('<script')
        ->not->toContain('unsafe');
});
