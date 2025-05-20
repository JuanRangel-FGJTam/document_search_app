<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\{
    DashboardController,
    SearchController,
    UserController
};

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('home');
    }

    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
    ]);
});

Route::middleware([ 'auth:sanctum', config('jetstream.auth_session'), 'verified' ])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('home');

    Route::prefix('search')->group(function()
    {
        Route::get('', [SearchController::class, "search"])->name("search");
    });

    Route::prefix('/admin/usuarios')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/store', [UserController::class, 'store'])->name('users.store');
        Route::get('/edit/{user_id}', [UserController::class, 'edit'])->name('users.edit');
        Route::post('/update/{user_id}', [UserController::class, 'update'])->name('users.update');
        Route::get('/delete/{user_id}', [UserController::class, 'delete'])->name('users.delete');
        Route::get('/reintegrar/{user_id}', [UserController::class, 'refund'])->name('users.refund');
    });

});

Route::get('/register', function () {
    abort(404);
});
