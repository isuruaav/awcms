<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'admin.access',
            'dashboard.view',

            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.assign-role',
            'users.reset-password',

            'roles.manage',

            'pages.view',
            'pages.create',
            'pages.update',
            'pages.delete',
            'pages.publish',

            'news.view',
            'news.create',
            'news.update',
            'news.delete',
            'news.publish',

            'galleries.view',
            'galleries.create',
            'galleries.update',
            'galleries.delete',
            'galleries.publish',

            'documents.view',
            'documents.create',
            'documents.update',
            'documents.delete',
            'documents.publish',

            'media.view',
            'media.upload',
            'media.delete',

            'menus.manage',
            'settings.manage',
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roles = [
            'Super Administrator' => $permissions,

            'Site Administrator' => [
                'admin.access',
                'dashboard.view',

                'users.view',
                'users.create',
                'users.update',

                'pages.view',
                'pages.create',
                'pages.update',
                'pages.delete',
                'pages.publish',
                'users.assign-role',
                'users.reset-password',

                'news.view',
                'news.create',
                'news.update',
                'news.delete',
                'news.publish',

                'galleries.view',
                'galleries.create',
                'galleries.update',
                'galleries.delete',
                'galleries.publish',

                'documents.view',
                'documents.create',
                'documents.update',
                'documents.delete',
                'documents.publish',

                'media.view',
                'media.upload',
                'media.delete',

                'menus.manage',
                'settings.manage',
                'audit.view',
            ],

            'Publisher' => [
                'admin.access',
                'dashboard.view',

                'pages.view',
                'pages.update',
                'pages.publish',

                'news.view',
                'news.update',
                'news.publish',

                'galleries.view',
                'galleries.update',
                'galleries.publish',

                'documents.view',
                'documents.update',
                'documents.publish',

                'media.view',
            ],

            'Content Editor' => [
                'admin.access',
                'dashboard.view',

                'pages.view',
                'pages.create',
                'pages.update',

                'news.view',
                'news.create',
                'news.update',

                'galleries.view',
                'galleries.create',
                'galleries.update',

                'documents.view',
                'documents.create',
                'documents.update',

                'media.view',
                'media.upload',
            ],

            'Media Operator' => [
                'admin.access',
                'dashboard.view',

                'galleries.view',
                'galleries.create',
                'galleries.update',

                'documents.view',
                'documents.create',
                'documents.update',

                'media.view',
                'media.upload',
            ],

            'Auditor' => [
                'admin.access',
                'dashboard.view',

                'pages.view',
                'news.view',
                'galleries.view',
                'documents.view',
                'media.view',
                'users.view',
                'audit.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
