<?php

use App\Services\PageBlockSanitizer;
use Illuminate\Validation\ValidationException;

test('page block sanitizer normalizes supported blocks', function (): void {
    $blocks = [
        [
            'id' => 'block_heading_01',
            'type' => 'heading',
            'data' => [
                'level' => 'h2',
                'text' => 'Welcome',
            ],
        ],

        [
            'id' => 'block_text_0001',
            'type' => 'text',
            'data' => [
                'content' => '<p>Hello <strong>World</strong></p>',
            ],
        ],

        [
            'id' => 'block_divider_01',
            'type' => 'divider',
            'data' => [],
        ],
    ];

    $normalized = app(
        PageBlockSanitizer::class,
    )->normalize(
        $blocks,
    );

    expect($normalized)
        ->toHaveCount(3)
        ->and($normalized[0]['type'])
        ->toBe('heading')
        ->and($normalized[0]['data']['level'])
        ->toBe('h2')
        ->and($normalized[0]['data']['text'])
        ->toBe('Welcome')
        ->and($normalized[1]['type'])
        ->toBe('text')
        ->and($normalized[1]['data']['content'])
        ->toContain('<strong>World</strong>')
        ->and($normalized[2]['data'])
        ->toBe([]);
});

test('page block rich text removes dangerous html', function (): void {
    $blocks = [
        [
            'id' => 'block_text_0001',
            'type' => 'text',
            'data' => [
                'content' => '<p>Safe text</p>'.
                    '<script>alert("unsafe-block")</script>'.
                    '<p onclick="alert(1)">More text</p>',
            ],
        ],
    ];

    $normalized = app(
        PageBlockSanitizer::class,
    )->normalize(
        $blocks,
    );

    $content = $normalized[0]['data']['content'];

    expect($content)
        ->toContain('Safe text')
        ->toContain('More text')
        ->not->toContain('<script')
        ->not->toContain('unsafe-block')
        ->not->toContain('onclick');
});

test('heading text is stored as plain text', function (): void {
    $blocks = [
        [
            'id' => 'block_heading_01',
            'type' => 'heading',
            'data' => [
                'level' => 'h3',
                'text' => '<strong>About Us</strong>',
            ],
        ],
    ];

    $normalized = app(
        PageBlockSanitizer::class,
    )->normalize(
        $blocks,
    );

    expect(
        $normalized[0]['data']['text'],
    )->toBe('About Us');
});

test('invalid heading level uses safe default', function (): void {
    $blocks = [
        [
            'id' => 'block_heading_01',
            'type' => 'heading',
            'data' => [
                'level' => 'h1',
                'text' => 'Heading',
            ],
        ],
    ];

    $normalized = app(
        PageBlockSanitizer::class,
    )->normalize(
        $blocks,
    );

    expect(
        $normalized[0]['data']['level'],
    )->toBe('h2');
});

test('javascript button url is rejected', function (): void {
    $blocks = [
        [
            'id' => 'block_button_01',
            'type' => 'button',
            'data' => [
                'label' => 'Click',
                'url' => 'javascript:alert(1)',
            ],
        ],
    ];

    expect(
        fn () => app(
            PageBlockSanitizer::class,
        )->normalize(
            $blocks,
        ),
    )->toThrow(
        ValidationException::class,
    );
});

test('button allows safe internal url', function (): void {
    $blocks = [
        [
            'id' => 'block_button_01',
            'type' => 'button',
            'data' => [
                'label' => 'Contact Us',
                'url' => '/contact',
                'target' => '_self',
                'style' => 'primary',
            ],
        ],
    ];

    $normalized = app(
        PageBlockSanitizer::class,
    )->normalize(
        $blocks,
    );

    expect(
        $normalized[0]['data']['url'],
    )->toBe('/contact');
});

test('protocol relative urls are rejected', function (): void {
    $blocks = [
        [
            'id' => 'block_image_0001',
            'type' => 'image',
            'data' => [
                'src' => '//attacker.example/image.jpg',
            ],
        ],
    ];

    expect(
        fn () => app(
            PageBlockSanitizer::class,
        )->normalize(
            $blocks,
        ),
    )->toThrow(
        ValidationException::class,
    );
});

test('unsupported block type is rejected', function (): void {
    $blocks = [
        [
            'id' => 'block_unknown_01',
            'type' => 'raw_html',
            'data' => [
                'content' => '<script>alert(1)</script>',
            ],
        ],
    ];

    expect(
        fn () => app(
            PageBlockSanitizer::class,
        )->normalize(
            $blocks,
        ),
    )->toThrow(
        ValidationException::class,
    );
});

test('two column block sanitizes both columns', function (): void {
    $blocks = [
        [
            'id' => 'block_columns_01',
            'type' => 'two_columns',
            'data' => [
                'ratio' => '40-60',

                'left' => '<p>Left column</p>',

                'right' => '<script>alert("unsafe")</script>'.
                    '<p>Right column</p>',
            ],
        ],
    ];

    $normalized = app(
        PageBlockSanitizer::class,
    )->normalize(
        $blocks,
    );

    expect(
        $normalized[0]['data']['ratio'],
    )->toBe('40-60')
        ->and(
            $normalized[0]['data']['left'],
        )
        ->toContain('Left column')
        ->and(
            $normalized[0]['data']['right'],
        )
        ->toContain('Right column')
        ->and(
            $normalized[0]['data']['right'],
        )
        ->not->toContain('unsafe');
});

test('spacer only accepts predefined sizes', function (): void {
    $blocks = [
        [
            'id' => 'block_spacer_01',
            'type' => 'spacer',
            'data' => [
                'size' => 'huge-custom-spacing',
            ],
        ],
    ];

    $normalized = app(
        PageBlockSanitizer::class,
    )->normalize(
        $blocks,
    );

    expect(
        $normalized[0]['data']['size'],
    )->toBe('medium');
});
