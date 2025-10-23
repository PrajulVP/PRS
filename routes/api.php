<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\AreaController;


// ----------------------
// Web routes (Blade)
// ----------------------
Route::prefix('admin')->group(function () {
    // Login form (GET)
    Route::get('login', [AdminController::class, 'showLogin'])->name('admin.login.form');

    // Login action (POST)
    Route::post('login', [AdminController::class, 'login'])->name('admin.login');

    // Protected dashboard (web guard)
    Route::get('dashboard', [AdminController::class, 'index'])
        ->middleware('auth:admin')
        ->name('admin.dashboard');

    // Logout (web guard)
    Route::post('logout', [AdminController::class, 'logout'])
        ->middleware('auth:admin')
        ->name('admin.logout');
});

// ----------------------
// API routes (JWT)
// ----------------------
Route::prefix('api/admin')->group(function () {

    // Login (POST only)
    Route::post('login', [AdminController::class, 'login'])->name('api.admin.login');

    // Protected routes (JWT)
    Route::middleware('jwt.auth:admin')->group(function () {
        Route::get('dashboard', [AdminController::class, 'index'])->name('api.admin.dashboard');
        Route::post('logout', [AdminController::class, 'logout'])->name('api.admin.logout');

        Route::apiResource('districts', DistrictController::class)->names('api.districts');
        Route::apiResource('areas', AreaController::class)->names('api.areas');
    });
});
