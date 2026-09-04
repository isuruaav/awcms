<?php

namespace App\Providers;

use App\Services\ThemeManager;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

final class ThemeServiceProvider extends ServiceProvider
{
    public function boot(
        ThemeManager $themeManager,
    ): void {
        foreach (array_keys($themeManager->all()) as $slug) {
            $viewsPath = $themeManager->viewsPath($slug);

            if ($viewsPath === null) {
                continue;
            }

            $namespace = 'theme-'.$slug;

            View::addNamespace(
                $namespace,
                $viewsPath,
            );

            $translationsPath = dirname($viewsPath)
                .DIRECTORY_SEPARATOR
                .'lang';

            if (is_dir($translationsPath)) {
                $this->loadTranslationsFrom(
                    $translationsPath,
                    $namespace,
                );
            }
        }
    }
}
