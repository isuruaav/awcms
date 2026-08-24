<?php

namespace App\Livewire\Admin\Galleries;

use App\Enums\GalleryStatus;
use App\Models\Gallery;
use App\Models\User;
use App\Services\GalleryService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

final class GalleryIndex extends Component
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
            'galleries.view',
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
            'event_date',
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
        int $galleryId,
    ): void {
        Gate::authorize(
            'galleries.delete',
        );

        $gallery =
            Gallery::query()
                ->findOrFail(
                    $galleryId,
                );

        app(
            GalleryService::class,
        )->delete(
            gallery: $gallery,
            actor: $this->actor(),
        );

        session()->flash(
            'status',
            'Gallery deleted successfully.',
        );

        $this->resetPage();
    }

    public function render(): View
    {
        Gate::authorize(
            'galleries.view',
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
            Gallery::query()
                ->with([
                    'coverMedia',
                    'creator',
                    'updater',
                ])
                ->withCount(
                    'images',
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
                        );
                },
            );
        }

        $validStatuses =
            array_map(
                static fn (
                    GalleryStatus $status,
                ): string => $status->value,

                GalleryStatus::cases(),
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

        $galleries =
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
            'livewire.admin.galleries.gallery-index',
            [
                'galleries' => $galleries,

                'statuses' => GalleryStatus::cases(),

                'totalCount' => Gallery::query()
                    ->count(),

                'draftCount' => Gallery::query()
                    ->where(
                        'status',
                        GalleryStatus::Draft->value,
                    )
                    ->count(),

                'publishedCount' => Gallery::query()
                    ->where(
                        'status',
                        GalleryStatus::Published->value,
                    )
                    ->count(),

                'archivedCount' => Gallery::query()
                    ->where(
                        'status',
                        GalleryStatus::Archived->value,
                    )
                    ->count(),
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Galleries',
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
