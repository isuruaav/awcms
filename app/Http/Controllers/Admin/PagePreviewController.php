<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\ContentSanitizer;
use App\Support\PageSeo;
use Illuminate\Contracts\View\View;

final class PagePreviewController extends Controller
{
    public function __invoke(
        Page $page,
    ): View {
        return view(
            'admin.pages.preview',
            [
                'page' => $page,

                'safeContent' => app(
                    ContentSanitizer::class,
                )->sanitize(
                    is_string($page->content)
                        ? $page->content
                        : null,
                ),

                'pageTitle' => 'Preview: '.PageSeo::title(
                    $page,
                ),

                'metaDescription' => PageSeo::description(
                    $page,
                ),

                /*
                 * Admin preview must never be indexed.
                 */
                'robots' => 'noindex,nofollow',

                'canonicalUrl' => null,

                /*
                 * Do not emit public social metadata
                 * for administrative previews.
                 */
                'socialMetadata' => false,
            ],
        );
    }
}
