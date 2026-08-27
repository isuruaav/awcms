<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class ContactMessageService
{
    public function markRead(ContactMessage $message, User $actor): ContactMessage
    {
        Gate::forUser($actor)->authorize('contacts.manage');
        $message->forceFill([
            'status' => $message->status === 'resolved' ? 'resolved' : 'read',
            'read_at' => $message->read_at ?? now(),
            'handled_by' => $actor->id,
        ])->save();

        return $message->refresh();
    }

    public function resolve(ContactMessage $message, User $actor): ContactMessage
    {
        Gate::forUser($actor)->authorize('contacts.manage');
        $message->forceFill([
            'status' => 'resolved',
            'read_at' => $message->read_at ?? now(),
            'resolved_at' => now(),
            'handled_by' => $actor->id,
        ])->save();

        app(AuditLogger::class)->log(
            event: 'contacts.resolved',
            description: 'A contact message was resolved.',
            actor: $actor,
            subject: $message,
        );

        return $message->refresh();
    }
}
