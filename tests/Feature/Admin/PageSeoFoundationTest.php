<?php

use App\Enums\PageStatus;
use App\Models\Page;
use App\Services\PageRevisionService;
use App\Support\PageSeo;

test('page seo values use configured metadata', function (): void {
    $page = Page::factory()->create([
        'title' => 'Normal Page Title',

        'seo_title' => 'Custom SEO Page Title',

        'meta_description' => 'Custom search engine description.',

        'canonical_url' => 'https://example.com/custom-page',

        'robots_index' => false,

        'og_title' => 'Custom Open Graph Title',

        'og_description' => 'Custom social media description.',

        'og_image' => 'https://example.com/images/social.jpg',
    ]);

    expect(PageSeo::title($page))
        ->toBe('Custom SEO Page Title')
        ->and(PageSeo::description($page))
        ->toBe(
            'Custom search engine description.',
        )
        ->and(PageSeo::canonicalUrl($page))
        ->toBe(
            'https://example.com/custom-page',
        )
        ->and(PageSeo::robots($page))
        ->toBe('noindex,nofollow')
        ->and(
            PageSeo::openGraphTitle($page),
        )
        ->toBe(
            'Custom Open Graph Title',
        )
        ->and(
            PageSeo::openGraphDescription($page),
        )
        ->toBe(
            'Custom social media description.',
        )
        ->and(
            PageSeo::openGraphImage($page),
        )
        ->toBe(
            'https://example.com/images/social.jpg',
        );
});

test('page seo falls back to page information', function (): void {
    $page = Page::factory()
        ->published()
        ->create([
            'title' => 'Fallback SEO Title',

            'excerpt' => 'Fallback page description.',

            'seo_title' => null,
            'meta_description' => null,
            'canonical_url' => null,
            'og_title' => null,
            'og_description' => null,
            'og_image' => null,
            'robots_index' => true,
        ]);

    expect(PageSeo::title($page))
        ->toBe('Fallback SEO Title')
        ->and(PageSeo::description($page))
        ->toBe(
            'Fallback page description.',
        )
        ->and(PageSeo::robots($page))
        ->toBe('index,follow')
        ->and(
            PageSeo::openGraphTitle($page),
        )
        ->toBe('Fallback SEO Title')
        ->and(
            PageSeo::openGraphDescription($page),
        )
        ->toBe(
            'Fallback page description.',
        )
        ->and(
            PageSeo::canonicalUrl($page),
        )
        ->toBe(
            route(
                'pages.show',
                $page->slug,
            ),
        );
});

test('revision snapshot contains page seo metadata', function (): void {
    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,

        'seo_title' => 'Revision SEO Title',

        'meta_description' => 'Revision description.',

        'canonical_url' => 'https://example.com/revision',

        'robots_index' => false,

        'og_title' => 'Revision OG Title',

        'og_description' => 'Revision OG description.',

        'og_image' => 'https://example.com/revision.jpg',
    ]);

    $revision = app(
        PageRevisionService::class,
    )->capture(
        page: $page,
        actor: null,
        summary: 'SEO revision test.',
    );

    expect($revision->seo_title)
        ->toBe('Revision SEO Title')
        ->and($revision->meta_description)
        ->toBe('Revision description.')
        ->and($revision->canonical_url)
        ->toBe(
            'https://example.com/revision',
        )
        ->and($revision->robots_index)
        ->toBeFalse()
        ->and($revision->og_title)
        ->toBe('Revision OG Title')
        ->and($revision->og_description)
        ->toBe(
            'Revision OG description.',
        )
        ->and($revision->og_image)
        ->toBe(
            'https://example.com/revision.jpg',
        );
});
