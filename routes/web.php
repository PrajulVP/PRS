<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes (session-based)
|--------------------------------------------------------------------------
*/

// Public / guest
Route::middleware(['guest'])->group(function () {
    Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');

    // If you also want a general user login later:
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login'); // optional
    Route::post('/login', [AuthController::class, 'login'])->name('login.post'); // optional
});

// Protected session routes
Route::middleware(['auth'])->group(function () {
    // Generic dashboard entrypoint — controller will redirect or show based on role
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // You can keep role-specific named views (optional)
    Route::get('/dashboard/superadmin', fn() => view('dashboard'))->name('dashboard.superadmin');
    Route::get('/dashboard/admin', fn() => view('dashboard'))->name('dashboard.admin');
    Route::get('/dashboard/manager', fn() => view('dashboard'))->name('dashboard.manager');
    Route::get('/dashboard/distributor', fn() => view('dashboard'))->name('dashboard.distributor');
    Route::get('/dashboard/fieldstaff', fn() => view('dashboard.fieldstaff'));
    Route::get('/dashboard/retailer', fn() => view('dashboard.retailer'))->name('dashboard.retailer');

    // Logout (session)
    Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');
});
