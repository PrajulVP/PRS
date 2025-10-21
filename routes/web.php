<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/adminlogin', [AdminController::class, 'showLogin'])->name('adminlogin');

Route::middleware('auth')->group(function() {
    Route::get('/admindashboard', [AdminController::class, 'dashboardData'])->name('admindashboard');
});