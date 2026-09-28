<?php

namespace App\Services;

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\Gallery;
use App\Models\MediaAsset;
use App\Models\News;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class PublicGalleryFeed
{
    /** @param Builder<MediaAsset> $query */
    public static function publicImage(Builder $query): void
    {
        $query->where('type', MediaType::Image->value)
            ->where('visibility', MediaVisibility::Public->value);
    }

    public function imageUrl(?MediaAsset $media, bool $original = false): ?string
    {
        if ($media === null || $media->trashed() || ! $media->isImage() || ! $media->isPublic()) {
            return null;
        }

        if ($media->isExternal()) {
            $url = $media->getAttribute('external_url');

            return is_string($url) && preg_match('~^https?://~i', $url) === 1 ? $url : null;
        }

        $urls = app(MediaUrlService::class);

        return $original ? $urls->original($media) : $urls->mediumOrOriginal($media);
    }

    /** @return Builder<Gallery> */
    public function galleries(): Builder
    {
        return Gallery::query()->published()
            ->whereHas('coverMedia', self::publicImage(...))
            ->whereHas('images.media', self::publicImage(...));
    }

    /** @return Builder<News> */
    public function news(?string $locale = null): Builder
    {
        return News::query()->published()
            ->where('show_in_gallery', true)
            ->when($locale !== null, fn (Builder $query) => $query->where('locale', $locale))
            ->whereHas('images.media', self::publicImage(...));
    }

    /** @return LengthAwarePaginator<int, Gallery|News> */
    public function paginate(?string $locale = null): LengthAwarePaginator
    {
        // Paginate identifiers in SQL, then hydrate only the current page.
        // toBase() applies the models' soft-delete scopes before the UNION.
        $manual = $this->galleries()
            ->selectRaw("galleries.id as album_id, galleries.published_at as album_date, 'gallery' as album_type")
            ->toBase();
        $news = $this->news($locale)
            ->selectRaw("news.id as album_id, news.published_at as album_date, 'news' as album_type")
            ->toBase();

        $rows = DB::query()->fromSub($manual->unionAll($news), 'public_albums')
            ->orderByDesc('album_date')->orderBy('album_type')->orderByDesc('album_id')
            ->paginate(12)->withQueryString();

        $galleryIds = [];
        $newsIds = [];
        foreach ($rows as $row) {
            if ($row->album_type === 'gallery') {
                $galleryIds[] = (int) $row->album_id;
            } else {
                $newsIds[] = (int) $row->album_id;
            }
        }

        $galleries = $this->galleries()->whereKey($galleryIds)
            ->with('coverMedia.variants')->get()->keyBy('id');
        $articles = $this->news($locale)->whereKey($newsIds)->with([
            'images' => fn ($query) => $query->whereHas('media', self::publicImage(...)),
            'images.media.variants',
        ])->get()->keyBy('id');

        /** @var Collection<int, Gallery|News> $items */
        $items = collect();
        foreach ($rows as $row) {
            $album = $row->album_type === 'gallery'
                ? $galleries->get((int) $row->album_id)
                : $articles->get((int) $row->album_id);
            // Recheck eligibility when hydrating in case an operator just unpublished it.
            if ($album !== null) {
                $items->push($album);
            }
        }

        return new LengthAwarePaginator(
            $items, $rows->total(), $rows->perPage(), $rows->currentPage(),
            ['path' => $rows->path(), 'query' => request()->query()],
        );
    }
}
