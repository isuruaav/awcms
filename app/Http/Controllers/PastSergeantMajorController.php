<?php

namespace App\Http\Controllers;

use App\Models\SchoolLeader;
use App\Services\ThemeViewResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;

final class PastSergeantMajorController extends Controller
{
    public function __construct(
        private readonly ThemeViewResolver $themeViewResolver,
    ) {}

    public function __invoke(Request $request): View
    {
        $locale = $request->route('locale', 'en');
        abort_unless(is_string($locale) && in_array($locale, ['en', 'si'], true), 404);
        app()->setLocale($locale);

        $data = [
            'sergeantMajors' => Schema::hasTable('school_leaders')
                ? $this->sergeantMajors()
                : collect(),
            'currentLocale' => $locale,
            'languageVersions' => [
                [
                    'code' => 'en',
                    'available' => true,
                    'url' => route('history.past-sgt-major'),
                ],
                [
                    'code' => 'si',
                    'available' => true,
                    'url' => route('history.past-sgt-major.localized'),
                ],
            ],
            'pageTitle' => ($locale === 'si'
                ? 'හිටපු රෙජිමේන්තු සැරයන් මේජර්වරු'
                : 'Past Regimental Sergeant Majors').' | '.config('app.name'),
            'metaDescription' => $locale === 'si'
                ? 'ශ්‍රී ලංකා සංඥා පාසලේ සේවය කළ සහ වර්තමාන රෙජිමේන්තු සැරයන් මේජර්වරු.'
                : 'The Regimental Sergeant Majors who served and currently serve the School of Signals.',
        ];

        $themeViewPath = $this->themeViewResolver->resolve('history.past-sgt-major');

        return $themeViewPath === null
            ? ViewFacade::make('public.history.past-sgt-major', $data)
            : ViewFacade::file($themeViewPath, $data);
    }

    /**
     * @return Collection<int, SchoolLeader>
     */
    private function sergeantMajors(): Collection
    {
        $query = SchoolLeader::query()
            ->where('is_active', true)
            ->with(['position', 'image.variants']);

        // This appointment is stored under the existing warrant_officer key.
        if (Schema::hasTable('school_leadership_positions')) {
            $query->where(function (Builder $query): void {
                $query
                    ->whereHas('position', static function (Builder $positionQuery): void {
                        $positionQuery
                            ->where('key', SchoolLeader::ROLE_WARRANT_OFFICER)
                            ->where('is_active', true);
                    })
                    ->orWhere(static function (Builder $legacyQuery): void {
                        $legacyQuery
                            ->whereNull('position_id')
                            ->where('role_key', SchoolLeader::ROLE_WARRANT_OFFICER);
                    });
            });
        } else {
            $query->where('role_key', SchoolLeader::ROLE_WARRANT_OFFICER);
        }

        return $query
            ->orderByRaw('CASE WHEN end_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('start_date')
            ->orderBy('end_date')
            ->orderBy('id')
            ->get();
    }
}
