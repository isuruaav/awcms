<?php

namespace App\Services;

final class CourseNavigation
{
    public function updateHeader(string $html, string $locale): string
    {
        $url = $locale === 'si' ? '[[site_url:/si/courses]]' : '[[site_url:/courses]]';
        $label = $locale === 'si' ? 'පාඨමාලා' : 'Courses';
        $hasCourses = preg_match('/>\s*(?:<[^>]+>\s*)*(?:courses|පාඨමාලා)\s*(?:<\/[^>]+>\s*)*</iu', $html) === 1;

        return preg_replace_callback('~<a\b([^>]*)>(.*?)</a>~isu', function (array $match) use ($url, $label, $hasCourses): string {
            $text = trim(html_entity_decode(strip_tags($match[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $isCourse = preg_match('/\Acourses\z/iu', $text) === 1 || $text === 'පාඨමාලා';
            $isGallery = in_array($text, ['Gallery', 'Galleries', 'ඡායාරූප', 'ඡායාරූප ගැලරිය'], true);
            if (! $isCourse && ($hasCourses || ! $isGallery)) {
                return $match[0];
            }

            $attributes = preg_replace('~\bhref\s*=\s*([\'"]).*?\1~isu', 'href="'.$url.'"', $match[1], 1, $count) ?? $match[1];
            if ($count === 0) {
                $attributes .= ' href="'.$url.'"';
            }
            if ($isCourse) {
                return '<a'.$attributes.'>'.$match[2].'</a>';
            }

            $attributes = preg_replace('~\s+(?:id|aria-label)\s*=\s*([\'"]).*?\1~isu', '', $attributes) ?? $attributes;

            return '<a'.$attributes.'>'.$label.'</a>'.$match[0];
        }, $html) ?? $html;
    }
}
