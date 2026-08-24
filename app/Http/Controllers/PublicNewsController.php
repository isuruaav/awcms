<?php

namespace App\Http\Controllers;

use App\Enums\MediaVariantPreset;
use App\Enums\NewsStatus;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use App\Models\News;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

final class PublicNewsController extends Controller
{
    public function index(): View
    {
        $news =
            News::query()
                ->with([
                    'category',
                    'featuredImage.variants',
                ])
                ->where(
                    'status',
                    NewsStatus::Published->value,
                )
                ->whereNotNull(
                    'published_at',
                )
                ->where(
                    'published_at',
                    '<=',
                    now(),
                )
                ->orderByDesc(
                    'is_featured',
                )
                ->orderByDesc(
                    'published_at',
                )
                ->paginate(
                    12,
                );

        return view(
            'public.news.index',
            [
                'news' => $news,
            ],
        );
    }

    public function show(
        string $slug,
    ): View {
        $news =
            News::query()
                ->with([
                    'category',
                    'featuredImage.variants',
                ])
                ->where(
                    'slug',
                    $slug,
                )
                ->where(
                    'status',
                    NewsStatus::Published->value,
                )
                ->whereNotNull(
                    'published_at',
                )
                ->where(
                    'published_at',
                    '<=',
                    now(),
                )
                ->firstOrFail();

        $relatedNews =
            News::query()
                ->with([
                    'category',
                    'featuredImage.variants',
                ])
                ->whereKeyNot(
                    $news->id,
                )
                ->where(
                    'status',
                    NewsStatus::Published->value,
                )
                ->whereNotNull(
                    'published_at',
                )
                ->where(
                    'published_at',
                    '<=',
                    now(),
                )
                ->when(
                    $news->category_id !== null,
                    fn ($query) => $query->where(
                        'category_id',
                        $news->category_id,
                    ),
                )
                ->orderByDesc(
                    'published_at',
                )
                ->limit(
                    3,
                )
                ->get();

        return view(
            'public.news.show',
            [
                'news' => $news,

                'relatedNews' => $relatedNews,

                'featuredImageUrl' => $this->imageUrl(
                    $news->featuredImage,
                ),
            ],
        );
    }

    public static function imageUrl(
        ?MediaAsset $media,
    ): ?string {
        if (
            ! $media instanceof MediaAsset
            || $media->trashed()
            || ! $media->isImage()
            || ! $media->isPublic()
        ) {
            return null;
        }

        $variant =
            $media->variants
                ->first(
                    static fn (MediaVariant $variant): bool => $variant->name ===
                        MediaVariantPreset::Medium->value,
                );

        if ($variant instanceof MediaVariant) {
            $disk =
                $variant->getAttribute(
                    'disk',
                );

            $path =
                $variant->getAttribute(
                    'path',
                );

            if (
                is_string($disk)
                && trim($disk) !== ''
                && is_string($path)
                && trim($path) !== ''
            ) {
                return Storage::disk(
                    $disk,
                )->url(
                    $path,
                );
            }
        }

        $disk =
            $media->getAttribute(
                'disk',
            );

        $path =
            $media->getAttribute(
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

        return Storage::disk(
            $disk,
        )->url(
            $path,
        );
    }
}
