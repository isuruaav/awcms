<?php

use App\Enums\PageStatus;
use App\Livewire\Admin\Pages\PageIndex;
use App\Models\AuditLog;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('site administrator can move a draft page to trash', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,
    ]);

    Livewire::actingAs($administrator)
        ->test(PageIndex::class)
        ->call('deletePage', $page->id)
        ->assertHasNoErrors();

    expect(Page::query()->find($page->id))
        ->toBeNull();

    $deletedPage = Page::withTrashed()
        ->findOrFail($page->id);

    expect($deletedPage->trashed())
        ->toBeTrue();
});

test('content editor cannot delete a page', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,
    ]);

    Livewire::actingAs($editor)
        ->test(PageIndex::class)
        ->call('deletePage', $page->id)
        ->assertForbidden();

    expect(Page::query()->find($page->id))
        ->not->toBeNull();
});

test('published page cannot be directly deleted', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    $page = Page::factory()
        ->published()
        ->create();

    Livewire::actingAs($administrator)
        ->test(PageIndex::class)
        ->call('deletePage', $page->id)
        ->assertHasErrors([
            'workflow',
        ]);

    expect(Page::query()->find($page->id))
        ->not->toBeNull();
});

test('trashed pages can be displayed using the trash filter', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    Page::factory()->create([
        'title' => 'Active Website Page',
    ]);

    $deletedPage = Page::factory()->create([
        'title' => 'Deleted Website Page',
        'status' => PageStatus::Draft->value,
    ]);

    $deletedPage->delete();

    Livewire::actingAs($administrator)
        ->test(PageIndex::class)
        ->set('recordState', 'trashed')
        ->assertSee('Deleted Website Page')
        ->assertDontSee('Active Website Page');
});

test('site administrator can restore a trashed page', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,
    ]);

    $page->delete();

    Livewire::actingAs($administrator)
        ->test(PageIndex::class)
        ->set('recordState', 'trashed')
        ->call('restorePage', $page->id)
        ->assertHasNoErrors();

    $restoredPage = Page::query()
        ->findOrFail($page->id);

    expect($restoredPage->trashed())
        ->toBeFalse();
});

test('page delete and restore actions create audit records', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,
    ]);

    Livewire::actingAs($administrator)
        ->test(PageIndex::class)
        ->call('deletePage', $page->id)
        ->set('recordState', 'trashed')
        ->call('restorePage', $page->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'pages.deleted',
        'actor_id' => $administrator->id,
        'subject_id' => $page->id,
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'pages.restored',
        'actor_id' => $administrator->id,
        'subject_id' => $page->id,
    ]);

    $events = AuditLog::query()
        ->where('subject_id', $page->id)
        ->whereIn('event', [
            'pages.deleted',
            'pages.restored',
        ])
        ->pluck('event');

    expect($events)
        ->toContain('pages.deleted')
        ->toContain('pages.restored');
});
