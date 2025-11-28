<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('login', [AuthController::class, 'apiLogin']);

Route::middleware('auth:api')->group(function () {
    Route::get('profile', [AuthController::class, 'apiProfile']);
    Route::post('logout', [AuthController::class, 'apiLogout']);
});
