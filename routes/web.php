<?php

use App\Http\Controllers\RequestController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::prefix('/dashboard')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');
    });

    Route::prefix('/admin/request')->group(function () {
        Route::get('/', [RequestController::class, 'index'])->name('misplacement.index');
        Route::get('/show/{misplacement_id}', [RequestController::class, 'show'])->name('misplacement.show');
    });
});
