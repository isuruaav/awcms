<?php

namespace App\Livewire\Admin\ContactMessages;

use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class ContactMessageNavLink extends Component
{
    #[Locked]
    public bool $active = false;

    #[Locked]
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->active = request()->routeIs('admin.contact-messages.*');
        $this->refreshUnreadCount();
    }

    #[On('contact-messages-updated')]
    public function refreshUnreadCount(): void
    {
        Gate::authorize('contacts.manage');

        $this->unreadCount = ContactMessage::query()->where('status', 'new')->count();
    }

    public function render(): View
    {
        Gate::authorize('contacts.manage');

        return view('livewire.admin.contact-messages.contact-message-nav-link');
    }
}
