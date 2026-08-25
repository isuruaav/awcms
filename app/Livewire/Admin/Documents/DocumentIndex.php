<?php

namespace App\Livewire\Admin\Documents;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

final class DocumentIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public int $perPage = 15;

    public string $sortField = 'updated_at';

    /**
     * @var 'asc'|'desc'
     */
    public string $sortDirection = 'desc';

    public function mount(): void
    {
        Gate::authorize(
            'documents.view',
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        if (
            ! in_array(
                $this->perPage,
                [
                    10,
                    15,
                    25,
                    50,
                ],
                true,
            )
        ) {
            $this->perPage = 15;
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';

        $this->statusFilter = '';

        $this->perPage = 15;

        $this->sortField =
            'updated_at';

        $this->sortDirection =
            'desc';

        $this->resetPage();
    }

    public function sort(
        string $field,
    ): void {
        $allowedFields = [
            'title',
            'status',
            'document_date',
            'current_version',
            'published_at',
            'updated_at',
        ];

        if (
            ! in_array(
                $field,
                $allowedFields,
                true,
            )
        ) {
            return;
        }

        if (
            $this->sortField ===
            $field
        ) {
            $this->sortDirection =
                $this->sortDirection === 'asc'
                    ? 'desc'
                    : 'asc';

            return;
        }

        $this->sortField =
            $field;

        $this->sortDirection =
            'asc';

        $this->resetPage();
    }

    public function delete(
        int $documentId,
    ): void {
        Gate::authorize(
            'documents.delete',
        );

        $document =
            Document::query()
                ->findOrFail(
                    $documentId,
                );

        app(
            DocumentService::class,
        )->delete(
            document: $document,
            actor: $this->actor(),
        );

        session()->flash(
            'status',
            'Document deleted successfully.',
        );

        $this->resetPage();
    }

    public function render(): View
    {
        Gate::authorize(
            'documents.view',
        );

        $search =
            trim(
                $this->search,
            );

        $statusFilter =
            trim(
                $this->statusFilter,
            );

        $query =
            Document::query()
                ->with([
                    'category',
                    'creator',
                    'updater',
                ])
                ->withCount(
                    'versions',
                );

        if ($search !== '') {
            $query->where(
                static function (
                    Builder $query,
                ) use ($search): void {
                    $query
                        ->where(
                            'title',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'slug',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'description',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhereHas(
                            'category',
                            static function (
                                Builder $categoryQuery,
                            ) use ($search): void {
                                $categoryQuery->where(
                                    'name',
                                    'like',
                                    '%'.$search.'%',
                                );
                            },
                        );
                },
            );
        }

        $validStatuses =
            array_map(
                static fn (
                    DocumentStatus $status,
                ): string => $status->value,

                DocumentStatus::cases(),
            );

        if (
            $statusFilter !== ''
            && in_array(
                $statusFilter,
                $validStatuses,
                true,
            )
        ) {
            $query->where(
                'status',
                $statusFilter,
            );
        }

        $documents =
            $query
                ->orderBy(
                    $this->sortField,
                    $this->direction(),
                )
                ->orderByDesc(
                    'id',
                )
                ->paginate(
                    $this->perPage,
                );

        return view(
            'livewire.admin.documents.document-index',
            [
                'documents' => $documents,

                'statuses' => DocumentStatus::cases(),

                'totalCount' => Document::query()
                    ->count(),

                'draftCount' => Document::query()
                    ->where(
                        'status',
                        DocumentStatus::Draft->value,
                    )
                    ->count(),

                'publishedCount' => Document::query()
                    ->where(
                        'status',
                        DocumentStatus::Published->value,
                    )
                    ->count(),

                'archivedCount' => Document::query()
                    ->where(
                        'status',
                        DocumentStatus::Archived->value,
                    )
                    ->count(),
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Documents',
            ],
        );
    }

    /**
     * @return 'asc'|'desc'
     */
    private function direction(): string
    {
        return $this->sortDirection === 'asc'
            ? 'asc'
            : 'desc';
    }

    private function actor(): User
    {
        $actor =
            Auth::user();

        abort_unless(
            $actor instanceof User,
            403,
        );

        return $actor;
    }
}
