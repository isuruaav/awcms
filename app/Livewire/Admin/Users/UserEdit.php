<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Services\AuditLogger;
use App\Support\UserManagementRules;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Models\Role;

final class UserEdit extends Component
{
    #[Locked]
    public int $userId;

    public string $name = '';

    public string $email = '';

    public string $role = '';

    public bool $isActive = true;

    public string $administrator_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public function mount(User $user): void
    {
        Gate::authorize('users.update');

        $actor = $this->actor();

        $user->loadMissing('roles');

        UserManagementRules::assertTargetManageable(
            $actor,
            $user,
        );

        $assignedRole = $user->roles->first();

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;

        $this->role = $assignedRole instanceof Role
            ? $assignedRole->name
            : '';

        $this->isActive = (bool) $user->is_active;
    }

    public function save(): void
    {
        Gate::authorize('users.update');

        $actor = $this->actor();
        $target = $this->target();

        UserManagementRules::assertTargetManageable(
            $actor,
            $target,
        );

        $this->normaliseInput();

        $this->validate($this->profileRules());

        $assignedRole = $target->roles->first();

        $currentRole = $assignedRole instanceof Role
            ? $assignedRole->name
            : null;

        $nameChanged = $target->name !== $this->name;
        $emailChanged = $target->email !== $this->email;
        $roleChanged = $currentRole !== $this->role;

        $statusChanged = (bool) $target->is_active
            !== $this->isActive;

        if ($roleChanged) {
            Gate::authorize('users.assign-role');

            UserManagementRules::ensureRoleChangeAllowed(
                $actor,
                $target,
                $this->role,
            );
        }

        if ($statusChanged) {
            UserManagementRules::ensureStatusChangeAllowed(
                $actor,
                $target,
                $this->isActive,
            );
        }

        DB::transaction(function () use (
            $actor,
            $target,
            $currentRole,
            $nameChanged,
            $emailChanged,
            $roleChanged,
            $statusChanged,
        ): void {
            $oldName = $target->name;
            $oldEmail = $target->email;
            $oldStatus = (bool) $target->is_active;

            $attributes = [
                'name' => $this->name,
                'email' => $this->email,
                'is_active' => $this->isActive,
                'updated_by' => $actor->id,
            ];

            /*
             * An administrator-entered email address is treated as
             * verified. Change this to null if email re-verification
             * is required by the deployment policy.
             */
            if ($emailChanged) {
                $attributes['email_verified_at'] = Carbon::now();
            }

            /*
             * Rotate the remember token when disabling an account.
             */
            if ($statusChanged && ! $this->isActive) {
                $attributes['remember_token'] = Str::random(60);
            }

            $target->forceFill($attributes)->save();

            if ($roleChanged) {
                $target->syncRoles([
                    $this->role,
                ]);
            }

            /*
             * Terminate all database sessions when the account
             * is disabled.
             */
            if ($statusChanged && ! $this->isActive) {
                DB::table('sessions')
                    ->where('user_id', $target->id)
                    ->delete();
            }

            $auditLogger = app(AuditLogger::class);

            if ($nameChanged || $emailChanged) {
                $auditLogger->log(
                    event: 'users.profile-updated',
                    description: 'Administrator account details updated.',
                    actor: $actor,
                    subject: $target,
                    oldValues: [
                        'name' => $oldName,
                        'email' => $oldEmail,
                    ],
                    newValues: [
                        'name' => $this->name,
                        'email' => $this->email,
                    ],
                );
            }

            if ($roleChanged) {
                $auditLogger->log(
                    event: 'users.role-changed',
                    description: 'Administrator account role changed.',
                    actor: $actor,
                    subject: $target,
                    oldValues: [
                        'role' => $currentRole,
                    ],
                    newValues: [
                        'role' => $this->role,
                    ],
                );
            }

            if ($statusChanged) {
                $auditLogger->log(
                    event: $this->isActive
                        ? 'users.activated'
                        : 'users.disabled',
                    description: $this->isActive
                        ? 'Administrator account activated.'
                        : 'Administrator account disabled.',
                    actor: $actor,
                    subject: $target,
                    oldValues: [
                        'is_active' => $oldStatus,
                    ],
                    newValues: [
                        'is_active' => $this->isActive,
                        'sessions_terminated' => ! $this->isActive,
                    ],
                );
            }
        });

        session()->flash(
            'status',
            "{$target->name}'s account was updated successfully.",
        );
    }

    public function resetPassword(): void
    {
        Gate::authorize('users.reset-password');

        $actor = $this->actor();
        $target = $this->target();

        UserManagementRules::ensurePasswordResetAllowed(
            $actor,
            $target,
        );

        $this->validate(
            [
                'administrator_password' => [
                    'required',
                    'string',
                    'current_password:web',
                ],

                'new_password' => [
                    'required',
                    'string',
                    'confirmed',
                    Password::min(12)
                        ->mixedCase()
                        ->numbers()
                        ->symbols(),
                ],
            ],
            [
                'administrator_password.current_password' => 'Your administrator password is incorrect.',
            ],
        );

        DB::transaction(function () use (
            $actor,
            $target,
        ): void {
            $target->forceFill([
                'password' => $this->new_password,
                'remember_token' => Str::random(60),
                'updated_by' => $actor->id,
            ])->save();

            /*
             * Terminate all active database sessions.
             */
            DB::table('sessions')
                ->where('user_id', $target->id)
                ->delete();

            /*
             * Invalidate outstanding password reset links.
             */
            PasswordBroker::broker()
                ->deleteToken($target);

            /*
             * Password values are deliberately excluded from
             * the audit event.
             */
            app(AuditLogger::class)->log(
                event: 'users.password-reset',
                description: 'Administrator reset another user password.',
                actor: $actor,
                subject: $target,
                newValues: [
                    'sessions_terminated' => true,
                    'password_reset_token_invalidated' => true,
                    'passkeys_preserved' => true,
                    'two_factor_authentication_preserved' => true,
                ],
            );
        });

        $this->reset([
            'administrator_password',
            'new_password',
            'new_password_confirmation',
        ]);

        session()->flash(
            'password_status',
            "{$target->name}'s password was reset and active sessions were terminated.",
        );
    }

    /**
     * @return array<string, list<mixed>>
     */
    private function profileRules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($this->userId),
            ],

            'role' => [
                'required',
                'string',
                Rule::in(
                    UserManagementRules::assignableRoleNames(
                        $this->actor(),
                    ),
                ),
            ],

            'isActive' => [
                'boolean',
            ],
        ];
    }

    public function render(): View
    {
        $target = $this->target();

        $roles = UserManagementRules::assignableRoleNames(
            $this->actor(),
        );

        return view(
            'livewire.admin.users.user-edit',
            compact(
                'target',
                'roles',
            ),
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Edit User',
            ],
        );
    }

    private function actor(): User
    {
        $actor = Auth::user();

        abort_unless(
            $actor instanceof User,
            403,
        );

        return $actor;
    }

    private function target(): User
    {
        return User::query()
            ->with('roles')
            ->findOrFail($this->userId);
    }

    private function normaliseInput(): void
    {
        $this->name = trim($this->name);

        $this->email = mb_strtolower(
            trim($this->email),
        );

        $this->role = trim($this->role);
    }
}
