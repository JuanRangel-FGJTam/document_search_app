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
        Route::get('/attend-request/{misplacement_id}',[RequestController::class,'attendRequest'])->name('misplacement.attend');
        Route::get('/cancel-request/{misplacement_id}',[RequestController::class,'cancelRequest'])->name('misplacement.cancel');
        Route::post('/store-cancel-request/{misplacement_id}',[RequestController::class,'storeCancelRequest'])->name('misplacement.store.cancel');

        Route::get('/accept-request/{misplacement_id}',[RequestController::class,'acceptRequest'])->name('misplacement.accept');
        Route::post('/store-accept-request/{misplacement_id}',[RequestController::class,'storeAcceptRequest'])->name('misplacement.store.accept');

    });
});
