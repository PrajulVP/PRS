<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes (session-based)
|--------------------------------------------------------------------------
*/

// Public / guest
Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');

    // If you also want a general user login later:
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login'); // optional
    Route::post('/login', [AuthController::class, 'login'])->name('login.post'); // optional
});

// Protected session routes
Route::middleware(['auth:superadmin,admin,manager,distributor,fieldstaff,retailer'])->group(function () {
   
    // Generic dashboard entrypoint — controller will redirect or show based on role
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // User Management
    Route::get('/admin/users', [App\Http\Controllers\UserController::class, 'index'])->name('admin.users');
    Route::get('/admin/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('admin.users.create')->middleware('role:superadmin');
    Route::post('/admin/users', [App\Http\Controllers\UserController::class, 'store'])->name('admin.users.store')->middleware('role:superadmin');
    Route::get('/admin/users/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('admin.users.edit')->middleware('role:superadmin');
    Route::put('/admin/users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('admin.users.update')->middleware('role:superadmin');
    Route::delete('/admin/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('admin.users.destroy')->middleware('role:superadmin');

    // Logout (session)
    Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

    
});
