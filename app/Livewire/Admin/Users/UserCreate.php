<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Support\UserManagementRules;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

final class UserCreate extends Component
{
    public string $name = '';

    public string $email = '';

    public string $role = '';

    public bool $isActive = true;

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        Gate::authorize('users.create');
        Gate::authorize('users.assign-role');
    }

    public function save(): void
    {
        Gate::authorize('users.create');
        Gate::authorize('users.assign-role');

        $actor = $this->actor();

        $this->normaliseInput();

        $this->validate();

        UserManagementRules::ensureRoleCanBeAssigned(
            $actor,
            $this->role,
        );

        $user = DB::transaction(function () use ($actor): User {
            $user = User::query()->create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'is_active' => $this->isActive,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $user->forceFill([
                'email_verified_at' => Carbon::now(),
            ])->saveQuietly();

            $user->syncRoles([$this->role]);

            return $user;
        });

        session()->flash(
            'status',
            "{$user->name}'s account was created successfully.",
        );

        $this->redirectRoute(
            'admin.users.edit',
            ['user' => $user->id],
            navigate: true,
        );
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
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
                Rule::unique('users', 'email'),
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

            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }

    public function render(): View
    {
        $roles = UserManagementRules::assignableRoleNames(
            $this->actor(),
        );

        return view(
            'livewire.admin.users.user-create',
            compact('roles'),
        )->layout(
            'components.layouts.admin',
            ['title' => 'Create User'],
        );
    }

    private function actor(): User
    {
        $actor = Auth::user();

        if (! $actor instanceof User) {
            abort(403);
        }

        return $actor;
    }

    private function normaliseInput(): void
    {
        $this->name = trim($this->name);
        $this->email = mb_strtolower(trim($this->email));
        $this->role = trim($this->role);
    }
}
