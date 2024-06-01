<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Middleware\Authenticate;
use App\Http\Controllers\Auth\LoginController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [LoginController::class, 'redirectToProvider']);
Route::get('/auth/redirect', [LoginController::class, 'redirectToProvider']);
Route::get('/auth/callback', [LoginController::class, 'handleProviderCallback']);

Route::prefix('dashboard')->middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return 'HOLA MUNDO';
    });
});