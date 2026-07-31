<?php

use App\Enums\PageStatus;
use App\Livewire\Admin\Pages\PageEdit;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\User;
use App\Services\PageRevisionService;
use App\Services\PageWorkflowService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('page revision snapshot can be created', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'title' => 'Initial Page Title',
        'slug' => 'initial-page-title',
        'excerpt' => 'Initial description.',
        'content' => '<p>Initial content.</p>',
        'status' => PageStatus::Draft->value,
    ]);

    $revision = app(
        PageRevisionService::class,
    )->capture(
        page: $page,
        actor: $editor,
        summary: 'Initial test snapshot.',
    );

    expect($revision)
        ->toBeInstanceOf(PageRevision::class)
        ->and($revision->page_id)
        ->toBe($page->id)
        ->and($revision->revision_number)
        ->toBe(1)
        ->and($revision->title)
        ->toBe('Initial Page Title')
        ->and($revision->slug)
        ->toBe('initial-page-title')
        ->and($revision->status)
        ->toBe(PageStatus::Draft)
        ->and($revision->created_by)
        ->toBe($editor->id);
});

test('identical page snapshots do not create duplicate revisions', function (): void {
    $editor = User::factory()->create();

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,
    ]);

    $firstRevision = app(
        PageRevisionService::class,
    )->capture(
        $page,
        $editor,
        'First snapshot.',
    );

    $secondRevision = app(
        PageRevisionService::class,
    )->capture(
        $page,
        $editor,
        'Duplicate snapshot.',
    );

    expect($secondRevision->id)
        ->toBe($firstRevision->id)
        ->and(
            PageRevision::query()
                ->where(
                    'page_id',
                    $page->id,
                )
                ->count(),
        )
        ->toBe(1);
});

test('editing a page creates a new revision', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'title' => 'Old Revision Title',
        'slug' => 'old-revision-title',
        'status' => PageStatus::Draft->value,
    ]);

    app(PageRevisionService::class)
        ->capture(
            $page,
            $editor,
            'Initial snapshot.',
        );

    Livewire::actingAs($editor)
        ->test(PageEdit::class, [
            'page' => $page,
        ])
        ->set(
            'title',
            'New Revision Title',
        )
        ->set(
            'slug',
            'new-revision-title',
        )
        ->call('save')
        ->assertHasNoErrors();

    $revisions = PageRevision::query()
        ->where(
            'page_id',
            $page->id,
        )
        ->orderBy(
            'revision_number',
        )
        ->get();

    expect($revisions)
        ->toHaveCount(2)
        ->and($revisions[0]->title)
        ->toBe('Old Revision Title')
        ->and($revisions[1]->title)
        ->toBe('New Revision Title');
});

test('workflow transition creates a new revision', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,
    ]);

    app(PageRevisionService::class)
        ->capture(
            $page,
            $editor,
            'Initial snapshot.',
        );

    app(PageWorkflowService::class)
        ->submit(
            $page,
            $editor,
        );

    $revisions = PageRevision::query()
        ->where(
            'page_id',
            $page->id,
        )
        ->orderBy(
            'revision_number',
        )
        ->get();

    expect($revisions)
        ->toHaveCount(2)
        ->and($revisions[0]->status)
        ->toBe(PageStatus::Draft)
        ->and($revisions[1]->status)
        ->toBe(PageStatus::Submitted);
});

test('revision stores sanitized page content', function (): void {
    $editor = User::factory()->create();

    $page = Page::factory()->create([
        'content' => '<h2>Safe heading</h2>'
            .'<script>alert("unsafe")</script>',
    ]);

    $revision = app(
        PageRevisionService::class,
    )->capture(
        $page,
        $editor,
        'Safe snapshot.',
    );

    expect($revision->content)
        ->toContain(
            '<h2>Safe heading</h2>',
        )
        ->not->toContain(
            '<script',
        )
        ->not->toContain(
            'alert("unsafe")',
        );
});
