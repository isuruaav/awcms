<?php

use App\Enums\PageStatus;
use App\Livewire\Admin\Pages\PageRevisionHistory;
use App\Models\AuditLog;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\User;
use App\Services\PageRevisionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('content editor can view page revision history', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'title' => 'Revision Test Page',
        'status' => PageStatus::Draft->value,
    ]);

    app(PageRevisionService::class)
        ->capture(
            $page,
            $editor,
            'Initial snapshot.',
        );

    $this->actingAs($editor)
        ->get(
            route(
                'admin.pages.revisions',
                $page,
            ),
        )
        ->assertOk()
        ->assertSee('Revision History')
        ->assertSee('Revision Test Page')
        ->assertSee('Revision #1');
});

test('user without revision permission cannot view history', function (): void {
    $mediaOperator = User::factory()->create();
    $mediaOperator->assignRole('Media Operator');

    $page = Page::factory()->create();

    $this->actingAs($mediaOperator)
        ->get(
            route(
                'admin.pages.revisions',
                $page,
            ),
        )
        ->assertForbidden();
});

test('revision history displays multiple saved versions', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'title' => 'Version One',
        'status' => PageStatus::Draft->value,
    ]);

    app(PageRevisionService::class)
        ->capture(
            $page,
            $editor,
            'Version one.',
        );

    $page->forceFill([
        'title' => 'Version Two',
    ])->save();

    app(PageRevisionService::class)
        ->capture(
            $page,
            $editor,
            'Version two.',
        );

    Livewire::actingAs($editor)
        ->test(
            PageRevisionHistory::class,
            [
                'page' => $page,
            ],
        )
        ->assertSee('Revision #1')
        ->assertSee('Revision #2')
        ->assertSee('Version one.')
        ->assertSee('Version two.');
});

test('revision can be selected for comparison', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'title' => 'Original Title',
        'content' => '<p>Original content.</p>',
        'status' => PageStatus::Draft->value,
    ]);

    $revision = app(
        PageRevisionService::class,
    )->capture(
        $page,
        $editor,
        'Original version.',
    );

    $page->forceFill([
        'title' => 'Current Title',
        'content' => '<p>Current content.</p>',
    ])->save();

    Livewire::actingAs($editor)
        ->test(
            PageRevisionHistory::class,
            [
                'page' => $page,
            ],
        )
        ->call(
            'selectRevision',
            $revision->id,
        )
        ->assertSet(
            'selectedRevisionId',
            $revision->id,
        )
        ->assertSee('Original Title')
        ->assertSee('Current Title')
        ->assertSee('Original content.')
        ->assertSee('Current content.');
});

test('content editor can restore a revision when page is draft', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'title' => 'Original Title',
        'slug' => 'original-title',
        'content' => '<p>Original content.</p>',
        'status' => PageStatus::Draft->value,
    ]);

    $revision = app(
        PageRevisionService::class,
    )->capture(
        $page,
        $editor,
        'Original snapshot.',
    );

    $page->forceFill([
        'title' => 'Current Title',
        'slug' => 'current-title',
        'content' => '<p>Current content.</p>',
    ])->save();

    app(PageRevisionService::class)
        ->capture(
            $page,
            $editor,
            'Current snapshot.',
        );

    Livewire::actingAs($editor)
        ->test(
            PageRevisionHistory::class,
            [
                'page' => $page,
            ],
        )
        ->call(
            'restoreRevision',
            $revision->id,
        )
        ->assertHasNoErrors();

    $page->refresh();

    expect($page->title)
        ->toBe('Original Title')
        ->and($page->slug)
        ->toBe('original-title')
        ->and($page->content)
        ->toContain('Original content.');
});

test('restoring a revision creates a new revision', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'title' => 'Version One',
        'status' => PageStatus::Draft->value,
    ]);

    $revisionOne = app(
        PageRevisionService::class,
    )->capture(
        $page,
        $editor,
        'Version one.',
    );

    $page->forceFill([
        'title' => 'Version Two',
    ])->save();

    app(PageRevisionService::class)
        ->capture(
            $page,
            $editor,
            'Version two.',
        );

    Livewire::actingAs($editor)
        ->test(
            PageRevisionHistory::class,
            [
                'page' => $page,
            ],
        )
        ->call(
            'restoreRevision',
            $revisionOne->id,
        )
        ->assertHasNoErrors();

    $latestRevision = PageRevision::query()
        ->where(
            'page_id',
            $page->id,
        )
        ->orderByDesc(
            'revision_number',
        )
        ->firstOrFail();

    expect($latestRevision->revision_number)
        ->toBe(3)
        ->and(
            $latestRevision
                ->restored_from_revision_number,
        )
        ->toBe(1)
        ->and($latestRevision->title)
        ->toBe('Version One');
});

test('published page cannot restore a revision directly', function (): void {
    $publisher = User::factory()->create();
    $publisher->assignRole('Publisher');

    $page = Page::factory()
        ->published()
        ->create([
            'title' => 'Published Page',
        ]);

    $revision = app(
        PageRevisionService::class,
    )->capture(
        $page,
        $publisher,
        'Published snapshot.',
    );

    Livewire::actingAs($publisher)
        ->test(
            PageRevisionHistory::class,
            [
                'page' => $page,
            ],
        )
        ->call(
            'restoreRevision',
            $revision->id,
        )
        ->assertHasErrors([
            'revision',
        ]);

    $page->refresh();

    expect($page->status)
        ->toBe(PageStatus::Published);
});

test('revision restoration is written to audit log', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    $page = Page::factory()->create([
        'title' => 'Old Version',
        'status' => PageStatus::Draft->value,
    ]);

    $revision = app(
        PageRevisionService::class,
    )->capture(
        $page,
        $administrator,
        'Old version.',
    );

    $page->forceFill([
        'title' => 'New Version',
    ])->save();

    app(PageRevisionService::class)
        ->capture(
            $page,
            $administrator,
            'New version.',
        );

    Livewire::actingAs($administrator)
        ->test(
            PageRevisionHistory::class,
            [
                'page' => $page,
            ],
        )
        ->call(
            'restoreRevision',
            $revision->id,
        )
        ->assertHasNoErrors();

    $log = AuditLog::query()
        ->where(
            'event',
            'pages.revision-restored',
        )
        ->latest('id')
        ->firstOrFail();

    expect($log->actor_id)
        ->toBe($administrator->id)
        ->and($log->subject_id)
        ->toBe($page->id)
        ->and($log->new_values)
        ->toMatchArray([
            'restored_from_revision' => 1,
        ]);
});
