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
        foreach ($themeManager->all() as $slug => $theme) {
            $viewsPath = $themeManager->viewsPath(
                $slug,
            );

            if ($viewsPath === null) {
                continue;
            }

            View::addNamespace(
                'theme-'.$slug,
                $viewsPath,
            );
        }
    }
}
