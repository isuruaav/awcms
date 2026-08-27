<?php

use App\Livewire\Admin\Roles\RolePermissionIndex;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super administrator can open role permission management', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Super Administrator');

    Livewire::actingAs($administrator)
        ->test(RolePermissionIndex::class)
        ->assertOk()
        ->assertSee('Roles & Permissions');
});

test('site administrator cannot open role permission management', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    Livewire::actingAs($administrator)
        ->test(RolePermissionIndex::class)
        ->assertForbidden();
});
