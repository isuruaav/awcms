<?php

use App\Livewire\Admin\HeroSlides\HeroSlideIndex;
use App\Livewire\Admin\PastChiefInstructors\PastChiefInstructorIndex;
use App\Livewire\Admin\PastCommandants\PastCommandantIndex;
use App\Livewire\Admin\Roles\RolePermissionIndex;
use App\Livewire\Admin\SchoolLeaders\SchoolLeaderIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

foreach ([
    'hero-slides' => HeroSlideIndex::class,
    'school-leaders' => SchoolLeaderIndex::class,
    'past-commandants' => PastCommandantIndex::class,
    'past-chief-instructors' => PastChiefInstructorIndex::class,
] as $module => $component) {
    test($module.' view-only access hides the editor and rejects saving', function () use ($module, $component): void {
        $actor = User::factory()->create(['is_active' => true]);
        $actor->givePermissionTo(Permission::findOrCreate($module.'.view', 'web'));

        Livewire::actingAs($actor)
            ->test($component)
            ->assertOk()
            ->assertDontSeeHtml('wire:submit="save"')
            ->assertDontSeeHtml('wire:model="newImage"')
            ->call('save')
            ->assertForbidden();
    });
}

foreach ([
    'hero-slides' => HeroSlideIndex::class,
    'past-commandants' => PastCommandantIndex::class,
    'past-chief-instructors' => PastChiefInstructorIndex::class,
] as $module => $component) {
    test($module.' view-only access rejects direct delete calls', function () use ($module, $component): void {
        $actor = User::factory()->create(['is_active' => true]);
        $actor->givePermissionTo(Permission::findOrCreate($module.'.view', 'web'));

        // Authorization must reject before attempting to find this record.
        Livewire::actingAs($actor)
            ->test($component)
            ->call('delete', 999999)
            ->assertForbidden();
    });
}

test('hero creator without media upload cannot see the upload input', function (): void {
    $actor = User::factory()->create(['is_active' => true]);

    foreach (['hero-slides.view', 'hero-slides.create'] as $permission) {
        $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
    }

    Livewire::actingAs($actor)
        ->test(HeroSlideIndex::class)
        ->assertSeeHtml('wire:submit="save"')
        ->assertDontSeeHtml('wire:model="newImage"');
});

test('delegated roles manage permission allows inspection but not role creation', function (): void {
    $actor = User::factory()->create(['is_active' => true]);
    $actor->givePermissionTo(Permission::findOrCreate('roles.manage', 'web'));

    Livewire::actingAs($actor)
        ->test(RolePermissionIndex::class)
        ->assertSee('Read-only access.')
        ->assertDontSeeHtml('wire:click="createRole"')
        ->set('newRoleName', 'Unauthorised New Role')
        ->call('createRole')
        ->assertForbidden();

    expect(Role::query()->where('name', 'Unauthorised New Role')->exists())->toBeFalse();
});

test('delegated roles manage permission cannot change role grants', function (): void {
    $actor = User::factory()->create(['is_active' => true]);
    $actor->givePermissionTo(Permission::findOrCreate('roles.manage', 'web'));
    $target = Role::findOrCreate('Permission Review Target', 'web');

    Livewire::actingAs($actor)
        ->test(RolePermissionIndex::class)
        ->call('selectRole', (int) $target->getKey())
        ->set('selectedPermissions', ['roles.manage'])
        ->call('savePermissions')
        ->assertForbidden();

    expect($target->fresh()?->permissions->count())->toBe(0);
});

test('unknown permission values are rejected without removing existing role grants', function (): void {
    $actor = User::factory()->create(['is_active' => true]);
    $actor->assignRole(Role::findOrCreate('Super Administrator', 'web'));
    $access = Permission::findOrCreate('admin.access', 'web');
    Permission::findOrCreate('dashboard.view', 'web');
    $target = Role::findOrCreate('Permission Validation Target', 'web');
    $target->givePermissionTo($access);

    Livewire::actingAs($actor)
        ->test(RolePermissionIndex::class)
        ->call('selectRole', (int) $target->getKey())
        ->set('selectedPermissions', ['admin.access', 'not-a-registered-permission'])
        ->call('savePermissions')
        ->assertHasErrors(['selectedPermissions.1']);

    expect($target->fresh()?->permissions->pluck('name')->all())->toBe(['admin.access']);
});

test('module actions require the corresponding view permission', function (): void {
    $actor = User::factory()->create(['is_active' => true]);
    $actor->assignRole(Role::findOrCreate('Super Administrator', 'web'));

    foreach (['admin.access', 'hero-slides.view', 'hero-slides.update'] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $target = Role::findOrCreate('Permission Dependency Target', 'web');

    Livewire::actingAs($actor)
        ->test(RolePermissionIndex::class)
        ->call('selectRole', (int) $target->getKey())
        ->set('selectedPermissions', ['admin.access', 'hero-slides.update'])
        ->call('savePermissions')
        ->assertHasErrors(['selectedPermissions']);

    expect($target->fresh()?->permissions->count())->toBe(0);
});

test('super administrator can save a valid module permission set', function (): void {
    $actor = User::factory()->create(['is_active' => true]);
    $actor->assignRole(Role::findOrCreate('Super Administrator', 'web'));
    $permissions = ['admin.access', 'hero-slides.update', 'hero-slides.view'];

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $target = Role::findOrCreate('Valid Permission Target', 'web');

    Livewire::actingAs($actor)
        ->test(RolePermissionIndex::class)
        ->call('selectRole', (int) $target->getKey())
        ->set('selectedPermissions', $permissions)
        ->call('savePermissions')
        ->assertHasNoErrors();

    expect($target->fresh()?->permissions->pluck('name')->sort()->values()->all())
        ->toBe($permissions);
});
