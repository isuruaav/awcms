<?php

namespace App\Support;

use App\Models\Page;
use App\Services\ContentSanitizer;

final class PageSeo
{
    public static function title(Page $page): string
    {
        $seoTitle = self::plain(
            $page->seo_title,
            70,
        );

        if ($seoTitle !== '') {
            return $seoTitle;
        }

        return self::plain(
            $page->title,
            70,
        );
    }

    public static function description(Page $page): string
    {
        $metaDescription = self::plain(
            $page->meta_description,
            160,
        );

        if ($metaDescription !== '') {
            return $metaDescription;
        }

        $excerpt = self::plain(
            $page->excerpt,
            160,
        );

        if ($excerpt !== '') {
            return $excerpt;
        }

        $content = app(
            ContentSanitizer::class,
        )->plainText(
            is_string($page->content)
                ? $page->content
                : null,
            160,
        );

        if ($content !== '') {
            return $content;
        }

        $appName = config('app.name');

        return is_string($appName)
            ? $appName
            : 'Website';
    }

    public static function canonicalUrl(
        Page $page,
    ): string {
        $canonicalUrl = is_string(
            $page->canonical_url,
        )
            ? trim($page->canonical_url)
            : '';

        if ($canonicalUrl !== '') {
            return $canonicalUrl;
        }

        return route(
            'pages.show',
            $page->slug,
        );
    }

    public static function robots(Page $page): string
    {
        return $page->robots_index
            ? 'index,follow'
            : 'noindex,nofollow';
    }

    public static function openGraphTitle(
        Page $page,
    ): string {
        $ogTitle = self::plain(
            $page->og_title,
            95,
        );

        return $ogTitle !== ''
            ? $ogTitle
            : self::title($page);
    }

    public static function openGraphDescription(
        Page $page,
    ): string {
        $ogDescription = self::plain(
            $page->og_description,
            200,
        );

        return $ogDescription !== ''
            ? $ogDescription
            : self::description($page);
    }

    public static function openGraphImage(
        Page $page,
    ): ?string {
        if (! is_string($page->og_image)) {
            return null;
        }

        $image = trim($page->og_image);

        return $image !== ''
            ? $image
            : null;
    }

    private static function plain(
        mixed $value,
        int $maximumLength,
    ): string {
        return app(
            ContentSanitizer::class,
        )->plainText(
            is_string($value)
                ? $value
                : null,
            $maximumLength,
        );
    }
}
