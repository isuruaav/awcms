<?php

namespace App\Livewire\Admin\Publications;

use App\Models\Publication;
use App\Models\User;
use App\Services\PublicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

final class PublicationUploads extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Locked]
    public ?int $editingId = null;

    #[Locked]
    public int $formVersion = 0;

    public bool $formOpen = false;

    public string $title = '';

    public string $titleSi = '';

    public string $locale = 'en';

    public bool $isPublished = false;

    public mixed $pdf = null;

    public mixed $cover = null;

    public string $search = '';

    public string $statusFilter = 'all';

    public function mount(): void
    {
        Gate::authorize('documents.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        Gate::authorize('documents.create');
        Gate::authorize('media.upload');
        $this->clearForm();
        $this->isPublished = Gate::allows('documents.publish');
        $this->formOpen = true;
    }

    public function edit(int $id): void
    {
        Gate::authorize('documents.view');
        Gate::authorize('documents.update');
        $record = Publication::query()->findOrFail($id);
        if ($record->is_published) {
            Gate::authorize('documents.publish');
        }
        $this->clearForm();
        $this->editingId = $id;
        $this->title = $record->title;
        $this->titleSi = $record->title_si ?? '';
        $this->locale = $record->locale;
        $this->isPublished = $record->is_published;
        $this->formOpen = true;
    }

    public function cancel(): void
    {
        Gate::authorize('documents.view');
        $this->clearForm();
    }

    public function save(): void
    {
        foreach (['pdf', 'cover'] as $field) {
            if ($this->{$field} !== null && ! $this->{$field} instanceof TemporaryUploadedFile) {
                $message = $field === 'pdf'
                    ? 'Select the PDF again and wait for its upload to finish.'
                    : 'Select the cover image again and wait for its upload to finish.';
                throw ValidationException::withMessages([$field => $message]);
            }
        }
        app(PublicationService::class)->save(
            actor: $this->actor(), title: $this->title, titleSi: $this->titleSi,
            publish: $this->isPublished, pdf: $this->pdf, cover: $this->cover,
            publication: $this->editingId === null ? null : Publication::query()->findOrFail($this->editingId),
            locale: $this->locale,
        );
        session()->flash('publication-success', $this->isPublished ? 'Publication saved and published.' : 'Publication saved as a draft.');
        $this->clearForm();
        $this->resetPage();
    }

    public function publish(int $id): void
    {
        app(PublicationService::class)->setPublished(Publication::query()->findOrFail($id), $this->actor(), true);
        session()->flash('publication-success', 'Publication published.');
    }

    public function unpublish(int $id): void
    {
        app(PublicationService::class)->setPublished(Publication::query()->findOrFail($id), $this->actor(), false);
        session()->flash('publication-success', 'Publication returned to draft.');
    }

    public function delete(int $id): void
    {
        app(PublicationService::class)->delete(Publication::query()->findOrFail($id), $this->actor());
        if ($this->editingId === $id) {
            $this->clearForm();
        }
        session()->flash('publication-success', 'Publication removed.');
        $this->resetPage();
    }

    public function render(): View
    {
        Gate::authorize('documents.view');
        $publications = Publication::query()
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('title', 'like', '%'.trim($this->search).'%')->orWhere('title_si', 'like', '%'.trim($this->search).'%');
                });
            })
            ->when(in_array($this->statusFilter, ['published', 'draft'], true), fn (Builder $query) => $query->where('is_published', $this->statusFilter === 'published'))
            ->latest('id')->paginate(10);

        return view('livewire.admin.publications.publication-uploads', [
            'publications' => $publications,
            'editing' => $this->editingId === null ? null : Publication::query()->find($this->editingId),
        ])->layout('components.layouts.admin', ['title' => 'Publications']);
    }

    private function clearForm(): void
    {
        $this->reset(['editingId', 'title', 'titleSi', 'pdf', 'cover', 'isPublished', 'formOpen', 'locale']);
        $this->formVersion++;
        $this->resetValidation();
    }

    private function actor(): User
    {
        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);

        return $actor;
    }
}
