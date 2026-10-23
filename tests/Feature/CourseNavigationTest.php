<?php

use App\Models\ThemeLayout;
use App\Services\CourseNavigation;

it('adds localized course links beside existing galleries without duplicating them', function (): void {
    $english = '<nav><a class="gallery" href="/galleries">Gallery</a><a class="mobile" href="/galleries"><span>Gallery</span></a></nav>';
    $updatedEnglish = app(CourseNavigation::class)->updateHeader($english, 'en');
    expect(substr_count($updatedEnglish, '[[site_url:/courses]]'))->toBe(2)
        ->and(app(CourseNavigation::class)->updateHeader($updatedEnglish, 'en'))->toBe($updatedEnglish);

    $sinhala = '<nav><a href="/si/galleries">ඡායාරූප ගැලරිය</a></nav>';
    $updatedSinhala = app(CourseNavigation::class)->updateHeader($sinhala, 'si');
    expect($updatedSinhala)->toContain('[[site_url:/si/courses]]')
        ->and($updatedSinhala)->toContain('පාඨමාලා');
});

it('updates editable headers and preserves their original revision when adding courses', function (): void {
    $original = '<a href="/galleries">Gallery</a>';
    $layout = ThemeLayout::query()->create([
        'theme_slug' => 'school-of-signals',
        'region' => 'header',
        'locale' => 'en',
        'status' => 'published',
        'published_html' => $original,
        'published_css' => '.nav{color:green}',
        'draft_html' => $original,
    ]);

    $migration = require database_path('migrations/2026_10_23_150400_add_courses_to_navigation.php');
    $migration->up();
    $layout->refresh();

    expect($layout->published_html)->toContain('[[site_url:/courses]]')
        ->and($layout->published_css)->toBe('.nav{color:green}')
        ->and($layout->revisions()->where('content_html', $original)->count())->toBe(2);

    $revision = $layout->revision_number;
    $migration->up();
    expect($layout->fresh()->revision_number)->toBe($revision);
});
