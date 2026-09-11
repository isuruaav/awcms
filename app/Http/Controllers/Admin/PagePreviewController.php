<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\PageBlockRenderer;
use App\Services\PageHtmlSanitizer;
use App\Support\PageSeo;
use Illuminate\Contracts\View\View;

final class PagePreviewController extends Controller
{
    public function __invoke(
        Page $page,
    ): View {
        $pageLocale = $page->getRawOriginal('locale');

        if (
            is_string($pageLocale)
            && in_array($pageLocale, ['en', 'si', 'ta'], true)
        ) {
            app()->setLocale($pageLocale);
        }

        $safeContent = app(
            PageHtmlSanitizer::class,
        )->sanitize(
            is_string($page->content)
                ? $page->content
                : null,
        );

        // Existing code continues here.

        $pageBlocks = app(
            PageBlockRenderer::class,
        )->forPage(
            $page,
        );

        return view(
            'admin.pages.preview',
            [
                'page' => $page,

                'safeContent' => $safeContent,

                'pageBlocks' => $pageBlocks,

                'pageTitle' => 'Preview: '.PageSeo::title(
                    $page,
                ),

                'metaDescription' => PageSeo::description(
                    $page,
                ),

                /*
                 * Admin preview is never indexed.
                 */
                'robots' => 'noindex,nofollow',

                'canonicalUrl' => null,

                /*
                 * Administrative previews should
                 * not expose public social metadata.
                 */
                'socialMetadata' => false,
            ],
        );
    }
}
