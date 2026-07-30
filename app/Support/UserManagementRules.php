<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

final class UserManagementRules
{
    /**
     * @var list<string>
     */
    private const PROTECTED_ROLES = [
        'Super Administrator',
        'Site Administrator',
    ];

    public static function canManageTarget(
        User $actor,
        User $target,
    ): bool {
        if ($actor->hasRole('Super Administrator')) {
            return true;
        }

        return ! $target->hasAnyRole(self::PROTECTED_ROLES);
    }

    public static function assertTargetManageable(
        User $actor,
        User $target,
    ): void {
        abort_unless(
            self::canManageTarget($actor, $target),
            403,
            'You cannot manage this protected administrator account.',
        );
    }

    /**
     * @return list<string>
     */
    public static function assignableRoleNames(User $actor): array
    {
        $query = Role::query()
            ->where('guard_name', 'web');

        if (! $actor->hasRole('Super Administrator')) {
            $query->whereNotIn(
                'name',
                self::PROTECTED_ROLES,
            );
        }

        $roleNames = $query
            ->orderBy('name')
            ->pluck('name')
            ->all();

        return array_values(
            array_map(
                static fn (mixed $roleName): string => (string) $roleName,
                $roleNames,
            ),
        );
    }

    /**
     * @throws ValidationException
     */
    public static function ensureRoleCanBeAssigned(
        User $actor,
        string $roleName,
    ): void {
        if (
            in_array(
                $roleName,
                self::assignableRoleNames($actor),
                true,
            )
        ) {
            return;
        }

        throw ValidationException::withMessages([
            'role' => 'You are not permitted to assign this role.',
        ]);
    }

    /**
     * @throws ValidationException
     */
    public static function ensureRoleChangeAllowed(
        User $actor,
        User $target,
        string $newRole,
    ): void {
        self::assertTargetManageable($actor, $target);
        self::ensureRoleCanBeAssigned($actor, $newRole);

        if (
            $actor->is($target)
            && $target->hasRole('Super Administrator')
            && $newRole !== 'Super Administrator'
        ) {
            throw ValidationException::withMessages([
                'role' => 'You cannot remove your own Super Administrator role.',
            ]);
        }

        if (
            $target->is_active
            && $target->hasRole('Super Administrator')
            && $newRole !== 'Super Administrator'
            && self::activeSuperAdministratorCount() <= 1
        ) {
            throw ValidationException::withMessages([
                'role' => 'The last active Super Administrator cannot be demoted.',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    public static function ensureStatusChangeAllowed(
        User $actor,
        User $target,
        bool $newStatus,
    ): void {
        if ($actor->is($target) && $newStatus === false) {
            throw ValidationException::withMessages([
                'user' => 'You cannot disable your own account.',
            ]);
        }

        if (
            $newStatus === false
            && $target->hasRole('Super Administrator')
            && self::activeSuperAdministratorCount() <= 1
        ) {
            throw ValidationException::withMessages([
                'user' => 'The last active Super Administrator cannot be disabled.',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    public static function ensurePasswordResetAllowed(
        User $actor,
        User $target,
    ): void {
        self::assertTargetManageable($actor, $target);

        if ($actor->is($target)) {
            throw ValidationException::withMessages([
                'administrator_password' => 'Use your own Security Settings page to change your password.',
            ]);
        }
    }

    private static function activeSuperAdministratorCount(): int
    {
        return User::role('Super Administrator')
            ->where('is_active', true)
            ->count();
    }
}
