<?php

namespace App\Http\Controllers;

use App\Models\PastCommandant;
use App\Models\SchoolLeader;
use App\Services\ThemeViewResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;

final class PastCommandantController extends Controller
{
    public function __construct(
        private readonly ThemeViewResolver $themeViewResolver,
    ) {}

    public function __invoke(Request $request): View
    {
        $locale = $this->routeLocale($request);
        app()->setLocale($locale);

        $currentCommandant = Schema::hasTable('school_leaders')
            ? SchoolLeader::query()
                ->where('role_key', SchoolLeader::ROLE_COMMANDANT)
                ->where('is_active', true)
                ->with('image.variants')
                ->first()
            : null;

        $pastCommandants = Schema::hasTable('past_commandants')
            ? PastCommandant::query()
                ->with('image.variants')
                ->orderByDesc('to_date')
                ->orderByDesc('from_date')
                ->get()
            : collect();

        $languageVersions = [
            [
                'code' => 'en',
                'available' => true,
                'url' => route('history.past-commandants'),
            ],
            [
                'code' => 'si',
                'available' => true,
                'url' => route('history.past-commandants.localized'),
            ],
        ];

        $data = [
            'currentCommandant' => $currentCommandant,
            'pastCommandants' => $pastCommandants,
            'currentLocale' => $locale,
            'languageVersions' => $languageVersions,
            'pageTitle' => $locale === 'si'
                ? 'හිටපු සේනාවිධායකවරු | '.config('app.name')
                : 'Past Commandants | '.config('app.name'),
            'metaDescription' => $locale === 'si'
                ? 'ශ්‍රී ලංකා සංඥා පාසලේ හිටපු සේනාවිධායකවරු.'
                : 'The Past Commandants of the School of Signals.',
        ];

        $themeViewPath = $this->themeViewResolver->resolve('history.past-commandants');

        return $themeViewPath === null
            ? ViewFacade::make('public.history.past-commandants', $data)
            : ViewFacade::file($themeViewPath, $data);
    }

    private function routeLocale(Request $request): string
    {
        $routeLocale = $request->route('locale');

        if (is_string($routeLocale) && trim($routeLocale) !== '') {
            abort_unless(in_array($routeLocale, ['en', 'si'], true), 404);

            return $routeLocale;
        }

        return $request->routeIs('history.past-commandants.localized')
            ? 'si'
            : 'en';
    }
}
