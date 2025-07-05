<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DataAdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\MonteCarlo\BerangkatController;
use App\Http\Controllers\MonteCarlo\DatangController;
use App\Http\Controllers\Smothing\BerangkatControllers;
use App\Http\Controllers\Smothing\DatangControllers;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {
    ;

    Route::get('/', [AuthController::class, 'index']);  // Add this line
    Route::get('/login', [AuthController::class, 'index'])->name('login');  // Add this line
    Route::post('/loginForm', [AuthController::class, 'loginForm'])->name('loginForm');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::resource('data-admin', DataAdminController::class);
    Route::resource('dataset', DatasetController::class);
    Route::post('/dataset/import', [DatasetController::class, 'import'])->name('dataset.import');
    Route::post('/dataset/delete-all', [DatasetController::class, 'deleteAll'])->name('dataset.deleteAll');

    // Monte Carlo
    Route::resource('monteCarlo-datang', DatangController::class);
    Route::resource('monteCarlo-berangkat', BerangkatController::class);
    Route::get('/monteCarlo-datang/refresh', [DatangController::class, 'refresh']);
    Route::get('/montecarlo/refresh-all', [DatangController::class, 'refreshAll'])->name('montecarlo.refreshAll');
    Route::get('/monte-carlo', [DatangController::class, 'index'])->name('monte-carlo.datang.index');


    // Smothing
    Route::resource('smothing-datang', DatangControllers::class);
    Route::resource('smothing-berangkat', BerangkatControllers::class);

});

Route::fallback(function () {
    return view('error404');
});