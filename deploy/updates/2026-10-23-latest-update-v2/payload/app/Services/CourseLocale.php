<?php

namespace App\Services;

final class CourseLocale
{
    public static function indexUrl(string $locale): string
    {
        return $locale === 'si'
            ? route('courses.index.localized', ['locale' => 'si'])
            : route('courses.index');
    }
}
