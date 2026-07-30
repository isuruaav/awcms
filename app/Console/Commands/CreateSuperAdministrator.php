<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class CreateSuperAdministrator extends Command
{
    protected $signature = 'awcms:make-super-admin
                            {--email= : Super Administrator email address}';

    protected $description = 'Create or promote an AWCMS Super Administrator';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Full name'));

        $email = strtolower(trim((string) (
            $this->option('email')
            ?: $this->ask('Email address')
        )));

        $password = (string) $this->secret('Password');
        $passwordConfirmation = (string) $this->secret('Confirm password');

        $data = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ];

        $validator = Validator::make($data, [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = DB::transaction(function () use ($data): User {
            $user = User::query()->firstOrNew([
                'email' => $data['email'],
            ]);

            $user->name = $data['name'];
            $user->password = $data['password'];
            $user->email_verified_at = Carbon::now();
            $user->save();

            $role = Role::findOrCreate('Super Administrator', 'web');

            $user->syncRoles([$role]);

            return $user;
        });

        $this->newLine();
        $this->info('Super Administrator created successfully.');
        $this->line("Name: {$user->name}");
        $this->line("Email: {$user->email}");

        return self::SUCCESS;
    }
}
