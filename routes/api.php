<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\RetailerOrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\DistributorController;

//add a prefix to all routes
Route::post('login', [AuthApiController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('profile', [AuthApiController::class, 'profile']);
    Route::post('profile/update', [AuthApiController::class, 'updateProfile']);
    Route::post('logout', [AuthApiController::class, 'logout']);
    Route::get('retailer-orders', [RetailerOrderController::class, 'index']);
    Route::get('retailer-orders/{id}/products', [RetailerOrderController::class, 'getOrderItems']);

    // Distributor Orders
    Route::get('distributor-orders', [\App\Http\Controllers\Api\DistributorOrderApiController::class, 'index']);
    Route::get('distributor-orders/history', [\App\Http\Controllers\Api\DistributorOrderApiController::class, 'index']);
    Route::get('distributor-orders/{id}', [\App\Http\Controllers\Api\DistributorOrderApiController::class, 'show']);

    // Distributor-specific listings (for Admin/Manager navigation)
    Route::post('distributor-orders', [\App\Http\Controllers\Api\DistributorOrderApiController::class, 'store']);
    Route::post('distributor-orders/{id}/update-status', [\App\Http\Controllers\Api\DistributorOrderApiController::class, 'updateStatus']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('distributors/{distributorId}/products/{productId}/availability', [DistributorController::class, 'checkProductAvailability']);
    Route::get('distributor/inventory', [\App\Http\Controllers\Api\InventoryController::class, 'index']);
    Route::apiResource('inventory', \App\Http\Controllers\Api\InventoryController::class)->only(['index', 'show']);
});
