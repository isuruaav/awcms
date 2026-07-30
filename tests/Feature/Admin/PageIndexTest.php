<?php

use App\Enums\PageStatus;
use App\Livewire\Admin\Pages\PageIndex;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('content editor can access pages index', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $this->actingAs($editor)
        ->get(route('admin.pages.index'))
        ->assertOk()
        ->assertSee('Pages');
});

test('user without pages view permission is forbidden', function (): void {
    $mediaOperator = User::factory()->create();
    $mediaOperator->assignRole('Media Operator');

    $this->actingAs($mediaOperator)
        ->get(route('admin.pages.index'))
        ->assertForbidden();
});

test('pages can be searched by title', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    Page::factory()->create([
        'title' => 'Regimental History',
        'slug' => 'regimental-history',
    ]);

    Page::factory()->create([
        'title' => 'Contact Information',
        'slug' => 'contact-information',
    ]);

    Livewire::actingAs($editor)
        ->test(PageIndex::class)
        ->set('search', 'Regimental')
        ->assertSee('Regimental History')
        ->assertDontSee('Contact Information');
});

test('pages can be filtered by workflow status', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    Page::factory()->create([
        'title' => 'Draft Page',
        'status' => PageStatus::Draft->value,
    ]);

    Page::factory()
        ->published()
        ->create([
            'title' => 'Published Page',
        ]);

    Livewire::actingAs($editor)
        ->test(PageIndex::class)
        ->set(
            'status',
            PageStatus::Published->value,
        )
        ->assertSee('Published Page')
        ->assertDontSee('Draft Page');
});

test('invalid page filters are normalised', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    Livewire::actingAs($editor)
        ->test(PageIndex::class)
        ->set('status', 'invalid-status')
        ->set('perPage', 999)
        ->set('sortField', 'password')
        ->call('resetFilters')
        ->assertSet('status', 'all')
        ->assertSet('perPage', 15)
        ->assertSet('sortField', 'updated_at')
        ->assertSet('sortDirection', 'desc');
});
