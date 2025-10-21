<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::prefix('admin')->name('admin.')->group(function () {
    // Admin login routes (no auth:sanctum middleware)

    Route::post('/login', [AdminController::class, 'login'])->name('login');

    // Authenticated admin routes (with auth:sanctum middleware)
    Route::middleware('auth:sanctum')->group(function(){
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::post('/users', [AdminController::class, 'store']);
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    });
});

Route::get('/', function () {
    return view('welcome');
})->name('welcome');
