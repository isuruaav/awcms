<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware([
    'auth',
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
        });
});

require __DIR__.'/settings.php';