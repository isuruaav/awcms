<?php

namespace App\Http\Controllers;

use App\Models\PastChiefInstructor;
use App\Models\SchoolLeader;
use App\Services\ThemeViewResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;

final class PastChiefInstructorController extends Controller
{
    public function __construct(private readonly ThemeViewResolver $themeViewResolver) {}

    public function __invoke(Request $request): View
    {
        $locale = $this->routeLocale($request);
        app()->setLocale($locale);

        $pastChiefInstructors = Schema::hasTable('past_chief_instructors')
            ? PastChiefInstructor::query()->with('image.variants')->orderByDesc('to_date')->orderByDesc('from_date')->get()
            : collect();

        $currentChiefInstructor = Schema::hasTable('school_leaders')
            ? SchoolLeader::query()
                ->where('role_key', SchoolLeader::ROLE_CHIEF_INSTRUCTOR)
                ->where('is_active', true)
                ->with('image.variants')
                ->first()
            : null;

        $data = [
            'currentChiefInstructor' => $currentChiefInstructor,
            'pastChiefInstructors' => $pastChiefInstructors,
            'currentLocale' => $locale,
            'languageVersions' => [
                ['code' => 'en', 'available' => true, 'url' => route('history.past-chief-instructors')],
                ['code' => 'si', 'available' => true, 'url' => route('history.past-chief-instructors.localized')],
            ],
            'pageTitle' => $locale === 'si'
                ? 'හිටපු ප්‍රධාන උපදේශකවරු | '.config('app.name')
                : 'Past Chief Instructors | '.config('app.name'),
            'metaDescription' => $locale === 'si'
                ? 'ශ්‍රී ලංකා සංඥා පාසලේ හිටපු ප්‍රධාන උපදේශකවරු.'
                : 'The Past Chief Instructors of the School of Signals.',
        ];

        $themeViewPath = $this->themeViewResolver->resolve('history.past-chief-instructors');

        return $themeViewPath === null
            ? ViewFacade::make('public.history.past-chief-instructors', $data)
            : ViewFacade::file($themeViewPath, $data);
    }

    private function routeLocale(Request $request): string
    {
        $routeLocale = $request->route('locale');

        if (is_string($routeLocale) && trim($routeLocale) !== '') {
            abort_unless(in_array($routeLocale, ['en', 'si'], true), 404);

            return $routeLocale;
        }

        return $request->routeIs('history.past-chief-instructors.localized') ? 'si' : 'en';
    }
}
