<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::post('/adminlogin', [AdminController::class, 'login'])->name('api.adminlogin');

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/admindashboard', [AdminController::class, 'index'])->name('api.admindashboard');
    Route::post('/users', [AdminController::class, 'store']);
    //logout
    Route::post('/logout', [AdminController::class, 'logout'])->name('api.logout');
});
