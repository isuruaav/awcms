<?php

use App\Enums\PageStatus;
use App\Livewire\Admin\Pages\PageIndex;
use App\Models\AuditLog;
use App\Models\Page;
use App\Models\User;
use App\Services\PageWorkflowService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('content editor can submit a draft page', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,
    ]);

    Livewire::actingAs($editor)
        ->test(PageIndex::class)
        ->call('submitPage', $page->id)
        ->assertHasNoErrors();

    $page->refresh();

    expect($page->getRawOriginal('status'))
        ->toBe(PageStatus::Submitted->value)
        ->and($page->submitted_at)
        ->not->toBeNull();
});

test('publisher can approve a submitted page', function (): void {
    $publisher = User::factory()->create();
    $publisher->assignRole('Publisher');

    $page = Page::factory()
        ->submitted()
        ->create();

    Livewire::actingAs($publisher)
        ->test(PageIndex::class)
        ->call('approvePage', $page->id)
        ->assertHasNoErrors();

    $page->refresh();

    expect($page->getRawOriginal('status'))
        ->toBe(PageStatus::Approved->value)
        ->and($page->approved_by)
        ->toBe($publisher->id)
        ->and($page->approved_at)
        ->not->toBeNull();
});

test('publisher can publish an approved page', function (): void {
    $publisher = User::factory()->create();
    $publisher->assignRole('Publisher');

    $page = Page::factory()
        ->approved()
        ->create();

    Livewire::actingAs($publisher)
        ->test(PageIndex::class)
        ->call('publishPage', $page->id)
        ->assertHasNoErrors();

    $page->refresh();

    expect($page->getRawOriginal('status'))
        ->toBe(PageStatus::Published->value)
        ->and($page->published_at)
        ->not->toBeNull();
});

test('content editor cannot approve a submitted page', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()
        ->submitted()
        ->create();

    Livewire::actingAs($editor)
        ->test(PageIndex::class)
        ->call('approvePage', $page->id)
        ->assertForbidden();
});

test('submitted page cannot be published directly', function (): void {
    $publisher = User::factory()->create();
    $publisher->assignRole('Publisher');

    $page = Page::factory()
        ->submitted()
        ->create();

    expect(
        fn () => app(PageWorkflowService::class)
            ->publish($page, $publisher),
    )->toThrow(ValidationException::class);

    $page->refresh();

    expect($page->getRawOriginal('status'))
        ->toBe(PageStatus::Submitted->value);
});

test('published page can be archived', function (): void {
    $publisher = User::factory()->create();
    $publisher->assignRole('Publisher');

    $page = Page::factory()
        ->published()
        ->create();

    Livewire::actingAs($publisher)
        ->test(PageIndex::class)
        ->call('archivePage', $page->id)
        ->assertHasNoErrors();

    $page->refresh();

    expect($page->getRawOriginal('status'))
        ->toBe(PageStatus::Archived->value)
        ->and($page->archived_at)
        ->not->toBeNull();
});

test('workflow transitions create audit records', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,
    ]);

    app(PageWorkflowService::class)
        ->submit($page, $editor);

    $log = AuditLog::query()
        ->where('event', 'pages.submitted')
        ->latest('id')
        ->firstOrFail();

    expect($log->actor_id)
        ->toBe($editor->id)
        ->and($log->subject_id)
        ->toBe($page->id)
        ->and($log->old_values)
        ->toMatchArray([
            'status' => PageStatus::Draft->value,
        ])
        ->and($log->new_values)
        ->toMatchArray([
            'status' => PageStatus::Submitted->value,
        ]);
});
