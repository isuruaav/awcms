<?php

use App\Enums\PageEditorMode;
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

test('new pages default to the visual editor', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->assertSet(
            'editorMode',
            PageEditorMode::Visual->value,
        )
        ->set('title', 'Visual Editor Page')
        ->set(
            'content',
            '<h2>Welcome</h2><p><strong>Visual content</strong></p>',
        )
        ->call('save')
        ->assertHasNoErrors();

    $page = Page::query()
        ->where('slug', 'visual-editor-page')
        ->firstOrFail();

    expect($page->editor_mode)
        ->toBe(PageEditorMode::Visual)
        ->and($page->content)
        ->toContain('<h2>Welcome</h2>');

    $this->assertDatabaseHas('page_revisions', [
        'page_id' => $page->id,
        'editor_mode' => PageEditorMode::Visual->value,
    ]);
});

test('developer can switch from visual to html and tailwind mode', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->call(
            'setEditorMode',
            PageEditorMode::Html->value,
        )
        ->assertSet(
            'editorMode',
            PageEditorMode::Html->value,
        )
        ->set('title', 'Tailwind Layout Page')
        ->set(
            'content',
            '<section class="bg-slate-900 px-6 py-20 text-white"><h1 class="text-4xl font-bold">Hello</h1><script>alert(1)</script></section>',
        )
        ->call('save')
        ->assertHasNoErrors();

    $page = Page::query()
        ->where('slug', 'tailwind-layout-page')
        ->firstOrFail();

    expect($page->editor_mode)
        ->toBe(PageEditorMode::Html)
        ->and($page->content)
        ->toContain('bg-slate-900')
        ->toContain('text-4xl')
        ->not->toContain('<script>');
});

test('editor mode can change while page content exists without duplicating content', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->set(
            'content',
            '<p>Shared canonical content.</p>',
        )
        ->call(
            'setEditorMode',
            PageEditorMode::Html->value,
        )
        ->assertSet(
            'editorMode',
            PageEditorMode::Html->value,
        )
        ->assertSet(
            'content',
            '<p>Shared canonical content.</p>',
        )
        ->assertHasNoErrors();
});

test('existing html page can choose visual mode while preserving the same source', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,
        'editor_mode' => PageEditorMode::Html->value,
        'content' => '<section class="grid md:grid-cols-3">Advanced layout</section>',
    ]);

    Livewire::actingAs($editor)
        ->test(PageEdit::class, [
            'page' => $page,
        ])
        ->assertSet(
            'editorMode',
            PageEditorMode::Html->value,
        )
        ->call(
            'setEditorMode',
            PageEditorMode::Visual->value,
        )
        ->assertSet(
            'editorMode',
            PageEditorMode::Visual->value,
        )
        ->assertSet(
            'content',
            '<section class="grid md:grid-cols-3">Advanced layout</section>',
        )
        ->assertHasNoErrors();
});

test('visual and html pages use different public content wrappers', function (): void {
    $visualPage = Page::factory()
        ->published()
        ->create([
            'slug' => 'visual-public-page',
            'editor_mode' => PageEditorMode::Visual->value,
            'content' => '<h2>Visual public content</h2>',
        ]);

    $htmlPage = Page::factory()
        ->published()
        ->create([
            'slug' => 'html-public-page',
            'editor_mode' => PageEditorMode::Html->value,
            'content' => '<section class="bg-slate-900">HTML public content</section>',
        ]);

    $this->get(
        route('pages.show', $visualPage->slug),
    )
        ->assertOk()
        ->assertSee(
            'class="awcms-content"',
            false,
        );

    $this->get(
        route('pages.show', $htmlPage->slug),
    )
        ->assertOk()
        ->assertSee(
            'class="page-html-content"',
            false,
        );
});
