<?php

namespace App\Services;

use App\Models\News;

final class GalleryLocale
{
    public static function indexUrl(string $locale): string
    {
        return $locale === 'si' ? route('galleries.index.localized', ['locale' => 'si']) : route('galleries.index');
    }

    public static function showUrl(string $locale, string $slug): string
    {
        return $locale === 'si'
            ? route('galleries.show.localized', ['locale' => 'si', 'slug' => $slug])
            : route('galleries.show', ['slug' => $slug]);
    }

    public static function newsUrl(string $locale, int $newsId): string
    {
        return $locale === 'si'
            ? route('galleries.news.localized', ['locale' => 'si', 'newsId' => $newsId])
            : route('galleries.news', ['newsId' => $newsId]);
    }

    /** @return list<array{code: string, native_label: string, available: bool, active: bool, url: ?string}> */
    public static function versions(string $locale, ?string $slug = null, ?News $news = null): array
    {
        $versions = [];
        foreach (['en' => 'English', 'si' => 'සිංහල'] as $code => $label) {
            $url = $slug === null ? self::indexUrl($code) : self::showUrl($code, $slug);
            if ($news !== null) {
                $group = $news->getAttribute('translation_group');
                $query = app(PublicGalleryFeed::class)->news($code);
                if (is_string($group) && trim($group) !== '') {
                    $query->where('translation_group', $group);
                } else {
                    $query->whereKey((int) $news->getKey());
                }
                $translated = $query->orderBy('id')->first();
                $url = $translated === null ? null : self::newsUrl($code, (int) $translated->getKey());
            }
            $versions[] = ['code' => $code, 'native_label' => $label, 'available' => $url !== null,
                'active' => $code === $locale, 'url' => $url];
        }

        return $versions;
    }
}
