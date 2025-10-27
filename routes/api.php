<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'apiLogin'])->name('api.login');

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AdminController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('login.post');
});

Route::middleware(['auth'])->group(function () {

    // ✅ ROLE-BASED DASHBOARD ROUTES
    Route::get('/dashboard/superadmin', fn() => view('dashboard'))->name('dashboard.superadmin');
    Route::get('/dashboard/admin', fn() => view('dashboard'))->name('dashboard.admin');
    Route::get('/dashboard/manager', fn() => view('dashboard'))->name('dashboard.manager');
    Route::get('/dashboard/distributor', fn() => view('dashboard'))->name('dashboard.distributor');
    Route::get('/dashboard/fieldstaff', fn() => view('dashboard'))->name('dashboard.fieldstaff');
    Route::get('/dashboard/retailer', fn() => view('dashboard'))->name('dashboard.retailer');

    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
});


// // Redirect root to admin login
// Route::get('/', function () {
//     return redirect()->route('admin.login');
// });

// // Web Authentication (Session-based)
// Route::middleware(['web', 'guest:admin'])->group(function () {
//     Route::get('/admin/login', [AdminController::class, 'showLogin'])->name('admin.login');
//     Route::post('/admin/login', [AdminController::class, 'login'])->name('login.post');
// });

// Route::middleware(['web', 'auth.admin'])->group(function () {
//     Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
//     Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

//     Route::resource('districts', DistrictController::class);
//     Route::resource('areas', AreaController::class);

//     Route::resource('distributors', DistributorController::class);
//     // AJAX route to get areas by district
//     Route::get('/get-areas/{district}', [DistributorController::class, 'getAreas']);

//     Route::resource('retailers', ChemistController::class);
//     // AJAX: get areas by district
//     Route::get('/get-areas/{district}', [ChemistController::class, 'getAreas']);
// });