<?php

namespace App\Services;

final class ThemeViewResolver
{
    public function __construct(
        private readonly ThemeManager $themeManager,
    ) {}

    /**
     * Resolve a view inside the configured active theme to a validated file.
     *
     * Examples: home, pages.show, news.index.
     */
    public function resolve(string $view): ?string
    {
        $activeTheme = config('awcms.active_theme');

        if (! is_string($activeTheme) || trim($activeTheme) === '') {
            return null;
        }

        if (preg_match('/\A[a-z0-9]+(?:[._-][a-z0-9]+)*\z/', $view) !== 1) {
            return null;
        }

        $viewsPath = $this->themeManager->viewsPath(trim($activeTheme));

        if ($viewsPath === null) {
            return null;
        }

        $relativePath = str_replace('.', DIRECTORY_SEPARATOR, $view).'.blade.php';
        $candidate = $viewsPath.DIRECTORY_SEPARATOR.$relativePath;
        $resolvedViewsPath = realpath($viewsPath);
        $resolvedCandidate = realpath($candidate);

        if ($resolvedViewsPath === false || $resolvedCandidate === false) {
            return null;
        }

        $viewsPrefix = rtrim($resolvedViewsPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if (! str_starts_with($resolvedCandidate, $viewsPrefix)) {
            return null;
        }

        return is_file($resolvedCandidate)
            ? $resolvedCandidate
            : null;
    }
}
