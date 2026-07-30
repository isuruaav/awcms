<?php

use App\Livewire\Admin\Users\UserCreate;
use App\Livewire\Admin\Users\UserEdit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super administrator can create a user', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'password' => 'AdminPassword123!',
    ]);

    $admin->assignRole('Super Administrator');

    Livewire::actingAs($admin)
        ->test(UserCreate::class)
        ->set('name', 'New Content Editor')
        ->set('email', 'editor@example.com')
        ->set('role', 'Content Editor')
        ->set('isActive', true)
        ->set('password', 'TemporaryPassword123!')
        ->set(
            'password_confirmation',
            'TemporaryPassword123!',
        )
        ->call('save')
        ->assertHasNoErrors();

    $user = User::query()
        ->where('email', 'editor@example.com')
        ->firstOrFail();

    expect($user->name)->toBe('New Content Editor')
        ->and($user->is_active)->toBeTrue()
        ->and($user->created_by)->toBe($admin->id)
        ->and($user->hasRole('Content Editor'))->toBeTrue()
        ->and(Hash::check(
            'TemporaryPassword123!',
            $user->password,
        ))->toBeTrue();
});

test('site administrator cannot assign a protected role', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('Site Administrator');

    Livewire::actingAs($admin)
        ->test(UserCreate::class)
        ->set('name', 'Protected Role Attempt')
        ->set('email', 'protected@example.com')
        ->set('role', 'Super Administrator')
        ->set('password', 'TemporaryPassword123!')
        ->set(
            'password_confirmation',
            'TemporaryPassword123!',
        )
        ->call('save')
        ->assertHasErrors('role');

    $this->assertDatabaseMissing('users', [
        'email' => 'protected@example.com',
    ]);
});

test('super administrator can update user and role', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('Super Administrator');

    $target = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $target->assignRole('Content Editor');

    Livewire::actingAs($admin)
        ->test(UserEdit::class, ['user' => $target])
        ->set('name', 'Updated Publisher')
        ->set('email', 'publisher@example.com')
        ->set('role', 'Publisher')
        ->set('isActive', true)
        ->call('save')
        ->assertHasNoErrors();

    $target->refresh();

    expect($target->name)->toBe('Updated Publisher')
        ->and($target->email)->toBe('publisher@example.com')
        ->and($target->updated_by)->toBe($admin->id)
        ->and($target->hasRole('Publisher'))->toBeTrue()
        ->and($target->hasRole('Content Editor'))->toBeFalse();
});

test('super administrator cannot remove their own role', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('Super Administrator');

    Livewire::actingAs($admin)
        ->test(UserEdit::class, ['user' => $admin])
        ->set('role', 'Content Editor')
        ->call('save')
        ->assertHasErrors('role');

    expect(
        $admin->fresh()->hasRole('Super Administrator'),
    )->toBeTrue();
});

test('administrator can securely reset another user password', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'password' => 'AdminPassword123!',
    ]);

    $admin->assignRole('Super Administrator');

    $target = User::factory()->create([
        'email_verified_at' => now(),
        'password' => 'OldPassword123!',
        'remember_token' => 'old-remember-token',
    ]);

    $target->assignRole('Content Editor');

    DB::table('sessions')->insert([
        'id' => 'target-user-session',
        'user_id' => $target->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest Test',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);

    Livewire::actingAs($admin)
        ->test(UserEdit::class, ['user' => $target])
        ->set(
            'administrator_password',
            'AdminPassword123!',
        )
        ->set(
            'new_password',
            'NewSecurePassword123!',
        )
        ->set(
            'new_password_confirmation',
            'NewSecurePassword123!',
        )
        ->call('resetPassword')
        ->assertHasNoErrors();

    $target->refresh();

    expect(
        Hash::check(
            'NewSecurePassword123!',
            $target->password,
        ),
    )->toBeTrue()
        ->and($target->remember_token)
        ->not->toBe('old-remember-token');

    expect(
        DB::table('sessions')
            ->where('user_id', $target->id)
            ->count(),
    )->toBe(0);
});

test('password reset requires administrator current password', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'password' => 'AdminPassword123!',
    ]);

    $admin->assignRole('Super Administrator');

    $target = User::factory()->create([
        'email_verified_at' => now(),
        'password' => 'OldPassword123!',
    ]);

    $target->assignRole('Content Editor');

    Livewire::actingAs($admin)
        ->test(UserEdit::class, ['user' => $target])
        ->set('administrator_password', 'WrongPassword123!')
        ->set('new_password', 'NewSecurePassword123!')
        ->set(
            'new_password_confirmation',
            'NewSecurePassword123!',
        )
        ->call('resetPassword')
        ->assertHasErrors('administrator_password');

    expect(
        Hash::check(
            'OldPassword123!',
            $target->fresh()->password,
        ),
    )->toBeTrue();
});

test('administrator cannot use admin reset on own password', function (): void {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'password' => 'AdminPassword123!',
    ]);

    $admin->assignRole('Super Administrator');

    Livewire::actingAs($admin)
        ->test(UserEdit::class, ['user' => $admin])
        ->set(
            'administrator_password',
            'AdminPassword123!',
        )
        ->set('new_password', 'AnotherPassword123!')
        ->set(
            'new_password_confirmation',
            'AnotherPassword123!',
        )
        ->call('resetPassword')
        ->assertHasErrors('administrator_password');
});
