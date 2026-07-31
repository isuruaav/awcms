<?php

namespace App\Http\Controllers;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Services\ContentSanitizer;
use App\Support\PageMeta;
use Illuminate\Contracts\View\View;

final class PublicPageController extends Controller
{
    public function __invoke(string $slug): View
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where(
                'status',
                PageStatus::Published->value,
            )
            ->whereNotNull('published_at')
            ->where(
                'published_at',
                '<=',
                now(),
            )
            ->firstOrFail();

        return view(
            'pages.show',
            [
                'page' => $page,

                'safeContent' => app(
                    ContentSanitizer::class,
                )->sanitize(
                    $page->content,
                ),

                'pageTitle' => sprintf(
                    '%s | %s',
                    $page->title,
                    config('app.name'),
                ),

                'metaDescription' => PageMeta::description($page),

                'robots' => 'index,follow',

                'canonicalUrl' => route(
                    'pages.show',
                    $page->slug,
                ),
            ],
        );
    }
}
