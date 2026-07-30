<?php

use App\Livewire\Admin\Users\UserCreate;
use App\Livewire\Admin\Users\UserEdit;
use App\Livewire\Admin\Users\UserIndex;
use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('creating a user creates an audit record', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Super Administrator');

    Livewire::actingAs($admin)
        ->test(UserCreate::class)
        ->set('name', 'Audit Test User')
        ->set('email', 'audit.user@example.com')
        ->set('role', 'Content Editor')
        ->set('isActive', true)
        ->set('password', 'TemporaryPassword123!')
        ->set(
            'password_confirmation',
            'TemporaryPassword123!',
        )
        ->call('save')
        ->assertHasNoErrors();

    $target = User::query()
        ->where('email', 'audit.user@example.com')
        ->firstOrFail();

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'users.created',
        'actor_id' => $admin->id,
        'subject_id' => $target->id,
    ]);
});

test('changing a user role creates an audit record', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Super Administrator');

    $target = User::factory()->create();
    $target->assignRole('Content Editor');

    Livewire::actingAs($admin)
        ->test(UserEdit::class, ['user' => $target])
        ->set('role', 'Publisher')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'users.role-changed',
        'actor_id' => $admin->id,
        'subject_id' => $target->id,
    ]);
});

test('disabling a user creates an audit record', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Super Administrator');

    $target = User::factory()->create([
        'is_active' => true,
    ]);

    $target->assignRole('Content Editor');

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('toggleActive', $target->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'users.disabled',
        'actor_id' => $admin->id,
        'subject_id' => $target->id,
    ]);
});

test('password reset audit does not contain passwords', function (): void {
    $admin = User::factory()->create([
        'password' => 'AdminPassword123!',
    ]);

    $admin->assignRole('Super Administrator');

    $target = User::factory()->create([
        'password' => 'OldPassword123!',
    ]);

    $target->assignRole('Content Editor');

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

    $log = AuditLog::query()
        ->where('event', 'users.password-reset')
        ->latest('id')
        ->firstOrFail();

    $encodedValues = json_encode([
        $log->old_values,
        $log->new_values,
    ]);

    expect($encodedValues)
        ->not->toContain('AdminPassword123!')
        ->not->toContain('NewSecurePassword123!');
});
