<?php

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

test('published page renders supported page builder blocks', function (): void {
    $page = Page::factory()
        ->published()
        ->create([
            'title' => 'Builder Public Page',

            'slug' => 'builder-public-page',

            'blocks' => [
                [
                    'id' => 'heading_block_01',

                    'type' => 'heading',

                    'data' => [
                        'level' => 'h2',

                        'text' => 'Training Programmes',
                    ],
                ],

                [
                    'id' => 'text_block_0001',

                    'type' => 'text',

                    'data' => [
                        'content' => '<p>Official <strong>training information</strong>.</p>',
                    ],
                ],

                [
                    'id' => 'image_block_001',

                    'type' => 'image',

                    'data' => [
                        'src' => 'https://example.com/training.jpg',

                        'alt' => 'Training programme',

                        'caption' => 'Official training activity',

                        'alignment' => 'center',
                    ],
                ],

                [
                    'id' => 'button_block_01',

                    'type' => 'button',

                    'data' => [
                        'label' => 'Contact Us',

                        'url' => '/contact',

                        'target' => '_self',

                        'style' => 'primary',
                    ],
                ],

                [
                    'id' => 'columns_block_1',

                    'type' => 'two_columns',

                    'data' => [
                        'ratio' => '40-60',

                        'left' => '<p>Left column information.</p>',

                        'right' => '<p>Right column information.</p>',
                    ],
                ],

                [
                    'id' => 'callout_block_1',

                    'type' => 'callout',

                    'data' => [
                        'title' => 'Important Notice',

                        'content' => '<p>Please read this notice.</p>',

                        'style' => 'info',
                    ],
                ],

                [
                    'id' => 'divider_block_1',

                    'type' => 'divider',

                    'data' => [],
                ],

                [
                    'id' => 'spacer_block_01',

                    'type' => 'spacer',

                    'data' => [
                        'size' => 'medium',
                    ],
                ],
            ],
        ]);

    $this->get(
        route(
            'pages.show',
            $page->slug,
        ),
    )
        ->assertOk()
        ->assertSee(
            'Training Programmes',
        )
        ->assertSee(
            'training information',
        )
        ->assertSee(
            'Training programme',
        )
        ->assertSee(
            'Official training activity',
        )
        ->assertSee(
            'Contact Us',
        )
        ->assertSee(
            'href="/contact"',
            false,
        )
        ->assertSee(
            'Left column information.',
        )
        ->assertSee(
            'Right column information.',
        )
        ->assertSee(
            'Important Notice',
        )
        ->assertSee(
            'Please read this notice.',
        );
});

test('page builder rich text is sanitized before public rendering', function (): void {
    $page = Page::factory()
        ->published()
        ->create([
            'slug' => 'secure-builder-rendering',

            'blocks' => [
                [
                    'id' => 'secure_text_0001',

                    'type' => 'text',

                    'data' => [
                        'content' => '<p>Safe builder content.</p>'.
                            '<script>'.
                            'alert("unsafe-builder-script")'.
                            '</script>'.
                            '<p onclick="alert(1)">'.
                            'More safe content.'.
                            '</p>',
                    ],
                ],
            ],
        ]);

    $response = $this->get(
        route(
            'pages.show',
            $page->slug,
        ),
    );

    $response
        ->assertOk()
        ->assertSee(
            'Safe builder content.',
        )
        ->assertSee(
            'More safe content.',
        )
        ->assertDontSee(
            'unsafe-builder-script',
            false,
        )
        ->assertDontSee(
            'onclick=',
            false,
        );
});

test('invalid unsafe blocks are ignored during public rendering', function (): void {
    $page = Page::factory()
        ->published()
        ->create([
            'slug' => 'invalid-builder-block',

            'blocks' => [
                [
                    'id' => 'safe_heading_01',

                    'type' => 'heading',

                    'data' => [
                        'level' => 'h2',

                        'text' => 'Safe Heading',
                    ],
                ],

                [
                    'id' => 'unsafe_button_1',

                    'type' => 'button',

                    'data' => [
                        'label' => 'Unsafe Link',

                        'url' => 'javascript:alert(1)',

                        'target' => '_self',

                        'style' => 'primary',
                    ],
                ],

                [
                    'id' => 'unsupported_01',

                    'type' => 'raw_html',

                    'data' => [
                        'content' => '<script>alert(1)</script>',
                    ],
                ],
            ],
        ]);

    $response = $this->get(
        route(
            'pages.show',
            $page->slug,
        ),
    );

    $response
        ->assertOk()
        ->assertSee(
            'Safe Heading',
        )
        ->assertDontSee(
            'Unsafe Link',
        )
        ->assertDontSee(
            'javascript:',
            false,
        )
        ->assertDontSee(
            'raw_html',
            false,
        );
});

test('admin preview renders draft page builder blocks', function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );

    $editor = User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,

        'blocks' => [
            [
                'id' => 'preview_heading_1',

                'type' => 'heading',

                'data' => [
                    'level' => 'h2',

                    'text' => 'Draft Preview Heading',
                ],
            ],

            [
                'id' => 'preview_text_001',

                'type' => 'text',

                'data' => [
                    'content' => '<p>Draft preview block content.</p>',
                ],
            ],
        ],
    ]);

    $this->actingAs(
        $editor,
    )
        ->get(
            route(
                'admin.pages.preview',
                $page,
            ),
        )
        ->assertOk()
        ->assertSee(
            'Administrator Preview',
        )
        ->assertSee(
            'Draft Preview Heading',
        )
        ->assertSee(
            'Draft preview block content.',
        )
        ->assertSee(
            'content="noindex,nofollow"',
            false,
        );
});

test('page without builder blocks still renders main content', function (): void {
    $page = Page::factory()
        ->published()
        ->create([
            'slug' => 'legacy-main-content',

            'content' => '<p>Existing main page content.</p>',

            'blocks' => null,
        ]);

    $this->get(
        route(
            'pages.show',
            $page->slug,
        ),
    )
        ->assertOk()
        ->assertSee(
            'Existing main page content.',
        );
});
