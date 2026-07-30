<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class AuditLogger
{
    /**
     * These values must never be written to an audit log.
     *
     * @var list<string>
     */
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'administrator_password',
        'new_password',
        'new_password_confirmation',
        'remember_token',
        'token',
        'secret',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'recovery_codes',
        'passkey',
        'credential',
    ];

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function log(
        string $event,
        string $description,
        ?User $actor = null,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
    ): AuditLog {
        $request = $this->currentRequest();
        $subjectKey = $subject?->getKey();

        $subjectId = is_int($subjectKey)
            ? $subjectKey
            : (
                is_numeric($subjectKey)
                    ? (int) $subjectKey
                    : null
            );

        $userAgent = $request?->userAgent();

        return AuditLog::query()->create([
            'event' => Str::limit(
                trim($event),
                100,
                '',
            ),

            'actor_id' => $actor?->id,

            /*
             * Actor details remain available even if the user
             * account is later deleted.
             */
            'actor_name' => $actor?->name,
            'actor_email' => $actor?->email,

            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subjectId,

            'description' => Str::limit(
                trim($description),
                255,
                '',
            ),

            'old_values' => $oldValues === []
                ? null
                : $this->redact($oldValues),

            'new_values' => $newValues === []
                ? null
                : $this->redact($newValues),

            'ip_address' => $request?->ip(),

            'user_agent' => is_string($userAgent)
                ? Str::limit(
                    $userAgent,
                    1000,
                    '',
                )
                : null,

            'request_method' => $request?->method(),
            'request_path' => $request?->path(),

            'request_id' => $this->requestId($request),
            'created_at' => now(),
        ]);
    }

    private function currentRequest(): ?Request
    {
        if (! app()->bound('request')) {
            return null;
        }

        return request();
    }

    private function requestId(?Request $request): string
    {
        if ($request === null) {
            return (string) Str::uuid();
        }

        $existingRequestId = $request->attributes->get(
            'awcms_request_id',
        );

        if (
            is_string($existingRequestId)
            && $existingRequestId !== ''
        ) {
            return $existingRequestId;
        }

        $requestId = (string) Str::uuid();

        $request->attributes->set(
            'awcms_request_id',
            $requestId,
        );

        return $requestId;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function redact(array $values): array
    {
        $sanitised = [];

        foreach ($values as $key => $value) {
            $normalisedKey = mb_strtolower($key);

            if ($this->isSensitiveKey($normalisedKey)) {
                $sanitised[$key] = '[REDACTED]';

                continue;
            }

            $sanitised[$key] = is_array($value)
                ? $this->redact($value)
                : $value;
        }

        return $sanitised;
    }

    private function isSensitiveKey(string $key): bool
    {
        if (
            in_array(
                $key,
                self::SENSITIVE_KEYS,
                true,
            )
        ) {
            return true;
        }

        return str_contains($key, 'password')
            || str_contains($key, 'token')
            || str_contains($key, 'secret')
            || str_contains($key, 'credential')
            || str_contains($key, 'recovery');
    }
}
