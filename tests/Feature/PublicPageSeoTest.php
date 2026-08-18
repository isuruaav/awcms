<?php

use App\Models\Page;

test('published page renders configured seo metadata', function (): void {
    $page = Page::factory()
        ->published()
        ->create([
            'title' => 'Normal Page Title',

            'slug' => 'seo-public-page',

            'seo_title' => 'Custom Search Title',

            'meta_description' => 'Custom search description.',

            'canonical_url' => 'https://example.com/custom-canonical',

            'robots_index' => true,

            'og_title' => 'Social Sharing Title',

            'og_description' => 'Social sharing description.',

            'og_image' => 'https://example.com/share-image.jpg',
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
            '<title>Custom Search Title</title>',
            false,
        )

        ->assertSee(
            'name="description"',
            false,
        )

        ->assertSee(
            'content="Custom search description."',
            false,
        )

        ->assertSee(
            'content="index,follow"',
            false,
        )

        ->assertSee(
            'rel="canonical"',
            false,
        )

        ->assertSee(
            'href="https://example.com/custom-canonical"',
            false,
        )

        ->assertSee(
            'property="og:title"',
            false,
        )

        ->assertSee(
            'content="Social Sharing Title"',
            false,
        )

        ->assertSee(
            'property="og:image"',
            false,
        )

        ->assertSee(
            'content="https://example.com/share-image.jpg"',
            false,
        )

        ->assertSee(
            'name="twitter:card"',
            false,
        )

        ->assertSee(
            'content="summary_large_image"',
            false,
        );
});

test('page without custom seo metadata uses automatic fallbacks', function (): void {
    $page = Page::factory()
        ->published()
        ->create([
            'title' => 'Automatic SEO Page',

            'slug' => 'automatic-seo-page',

            'excerpt' => 'Automatic SEO description.',

            'seo_title' => null,

            'meta_description' => null,

            'canonical_url' => null,

            'robots_index' => true,

            'og_title' => null,

            'og_description' => null,

            'og_image' => null,
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
            '<title>Automatic SEO Page</title>',
            false,
        )

        ->assertSee(
            'content="Automatic SEO description."',
            false,
        )

        ->assertSee(
            route(
                'pages.show',
                $page->slug,
            ),
            false,
        )

        ->assertSee(
            'content="summary"',
            false,
        );
});

test('page can instruct search engines not to index it', function (): void {
    $page = Page::factory()
        ->published()
        ->create([
            'slug' => 'noindex-public-page',

            'robots_index' => false,
        ]);

    $this->get(
        route(
            'pages.show',
            $page->slug,
        ),
    )
        ->assertOk()
        ->assertSee(
            'content="noindex,nofollow"',
            false,
        );
});

test('seo metadata is escaped safely', function (): void {
    $page = Page::factory()
        ->published()
        ->create([
            'slug' => 'safe-seo-page',

            'seo_title' => '<script>alert("unsafe-title")</script>Safe Title',

            'meta_description' => '<strong>Safe description</strong>',
        ]);

    $response = $this->get(
        route(
            'pages.show',
            $page->slug,
        ),
    );

    $response
        ->assertOk()

        ->assertDontSee(
            'unsafe-title',
            false,
        )

        ->assertDontSee(
            '<strong>Safe description</strong>',
            false,
        )

        ->assertSee(
            'Safe description',
        );
});
