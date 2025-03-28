<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    Route::prefix('/admin/request')->group(function () {
        Route::get('/', [RequestController::class, 'index'])->name('misplacement.index');
        Route::get('/show/{misplacement_id}', [RequestController::class, 'show'])->name('misplacement.show');
        Route::get('/attend-request/{misplacement_id}', [RequestController::class, 'attendRequest'])->name('misplacement.attend');
        Route::get('/cancel-request/{misplacement_id}', [RequestController::class, 'cancelRequest'])->name('misplacement.cancel');
        Route::post('/store-cancel-request/{misplacement_id}', [RequestController::class, 'storeCancelRequest'])->name('misplacement.store.cancel');

        Route::get('/accept-request/{misplacement_id}', [RequestController::class, 'acceptRequest'])->name('misplacement.accept');
        Route::post('/store-accept-request/{misplacement_id}', [RequestController::class, 'storeAcceptRequest'])->name('misplacement.store.accept');

        Route::get('/resend-request/{misplacement_id}', [RequestController::class, 'reSendDocument'])->name('misplacement.reSendDocument');

    });

    Route::prefix('/admin/reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/createByYear', [ReportController::class, 'createByYear'])->name('reports.createByYear');
        Route::post('/getByYear', [ReportController::class, 'getByYear'])->name('reports.getByYear');

        Route::get('/create/surveys', [ReportController::class, 'createSurveys'])->name('reports.createSurveys');
        Route::post('/getSurveys', [ReportController::class, 'getSurveys'])->name('reports.getSurveys');

        Route::post('/generate', [ReportController::class, 'generateReport'])->name('reports.generate');


    });

    Route::prefix('/admin/surveys')->group(function () {
        Route::get('/', [SurveyController::class, 'index'])->name('surveys.index');
        Route::get('/show/{survey_id}', [SurveyController::class, 'show'])->name('surveys.show');
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

