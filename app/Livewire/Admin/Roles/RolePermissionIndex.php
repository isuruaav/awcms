<?php

namespace App\Livewire\Admin\Roles;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolePermissionIndex extends Component
{
    public ?int $selectedRoleId = null;

    /** @var list<string> */
    public array $selectedPermissions = [];

    public string $newRoleName = '';

    public function mount(): void
    {
        Gate::authorize('roles.manage');

        $role = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->first();

        $this->selectedRoleId = $role instanceof Role
            ? (int) $role->getKey()
            : null;
        $this->loadSelectedPermissions();
    }

    public function selectRole(int $roleId): void
    {
        Gate::authorize('roles.manage');

        Role::query()
            ->where('guard_name', 'web')
            ->findOrFail($roleId);

        $this->selectedRoleId = $roleId;
        $this->loadSelectedPermissions();
    }

    public function createRole(): void
    {
        Gate::authorize('roles.manage');

        $this->validate([
            'newRoleName' => ['required', 'string', 'max:100'],
        ]);

        $name = trim(
            strip_tags($this->newRoleName),
        );

        if ($name === '') {
            throw ValidationException::withMessages([
                'newRoleName' => 'Role name is required.',
            ]);
        }

        if (
            Role::query()
                ->where('name', $name)
                ->where('guard_name', 'web')
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'newRoleName' => 'That role already exists.',
            ]);
        }

        $role = Role::query()->create([
            'name' => $name,
            'guard_name' => 'web',
        ]);

        $this->selectedRoleId = (int) $role->getKey();
        $this->selectedPermissions = [];
        $this->newRoleName = '';

        app(AuditLogger::class)->log(
            event: 'roles.created',
            description: 'A role was created.',
            actor: $this->actor(),
            subject: $role,
            newValues: [
                'name' => $name,
            ],
        );

        session()->flash(
            'status',
            'Role created successfully.',
        );
    }

    public function savePermissions(): void
    {
        Gate::authorize('roles.manage');

        $role = $this->selectedRole();

        if ($role->name === 'Super Administrator') {
            throw ValidationException::withMessages([
                'role' => 'Super Administrator permissions are managed by the system seeder and cannot be reduced here.',
            ]);
        }

        $valid = $this->allPermissionNames();
        $permissions = array_values(
            array_intersect(
                $valid,
                $this->selectedPermissions,
            ),
        );
        $old = $this->rolePermissionNames($role);

        $role->syncPermissions($permissions);

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        app(AuditLogger::class)->log(
            event: 'roles.permissions-updated',
            description: 'Role permissions were updated.',
            actor: $this->actor(),
            subject: $role,
            oldValues: [
                'permissions' => $old,
            ],
            newValues: [
                'permissions' => $permissions,
            ],
        );

        $this->loadSelectedPermissions();

        session()->flash(
            'status',
            'Role permissions updated successfully.',
        );
    }

    public function render(): View
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        $grouped = $permissions->groupBy(
            static function (Permission $permission): string {
                $parts = explode(
                    '.',
                    $permission->name,
                    2,
                );

                return ucfirst($parts[0]);
            },
        );

        return view(
            'livewire.admin.roles.role-permission-index',
            [
                'roles' => Role::query()
                    ->where('guard_name', 'web')
                    ->withCount('users')
                    ->orderBy('name')
                    ->get(),
                'groupedPermissions' => $grouped,
                'selectedRole' => $this->selectedRoleId !== null
                    ? Role::query()->find($this->selectedRoleId)
                    : null,
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Roles & Permissions',
            ],
        );
    }

    private function loadSelectedPermissions(): void
    {
        if ($this->selectedRoleId === null) {
            $this->selectedPermissions = [];

            return;
        }

        $role = Role::query()->find($this->selectedRoleId);

        $this->selectedPermissions = $role instanceof Role
            ? $this->rolePermissionNames($role)
            : [];
    }

    private function selectedRole(): Role
    {
        if ($this->selectedRoleId === null) {
            throw ValidationException::withMessages([
                'role' => 'Select a role first.',
            ]);
        }

        return Role::query()
            ->where('guard_name', 'web')
            ->findOrFail($this->selectedRoleId);
    }

    /** @return list<string> */
    private function allPermissionNames(): array
    {
        $names = [];

        foreach (
            Permission::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(['name']) as $permission
        ) {
            $names[] = $permission->name;
        }

        return $names;
    }

    /** @return list<string> */
    private function rolePermissionNames(Role $role): array
    {
        $names = [];

        foreach ($role->permissions as $permission) {
            $names[] = $permission->name;
        }

        sort($names);

        return $names;
    }

    private function actor(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            throw ValidationException::withMessages([
                'authorization' => 'An authenticated administrator is required.',
            ]);
        }

        return $user;
    }
}
