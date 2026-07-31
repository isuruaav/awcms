<?php

namespace App\Support;

use App\Models\Page;
use App\Services\ContentSanitizer;

final class PageMeta
{
    public static function description(Page $page): string
    {
        $source = is_string($page->excerpt)
            && $page->excerpt !== ''
                ? $page->excerpt
                : $page->content;

        $description = app(
            ContentSanitizer::class,
        )->plainText(
            is_string($source)
                ? $source
                : null,
            160,
        );

        if ($description !== '') {
            return $description;
        }

        $appName = config('app.name');

        return is_string($appName)
            ? $appName
            : 'Website';
    }
}
