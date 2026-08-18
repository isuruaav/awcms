<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\ContentSanitizer;
use App\Services\PageBlockRenderer;
use App\Support\PageSeo;
use Illuminate\Contracts\View\View;

final class PagePreviewController extends Controller
{
    public function __invoke(
        Page $page,
    ): View {
        $safeContent = app(
            ContentSanitizer::class,
        )->sanitize(
            is_string($page->content)
                ? $page->content
                : null,
        );

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
