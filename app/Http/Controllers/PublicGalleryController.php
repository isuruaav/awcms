<?php

namespace App\Http\Controllers;

use App\Enums\GalleryStatus;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\Gallery;
use App\Models\GalleryImage;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

final class PublicGalleryController extends Controller
{
    public function index(): View
    {
        $galleries =
            Gallery::query()
                ->where(
                    'status',
                    GalleryStatus::Published->value,
                )
                ->whereNotNull(
                    'published_at',
                )
                ->where(
                    'published_at',
                    '<=',
                    now(),
                )
                ->whereHas(
                    'coverMedia',
                    static function (
                        Builder $query,
                    ): void {
                        $query
                            ->where(
                                'type',
                                MediaType::Image->value,
                            )
                            ->where(
                                'visibility',
                                MediaVisibility::Public->value,
                            );
                    },
                )
                ->whereHas(
                    'images',
                    static function (
                        Builder $query,
                    ): void {
                        $query->whereHas(
                            'media',
                            static function (
                                Builder $mediaQuery,
                            ): void {
                                $mediaQuery
                                    ->where(
                                        'type',
                                        MediaType::Image->value,
                                    )
                                    ->where(
                                        'visibility',
                                        MediaVisibility::Public->value,
                                    );
                            },
                        );
                    },
                )
                ->with([
                    'coverMedia.variants',
                ])
                ->orderByDesc(
                    'published_at',
                )
                ->orderByDesc(
                    'id',
                )
                ->paginate(
                    12,
                )
                ->withQueryString();

        return view(
            'public.galleries.index',
            [
                'galleries' => $galleries,
            ],
        );
    }

    public function show(
        string $slug,
    ): View {
        $gallery =
            Gallery::query()
                ->where(
                    'status',
                    GalleryStatus::Published->value,
                )
                ->whereNotNull(
                    'published_at',
                )
                ->where(
                    'published_at',
                    '<=',
                    now(),
                )
                ->where(
                    'slug',
                    $slug,
                )
                ->whereHas(
                    'coverMedia',
                    static function (
                        Builder $query,
                    ): void {
                        $query
                            ->where(
                                'type',
                                MediaType::Image->value,
                            )
                            ->where(
                                'visibility',
                                MediaVisibility::Public->value,
                            );
                    },
                )
                ->whereHas(
                    'images',
                    static function (
                        Builder $query,
                    ): void {
                        $query->whereHas(
                            'media',
                            static function (
                                Builder $mediaQuery,
                            ): void {
                                $mediaQuery
                                    ->where(
                                        'type',
                                        MediaType::Image->value,
                                    )
                                    ->where(
                                        'visibility',
                                        MediaVisibility::Public->value,
                                    );
                            },
                        );
                    },
                )
                ->with([
                    'coverMedia.variants',
                ])
                ->firstOrFail();

        $images =
            GalleryImage::query()
                ->where(
                    'gallery_id',
                    (int) $gallery->getKey(),
                )
                ->whereHas(
                    'media',
                    static function (
                        Builder $query,
                    ): void {
                        $query
                            ->where(
                                'type',
                                MediaType::Image->value,
                            )
                            ->where(
                                'visibility',
                                MediaVisibility::Public->value,
                            );
                    },
                )
                ->with([
                    'media.variants',
                ])
                ->orderBy(
                    'sort_order',
                )
                ->orderBy(
                    'id',
                )
                ->get();

        $gallery->setRelation(
            'images',
            $images,
        );

        return view(
            'public.galleries.show',
            [
                'gallery' => $gallery,
            ],
        );
    }
}
