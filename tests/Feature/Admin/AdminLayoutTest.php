<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super administrator sees the secure admin layout', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $user->assignRole('Super Administrator');

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('AWCMS Dashboard')
        ->assertSee('Content Management')
        ->assertSee('Administration')
        ->assertSee('Pages')
        ->assertSee('Users')
        ->assertSee('Activity Logs');
});

test('content editor only sees permitted navigation items', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $user->assignRole('Content Editor');

    $response = $this->actingAs($user)
        ->get(route('admin.dashboard'));

    $response
        ->assertOk()
        ->assertSee('Pages')
        ->assertSee('News')
        ->assertSee('Galleries')
        ->assertSee('Documents')
        ->assertDontSee('Roles & Permissions')
        ->assertDontSee('Site Settings');
});
