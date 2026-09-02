<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Gallery;
use App\Models\HeroSlide;
use App\Models\News;
use App\Models\SiteSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;

final class PublicHomeController
{
    public function __invoke(): View
    {
        $settings = Schema::hasTable('site_settings') ? SiteSetting::query()->first() : null;
        $slides = Schema::hasTable('hero_slides')
            ? HeroSlide::query()->active()->with(['image.variants'])->get()
            : collect();

        return view('public.home', [
            'settings' => $settings,
            'slides' => $slides,
            'latestNews' => News::query()->published()->where('locale', 'en')->latest('published_at')->limit(3)->get(),
            'latestGalleries' => Gallery::query()->published()->latest('published_at')->limit(3)->get(),
            'latestDocuments' => Document::query()->published()->latest('published_at')->limit(5)->get(),
        ]);
    }
}
