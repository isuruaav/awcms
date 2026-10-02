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

final class PastCommandantController extends Controller
{
    public function __construct(
        private readonly ThemeViewResolver $themeViewResolver,
    ) {}

    public function __invoke(Request $request): View
    {
        $locale = $this->routeLocale($request);

        app()->setLocale($locale);

        $commandants = Schema::hasTable('school_leaders')
            ? $this->commandants()
            : collect();

        $languageVersions = [
            [
                'code' => 'en',
                'available' => true,
                'url' => route(
                    'history.past-commandants',
                ),
            ],
            [
                'code' => 'si',
                'available' => true,
                'url' => route(
                    'history.past-commandants.localized',
                ),
            ],
        ];

        $data = [
            'commandants' => $commandants,

            'currentLocale' => $locale,

            'languageVersions' => $languageVersions,

            'pageTitle' => $locale === 'si'
                ? 'හිටපු සේනාවිධායකවරු | '.config('app.name')
                : 'Past Commandants | '.config('app.name'),

            'metaDescription' => $locale === 'si'
                ? 'ශ්‍රී ලංකා සංඥා පාසලේ සේවය කළ හිටපු සේනාවිධායකවරු.'
                : 'Past Commandants who served the School of Signals.',
        ];

        $themeViewPath = $this->themeViewResolver->resolve(
            'history.past-commandants',
        );

        return $themeViewPath === null
            ? ViewFacade::make(
                'public.history.past-commandants',
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
    private function commandants(): Collection
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
                                    SchoolLeader::ROLE_COMMANDANT,
                                );
                            },
                        )
                        ->orWhere(
                            function (Builder $legacyQuery): void {
                                $legacyQuery
                                    ->whereNull('position_id')
                                    ->where(
                                        'role_key',
                                        SchoolLeader::ROLE_COMMANDANT,
                                    );
                            },
                        );
                },
            );
        } else {
            $query->where(
                'role_key',
                SchoolLeader::ROLE_COMMANDANT,
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
            'history.past-commandants.localized',
        )
            ? 'si'
            : 'en';
    }
}
