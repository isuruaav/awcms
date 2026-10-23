<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Contracts\View\View;

final class PublicCourseController extends Controller
{
    public function index(string $locale = 'en'): View
    {
        abort_unless(in_array($locale, ['en', 'si'], true), 404);
        app()->setLocale($locale);

        return view('public.courses.index', [
            'courses' => Course::query()
                ->where('is_active', true)
                ->with('qualifications')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'currentLocale' => $locale,
            'languageVersions' => [
                ['code' => 'en', 'available' => true, 'url' => route('courses.index')],
                ['code' => 'si', 'available' => true, 'url' => route('courses.index.localized', ['locale' => 'si'])],
            ],
        ]);
    }
}
