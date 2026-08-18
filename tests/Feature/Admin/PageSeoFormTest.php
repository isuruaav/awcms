<?php

use App\Enums\PageStatus;
use App\Livewire\Admin\Pages\PageCreate;
use App\Livewire\Admin\Pages\PageEdit;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('page create stores seo metadata', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->set('title', 'SEO Test Page')
        ->set('slug', 'seo-test-page')
        ->set('seoTitle', 'SEO Search Title')
        ->set(
            'metaDescription',
            'SEO search engine description.',
        )
        ->set(
            'canonicalUrl',
            'https://example.com/seo-test-page',
        )
        ->set('robotsIndex', false)
        ->set(
            'ogTitle',
            'Social Sharing Title',
        )
        ->set(
            'ogDescription',
            'Social sharing description.',
        )
        ->set(
            'ogImage',
            'https://example.com/share.jpg',
        )
        ->call('save')
        ->assertHasNoErrors();

    $page = Page::query()
        ->where(
            'slug',
            'seo-test-page',
        )
        ->firstOrFail();

    expect($page->seo_title)
        ->toBe('SEO Search Title')
        ->and($page->meta_description)
        ->toBe(
            'SEO search engine description.',
        )
        ->and($page->canonical_url)
        ->toBe(
            'https://example.com/seo-test-page',
        )
        ->and($page->robots_index)
        ->toBeFalse()
        ->and($page->og_title)
        ->toBe('Social Sharing Title')
        ->and($page->og_description)
        ->toBe(
            'Social sharing description.',
        )
        ->and($page->og_image)
        ->toBe(
            'https://example.com/share.jpg',
        );
});

test('page edit updates seo metadata', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,

        'seo_title' => 'Old SEO Title',

        'robots_index' => true,
    ]);

    Livewire::actingAs($editor)
        ->test(
            PageEdit::class,
            [
                'page' => $page,
            ],
        )
        ->set(
            'seoTitle',
            'Updated SEO Title',
        )
        ->set(
            'metaDescription',
            'Updated meta description.',
        )
        ->set(
            'robotsIndex',
            false,
        )
        ->set(
            'ogTitle',
            'Updated OG Title',
        )
        ->call('save')
        ->assertHasNoErrors();

    $page->refresh();

    expect($page->seo_title)
        ->toBe('Updated SEO Title')
        ->and($page->meta_description)
        ->toBe(
            'Updated meta description.',
        )
        ->and($page->robots_index)
        ->toBeFalse()
        ->and($page->og_title)
        ->toBe('Updated OG Title');
});

test('seo text fields are converted to plain text', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->set('title', 'Plain SEO')
        ->set(
            'seoTitle',
            '<strong>Safe SEO Title</strong>',
        )
        ->set(
            'metaDescription',
            '<script>alert(1)</script>Safe description',
        )
        ->call('save')
        ->assertHasNoErrors();

    $page = Page::query()
        ->where('slug', 'plain-seo')
        ->firstOrFail();

    expect($page->seo_title)
        ->toBe('Safe SEO Title')
        ->and($page->meta_description)
        ->not->toContain('<script>')
        ->not->toContain('<strong>');
});

test('invalid canonical url is rejected', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->set('title', 'Invalid Canonical')
        ->set(
            'canonicalUrl',
            'javascript:alert(1)',
        )
        ->call('save')
        ->assertHasErrors([
            'canonicalUrl',
        ]);
});

test('invalid open graph image url is rejected', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->set('title', 'Invalid OG Image')
        ->set(
            'ogImage',
            'data:text/html,unsafe',
        )
        ->call('save')
        ->assertHasErrors([
            'ogImage',
        ]);
});
