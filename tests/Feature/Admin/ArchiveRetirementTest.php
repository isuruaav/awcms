<?php

use App\Models\Document;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Page;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\GalleryService;
use App\Services\NewsWorkflowService;
use App\Services\PageWorkflowService;
use Database\Seeders\RetireArchivePermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

test('legacy archive actions reject even a super administrator without modifying records', function (): void {
    $actor = User::factory()->create(['is_active' => true]);
    $actor->assignRole(Role::findOrCreate('Super Administrator', 'web'));

    $attempts = [
        static fn () => app(PageWorkflowService::class)->archive(new Page, $actor),
        static fn () => app(NewsWorkflowService::class)->archive(new News, $actor),
        static fn () => app(GalleryService::class)->archive(new Gallery, $actor),
        static fn () => app(DocumentService::class)->archive(new Document, $actor),
    ];

    foreach ($attempts as $attempt) {
        try {
            $attempt();
            $this->fail('Archive must be rejected before accessing a document or page record.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['Archiving is no longer available.'],
                $exception->errors()['workflow'] ?? [],
            );
        }
    }
});

test('archive permission cleanup preserves unrelated grants and can run twice', function (): void {
    $role = Role::findOrCreate('Archive retirement test role', 'web');
    $actor = User::factory()->create();
    $actor->assignRole($role);
    $keep = Permission::findOrCreate('pages.view', 'web');
    $role->givePermissionTo($keep);
    $actor->givePermissionTo($keep);

    foreach (['pages.archive', 'news.archive', 'galleries.archive', 'documents.archive'] as $name) {
        $permission = Permission::findOrCreate($name, 'web');
        $role->givePermissionTo($permission);
        $actor->givePermissionTo($permission);
    }

    $this->seed(RetireArchivePermissionsSeeder::class);
    $this->seed(RetireArchivePermissionsSeeder::class);

    $this->assertSame(['pages.view'], $role->refresh()->permissions->pluck('name')->all());
    $this->assertSame(['pages.view'], $actor->refresh()->permissions->pluck('name')->all());

    foreach (['pages.archive', 'news.archive', 'galleries.archive', 'documents.archive'] as $name) {
        $this->assertDatabaseMissing('permissions', ['name' => $name, 'guard_name' => 'web']);
    }
});
