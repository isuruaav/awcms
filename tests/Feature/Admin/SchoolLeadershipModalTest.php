<?php

use App\Livewire\Admin\SchoolLeaders\SchoolLeaderIndex;
use App\Livewire\Admin\SchoolLeaders\SchoolLeadershipPositionIndex;
use App\Models\SchoolLeader;
use App\Models\SchoolLeadershipPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function leadershipModalActor(bool $canUpdate = true): User
{
    $actor = User::factory()->create(['is_active' => true]);
    $permissions = ['admin.access', 'school-leaders.view'];
    if ($canUpdate) {
        $permissions[] = 'school-leaders.update';
    }
    foreach ($permissions as $permission) {
        $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
    }

    return $actor;
}

function leadershipModalRecord(string $component): SchoolLeader|SchoolLeadershipPosition
{
    $position = SchoolLeadershipPosition::query()->where('key', 'commandant')->firstOrFail();
    if ($component === SchoolLeadershipPositionIndex::class) {
        return $position;
    }

    return SchoolLeader::query()->create([
        'position_id' => $position->id,
        'role_key' => $position->key,
        'title_en' => $position->name_en,
        'name_en' => 'Current Officer',
        'name_si' => 'වත්මන් නිලධාරියා',
        'rank' => 'colonel',
        'start_date' => '2025-06-10',
        'is_active' => true,
    ]);
}

foreach ([
    'leadership appointments' => [SchoolLeaderIndex::class, 'admin.school-leaders.index', 'school-leader-editor'],
    'appointment types' => [SchoolLeadershipPositionIndex::class, 'admin.school-leadership-positions.index', 'school-leadership-position-editor'],
] as $label => [$component, $route, $modal]) {
    test($label.' open in a modal and cancel without changing saved data or filters', function () use ($component): void {
        $record = leadershipModalRecord($component);
        $originalName = $record->name_en;
        $actor = leadershipModalActor();

        Livewire::actingAs($actor)->test($component)
            ->assertSet('showEditor', false)
            ->set('search', 'Officer')
            ->call('edit', $record->id)
            ->assertSet('showEditor', true)
            ->assertSet('nameEn', $originalName)
            ->set('nameEn', 'Unsaved replacement')
            ->call('closeEditor')
            ->assertSet('showEditor', false)
            ->assertSet('editingId', null)
            ->assertSet('nameEn', '')
            ->assertSet('search', 'Officer')
            ->call('startCreate')
            ->assertSet('showEditor', true)
            ->assertSet('editingId', null)
            ->call('closeEditor')
            ->assertSet('showEditor', false);

        expect($record->refresh()->name_en)->toBe($originalName);
    });

    test($label.' keep the modal open on validation errors and close after a successful save', function () use ($component): void {
        $record = leadershipModalRecord($component);
        Livewire::actingAs(leadershipModalActor())->test($component)
            ->call('edit', $record->id)
            ->set('nameEn', '')
            ->call('save')
            ->assertHasErrors('nameEn')
            ->assertSet('showEditor', true)
            ->assertSet('editingId', $record->id)
            ->set('nameEn', 'Updated leadership name')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showEditor', false)
            ->assertSet('editingId', null);

        expect($record->refresh()->name_en)->toBe('Updated leadership name');
    });

    test($label.' reject opening an editor for read-only users', function () use ($component, $modal): void {
        $actor = leadershipModalActor(false);
        Livewire::actingAs($actor)->test($component)
            ->assertDontSeeHtml('data-modal="'.$modal.'"')
            ->call('edit', 999999)->assertForbidden();

        Livewire::actingAs($actor)->test($component)
            ->call('startCreate')->assertForbidden();
    });

    test($label.' pages load their dialog and the Flux browser runtime', function () use ($component, $route, $modal): void {
        leadershipModalRecord($component);
        $this->actingAs(leadershipModalActor())->get(route($route))
            ->assertOk()
            ->assertSee('data-modal="'.$modal.'"', false)
            ->assertSee('wire:model.self="showEditor"', false)
            ->assertSee('/flux/flux.', false);
    });
}
