<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\RetailerOrderController;
use App\Http\Controllers\Api\ProductController;

Route::post('login', [AuthApiController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('profile', [AuthApiController::class, 'profile']);
    Route::post('logout', [AuthApiController::class, 'logout']);
    Route::get('retailer-orders', [RetailerOrderController::class, 'index']);
    Route::get('retailer-orders/{id}/products', [RetailerOrderController::class, 'getOrderItems']);
    Route::get('products', [ProductController::class, 'index']);
});
