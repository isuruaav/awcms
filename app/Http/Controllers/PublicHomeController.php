<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Gallery;
use App\Models\HeroSlide;
use App\Models\News;
use App\Models\SiteSetting;
use App\Services\ThemeManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;

final class PublicHomeController
{
    public function __construct(
        private readonly ThemeManager $themeManager,
    ) {}

    public function __invoke(): View
    {
        $settings = Schema::hasTable('site_settings')
            ? SiteSetting::query()->first()
            : null;

        $slides = Schema::hasTable('hero_slides')
            ? HeroSlide::query()->active()->with(['image.variants'])->get()
            : collect();

        $data = [
            'settings' => $settings,
            'slides' => $slides,
            'latestNews' => News::query()
                ->published()
                ->where('locale', 'en')
                ->with(['featuredImage.variants'])
                ->latest('published_at')
                ->limit(4)
                ->get(),
            'latestGalleries' => Gallery::query()
                ->published()
                ->with(['coverMedia.variants'])
                ->latest('published_at')
                ->limit(3)
                ->get(),
            'latestDocuments' => Document::query()
                ->published()
                ->latest('published_at')
                ->limit(5)
                ->get(),
        ];

        $themeHomePath = $this->themeHomePath();

        return $themeHomePath === null
            ? view('public.home', $data)
            : ViewFacade::file($themeHomePath, $data);
    }

    private function themeHomePath(): ?string
    {
        $activeTheme = config('awcms.active_theme');

        if (! is_string($activeTheme) || trim($activeTheme) === '') {
            return null;
        }

        $activeTheme = trim($activeTheme);

        $viewsPath = $this->themeManager->viewsPath($activeTheme);

        if ($viewsPath === null) {
            return null;
        }

        $homePath = $viewsPath.DIRECTORY_SEPARATOR.'home.blade.php';

        return is_file($homePath)
            ? $homePath
            : null;
    }
}
