<?php

use App\Livewire\Admin\Users\UserCreate;
use App\Livewire\Admin\Users\UserEdit;
use App\Livewire\Admin\Users\UserIndex;
use App\Models\User;
use App\Support\UserManagementRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

test('a role assigner cannot assign custom permissions beyond their own', function (): void {
    $actor = User::factory()->create(['is_active' => true]);
    $actor->givePermissionTo(Permission::findOrCreate('users.assign-role', 'web'));
    $strong = Role::findOrCreate('Custom Settings Administrator', 'web');
    $strong->givePermissionTo(Permission::findOrCreate('settings.manage', 'web'));
    Role::findOrCreate('Limited Empty Role', 'web');

    $names = UserManagementRules::assignableRoleNames($actor);
    expect($names)->toContain('Limited Empty Role')->not->toContain('Custom Settings Administrator');
});

test('protected administrator roles cannot be assigned by a non super administrator', function (): void {
    Role::findOrCreate('Super Administrator', 'web');
    Role::findOrCreate('Site Administrator', 'web');
    $actor = User::factory()->create(['is_active' => true]);
    $actor->givePermissionTo(Permission::findOrCreate('users.assign-role', 'web'));

    expect(UserManagementRules::assignableRoleNames($actor))
        ->not->toContain('Super Administrator')
        ->not->toContain('Site Administrator');
});

test('direct target permissions are included in account management checks', function (): void {
    $actor = User::factory()->create(['is_active' => true]);
    $actor->givePermissionTo(Permission::findOrCreate('users.update', 'web'));
    $target = User::factory()->create(['is_active' => true]);
    $target->givePermissionTo(Permission::findOrCreate('settings.manage', 'web'));

    expect(UserManagementRules::canManageTarget($actor, $target))->toBeFalse();
});

test('profile editing works without role assignment permission', function (): void {
    $actor = User::factory()->create(['is_active' => true]);
    $actor->givePermissionTo(Permission::findOrCreate('users.update', 'web'));
    $target = User::factory()->create(['is_active' => true]);
    $target->assignRole(Role::findOrCreate('Profile Test Reader', 'web'));

    Livewire::actingAs($actor)->test(UserEdit::class, ['user' => $target])
        ->set('name', 'Updated Profile Name')
        ->call('save')
        ->assertHasNoErrors();

    expect($target->fresh()?->name)->toBe('Updated Profile Name');
    expect($target->fresh()?->hasRole('Profile Test Reader'))->toBeTrue();
});

test('profile editor cannot forge a role change', function (): void {
    $actor = User::factory()->create(['is_active' => true]);
    $actor->givePermissionTo(Permission::findOrCreate('users.update', 'web'));
    $target = User::factory()->create(['is_active' => true]);
    $target->assignRole(Role::findOrCreate('Original Profile Role', 'web'));
    Role::findOrCreate('Other Profile Role', 'web');

    Livewire::actingAs($actor)->test(UserEdit::class, ['user' => $target])
        ->set('role', 'Other Profile Role')
        ->call('save')
        ->assertForbidden();

    expect($target->fresh()?->hasRole('Original Profile Role'))->toBeTrue();
});

test('account manager cannot disable a stronger custom account', function (): void {
    $actor = User::factory()->create(['is_active' => true]);
    foreach (['users.view', 'users.update'] as $permission) {
        $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
    }
    $target = User::factory()->create(['is_active' => true]);
    $target->givePermissionTo(Permission::findOrCreate('settings.manage', 'web'));

    Livewire::actingAs($actor)->test(UserIndex::class)
        ->call('toggleActive', $target->id)
        ->assertForbidden();

    expect($target->fresh()?->is_active)->toBeTrue();
});

test('super administrator cannot disable their own account', function (): void {
    $actor = User::factory()->create(['is_active' => true]);
    $actor->assignRole(Role::findOrCreate('Super Administrator', 'web'));

    Livewire::actingAs($actor)->test(UserIndex::class)
        ->call('toggleActive', $actor->id)
        ->assertHasErrors(['user']);

    expect($actor->fresh()?->is_active)->toBeTrue();
});

test('create-only user is redirected to the user list instead of the forbidden edit page', function (): void {
    $actor = User::factory()->create(['is_active' => true]);
    foreach (['users.view', 'users.create', 'users.assign-role'] as $permission) {
        $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
    }
    Role::findOrCreate('New Limited Account', 'web');

    Livewire::actingAs($actor)->test(UserCreate::class)
        ->set('name', 'New Test Account')
        ->set('email', 'new-user-permission-test@example.test')
        ->set('role', 'New Limited Account')
        ->set('password', 'Test-Only-Password!729')
        ->set('password_confirmation', 'Test-Only-Password!729')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.users.index'));

    expect(User::query()->where('email', 'new-user-permission-test@example.test')->exists())->toBeTrue();
});
