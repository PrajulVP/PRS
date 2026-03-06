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

    // Distributor — Retailer Orders (orders placed to this distributor by retailers)
    Route::get('distributor/retailer-orders', [\App\Http\Controllers\Api\DistributorRetailerOrderController::class, 'index']);
    Route::get('distributor/retailer-orders/{id}', [\App\Http\Controllers\Api\DistributorRetailerOrderController::class, 'show']);

    // Distributor — Retailers List
    Route::get('distributor/retailers', [\App\Http\Controllers\Api\DistributorRetailerApiController::class, 'index']);
    // Distributor Dashboard
    Route::prefix('distributor/dashboard')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\DistributorDashboardApiController::class, 'index']);
        Route::get('order-status-distribution', [\App\Http\Controllers\Api\DistributorDashboardApiController::class, 'getOrderStatusDistribution']);
        Route::get('total-orders-over-time', [\App\Http\Controllers\Api\DistributorDashboardApiController::class, 'getTotalOrdersOverTime']);
        Route::get('orders-by-retailer', [\App\Http\Controllers\Api\DistributorDashboardApiController::class, 'getOrdersByRetailer']);
        Route::get('top-products', [\App\Http\Controllers\Api\DistributorDashboardApiController::class, 'getTopProducts']);
        Route::get('actionable-orders-count', [\App\Http\Controllers\Api\DistributorDashboardApiController::class, 'getActionableOrdersCount']);
    });

    // Sales Manager Dashboard
    Route::prefix('sales-manager')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'index']);
        Route::get('pending-retailers', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getPendingRetailers']);
        Route::get('fieldstaffs', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getFieldStaffs']);
        Route::get('retailers', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getRetailers']);
        Route::get('retailers/{id}/loyalty-points', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getRetailerLoyaltyDetails']);
        Route::post('retailers/{id}/approve', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'approveRetailer']);
    });
});
