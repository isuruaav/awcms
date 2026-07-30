<?php

use App\Livewire\Admin\AuditLogs\AuditLogIndex;
use App\Models\User;
use App\Services\AuditLogger;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('auditor can access audit log viewer', function (): void {
    $auditor = User::factory()->create();
    $auditor->assignRole('Auditor');

    $this->actingAs($auditor)
        ->get(route('admin.audit-logs.index'))
        ->assertOk()
        ->assertSee('Audit Logs');
});

test('user without audit permission is forbidden', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Content Editor');

    $this->actingAs($user)
        ->get(route('admin.audit-logs.index'))
        ->assertForbidden();
});

test('audit records can be searched', function (): void {
    $auditor = User::factory()->create();
    $auditor->assignRole('Auditor');

    app(AuditLogger::class)->log(
        event: 'users.created',
        description: 'Unique searchable audit description.',
        actor: $auditor,
        subject: $auditor,
    );

    app(AuditLogger::class)->log(
        event: 'auth.logout',
        description: 'Different audit description.',
        actor: $auditor,
        subject: $auditor,
    );

    Livewire::actingAs($auditor)
        ->test(AuditLogIndex::class)
        ->set('search', 'Unique searchable')
        ->assertSee('Unique searchable audit description.')
        ->assertDontSee('Different audit description.');
});

test('audit records can be filtered by event', function (): void {
    $auditor = User::factory()->create();
    $auditor->assignRole('Auditor');

    app(AuditLogger::class)->log(
        event: 'users.created',
        description: 'Created event description.',
        actor: $auditor,
        subject: $auditor,
    );

    app(AuditLogger::class)->log(
        event: 'users.disabled',
        description: 'Disabled event description.',
        actor: $auditor,
        subject: $auditor,
    );

    Livewire::actingAs($auditor)
        ->test(AuditLogIndex::class)
        ->set('event', 'users.disabled')
        ->assertSee('Disabled event description.')
        ->assertDontSee('Created event description.');
});
