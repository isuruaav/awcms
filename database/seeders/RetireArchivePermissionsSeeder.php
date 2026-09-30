<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

final class RetireArchivePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        try {
            DB::transaction(static function (): void {
                $permissions = Permission::query()
                    ->where('guard_name', 'web')
                    ->whereIn('name', [
                        'pages.archive',
                        'news.archive',
                        'galleries.archive',
                        'documents.archive',
                    ])
                    ->lockForUpdate()
                    ->get();

                foreach ($permissions as $permission) {
                    // Delete model instances so permission relationship cleanup runs.
                    $permission->delete();
                }
            });
        } finally {
            $registrar->forgetCachedPermissions();
        }
    }
}
