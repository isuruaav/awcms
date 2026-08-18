<?php

namespace App\Livewire\Admin\Media;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

final class MediaIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $visibilityFilter = '';

    public string $view = 'active';

    public string $display = 'grid';

    public function mount(): void
    {
        Gate::authorize(
            'media.view',
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedVisibilityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedView(): void
    {
        if (
            ! in_array(
                $this->view,
                [
                    'active',
                    'trash',
                ],
                true,
            )
        ) {
            $this->view = 'active';
        }

        $this->resetPage();
    }

    public function setDisplay(
        string $display,
    ): void {
        if (
            ! in_array(
                $display,
                [
                    'grid',
                    'list',
                ],
                true,
            )
        ) {
            return;
        }

        $this->display = $display;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->visibilityFilter = '';

        $this->resetPage();
    }

    public function render(): View
    {
        $query = MediaAsset::query()
            ->with('uploader')
            ->latest('id');

        if ($this->view === 'trash') {
            $query->onlyTrashed();
        }

        $this->applySearch(
            $query,
        );

        $this->applyTypeFilter(
            $query,
        );

        $this->applyVisibilityFilter(
            $query,
        );

        $media = $query->paginate(
            12,
        );
        $publicUrls = [];

        foreach ($media->items() as $asset) {
            $publicUrls[(int) $asset->id] = $this->publicUrl(
                $asset,
            );
        }

        return view(
            'livewire.admin.media.media-index',
            [
                'media' => $media,

                'mediaTypes' => MediaType::cases(),

                'visibilities' => MediaVisibility::cases(),

                'publicUrls' => $publicUrls,

                'activeCount' => MediaAsset::query()
                    ->count(),

                'trashCount' => MediaAsset::onlyTrashed()
                    ->count(),
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Media Library',
            ],
        );
    }

    /**
     * @param  Builder<MediaAsset>  $query
     */
    private function applySearch(
        Builder $query,
    ): void {
        $search = trim(
            $this->search,
        );

        if ($search === '') {
            return;
        }

        $query->where(
            function (
                Builder $searchQuery,
            ) use ($search): void {
                $like = '%'.$search.'%';

                $searchQuery
                    ->where(
                        'title',
                        'like',
                        $like,
                    )
                    ->orWhere(
                        'original_name',
                        'like',
                        $like,
                    )
                    ->orWhere(
                        'alt_text',
                        'like',
                        $like,
                    )
                    ->orWhere(
                        'caption',
                        'like',
                        $like,
                    );
            },
        );
    }

    /**
     * @param  Builder<MediaAsset>  $query
     */
    private function applyTypeFilter(
        Builder $query,
    ): void {
        if ($this->typeFilter === '') {
            return;
        }

        $type = MediaType::tryFrom(
            $this->typeFilter,
        );

        if (! $type instanceof MediaType) {
            return;
        }

        $query->where(
            'type',
            $type->value,
        );
    }

    /**
     * @param  Builder<MediaAsset>  $query
     */
    private function applyVisibilityFilter(
        Builder $query,
    ): void {
        if (
            $this->visibilityFilter === ''
        ) {
            return;
        }

        $visibility =
            MediaVisibility::tryFrom(
                $this->visibilityFilter,
            );

        if (
            ! $visibility
                instanceof MediaVisibility
        ) {
            return;
        }

        $query->where(
            'visibility',
            $visibility->value,
        );
    }

    private function publicUrl(
        MediaAsset $media,
    ): ?string {
        $visibility =
            $media->getAttribute(
                'visibility',
            );

        if (
            ! $visibility
                instanceof MediaVisibility
            || $visibility
            !== MediaVisibility::Public
        ) {
            return null;
        }

        $disk = $media->getAttribute(
            'disk',
        );

        $path = $media->getAttribute(
            'path',
        );

        if (
            ! is_string($disk)
            || trim($disk) === ''
            || ! is_string($path)
            || trim($path) === ''
        ) {
            return null;
        }

        try {
            return Storage::disk(
                $disk,
            )->url(
                $path,
            );
        } catch (Throwable) {
            return null;
        }
    }
}
