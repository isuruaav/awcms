<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
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

                'pageTitle' => sprintf(
                    'Preview: %s',
                    $page->title,
                ),

                'metaDescription' => PageMeta::description($page),

                /*
                 * Search engines must never index
                 * administrative previews.
                 */
                'robots' => 'noindex,nofollow',

                'canonicalUrl' => null,
            ],
        );
    }
}
