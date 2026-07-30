<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Validation\ValidationException;

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

    private static function activeSuperAdministratorCount(): int
    {
        return User::role('Super Administrator')
            ->where('is_active', true)
            ->count();
    }
}
