<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Str;

final class PageMeta
{
    public static function description(Page $page): string
    {
        $source = is_string($page->excerpt)
            ? $page->excerpt
            : '';

        if ($source === '') {
            $source = is_string($page->content)
                ? $page->content
                : '';
        }

        $plainText = strip_tags($source);

        $normalised = preg_replace(
            '/\s+/u',
            ' ',
            trim($plainText),
        );

        if (! is_string($normalised)) {
            $normalised = '';
        }

        if ($normalised === '') {
            $appName = config('app.name');

            $normalised = is_string($appName)
                ? $appName
                : 'Website';
        }

        return Str::limit(
            $normalised,
            160,
            '',
        );
    }
}
