<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleBrandController;
use App\Http\Controllers\VehicleModelController;
use App\Http\Controllers\VehicleTypeController;
use App\Models\Vehicle;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

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

    Route::prefix('/admin/catalogos')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Catalogs/Index');
        })->name('catalogs.index');

        Route::prefix('/vehiculos-marcas')->group(function () {
            Route::get('/', [VehicleBrandController::class, 'index'])->name('vehicleBrand.index');
            Route::get('/create', [VehicleBrandController::class, 'create'])->name('vehicleBrand.create');
            Route::post('/store', [VehicleBrandController::class, 'store'])->name('vehicleBrand.store');
            Route::get('/edit/{vehicleBrand_id}', [VehicleBrandController::class, 'edit'])->name('vehicleBrand.edit');
            Route::post('/update/{vehicleBrand_id}', [VehicleBrandController::class, 'update'])->name('vehicleBrand.update');
            Route::delete('/delete/{vehicleBrand_id}', [VehicleBrandController::class, 'delete'])->name('vehicleBrand.delete');
        });

        Route::prefix('/vehiculos-modelos')->group(function () {
            Route::get('/', [VehicleModelController::class, 'index'])->name('vehicleModel.index');
            Route::get('/create', [VehicleModelController::class, 'create'])->name('vehicleModel.create');
            Route::post('/store', [VehicleModelController::class, 'store'])->name('vehicleModel.store');
            Route::get('/edit/{vehicleModel_id}', [VehicleModelController::class, 'edit'])->name('vehicleModel.edit');
            Route::post('/update/{vehicleModel_id}', [VehicleModelController::class, 'update'])->name('vehicleModel.update');
            Route::delete('/delete/{vehicleModel_id}', [VehicleModelController::class, 'destroy'])->name('vehicleModel.delete');
        });

        Route::prefix('/vehiculos-tipos')->group(function () {
            Route::get('/', [VehicleTypeController::class, 'index'])->name('vehicleType.index');
            Route::get('/create', [VehicleTypeController::class, 'create'])->name('vehicleType.create');
            Route::post('/store', [VehicleTypeController::class, 'store'])->name('vehicleType.store');
            Route::get('/edit/{vehicle_id}', [VehicleTypeController::class, 'edit'])->name('vehicleType.edit');
            Route::post('/update/{vehicle_id}', [VehicleTypeController::class, 'update'])->name('vehicleType.update');
            Route::delete('/delete/{vehicle_id}', [VehicleTypeController::class, 'destroy'])->name('vehicleType.delete');
        });

    });



    Route::get('/download/{id}', [RequestController::class, 'downloadPDF'])->name('downloadPDF');
});

Route::get('/register', function () {
    abort(404);
});
