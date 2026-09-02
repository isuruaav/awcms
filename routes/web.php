<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NewsPreviewController;
use App\Http\Controllers\Admin\PagePreviewController;
use App\Http\Controllers\PublicContactController;
use App\Http\Controllers\PublicDocumentController;
use App\Http\Controllers\PublicGalleryController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicRedirectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Home
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    PublicHomeController::class,
)->name(
    'home',
);

/*
|--------------------------------------------------------------------------
| Public Published Pages
|--------------------------------------------------------------------------
*/

Route::get(
    '/{locale}/pages/{slug}',
    PublicPageController::class,
)
    ->where(
        'locale',
        'en|si|ta',
    )
    ->where(
        'slug',
        '[a-z0-9]+(?:-[a-z0-9]+)*',
    )
    ->name(
        'pages.show.localized',
    );

/*
 * Backward-compatible English page URL. Existing menu links and bookmarks
 * continue to work while new language switching uses the locale-prefixed URL.
 */
Route::get(
    '/pages/{slug}',
    PublicPageController::class,
)
    ->where(
        'slug',
        '[a-z0-9]+(?:-[a-z0-9]+)*',
    )
    ->name(
        'pages.show',
    );

/*
|--------------------------------------------------------------------------
| Public News
|--------------------------------------------------------------------------
|
| Manual multilingual news URLs. English legacy routes remain available.
|
*/

Route::get(
    '/{locale}/news',
    [
        PublicNewsController::class,
        'index',
    ],
)
    ->where(
        'locale',
        'en|si|ta',
    )
    ->name(
        'news.index.localized',
    );

Route::get(
    '/{locale}/news/{slug}',
    [
        PublicNewsController::class,
        'show',
    ],
)
    ->where(
        'locale',
        'en|si|ta',
    )
    ->where(
        'slug',
        '[a-z0-9]+(?:-[a-z0-9]+)*',
    )
    ->name(
        'news.show.localized',
    );

Route::get(
    '/news',
    [
        PublicNewsController::class,
        'index',
    ],
)->name(
    'news.index',
);

Route::get(
    '/news/{slug}',
    [
        PublicNewsController::class,
        'show',
    ],
)
    ->where(
        'slug',
        '[a-z0-9]+(?:-[a-z0-9]+)*',
    )
    ->name(
        'news.show',
    );

/*
|--------------------------------------------------------------------------
| Public Galleries
|--------------------------------------------------------------------------
|
| These routes MUST remain outside the authenticated administration
| middleware group.
|
| PublicGalleryController is responsible for allowing only:
|
| - Published galleries
| - Galleries whose published_at is not null
| - Galleries whose published_at is now or in the past
| - Non-deleted galleries
|
*/

Route::get(
    '/galleries',
    [
        PublicGalleryController::class,
        'index',
    ],
)->name(
    'galleries.index',
);

Route::get(
    '/galleries/{slug}',
    [
        PublicGalleryController::class,
        'show',
    ],
)
    ->where(
        'slug',
        '[a-z0-9]+(?:-[a-z0-9]+)*',
    )
    ->name(
        'galleries.show',
    );

/*
|--------------------------------------------------------------------------
| Public Documents
|--------------------------------------------------------------------------
|
| These routes MUST remain outside the authenticated administration
| middleware group.
|
| PublicDocumentController is responsible for allowing only:
|
| - Published documents
| - Documents whose published_at is not null
| - Documents whose published_at is now or in the past
| - Non-deleted documents
| - Documents with a valid current public PDF version
|
*/

Route::get(
    '/documents',
    [
        PublicDocumentController::class,
        'index',
    ],
)->name(
    'documents.index',
);

Route::get(
    '/documents/{slug}/view',
    [
        PublicDocumentController::class,
        'view',
    ],
)
    ->where(
        'slug',
        '[a-z0-9]+(?:-[a-z0-9]+)*',
    )
    ->name(
        'documents.view',
    );

Route::get(
    '/documents/{slug}/download',
    [
        PublicDocumentController::class,
        'download',
    ],
)
    ->where(
        'slug',
        '[a-z0-9]+(?:-[a-z0-9]+)*',
    )
    ->name(
        'documents.download',
    );

Route::get(
    '/documents/{slug}',
    [
        PublicDocumentController::class,
        'show',
    ],
)
    ->where(
        'slug',
        '[a-z0-9]+(?:-[a-z0-9]+)*',
    )
    ->name(
        'documents.show',
    );

/*
|--------------------------------------------------------------------------
| Public Contact
|--------------------------------------------------------------------------
*/

Route::get(
    '/contact',
    [PublicContactController::class, 'create'],
)->name('contact.create');

Route::post(
    '/contact',
    [PublicContactController::class, 'store'],
)
    ->middleware('throttle:5,1')
    ->name('contact.store');

/*
|--------------------------------------------------------------------------
| Authenticated Administration
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'active',
    'verified',
])->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Default Dashboard Redirect
    |--------------------------------------------------------------------------
    |
    | Laravel authentication flows may redirect users to the
    | "dashboard" named route.
    |
    | Do not hard-code "/admin" here because the application may
    | run from a subdirectory such as /awcms/public.
    |
    */

    Route::get(
        '/dashboard',
        function () {
            return to_route(
                'admin.dashboard',
            );
        },
    )->name(
        'dashboard',
    );

    /*
    |--------------------------------------------------------------------------
    | Admin Area
    |--------------------------------------------------------------------------
    */

    Route::prefix(
        'admin',
    )
        ->name(
            'admin.',
        )
        ->middleware(
            'can:admin.access',
        )
        ->group(function (): void {

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/',
                DashboardController::class,
            )
                ->middleware(
                    'can:dashboard.view',
                )
                ->name(
                    'dashboard',
                );

            /*
            |--------------------------------------------------------------------------
            | User Management
            |--------------------------------------------------------------------------
            */

            Route::livewire(
                '/users',
                'admin.users.user-index',
            )
                ->middleware(
                    'can:users.view',
                )
                ->name(
                    'users.index',
                );

            Route::livewire(
                '/users/create',
                'admin.users.user-create',
            )
                ->middleware([
                    'can:users.create',
                    'can:users.assign-role',
                ])
                ->name(
                    'users.create',
                );

            Route::livewire(
                '/users/{user}/edit',
                'admin.users.user-edit',
            )
                ->whereNumber(
                    'user',
                )
                ->middleware(
                    'can:users.update',
                )
                ->name(
                    'users.edit',
                );

            /*
            |--------------------------------------------------------------------------
            | Pages
            |--------------------------------------------------------------------------
            */

            Route::livewire(
                '/pages',
                'admin.pages.page-index',
            )
                ->middleware(
                    'can:pages.view',
                )
                ->name(
                    'pages.index',
                );

            Route::livewire(
                '/pages/create',
                'admin.pages.page-create',
            )
                ->middleware(
                    'can:pages.create',
                )
                ->name(
                    'pages.create',
                );

            Route::livewire(
                '/pages/{pageId}/translations/{locale}/create',
                'admin.pages.page-create',
            )
                ->whereNumber(
                    'pageId',
                )
                ->where(
                    'locale',
                    'en|si|ta',
                )
                ->middleware(
                    'can:pages.create',
                )
                ->name(
                    'pages.translations.create',
                );

            Route::get(
                '/pages/{page}/preview',
                PagePreviewController::class,
            )
                ->whereNumber(
                    'page',
                )
                ->middleware(
                    'can:pages.view',
                )
                ->name(
                    'pages.preview',
                );

            Route::livewire(
                '/pages/{page}/revisions',
                'admin.pages.page-revision-history',
            )
                ->whereNumber(
                    'page',
                )
                ->middleware(
                    'can:pages.revisions.view',
                )
                ->name(
                    'pages.revisions',
                );

            Route::livewire(
                '/pages/{page}/edit',
                'admin.pages.page-edit',
            )
                ->whereNumber(
                    'page',
                )
                ->middleware(
                    'can:pages.update',
                )
                ->name(
                    'pages.edit',
                );

            /*
            |--------------------------------------------------------------------------
            | News
            |--------------------------------------------------------------------------
            */

            Route::livewire(
                '/news',
                'admin.news.news-index',
            )
                ->middleware(
                    'can:news.view',
                )
                ->name(
                    'news.index',
                );

            Route::livewire(
                '/news/create',
                'admin.news.news-create',
            )
                ->middleware(
                    'can:news.create',
                )
                ->name(
                    'news.create',
                );

            Route::livewire(
                '/news/{newsId}/translations/{locale}/create',
                'admin.news.news-create',
            )
                ->whereNumber(
                    'newsId',
                )
                ->where(
                    'locale',
                    'en|si|ta',
                )
                ->middleware(
                    'can:news.create',
                )
                ->name(
                    'news.translations.create',
                );

            Route::get(
                '/news/{news}/preview',
                NewsPreviewController::class,
            )
                ->whereNumber(
                    'news',
                )
                ->middleware(
                    'can:news.view',
                )
                ->name(
                    'news.preview',
                );

            /*
             * Keep static News routes before parameterised
             * /news/{news}/... routes.
             */

            Route::livewire(
                '/news/categories',
                'admin.news.news-category-index',
            )
                ->middleware(
                    'can:news.categories.manage',
                )
                ->name(
                    'news.categories.index',
                );

            Route::livewire(
                '/news/{news}/revisions',
                'admin.news.news-revision-history',
            )
                ->whereNumber(
                    'news',
                )
                ->middleware(
                    'can:news.view',
                )
                ->name(
                    'news.revisions',
                );

            Route::livewire(
                '/news/{news}/edit',
                'admin.news.news-edit',
            )
                ->whereNumber(
                    'news',
                )
                ->middleware([
                    'can:news.view',
                    'can:news.update',
                ])
                ->name(
                    'news.edit',
                );

            /*
            |--------------------------------------------------------------------------
            | Media Library
            |--------------------------------------------------------------------------
            */

            Route::livewire(
                '/media',
                'admin.media.media-index',
            )
                ->middleware(
                    'can:media.view',
                )
                ->name(
                    'media.index',
                );

            Route::livewire(
                '/media/upload',
                'admin.media.media-upload',
            )
                ->middleware(
                    'can:media.upload',
                )
                ->name(
                    'media.upload',
                );

            Route::livewire(
                '/media/{media}/edit',
                'admin.media.media-edit',
            )
                ->whereNumber(
                    'media',
                )
                ->middleware(
                    'can:media.view',
                )
                ->name(
                    'media.edit',
                );

            /*
            |--------------------------------------------------------------------------
            | Galleries
            |--------------------------------------------------------------------------
            */

            Route::livewire(
                '/galleries',
                'admin.galleries.gallery-index',
            )
                ->middleware(
                    'can:galleries.view',
                )
                ->name(
                    'galleries.index',
                );

            Route::livewire(
                '/galleries/create',
                'admin.galleries.gallery-create',
            )
                ->middleware(
                    'can:galleries.create',
                )
                ->name(
                    'galleries.create',
                );

            Route::livewire(
                '/galleries/{gallery}/edit',
                'admin.galleries.gallery-edit',
            )
                ->whereNumber(
                    'gallery',
                )
                ->middleware([
                    'can:galleries.view',
                    'can:galleries.update',
                ])
                ->name(
                    'galleries.edit',
                );

            /*
|--------------------------------------------------------------------------
| Documents
|--------------------------------------------------------------------------
*/

            Route::livewire(
                '/documents',
                'admin.documents.document-index',
            )
                ->middleware(
                    'can:documents.view',
                )
                ->name(
                    'documents.index',
                );

            Route::livewire(
                '/documents/create',
                'admin.documents.document-create',
            )
                ->middleware(
                    'can:documents.create',
                )
                ->name(
                    'documents.create',
                );

            Route::livewire(
                '/documents/categories',
                'admin.documents.document-category-index',
            )
                ->middleware(
                    'can:documents.categories.manage',
                )
                ->name(
                    'documents.categories.index',
                );

            Route::livewire(
                '/documents/{document}/edit',
                'admin.documents.document-edit',
            )
                ->whereNumber(
                    'document',
                )
                ->middleware([
                    'can:documents.view',
                    'can:documents.update',
                ])
                ->name(
                    'documents.edit',
                );

            /*
            |--------------------------------------------------------------------------
            | Roles & Permissions
            |--------------------------------------------------------------------------
            */

            Route::livewire(
                '/roles',
                'admin.roles.role-permission-index',
            )
                ->middleware('can:roles.manage')
                ->name('roles.index');

            /*
            |--------------------------------------------------------------------------
            | Menu Builder
            |--------------------------------------------------------------------------
            */

            Route::livewire(
                '/menus',
                'admin.menus.menu-index',
            )
                ->middleware('can:menus.manage')
                ->name('menus.index');

            /*
            |--------------------------------------------------------------------------
            | Site Settings
            |--------------------------------------------------------------------------
            */

            Route::livewire(
                '/site-settings',
                'admin.settings.site-settings-index',
            )
                ->middleware('can:settings.manage')
                ->name('site-settings.index');

            /*
            |--------------------------------------------------------------------------
            | Contact Messages
            |--------------------------------------------------------------------------
            */

            Route::livewire(
                '/contact-messages',
                'admin.contact-messages.contact-message-index',
            )
                ->middleware('can:contacts.manage')
                ->name('contact-messages.index');

            /*
            |--------------------------------------------------------------------------
            | Redirect Manager
            |--------------------------------------------------------------------------
            */

            Route::livewire(
                '/redirects',
                'admin.redirects.redirect-index',
            )
                ->middleware('can:redirects.manage')
                ->name('redirects.index');

            /*
            |--------------------------------------------------------------------------
            | Audit Logs
            |--------------------------------------------------------------------------
            */

            Route::livewire(
                '/audit-logs',
                'admin.audit-logs.audit-log-index',
            )
                ->middleware(
                    'can:audit.view',
                )
                ->name(
                    'audit-logs.index',
                );
        });
});

/*
|--------------------------------------------------------------------------
| Account / Profile Settings
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';

Route::fallback(PublicRedirectController::class);
