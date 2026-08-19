<?php

use App\Http\Controllers\Admin\PagePreviewController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')
    ->name('home');

/*
 * Public published pages
 */
Route::get(
    '/pages/{slug}',
    PublicPageController::class,
)
    ->where(
        'slug',
        '[a-z0-9]+(?:-[a-z0-9]+)*',
    )
    ->name('pages.show');

/*
 * Authenticated administration
 */
Route::middleware([
    'auth',
    'active',
    'verified',
])->group(function (): void {
    Route::redirect('/dashboard', '/admin')
        ->name('dashboard');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('can:admin.access')
        ->group(function (): void {
            Route::view('/', 'admin.dashboard')
                ->name('dashboard');

            /*
             * User Management
             */
            Route::livewire(
                '/users',
                'admin.users.user-index',
            )
                ->middleware('can:users.view')
                ->name('users.index');

            Route::livewire(
                '/users/create',
                'admin.users.user-create',
            )
                ->middleware([
                    'can:users.create',
                    'can:users.assign-role',
                ])
                ->name('users.create');

            Route::livewire(
                '/users/{user}/edit',
                'admin.users.user-edit',
            )
                ->middleware('can:users.update')
                ->name('users.edit');

            /*
             * Pages
             */
            Route::livewire(
                '/pages',
                'admin.pages.page-index',
            )
                ->middleware('can:pages.view')
                ->name('pages.index');

            Route::livewire(
                '/pages/create',
                'admin.pages.page-create',
            )
                ->middleware('can:pages.create')
                ->name('pages.create');

            Route::get(
                '/pages/{page}/preview',
                PagePreviewController::class,
            )
                ->whereNumber('page')
                ->middleware('can:pages.view')
                ->name('pages.preview');

            Route::livewire(
                '/pages/{page}/revisions',
                'admin.pages.page-revision-history',
            )
                ->whereNumber('page')
                ->middleware('can:pages.revisions.view')
                ->name('pages.revisions');

            Route::livewire(
                '/pages/{page}/edit',
                'admin.pages.page-edit',
            )
                ->whereNumber('page')
                ->middleware('can:pages.update')
                ->name('pages.edit');

            /*
 * News
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
                '/news/{news}/edit',
                'admin.news.news-edit',
            )
                ->whereNumber(
                    'news',
                )
                ->middleware(
                    'can:news.update',
                )
                ->name(
                    'news.edit',
                );

            Route::livewire(
                '/news/{news}/revisions',
                'admin.news.news-revision-history',
            )
                ->whereNumber('news')
                ->middleware('can:news.view')
                ->name('news.revisions');
            /*
             * Audit Logs
             */
            Route::livewire(
                '/audit-logs',
                'admin.audit-logs.audit-log-index',
            )
                ->middleware('can:audit.view')
                ->name('audit-logs.index');

            /*
            * Media
             */

            Route::livewire(
                '/media',
                'admin.media.media-index',
            )
                ->middleware('can:media.view')
                ->name('media.index');

            Route::livewire(
                '/media/upload',
                'admin.media.media-upload',
            )
                ->middleware('can:media.upload')
                ->name('media.upload');

            Route::livewire(
                '/media/{media}/edit',
                'admin.media.media-edit',
            )
                ->whereNumber('media')
                ->middleware('can:media.view')
                ->name('media.edit');
        });
});

require __DIR__.'/settings.php';
