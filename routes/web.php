<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/admin/login', function () {
    return view('admin/login');
})->name('admin.login');

Route::post('/login', [AdminController::class, 'login'])->name('login.post');

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::post('/users', [AdminController::class, 'store']);
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
});