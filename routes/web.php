<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('admin/login');
})->name('login');

Route::post('/login', [AdminController::class, 'login'])->name('login.post');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::post('/users', [AdminController::class, 'store']);
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
});