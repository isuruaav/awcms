<?php

use App\Livewire\Admin\Users\UserIndex;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super administrator can visit the users list', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('Super Administrator');

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('User Management');
});

test('users without users view permission are forbidden', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $user->assignRole('Content Editor');

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('users can be searched by name', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('Super Administrator');

    User::factory()->create([
        'name' => 'Test Search Operator',
        'email' => 'search.operator@example.com',
    ]);

    User::factory()->create([
        'name' => 'Different Person',
        'email' => 'different@example.com',
    ]);

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->set('search', 'Test Search')
        ->assertSee('Test Search Operator')
        ->assertDontSee('Different Person');
});

test('super administrator can disable another user', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('Super Administrator');

    $target = User::factory()->create([
        'is_active' => true,
    ]);

    $target->assignRole('Content Editor');

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('toggleActive', $target->id)
        ->assertHasNoErrors();

    expect($target->fresh()->is_active)->toBeFalse();
});

test('administrator cannot disable their own account', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'is_active' => true,
    ]);

    $admin->assignRole('Super Administrator');

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('toggleActive', $admin->id)
        ->assertHasErrors('user');

    expect($admin->fresh()->is_active)->toBeTrue();
});

test('last active super administrator cannot be disabled', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'is_active' => true,
    ]);

    $admin->assignRole('Super Administrator');

    $secondAdmin = User::factory()->create([
        'email_verified_at' => now(),
        'is_active' => false,
    ]);

    $secondAdmin->assignRole('Super Administrator');

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('toggleActive', $admin->id)
        ->assertHasErrors('user');

    expect($admin->fresh()->is_active)->toBeTrue();
});
