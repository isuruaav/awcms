<?php

namespace App\Livewire\Admin\Roles;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolePermissionIndex extends Component
{
    #[Locked]
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
        $this->resetValidation();
        $this->loadSelectedPermissions();
    }

    public function createRole(): void
    {
        $actor = $this->authorizeRoleChanges();
        $this->newRoleName = trim(strip_tags($this->newRoleName));

        $this->validate([
            'newRoleName' => [
                'required',
                'string',
                'max:100',
                Rule::unique(Role::class, 'name')->where('guard_name', 'web'),
            ],
        ]);

        if (in_array(mb_strtolower($this->newRoleName), [
            'super administrator',
            'site administrator',
        ], true)) {
            throw ValidationException::withMessages([
                'newRoleName' => 'This name is reserved for a system administrator role.',
            ]);
        }

        $role = DB::transaction(function () use ($actor): Role {
            $role = Role::query()->create([
                'name' => $this->newRoleName,
                'guard_name' => 'web',
            ]);

            app(AuditLogger::class)->log(
                event: 'roles.created',
                description: 'A role was created.',
                actor: $actor,
                subject: $role,
                newValues: ['name' => $role->name],
            );

            return $role;
        });

        $this->selectedRoleId = (int) $role->getKey();
        $this->selectedPermissions = [];
        $this->newRoleName = '';
        $this->resetValidation();

        session()->flash('status', 'Role created successfully. Assign its permissions before assigning users.');
    }

    public function savePermissions(): void
    {
        $actor = $this->authorizeRoleChanges();
        $role = $this->selectedRole();

        if ($role->name === 'Super Administrator') {
            throw ValidationException::withMessages([
                'role' => 'Super Administrator has full access through the system authorization rule and cannot be changed here.',
            ]);
        }

        $valid = $this->allPermissionNames();
        $this->validate([
            'selectedPermissions' => ['present', 'array', 'max:'.count($valid)],
            'selectedPermissions.*' => ['required', 'string', 'distinct', Rule::in($valid)],
        ]);

        $permissions = $this->selectedPermissions;
        sort($permissions);
        $this->assertPermissionDependencies($permissions);

        $registrar = app(PermissionRegistrar::class);

        try {
            DB::transaction(function () use ($actor, $role, $permissions): void {
                $lockedRole = Role::query()
                    ->where('guard_name', 'web')
                    ->whereKey($role->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                // Re-check the protected name inside the transaction.
                if ($lockedRole->name === 'Super Administrator') {
                    throw ValidationException::withMessages([
                        'role' => 'The Super Administrator role is protected.',
                    ]);
                }

                $old = $this->rolePermissionNames($lockedRole);
                $lockedRole->syncPermissions($permissions);

                app(AuditLogger::class)->log(
                    event: 'roles.permissions-updated',
                    description: 'Role permissions were updated.',
                    actor: $actor,
                    subject: $lockedRole,
                    oldValues: ['permissions' => $old],
                    newValues: ['permissions' => $permissions],
                );
            });
        } finally {
            $registrar->forgetCachedPermissions();
        }

        $this->loadSelectedPermissions();
        session()->flash('status', 'Role permissions updated successfully.');
    }

    public function render(): View
    {
        Gate::authorize('roles.manage');

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        $grouped = $permissions->groupBy(
            static function (Permission $permission): string {
                $prefix = explode('.', $permission->name, 2)[0];

                return ucwords(str_replace(['-', '_'], ' ', $prefix));
            },
        );

        return view('livewire.admin.roles.role-permission-index', [
            'roles' => Role::query()
                ->where('guard_name', 'web')
                ->withCount('users')
                ->orderBy('name')
                ->get(),
            'groupedPermissions' => $grouped,
            'canManageRoles' => $this->actor()->hasRole('Super Administrator'),
            'selectedRole' => $this->selectedRoleId === null
                ? null
                : $this->selectedRole(),
        ])->layout('components.layouts.admin', ['title' => 'Roles & Permissions']);
    }

    private function loadSelectedPermissions(): void
    {
        if ($this->selectedRoleId === null) {
            $this->selectedPermissions = [];

            return;
        }

        $role = $this->selectedRole();
        $this->selectedPermissions = $role->name === 'Super Administrator'
            ? $this->allPermissionNames()
            : $this->rolePermissionNames($role);
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

        foreach (Permission::query()->where('guard_name', 'web')->orderBy('name')->get(['name']) as $permission) {
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

    /** @param list<string> $permissions */
    private function assertPermissionDependencies(array $permissions): void
    {
        if ($permissions !== [] && ! in_array('admin.access', $permissions, true)) {
            throw ValidationException::withMessages([
                'selectedPermissions' => 'Select admin.access to allow this role to enter the administration panel.',
            ]);
        }

        $modules = [
            'users', 'pages', 'news', 'galleries', 'documents', 'media',
            'hero-slides', 'school-leaders', 'past-commandants', 'past-chief-instructors',
        ];

        foreach ($permissions as $permission) {
            $module = explode('.', $permission, 2)[0];

            if (
                in_array($module, $modules, true)
                && $permission !== $module.'.view'
                && ! in_array($module.'.view', $permissions, true)
            ) {
                throw ValidationException::withMessages([
                    'selectedPermissions' => 'Select '.$module.'.view before granting '.$permission.'.',
                ]);
            }
        }
    }

    private function authorizeRoleChanges(): User
    {
        Gate::authorize('roles.manage');
        $actor = $this->actor();

        abort_unless(
            $actor->hasRole('Super Administrator'),
            403,
            'Only a Super Administrator may create roles or change their permissions.',
        );

        return $actor;
    }

    private function actor(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
