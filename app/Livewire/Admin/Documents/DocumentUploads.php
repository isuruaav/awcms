<?php

namespace App\Livewire\Admin\Documents;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\Document;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\DocumentUploadManager;
use App\Services\MediaUploadService;
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

final class DocumentUploads extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Locked]
    public ?int $editingId = null;

    public string $title = '';

    public string $titleSi = '';

    public string $search = '';

    public mixed $englishFile = null;

    public mixed $sinhalaFile = null;

    public function mount(?Document $document = null): void
    {
        Gate::authorize('documents.view');

        if ($document !== null && $document->exists) {
            $this->edit((int) $document->getKey());
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        Gate::authorize('documents.create');

        $this->clearForm();
    }

    public function edit(int $id): void
    {
        Gate::authorize('documents.view');
        Gate::authorize('documents.update');

        $document = Document::query()->whereKey($id)->firstOrFail();

        $this->editingId = (int) $document->getKey();
        $this->title = $document->titleForLocale('en');

        $titleSi = $document->getAttribute('title_si');
        $this->titleSi = is_string($titleSi) ? $titleSi : '';

        $this->englishFile = null;
        $this->sinhalaFile = null;
        $this->resetValidation();
    }

    public function cancel(): void
    {
        Gate::authorize('documents.view');

        $this->clearForm();
    }

    public function save(): void
    {
        Gate::authorize('documents.view');
        Gate::authorize(
            $this->editingId === null
                ? 'documents.create'
                : 'documents.update',
        );
        Gate::authorize('documents.publish');

        $this->title = trim($this->title);
        $this->titleSi = trim($this->titleSi);

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'titleSi' => ['nullable', 'string', 'max:255'],
            'englishFile' => [
                'nullable',
                'file',
                'extensions:pdf,doc,docx,xls,xlsx',
                'max:10240',
            ],
            'sinhalaFile' => [
                'nullable',
                'file',
                'extensions:pdf,doc,docx,xls,xlsx',
                'max:10240',
            ],
        ]);

        if (
            $this->editingId === null
            && ! $this->englishFile instanceof TemporaryUploadedFile
            && ! $this->sinhalaFile instanceof TemporaryUploadedFile
        ) {
            throw ValidationException::withMessages([
                'englishFile' => 'Upload at least one document file.',
            ]);
        }

        $actor = $this->actor();

        $english = $this->upload($this->englishFile, 'englishFile', $actor);
        $sinhala = $this->upload($this->sinhalaFile, 'sinhalaFile', $actor);

        app(DocumentUploadManager::class)->save(
            actor: $actor,
            documentId: $this->editingId,
            title: $this->title,
            titleSi: $this->titleSi,
            englishFile: $english,
            sinhalaFile: $sinhala,
        );

        $this->clearForm();
        $this->resetPage();

        session()->flash('status', 'Document saved and published successfully.');
    }

    public function delete(int $id): void
    {
        Gate::authorize('documents.view');

        app(DocumentUploadManager::class)->delete($id, $this->actor());

        if ($this->editingId === $id) {
            $this->clearForm();
        }

        $this->resetPage();

        session()->flash('status', 'Document deleted successfully.');
    }

    public function render(): View
    {
        Gate::authorize('documents.view');

        $search = mb_substr(trim($this->search), 0, 255);

        $documents = Document::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $titles) use ($search): void {
                    $titles->where('title', 'like', '%'.$search.'%')
                        ->orWhere('title_si', 'like', '%'.$search.'%');
                });
            })
            ->latest('updated_at')
            ->orderByDesc('id')
            ->paginate(12);

        return view('livewire.admin.documents.document-uploads', [
            'documents' => $documents,
        ])->layout('components.layouts.admin', [
            'title' => 'Document Uploads',
        ]);
    }

    private function upload(
        mixed $file,
        string $field,
        User $actor,
    ): ?MediaAsset {
        if (! $file instanceof TemporaryUploadedFile) {
            return null;
        }

        Gate::authorize('media.upload');

        try {
            return app(MediaUploadService::class)->upload(
                file: $file,
                type: MediaType::Document,
                visibility: MediaVisibility::Public,
                actor: $actor,
                title: $field === 'sinhalaFile' && $this->titleSi !== ''
                    ? $this->titleSi
                    : $this->title,
            );
        } catch (ValidationException $exception) {
            throw ValidationException::withMessages([
                $field => collect($exception->errors())
                    ->flatten()
                    ->implode(' '),
            ]);
        }
    }

    private function clearForm(): void
    {
        $this->reset([
            'editingId',
            'title',
            'titleSi',
            'englishFile',
            'sinhalaFile',
        ]);

        $this->resetValidation();
    }

    private function actor(): User
    {
        $actor = Auth::user();

        abort_unless($actor instanceof User, 403);

        return $actor;
    }
}
