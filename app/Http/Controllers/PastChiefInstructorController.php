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

final class PastChiefInstructorController extends Controller
{
    public function __construct(
        private readonly ThemeViewResolver $themeViewResolver,
    ) {}

    public function __invoke(Request $request): View
    {
        $locale = $this->routeLocale($request);

        app()->setLocale($locale);

        $chiefInstructors = Schema::hasTable('school_leaders')
            ? $this->chiefInstructors()
            : collect();

        $languageVersions = [
            [
                'code' => 'en',
                'available' => true,
                'url' => route(
                    'history.past-chief-instructors',
                ),
            ],
            [
                'code' => 'si',
                'available' => true,
                'url' => route(
                    'history.past-chief-instructors.localized',
                ),
            ],
        ];

        $data = [
            'chiefInstructors' => $chiefInstructors,

            'currentLocale' => $locale,

            'languageVersions' => $languageVersions,

            'pageTitle' => $locale === 'si'
                ? 'හිටපු ප්‍රධාන උපදේශකවරු | '.config('app.name')
                : 'Past Chief Instructors | '.config('app.name'),

            'metaDescription' => $locale === 'si'
                ? 'ශ්‍රී ලංකා සංඥා පාසලේ සේවය කළ හිටපු ප්‍රධාන උපදේශකවරු.'
                : 'Past Chief Instructors who served the School of Signals.',
        ];

        $themeViewPath = $this->themeViewResolver->resolve(
            'history.past-chief-instructors',
        );

        return $themeViewPath === null
            ? ViewFacade::make(
                'public.history.past-chief-instructors',
                $data,
            )
            : ViewFacade::file(
                $themeViewPath,
                $data,
            );
    }

    /**
     * @return Collection<int, SchoolLeader>
     */
    private function chiefInstructors(): Collection
    {
        $query = SchoolLeader::query()
            ->with([
                'position',
                'image.variants',
            ]);

        if (
            Schema::hasTable(
                'school_leadership_positions',
            )
        ) {
            $query->where(
                function (Builder $query): void {
                    $query
                        ->whereHas(
                            'position',
                            function (Builder $positionQuery): void {
                                $positionQuery->where(
                                    'key',
                                    SchoolLeader::ROLE_CHIEF_INSTRUCTOR,
                                );
                            },
                        )
                        ->orWhere(
                            function (Builder $legacyQuery): void {
                                $legacyQuery
                                    ->whereNull('position_id')
                                    ->where(
                                        'role_key',
                                        SchoolLeader::ROLE_CHIEF_INSTRUCTOR,
                                    );
                            },
                        );
                },
            );
        } else {
            $query->where(
                'role_key',
                SchoolLeader::ROLE_CHIEF_INSTRUCTOR,
            );
        }

        return $query
            ->orderByRaw(
                'CASE WHEN end_date IS NULL THEN 1 ELSE 0 END',
            )
            ->orderBy('start_date')
            ->orderBy('end_date')
            ->orderBy('id')
            ->get();
    }

    private function routeLocale(
        Request $request,
    ): string {
        $routeLocale = $request->route(
            'locale',
        );

        if (
            is_string($routeLocale)
            && trim($routeLocale) !== ''
        ) {
            $locale = strtolower(
                trim($routeLocale),
            );

            abort_unless(
                in_array(
                    $locale,
                    [
                        'en',
                        'si',
                    ],
                    true,
                ),
                404,
            );

            return $locale;
        }

        return $request->routeIs(
            'history.past-chief-instructors.localized',
        )
            ? 'si'
            : 'en';
    }
}
