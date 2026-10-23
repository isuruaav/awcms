<?php

namespace App\Services;

final class PublicationNavigation
{
    public function updateHeader(string $html, string $locale): string
    {
        $url = $locale === 'si' ? '[[site_url:/si/publications]]' : '[[site_url:/publications]]';
        $label = $locale === 'si' ? 'ප්‍රකාශන' : 'Publication';
        $hasPublication = preg_match('/>\s*(?:<[^>]+>\s*)*(?:publications?|ප්‍රකාශන)\s*(?:<\/[^>]+>\s*)*</iu', $html) === 1;

        return preg_replace_callback('~<a\b([^>]*)>(.*?)</a>~isu', function (array $match) use ($url, $label, $hasPublication): string {
            $text = trim(html_entity_decode(strip_tags($match[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $isPublication = preg_match('/\Apublications?\z/iu', $text) === 1 || $text === 'ප්‍රකාශන';
            $isGallery = in_array($text, ['Gallery', 'Galleries', 'ඡායාරූප', 'ඡායාරූප ගැලරිය'], true);
            if (! $isPublication && ($hasPublication || ! $isGallery)) {
                return $match[0];
            }
            $attributes = preg_replace('~\bhref\s*=\s*([\'"]).*?\1~isu', 'href="'.$url.'"', $match[1], 1, $count) ?? $match[1];
            if ($count === 0) {
                $attributes .= ' href="'.$url.'"';
            }
            if ($isPublication) {
                return '<a'.$attributes.'>'.$match[2].'</a>';
            }
            // Preserve desktop/mobile styling, without copying a unique ID or label.
            $attributes = preg_replace('~\s+(?:id|aria-label)\s*=\s*([\'"]).*?\1~isu', '', $attributes) ?? $attributes;

            return '<a'.$attributes.'>'.$label.'</a>'.$match[0];
        }, $html) ?? $html;
    }
}
