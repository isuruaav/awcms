<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class SchoolModulePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        /** @var list<string> $permissions */
        $permissions = [
            'hero-slides.view',
            'hero-slides.create',
            'hero-slides.update',
            'hero-slides.delete',
            'school-leaders.view',
            'school-leaders.update',
            'past-commandants.view',
            'past-commandants.create',
            'past-commandants.update',
            'past-commandants.delete',
            'past-chief-instructors.view',
            'past-chief-instructors.create',
            'past-chief-instructors.update',
            'past-chief-instructors.delete',
        ];

        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        try {
            DB::transaction(function () use ($permissions): void {
                foreach ($permissions as $permission) {
                    Permission::findOrCreate($permission, 'web');
                }

                // Add the new grants only; preserve all existing role grants.
                // This intentionally grants these capabilities again if rerun.
                foreach (['Super Administrator', 'Site Administrator'] as $name) {
                    $role = Role::query()
                        ->where('guard_name', 'web')
                        ->where('name', $name)
                        ->first();

                    if ($role instanceof Role) {
                        $role->givePermissionTo($permissions);
                    }
                }
            });
        } finally {
            $registrar->forgetCachedPermissions();
        }
    }
}
