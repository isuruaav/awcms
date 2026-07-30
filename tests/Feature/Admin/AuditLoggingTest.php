<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Event;

test('successful login creates an audit log', function (): void {
    $user = User::factory()->create([
        'password' => 'Password123!',
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ])->assertRedirect();

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'auth.login',
        'actor_id' => $user->id,
        'subject_type' => $user->getMorphClass(),
        'subject_id' => $user->id,
    ]);
});

test('logout creates an audit log', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect();

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'auth.logout',
        'actor_id' => $user->id,
        'subject_id' => $user->id,
    ]);
});

test('failed login event creates an audit log', function (): void {
    Event::dispatch(new Failed(
        guard: 'web',
        user: null,
        credentials: [
            'email' => 'failed.user@example.com',
            'password' => 'NeverLogThisPassword123!',
        ],
    ));

    $log = AuditLog::query()
        ->where('event', 'auth.login-failed')
        ->firstOrFail();

    expect($log->actor_id)->toBeNull()
        ->and($log->new_values)->toMatchArray([
            'email' => 'failed.user@example.com',
            'guard' => 'web',
        ]);
});

test('sensitive values are redacted', function (): void {
    $logger = app(AuditLogger::class);

    $log = $logger->log(
        event: 'security.test',
        description: 'Sensitive data redaction test.',
        newValues: [
            'name' => 'Safe Value',
            'password' => 'SecretPassword123!',
            'remember_token' => 'secret-token',
            'nested' => [
                'two_factor_secret' => 'secret-value',
                'safe_key' => 'Visible Value',
            ],
        ],
    );

    expect($log->new_values)->toMatchArray([
        'name' => 'Safe Value',
        'password' => '[REDACTED]',
        'remember_token' => '[REDACTED]',
        'nested' => [
            'two_factor_secret' => '[REDACTED]',
            'safe_key' => 'Visible Value',
        ],
    ]);
});
