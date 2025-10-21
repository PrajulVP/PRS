<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AdminController::class, 'showLogin'])->name('login');

Route::get('/admindashboard', [AdminController::class, 'dashboardData'])->name('admindashboard');