<?php

use App\Enums\NewsEditorMode;
use App\Enums\NewsLocale;
use App\Models\NewsCategory;
use App\Models\User;
use App\Services\NewsArticleService;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('visual news content keeps approved word like formatting', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $category = NewsCategory::factory()->create();

    $news = app(NewsArticleService::class)->create(
        actor: $editor,
        category: $category,
        title: 'Visual News Editor Test',
        content: '<p style="text-align: center;"><span style="color: #ff0000; background-color: #ffff00;">Formatted visual content</span></p>',
        locale: NewsLocale::English,
        editorMode: NewsEditorMode::Visual,
    );

    expect($news->editor_mode)
        ->toBe(NewsEditorMode::Visual)
        ->and($news->content)
        ->toContain('text-align')
        ->toContain('color')
        ->toContain('background-color')
        ->toContain('Formatted visual content');
});

test('html tailwind news content keeps safe semantic markup and classes', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $category = NewsCategory::factory()->create();

    $news = app(NewsArticleService::class)->create(
        actor: $editor,
        category: $category,
        title: 'HTML Tailwind News Test',
        content: '<section class="bg-slate-900 px-6 py-20 text-white"><div class="mx-auto max-w-6xl"><h1 class="text-4xl font-bold">Tailwind News</h1></div></section><script>alert(1)</script>',
        locale: NewsLocale::English,
        editorMode: NewsEditorMode::Html,
    );

    expect($news->editor_mode)
        ->toBe(NewsEditorMode::Html)
        ->and($news->content)
        ->toContain('<section')
        ->toContain('bg-slate-900')
        ->toContain('text-4xl')
        ->not->toContain('<script')
        ->not->toContain('alert(1)');
});
