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
        |--------------------------------------------------------------------------
        | Clear Permission Cache
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        /**
         * @var list<string> $permissions
         */
        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | Administration
            |--------------------------------------------------------------------------
            */

            'admin.access',
            'dashboard.view',

            /*
            |--------------------------------------------------------------------------
            | User Management
            |--------------------------------------------------------------------------
            */

            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.assign-role',
            'users.reset-password',

            /*
            |--------------------------------------------------------------------------
            | Roles and Permissions
            |--------------------------------------------------------------------------
            */

            'roles.manage',

            /*
            |--------------------------------------------------------------------------
            | Pages
            |--------------------------------------------------------------------------
            */

            'pages.view',
            'pages.create',
            'pages.update',
            'pages.delete',

            'pages.submit',
            'pages.approve',
            'pages.publish',
            'pages.archive',

            'pages.revisions.view',
            'pages.revisions.restore',

            /*
            |--------------------------------------------------------------------------
            | News
            |--------------------------------------------------------------------------
            */

            'news.view',
            'news.create',
            'news.update',
            'news.delete',

            /*
             * News workflow permissions.
             */
            'news.submit',
            'news.request-changes',
            'news.approve',
            'news.publish',
            'news.archive',

            /*
             * News administration.
             */
            'news.categories.manage',

            /*
            |--------------------------------------------------------------------------
            | Galleries
            |--------------------------------------------------------------------------
            */

            'galleries.view',
            'galleries.create',
            'galleries.update',
            'galleries.delete',
            'galleries.publish',
            'galleries.archive',

            /*
            |--------------------------------------------------------------------------
            | Documents
            |--------------------------------------------------------------------------
            */

            'documents.view',
            'documents.create',
            'documents.update',
            'documents.delete',
            'documents.publish',
            'documents.archive',

            /*
             * Document administration.
             */
            'documents.categories.manage',

            /*
            |--------------------------------------------------------------------------
            | Media Library
            |--------------------------------------------------------------------------
            */

            'media.view',
            'media.upload',
            'media.update',
            'media.replace',
            'media.delete',

            /*
            |--------------------------------------------------------------------------
            | Site Management
            |--------------------------------------------------------------------------
            */

            'menus.manage',
            'settings.manage',

            /*
            |--------------------------------------------------------------------------
            | Audit Logs
            |--------------------------------------------------------------------------
            */

            'audit.view',
        ];

        /*
        |--------------------------------------------------------------------------
        | Create / Update Permissions
        |--------------------------------------------------------------------------
        */

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
            |--------------------------------------------------------------------------
            | Super Administrator
            |--------------------------------------------------------------------------
            |
            | Full CMS access.
            |
            | Gate::before may already allow this role unrestricted
            | access, but syncing every permission keeps the database
            | role definition complete and auditable.
            |
            */

            'Super Administrator' => $permissions,

            /*
            |--------------------------------------------------------------------------
            | Site Administrator
            |--------------------------------------------------------------------------
            |
            | Manages one independently deployed AWCMS website.
            |
            */

            'Site Administrator' => [

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

                'pages.revisions.view',
                'pages.revisions.restore',

                /*
                 * News
                 */
                'news.view',
                'news.create',
                'news.update',
                'news.delete',

                'news.submit',
                'news.request-changes',
                'news.approve',
                'news.publish',
                'news.archive',

                'news.categories.manage',

                /*
                 * Galleries
                 */
                'galleries.view',
                'galleries.create',
                'galleries.update',
                'galleries.delete',
                'galleries.publish',
                'galleries.archive',

                /*
                 * Documents
                 */
                'documents.view',
                'documents.create',
                'documents.update',
                'documents.delete',
                'documents.publish',
                'documents.archive',

                'documents.categories.manage',

                /*
                 * Media Library
                 */
                'media.view',
                'media.upload',
                'media.update',
                'media.replace',
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
            |--------------------------------------------------------------------------
            | Publisher
            |--------------------------------------------------------------------------
            |
            | Reviews, approves, publishes and archives public content.
            |
            */

            'Publisher' => [

                /*
                 * Administration
                 */
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

                'pages.revisions.view',
                'pages.revisions.restore',

                /*
                 * News
                 */
                'news.view',
                'news.create',
                'news.update',

                'news.submit',
                'news.request-changes',
                'news.approve',
                'news.publish',
                'news.archive',

                'news.categories.manage',

                /*
                 * Galleries
                 */
                'galleries.view',
                'galleries.create',
                'galleries.update',
                'galleries.publish',
                'galleries.archive',

                /*
                 * Documents
                 */
                'documents.view',
                'documents.create',
                'documents.update',
                'documents.publish',
                'documents.archive',

                'documents.categories.manage',

                /*
                 * Media Library
                 */
                'media.view',
                'media.upload',
                'media.update',
            ],

            /*
            |--------------------------------------------------------------------------
            | Content Editor
            |--------------------------------------------------------------------------
            |
            | Creates and edits content and submits it for review.
            |
            | Cannot approve, publish, archive or request changes.
            |
            */

            'Content Editor' => [

                /*
                 * Administration
                 */
                'admin.access',
                'dashboard.view',

                /*
                 * Pages
                 */
                'pages.view',
                'pages.create',
                'pages.update',
                'pages.submit',

                'pages.revisions.view',
                'pages.revisions.restore',

                /*
                 * News
                 */
                'news.view',
                'news.create',
                'news.update',
                'news.submit',

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
                'media.update',
            ],

            /*
            |--------------------------------------------------------------------------
            | Media Operator
            |--------------------------------------------------------------------------
            |
            | Uploads and manages media, galleries and documents.
            |
            */

            'Media Operator' => [

                /*
                 * Administration
                 */
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
                'media.update',
                'media.replace',
                'media.delete',
            ],

            /*
            |--------------------------------------------------------------------------
            | Auditor
            |--------------------------------------------------------------------------
            |
            | Read-only administrative inspection access.
            |
            */

            'Auditor' => [

                /*
                 * Administration
                 */
                'admin.access',
                'dashboard.view',

                /*
                 * User Management
                 */
                'users.view',

                /*
                 * Pages
                 */
                'pages.view',
                'pages.revisions.view',

                /*
                 * News
                 */
                'news.view',

                /*
                 * Galleries
                 */
                'galleries.view',

                /*
                 * Documents
                 */
                'documents.view',

                /*
                 * Media Library
                 */
                'media.view',

                /*
                 * Audit Logs
                 */
                'audit.view',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Synchronise Roles
        |--------------------------------------------------------------------------
        */

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
         * Clear the permission cache again so the updated
         * assignments become available immediately.
         */
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
