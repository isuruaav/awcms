<?php

use App\Enums\PageStatus;
use App\Models\Page;

test('published page can be viewed using its public slug', function (): void {
    $page = Page::factory()
        ->published()
        ->create([
            'title' => 'About Our Unit',
            'slug' => 'about-our-unit',
            'excerpt' => 'Official information about our unit.',
            'content' => 'This is the official page content.',
        ]);

    $this->get(
        route('pages.show', $page->slug),
    )
        ->assertOk()
        ->assertSee('About Our Unit')
        ->assertSee('Official information about our unit.')
        ->assertSee('This is the official page content.');
});

test('draft page is not publicly accessible', function (): void {
    $page = Page::factory()->create([
        'slug' => 'draft-page',
        'status' => PageStatus::Draft->value,
    ]);

    $this->get(
        route('pages.show', $page->slug),
    )->assertNotFound();
});

test('submitted page is not publicly accessible', function (): void {
    $page = Page::factory()
        ->submitted()
        ->create([
            'slug' => 'submitted-page',
        ]);

    $this->get(
        route('pages.show', $page->slug),
    )->assertNotFound();
});

test('future scheduled publication is not publicly accessible', function (): void {
    $page = Page::factory()
        ->published()
        ->create([
            'slug' => 'future-page',
            'published_at' => now()->addDay(),
        ]);

    $this->get(
        route('pages.show', $page->slug),
    )->assertNotFound();
});

test('soft deleted published page is not publicly accessible', function (): void {
    $page = Page::factory()
        ->published()
        ->create([
            'slug' => 'deleted-public-page',
        ]);

    $page->delete();

    $this->get(
        route('pages.show', $page->slug),
    )->assertNotFound();
});

test('page content html is escaped on the public page', function (): void {
    $page = Page::factory()
        ->published()
        ->create([
            'slug' => 'safe-content-page',

            'content' => '<script>alert("unsafe")</script>'
                .'<h2>Heading</h2>',
        ]);

    $this->get(
        route('pages.show', $page->slug),
    )
        ->assertOk()
        ->assertSee(
            '&lt;script&gt;',
            false,
        )
        ->assertDontSee(
            '<script>',
            false,
        )
        ->assertDontSee(
            '<h2>',
            false,
        );
});
