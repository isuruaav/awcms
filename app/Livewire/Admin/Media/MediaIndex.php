<?php

namespace App\Livewire\Admin\Media;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Services\MediaUrlService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

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

        $this->display =
            $display;
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
        /*
         * Eager-load uploader and variants.
         *
         * Variants are required by MediaUrlService
         * so the grid can use the optimized thumbnail
         * without causing N+1 queries.
         */
        $query = MediaAsset::query()
            ->with([
                'uploader',
                'variants',
            ])
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

        /**
         * Public preview URLs keyed by media ID.
         *
         * Images:
         * thumbnail WebP -> original fallback
         *
         * Documents:
         * original public URL
         *
         * Internal / Restricted:
         * null
         *
         * @var array<int, string|null> $publicUrls
         */
        $publicUrls = [];

        $mediaUrlService = app(
            MediaUrlService::class,
        );

        foreach (
            $media->items() as $asset
        ) {
            $publicUrls[
                (int) $asset->id
            ] = $this->previewUrl(
                media: $asset,

                mediaUrlService: $mediaUrlService,
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
                $like =
                    '%'.$search.'%';

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
        if (
            $this->typeFilter === ''
        ) {
            return;
        }

        $type = MediaType::tryFrom(
            $this->typeFilter,
        );

        if (
            ! $type
                instanceof MediaType
        ) {
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

    private function previewUrl(
        MediaAsset $media,
        MediaUrlService $mediaUrlService,
    ): ?string {
        $type = $media->getAttribute(
            'type',
        );

        if (
            ! $type
                instanceof MediaType
        ) {
            return null;
        }

        /*
         * Images should use the optimized
         * 480px WebP thumbnail.
         *
         * If an old image does not yet have
         * variants, safely fall back to original.
         */
        if ($type === MediaType::Image) {
            return $mediaUrlService
                ->thumbnailOrOriginal(
                    $media,
                );
        }

        /*
         * Public documents can still use their
         * original file URL.
         */
        if ($type === MediaType::Document) {
            return $mediaUrlService
                ->original(
                    $media,
                );
        }

        return null;
    }
}
