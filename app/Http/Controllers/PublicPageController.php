<?php

namespace App\Http\Controllers;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Services\ContentSanitizer;
use App\Services\PageBlockRenderer;
use App\Support\PageSeo;
use Illuminate\Contracts\View\View;

final class PublicPageController extends Controller
{
    public function __invoke(
        string $slug,
    ): View {
        $page = Page::query()
            ->where(
                'slug',
                $slug,
            )
            ->where(
                'status',
                PageStatus::Published->value,
            )
            ->whereNotNull(
                'published_at',
            )
            ->where(
                'published_at',
                '<=',
                now(),
            )
            ->firstOrFail();

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

        $seoTitle = PageSeo::title(
            $page,
        );

        $metaDescription =
            PageSeo::description(
                $page,
            );

        $canonicalUrl =
            PageSeo::canonicalUrl(
                $page,
            );

        $robots = PageSeo::robots(
            $page,
        );

        $ogTitle =
            PageSeo::openGraphTitle(
                $page,
            );

        $ogDescription =
            PageSeo::openGraphDescription(
                $page,
            );

        $ogImage =
            PageSeo::openGraphImage(
                $page,
            );

        return view(
            'pages.show',
            [
                'page' => $page,

                'safeContent' => $safeContent,

                'pageBlocks' => $pageBlocks,

                /*
                 * Browser / Search Engine
                 */
                'pageTitle' => $seoTitle,

                'metaDescription' => $metaDescription,

                'canonicalUrl' => $canonicalUrl,

                'robots' => $robots,

                /*
                 * Open Graph
                 */
                'ogType' => 'website',

                'ogTitle' => $ogTitle,

                'ogDescription' => $ogDescription,

                'ogUrl' => $canonicalUrl,

                'ogImage' => $ogImage,

                /*
                 * Twitter / X
                 */
                'twitterCard' => $ogImage !== null
                        ? 'summary_large_image'
                        : 'summary',

                'twitterTitle' => $ogTitle,

                'twitterDescription' => $ogDescription,

                'twitterImage' => $ogImage,

                'socialMetadata' => true,
            ],
        );
    }
}
