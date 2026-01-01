<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;

Route::post('login', [AuthApiController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('profile', [AuthApiController::class, 'profile']);
    Route::post('logout', [AuthApiController::class, 'logout']);
});
