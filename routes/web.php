<?php

use App\Livewire\Admin\Users\UserCreate;
use App\Livewire\Admin\Users\UserEdit;
use App\Livewire\Admin\Users\UserIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

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

            Route::get('/users', UserIndex::class)
                ->middleware('can:users.view')
                ->name('users.index');

            Route::get('/users', UserIndex::class)
                ->middleware('can:users.view')
                ->name('users.index');

            Route::get('/users/create', UserCreate::class)
                ->middleware([
                    'can:users.create',
                    'can:users.assign-role',
                ])
                ->name('users.create');

            Route::get('/users/{user}/edit', UserEdit::class)
                ->middleware('can:users.update')
                ->name('users.edit');

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
        });
});

require __DIR__.'/settings.php';
