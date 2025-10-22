<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/login', function () {
    return view('admin/login');
})->name('login');

// Public admin login routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::post('/login', [AdminController::class, 'login'])->name('login.post'); // Handle login POST

    // Authenticated admin routes
  Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::post('/users', [AdminController::class, 'store']);
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
});

});
