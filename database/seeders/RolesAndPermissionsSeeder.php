<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Clear cached permissions before creating or updating
         * permissions and roles.
         */
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        /**
         * @var list<string> $permissions
         */
        $permissions = [
            /*
             * Administration
             */
            'admin.access',
            'dashboard.view',

            /*
             * User Management
             */
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.assign-role',
            'users.reset-password',

            /*
             * Roles and Permissions
             */
            'roles.manage',

            /*
             * Pages
             */
            'pages.view',
            'pages.create',
            'pages.update',
            'pages.delete',
            'pages.submit',
            'pages.approve',
            'pages.publish',
            'pages.archive',

            /*
             * News
             */
            'news.view',
            'news.create',
            'news.update',
            'news.delete',
            'news.publish',

            /*
             * Galleries
             */
            'galleries.view',
            'galleries.create',
            'galleries.update',
            'galleries.delete',
            'galleries.publish',

            /*
             * Documents
             */
            'documents.view',
            'documents.create',
            'documents.update',
            'documents.delete',
            'documents.publish',

            /*
             * Media Library
             */
            'media.view',
            'media.upload',
            'media.delete',

            /*
             * Site Management
             */
            'menus.manage',
            'settings.manage',

            /*
             * Audit Logs
             */
            'audit.view',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate(
                $permissionName,
                'web',
            );
        }

        /**
         * @var array<string, list<string>> $roles
         */
        $roles = [
            /*
             * Full system access.
             *
             * Gate::before may already grant Super Administrator
             * unrestricted access, but synchronising all permissions
             * keeps the database role definition complete.
             */
            'Super Administrator' => $permissions,

            /*
             * Manages one independently deployed AWCMS website.
             */
            'Site Administrator' => [
                'admin.access',
                'dashboard.view',

                /*
                 * User Management
                 */
                'users.view',
                'users.create',
                'users.update',
                'users.assign-role',
                'users.reset-password',

                /*
                 * Pages
                 */
                'pages.view',
                'pages.create',
                'pages.update',
                'pages.delete',
                'pages.submit',
                'pages.approve',
                'pages.publish',
                'pages.archive',

                /*
                 * News
                 */
                'news.view',
                'news.create',
                'news.update',
                'news.delete',
                'news.publish',

                /*
                 * Galleries
                 */
                'galleries.view',
                'galleries.create',
                'galleries.update',
                'galleries.delete',
                'galleries.publish',

                /*
                 * Documents
                 */
                'documents.view',
                'documents.create',
                'documents.update',
                'documents.delete',
                'documents.publish',

                /*
                 * Media Library
                 */
                'media.view',
                'media.upload',
                'media.delete',

                /*
                 * Site Management
                 */
                'menus.manage',
                'settings.manage',

                /*
                 * Audit Logs
                 */
                'audit.view',
            ],

            /*
             * Reviews, approves and publishes public content.
             */
            'Publisher' => [
                'admin.access',
                'dashboard.view',

                /*
                 * Pages
                 */
                'pages.view',
                'pages.create',
                'pages.update',
                'pages.submit',
                'pages.approve',
                'pages.publish',
                'pages.archive',

                /*
                 * News
                 */
                'news.view',
                'news.create',
                'news.update',
                'news.publish',

                /*
                 * Galleries
                 */
                'galleries.view',
                'galleries.create',
                'galleries.update',
                'galleries.publish',

                /*
                 * Documents
                 */
                'documents.view',
                'documents.create',
                'documents.update',
                'documents.publish',

                /*
                 * Media Library
                 */
                'media.view',
                'media.upload',
            ],

            /*
             * Creates and edits content, then submits it for review.
             */
            'Content Editor' => [
                'admin.access',
                'dashboard.view',

                /*
                 * Pages
                 */
                'pages.view',
                'pages.create',
                'pages.update',
                'pages.submit',

                /*
                 * News
                 */
                'news.view',
                'news.create',
                'news.update',

                /*
                 * Galleries
                 */
                'galleries.view',
                'galleries.create',
                'galleries.update',

                /*
                 * Documents
                 */
                'documents.view',
                'documents.create',
                'documents.update',

                /*
                 * Media Library
                 */
                'media.view',
                'media.upload',
            ],

            /*
             * Uploads and manages media, galleries and documents.
             */
            'Media Operator' => [
                'admin.access',
                'dashboard.view',

                /*
                 * Galleries
                 */
                'galleries.view',
                'galleries.create',
                'galleries.update',

                /*
                 * Documents
                 */
                'documents.view',
                'documents.create',
                'documents.update',

                /*
                 * Media Library
                 */
                'media.view',
                'media.upload',
            ],

            /*
             * Read-only inspection access.
             */
            'Auditor' => [
                'admin.access',
                'dashboard.view',

                'users.view',
                'pages.view',
                'news.view',
                'galleries.view',
                'documents.view',
                'media.view',
                'audit.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate(
                $roleName,
                'web',
            );

            $role->syncPermissions(
                $rolePermissions,
            );
        }

        /*
         * Clear the cache again so the new role assignments become
         * available immediately.
         */
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
