<?php

namespace App\Livewire\Admin\ContactMessages;

use App\Models\ContactMessage;
use App\Models\User;
use App\Services\ContactMessageService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

final class ContactMessageIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public ?int $selectedMessageId = null;

    public function mount(): void
    {
        Gate::authorize('contacts.manage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openMessage(int $id): void
    {
        $message = ContactMessage::query()->findOrFail($id);
        app(ContactMessageService::class)->markRead($message, $this->actor());
        $this->selectedMessageId = $id;
    }

    public function resolve(int $id): void
    {
        $message = ContactMessage::query()->findOrFail($id);
        app(ContactMessageService::class)->resolve($message, $this->actor());
        session()->flash('status', 'Contact message resolved.');
    }

    public function render(): View
    {
        $query = ContactMessage::query();
        $search = trim($this->search);
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('subject', 'like', '%'.$search.'%');
            });
        }
        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.admin.contact-messages.contact-message-index', [
            'messages' => $query->latest()->paginate(15),
            'selectedMessage' => $this->selectedMessageId !== null ? ContactMessage::query()->with('handler')->find($this->selectedMessageId) : null,
            'newCount' => ContactMessage::query()->where('status', 'new')->count(),
        ])->layout('components.layouts.admin', ['title' => 'Contact Messages']);
    }

    private function actor(): User
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            throw ValidationException::withMessages(['authorization' => 'An authenticated administrator is required.']);
        }

        return $user;
    }
}
