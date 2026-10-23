<?php

use App\Models\ThemeLayout;
use App\Services\PublicationNavigation;

it('connects existing desktop and mobile Publication links while preserving Gallery links', function (): void {
    $html = '<nav><a class="desktop" href="[[site_url:/galleries]]">Publication</a><a href="/galleries">Gallery</a></nav><nav><a class="mobile" href="#"><span>Publication</span></a><a href="/galleries">Gallery</a></nav>';
    $updated = app(PublicationNavigation::class)->updateHeader($html, 'en');
    expect(substr_count($updated, '[[site_url:/publications]]'))->toBe(2)
        ->and(substr_count($updated, 'href="/galleries"'))->toBe(2)
        ->and(app(PublicationNavigation::class)->updateHeader($updated, 'en'))->toBe($updated);
});

it('adds Sinhala publication links beside desktop and mobile galleries without duplicates', function (): void {
    $html = '<a class="nav" href="[[site_url:/si/galleries]]">ඡායාරූප</a><a class="mobile" href="[[site_url:/si/galleries]]"><span>ඡායාරූප</span></a>';
    $updated = app(PublicationNavigation::class)->updateHeader($html, 'si');
    expect(substr_count($updated, '[[site_url:/si/publications]]'))->toBe(2)
        ->and(app(PublicationNavigation::class)->updateHeader($updated, 'si'))->toBe($updated);
});

it('keeps original header revisions when the navigation migration connects publications', function (): void {
    $original = '<a href="[[site_url:/galleries]]">Publication</a><a href="[[site_url:/galleries]]">Gallery</a>';
    $layout = ThemeLayout::query()->create(['theme_slug' => 'school-of-signals', 'region' => 'header', 'locale' => 'en', 'status' => 'published', 'published_html' => $original, 'published_css' => '.nav{color:green}', 'draft_html' => $original]);
    $migration = require database_path('migrations/2026_10_23_150100_connect_publication_navigation.php');
    $migration->up();
    $layout->refresh();
    expect($layout->published_html)->toContain('[[site_url:/publications]]')->and($layout->published_css)->toBe('.nav{color:green}');
    expect($layout->revisions()->where('content_html', $original)->count())->toBe(2);
    $revision = $layout->revision_number;
    $migration->up();
    expect($layout->fresh()->revision_number)->toBe($revision);
});
