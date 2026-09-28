<?php

namespace App\Http\Controllers;

use App\Enums\GalleryStatus;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Services\PublicGalleryFeed;
use App\Services\GalleryLocale;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

final class PublicGalleryController extends Controller
{
    public function index(PublicGalleryFeed $feed, string $locale = 'en'): View
    {
        abort_unless(in_array($locale, ['en', 'si'], true), 404);
        app()->setLocale($locale);
        $albums = $feed->paginate($locale);

        return view('public.galleries.index', [
            'languageVersions' => GalleryLocale::versions($locale),
            'albums' => $albums,
            'galleries' => $albums,
        ]);
    }

    public function newsAlbum(int $newsId, PublicGalleryFeed $feed, ?string $locale = null): View
    {
        $news = $feed->news($locale)->whereKey($newsId)->with([
            'images' => fn ($query) => $query->whereHas('media', PublicGalleryFeed::publicImage(...)),
            'images.media.variants',
        ])->firstOrFail();

        $locale ??= (string) $news->getRawOriginal('locale');
        app()->setLocale($locale);

        return view('public.galleries.news', [
            'news' => $news,
            'languageVersions' => GalleryLocale::versions($locale, news: $news),
        ]);
    }

    public function show(
        string $slug,
        string $locale = 'en',
    ): View {
        abort_unless(in_array($locale, ['en', 'si'], true), 404);
        app()->setLocale($locale);
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
                'languageVersions' => GalleryLocale::versions($locale, $gallery->slug),
            ],
        );
    }
    public function localizedIndex(string $locale, PublicGalleryFeed $feed): View
    {
        return $this->index($feed, $locale);
    }

    public function localizedShow(string $locale, string $slug): View
    {
        return $this->show($slug, $locale);
    }

    public function localizedNews(string $locale, int $newsId, PublicGalleryFeed $feed): View
    {
        abort_unless(in_array($locale, ['en', 'si'], true), 404);

        return $this->newsAlbum($newsId, $feed, $locale);
    }

}
