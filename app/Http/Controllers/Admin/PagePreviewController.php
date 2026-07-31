<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\ContentSanitizer;
use App\Support\PageMeta;
use Illuminate\Contracts\View\View;

final class PagePreviewController extends Controller
{
    public function __invoke(Page $page): View
    {
        return view(
            'admin.pages.preview',
            [
                'page' => $page,

                'safeContent' => app(
                    ContentSanitizer::class,
                )->sanitize(
                    $page->content,
                ),

                'pageTitle' => sprintf(
                    'Preview: %s',
                    $page->title,
                ),

                'metaDescription' => PageMeta::description($page),

                'robots' => 'noindex,nofollow',

                'canonicalUrl' => null,
            ],
        );
    }
}
