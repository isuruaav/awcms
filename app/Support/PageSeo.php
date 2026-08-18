<?php

namespace App\Support;

use App\Models\Page;
use App\Services\ContentSanitizer;

final class PageSeo
{
    public static function title(
        Page $page,
    ): string {
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

    public static function description(
        Page $page,
    ): string {
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

        $content = self::plain(
            $page->content,
            160,
        );

        if ($content !== '') {
            return $content;
        }

        $appName = config(
            'app.name',
        );

        return is_string($appName)
            ? self::plain(
                $appName,
                160,
            )
            : 'Website';
    }

    public static function canonicalUrl(
        Page $page,
    ): string {
        $canonicalUrl = self::safeUrl(
            $page->canonical_url,
        );

        if ($canonicalUrl !== null) {
            return $canonicalUrl;
        }

        return route(
            'pages.show',
            $page->slug,
        );
    }

    public static function robots(
        Page $page,
    ): string {
        return (bool) $page->robots_index
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

        if ($ogTitle !== '') {
            return $ogTitle;
        }

        return self::title(
            $page,
        );
    }

    public static function openGraphDescription(
        Page $page,
    ): string {
        $ogDescription = self::plain(
            $page->og_description,
            200,
        );

        if ($ogDescription !== '') {
            return $ogDescription;
        }

        return self::description(
            $page,
        );
    }

    public static function openGraphImage(
        Page $page,
    ): ?string {
        return self::safeUrl(
            $page->og_image,
        );
    }

    /**
     * Convert potentially unsafe HTML into safe plain text.
     *
     * We intentionally sanitize before converting to plain
     * text so forbidden elements such as script, iframe,
     * object and embed cannot leak unsafe payload text into
     * public metadata.
     */
    private static function plain(
        mixed $value,
        int $maximumLength,
    ): string {
        if (! is_string($value)) {
            return '';
        }

        $value = trim(
            $value,
        );

        if ($value === '') {
            return '';
        }

        $sanitizer = app(
            ContentSanitizer::class,
        );

        /*
         * First run the value through the HTML purifier.
         * This removes forbidden active HTML before the
         * remaining safe markup is converted to text.
         */
        $safeHtml = $sanitizer->sanitize(
            $value,
        );

        if ($safeHtml === '') {
            return '';
        }

        return $sanitizer->plainText(
            $safeHtml,
            $maximumLength,
        );
    }

    /**
     * Only HTTP and HTTPS URLs are allowed in public
     * SEO metadata.
     */
    private static function safeUrl(
        mixed $value,
    ): ?string {
        if (! is_string($value)) {
            return null;
        }

        $url = trim(
            $value,
        );

        if ($url === '') {
            return null;
        }

        if (
            filter_var(
                $url,
                FILTER_VALIDATE_URL,
            ) === false
        ) {
            return null;
        }

        $scheme = parse_url(
            $url,
            PHP_URL_SCHEME,
        );

        if (! is_string($scheme)) {
            return null;
        }

        $scheme = strtolower(
            $scheme,
        );

        if (
            $scheme !== 'http'
            && $scheme !== 'https'
        ) {
            return null;
        }

        return $url;
    }
}
