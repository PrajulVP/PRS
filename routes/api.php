<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ChemistController;
use App\Http\Controllers\DistributorController;

// Redirect root to admin login
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Web Authentication (Session-based)
Route::middleware(['web', 'guest:admin'])->group(function () {
    Route::get('/admin/login', [AdminController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AdminController::class, 'login'])->name('login.post');
});

Route::middleware(['web', 'auth.admin-api'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

    Route::resource('districts', DistrictController::class);
    Route::resource('areas', AreaController::class);

    Route::resource('distributors', DistributorController::class);
    // AJAX route to get areas by district
    Route::get('/get-areas/{district}', [DistributorController::class, 'getAreas']);

    Route::resource('chemists', ChemistController::class);
    // AJAX: get areas by district
    Route::get('/get-areas/{district}', [ChemistController::class, 'getAreas']);
});