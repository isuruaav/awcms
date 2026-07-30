<?php

namespace App\Providers;

use App\Models\User;
use App\Services\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use App\Livewire\Admin\AuditLogs\AuditLogIndex;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();
        $this->configureAuthenticationEvents();
    }

    /**
     * Configure application-wide defaults.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
                ? Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                : null,
        );
    }

    /**
     * Configure application authorization rules.
     */
    protected function configureAuthorization(): void
    {
        Gate::before(
            fn (User $user, string $ability): ?bool => $user->hasRole('Super Administrator') ? true : null,
        );
    }

    /**
     * Register authentication audit event listeners.
     */
    protected function configureAuthenticationEvents(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Successful Login
        |--------------------------------------------------------------------------
        */
        Event::listen(Login::class, function (Login $event): void {
            if (! $event->user instanceof User) {
                return;
            }

            $event->user->forceFill([
                'last_login_at' => now(),
                'last_login_ip' => $this->currentRequestIp(),
            ])->saveQuietly();

            app(AuditLogger::class)->log(
                event: 'auth.login',
                description: 'User successfully signed in.',
                actor: $event->user,
                subject: $event->user,
                newValues: [
                    'guard' => $event->guard,
                    'remember' => $event->remember,
                ],
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */
        Event::listen(Logout::class, function (Logout $event): void {
            if (! $event->user instanceof User) {
                return;
            }

            app(AuditLogger::class)->log(
                event: 'auth.logout',
                description: 'User signed out.',
                actor: $event->user,
                subject: $event->user,
                newValues: [
                    'guard' => $event->guard,
                ],
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Failed Login
        |--------------------------------------------------------------------------
        */
        Event::listen(Failed::class, function (Failed $event): void {
            $subject = $event->user instanceof User
                ? $event->user
                : null;

            $email = $event->credentials['email'] ?? null;

            app(AuditLogger::class)->log(
                event: 'auth.login-failed',
                description: 'Failed login attempt.',
                actor: null,
                subject: $subject,
                newValues: [
                    'email' => is_string($email)
                        ? mb_strtolower(trim($email))
                        : null,

                    'guard' => $event->guard,
                ],
            );
        });
    }

    /**
     * Get the current request IP address safely.
     */
    protected function currentRequestIp(): ?string
    {
        if (! $this->app->bound('request')) {
            return null;
        }

        return request()->ip();
    }
}
