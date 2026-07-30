<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Str;

final class PageSlugger
{
    public static function unique(
        string $value,
        ?int $ignorePageId = null,
    ): string {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'page';
        }

        /*
         * Reserve space for suffixes such as "-2" and "-100".
         */
        $baseSlug = Str::limit(
            $baseSlug,
            240,
            '',
        );

        $slug = $baseSlug;
        $suffix = 2;

        while (
            self::slugExists(
                $slug,
                $ignorePageId,
            )
        ) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private static function slugExists(
        string $slug,
        ?int $ignorePageId,
    ): bool {
        $query = Page::withTrashed()
            ->where('slug', $slug);

        if ($ignorePageId !== null) {
            $query->where(
                'id',
                '!=',
                $ignorePageId,
            );
        }

        return $query->exists();
    }
}
