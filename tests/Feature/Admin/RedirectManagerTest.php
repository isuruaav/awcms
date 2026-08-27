<?php

use App\Models\Redirect;
use App\Models\User;
use App\Services\RedirectService;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('site administrator can create a working redirect', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    $redirect = app(RedirectService::class)->save(
        redirect: null,
        actor: $administrator,
        sourcePath: '/old-publication',
        destinationUrl: '/documents',
        status: 301,
        isActive: true,
    );

    expect($redirect)->toBeInstanceOf(Redirect::class);

    $this->get('/old-publication')
        ->assertRedirect('/documents')
        ->assertStatus(301);

    expect($redirect->refresh()->hit_count)->toBe(1);
});
