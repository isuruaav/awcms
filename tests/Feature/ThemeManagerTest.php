<?php

use App\Services\ThemeManager;

test('theme manager discovers the school of signals manifest', function (): void {
    $theme = app(ThemeManager::class)->find('school-of-signals');

    expect($theme)
        ->not->toBeNull()
        ->and($theme['name'])->toBe('School of Signals')
        ->and($theme['slug'])->toBe('school-of-signals')
        ->and($theme['supports'])->toContain('pages', 'news', 'multilingual');
});

test('theme manager rejects an unsafe theme slug', function (): void {
    expect(app(ThemeManager::class)->find('../school-of-signals'))
        ->toBeNull();
});
