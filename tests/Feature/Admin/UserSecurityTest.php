<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('active users can authenticate', function (): void {
    $user = User::factory()->create([
        'is_active' => true,
        'password' => Hash::make('Password123!'),
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $this->assertAuthenticatedAs($user);
});

test('inactive users cannot authenticate', function (): void {
    $user = User::factory()->create([
        'is_active' => false,
        'password' => Hash::make('Password123!'),
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $this->assertGuest();
});

test('inactive authenticated users are logged out', function (): void {
    $user = User::factory()->create([
        'is_active' => false,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('successful login records date and IP address', function (): void {
    $user = User::factory()->create([
        'is_active' => true,
        'password' => Hash::make('Password123!'),
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ], [
        'REMOTE_ADDR' => '127.0.0.10',
    ]);

    $user->refresh();

    expect($user->last_login_at)->not->toBeNull()
        ->and($user->last_login_ip)->toBe('127.0.0.10');
});
