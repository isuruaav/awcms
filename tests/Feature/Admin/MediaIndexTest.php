<?php

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Livewire\Admin\Media\MediaIndex;
use App\Models\MediaAsset;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('media operator can open media library', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $this->actingAs($operator)
        ->get(
            route(
                'admin.media.index',
            ),
        )
        ->assertOk()
        ->assertSee('Media Library')
        ->assertSee('Upload Media')
        ->assertSee('Active Media')
        ->assertSee('Trash');
});

test('auditor can view media library but cannot see upload action', function (): void {
    $auditor = User::factory()->create();

    $auditor->assignRole(
        'Auditor',
    );

    $this->actingAs($auditor)
        ->get(
            route(
                'admin.media.index',
            ),
        )
        ->assertOk()
        ->assertSee('Media Library')
        ->assertDontSee('+ Upload Media');
});

test('media library displays active assets', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    MediaAsset::factory()->create([
        'title' => 'Training Photograph',
    ]);

    Livewire::actingAs($operator)
        ->test(MediaIndex::class)
        ->assertSee(
            'Training Photograph',
        );
});

test('media library search filters media assets', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    MediaAsset::factory()->create([
        'title' => 'Army Training Image',
    ]);

    MediaAsset::factory()->create([
        'title' => 'Commanders Conference',
    ]);

    Livewire::actingAs($operator)
        ->test(MediaIndex::class)
        ->set(
            'search',
            'Training',
        )
        ->assertSee(
            'Army Training Image',
        )
        ->assertDontSee(
            'Commanders Conference',
        );
});

test('media library filters by type', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    MediaAsset::factory()->create([
        'title' => 'Official Photograph',

        'type' => MediaType::Image->value,
    ]);

    MediaAsset::factory()
        ->document()
        ->create([
            'title' => 'Official PDF',
        ]);

    Livewire::actingAs($operator)
        ->test(MediaIndex::class)
        ->set(
            'typeFilter',
            MediaType::Document->value,
        )
        ->assertSee(
            'Official PDF',
        )
        ->assertDontSee(
            'Official Photograph',
        );
});

test('media library filters by visibility', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    MediaAsset::factory()->create([
        'title' => 'Public Photograph',

        'visibility' => MediaVisibility::Public->value,
    ]);

    MediaAsset::factory()->create([
        'title' => 'Restricted Photograph',

        'visibility' => MediaVisibility::Restricted->value,

        'disk' => 'local',
    ]);

    Livewire::actingAs($operator)
        ->test(MediaIndex::class)
        ->set(
            'visibilityFilter',
            MediaVisibility::Restricted->value,
        )
        ->assertSee(
            'Restricted Photograph',
        )
        ->assertDontSee(
            'Public Photograph',
        );
});

test('trash view only displays soft deleted media', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    MediaAsset::factory()->create([
        'title' => 'Active Media File',
    ]);

    $deleted = MediaAsset::factory()->create([
        'title' => 'Deleted Media File',
    ]);

    $deleted->delete();

    Livewire::actingAs($operator)
        ->test(MediaIndex::class)
        ->set(
            'view',
            'trash',
        )
        ->assertSee(
            'Deleted Media File',
        )
        ->assertDontSee(
            'Active Media File',
        );
});

test('media display can switch between grid and list', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    Livewire::actingAs($operator)
        ->test(MediaIndex::class)
        ->assertSet(
            'display',
            'grid',
        )
        ->call(
            'setDisplay',
            'list',
        )
        ->assertSet(
            'display',
            'list',
        )
        ->call(
            'setDisplay',
            'invalid',
        )
        ->assertSet(
            'display',
            'list',
        );
});

test('media filters can be cleared', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    Livewire::actingAs($operator)
        ->test(MediaIndex::class)
        ->set(
            'search',
            'training',
        )
        ->set(
            'typeFilter',
            MediaType::Image->value,
        )
        ->set(
            'visibilityFilter',
            MediaVisibility::Internal->value,
        )
        ->call(
            'clearFilters',
        )
        ->assertSet(
            'search',
            '',
        )
        ->assertSet(
            'typeFilter',
            '',
        )
        ->assertSet(
            'visibilityFilter',
            '',
        );
});
