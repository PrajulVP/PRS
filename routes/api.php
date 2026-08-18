<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\RetailerOrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\DistributorController;
use App\Http\Controllers\Api\PrescriptionApiController;
use App\Http\Controllers\Api\SettingsApiController;
use App\Http\Controllers\Api\FieldVisitController;

//add a prefix to all routes
Route::post('login', [AuthApiController::class, 'login']);
Route::post('send-otp', [AuthApiController::class, 'sendOtp']);
Route::post('login-otp', [AuthApiController::class, 'loginWithOtp']);

Route::get('settings/google-maps-api-key', [SettingsApiController::class, 'getGoogleMapsApiKey']);

// Location APIs wrapper (open to authenticated users)
Route::middleware('auth:api')->group(function () {
    Route::get('locations/districts', [\App\Http\Controllers\Api\LocationApiController::class, 'getDistricts']);
    Route::get('locations/areas', [\App\Http\Controllers\Api\LocationApiController::class, 'getAreas']);

    Route::get('profile', [AuthApiController::class, 'profile']);
    Route::post('profile/update', [AuthApiController::class, 'updateProfile']);
    Route::post('user/player-id', [\App\Http\Controllers\Api\UserApiController::class, 'updatePlayerId']);
    Route::post('logout', [AuthApiController::class, 'logout']);
    Route::get('retailer-orders', [RetailerOrderController::class, 'index']);
    Route::get('retailer-orders/calculate-price', [RetailerOrderController::class, 'calculatePrice']);
    Route::post('retailer-orders', [RetailerOrderController::class, 'store']);
    Route::post('retailer-orders/{id}/update-status', [RetailerOrderController::class, 'updateStatus']);

    Route::get('notifications', [\App\Http\Controllers\Api\NotificationApiController::class, 'index']);
    Route::post('notifications/{id}/read', [\App\Http\Controllers\Api\NotificationApiController::class, 'markAsRead']);
    Route::post('notifications/read-all', [\App\Http\Controllers\Api\NotificationApiController::class, 'markAllRead']);

    // Prescription Upload API
    Route::post('prescriptions/upload', [PrescriptionApiController::class, 'upload']);

    // Distributor Orders
    Route::get('distributor-orders', [\App\Http\Controllers\Api\DistributorOrderApiController::class, 'index']);
    Route::get('distributor-orders/calculate-price', [\App\Http\Controllers\Api\DistributorOrderApiController::class, 'calculatePrice']);
    Route::get('distributor-orders/history', [\App\Http\Controllers\Api\DistributorOrderApiController::class, 'index']);
    Route::get('distributor-orders/{id}', [\App\Http\Controllers\Api\DistributorOrderApiController::class, 'show']);

    // Distributor-specific listings (for Admin/Manager navigation)
    Route::post('distributor-orders', [\App\Http\Controllers\Api\DistributorOrderApiController::class, 'store']);
    Route::post('distributor-orders/{id}/update-status', [\App\Http\Controllers\Api\DistributorOrderApiController::class, 'updateStatus']);
    Route::post('distributor-orders/{id}/approve', [\App\Http\Controllers\Api\DistributorOrderApiController::class, 'approve']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::get('products/{product}/distributors', [ProductController::class, 'getDistributors']);
    Route::get('distributors/{distributorId}/products/{productId}/availability', [DistributorController::class, 'checkProductAvailability']);
    Route::get('distributor/inventory', [\App\Http\Controllers\Api\InventoryController::class, 'index']);
    Route::apiResource('inventory', \App\Http\Controllers\Api\InventoryController::class)->only(['index', 'show']);

    // Distributor — Retailer Orders (orders placed to this distributor by retailers)
    Route::get('distributor/retailer-orders', [\App\Http\Controllers\Api\DistributorRetailerOrderController::class, 'index']);
    Route::get('distributor/retailer-orders/{id}', [\App\Http\Controllers\Api\DistributorRetailerOrderController::class, 'show']);
    Route::post('distributor/retailer-orders/{id}/accept', [\App\Http\Controllers\Api\DistributorRetailerOrderController::class, 'acceptOrder']);
    Route::post('distributor/retailer-orders/{id}/upload-invoice', [\App\Http\Controllers\Api\DistributorRetailerOrderController::class, 'uploadInvoice']);
    Route::post('distributor/retailer-orders/{id}/reject', [\App\Http\Controllers\Api\DistributorRetailerOrderController::class, 'rejectOrder']);

    // Distributor — Retailers List
    Route::get('distributor/retailers', [\App\Http\Controllers\Api\DistributorRetailerApiController::class, 'index']);
    
    // Distributor — Rating APIs
    Route::get('distributor/rateable-staff', [\App\Http\Controllers\Api\RatingApiController::class, 'getDistributorRatingStaffList']);
    Route::post('distributor/rate-staff', [\App\Http\Controllers\Api\RatingApiController::class, 'distributorRateStaff']);

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
        Route::get('online-fieldstaffs', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getOnlineFieldStaffs']);
        Route::get('live-tracking', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getLiveTracking']);
        Route::get('route-map', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getRouteMap']);
        Route::get('fieldstaffs', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getFieldStaffs']);
        Route::post('fieldstaffs', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'storeFieldStaff']);
        Route::get('retailers', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getRetailers']);
        Route::get('retailers/{id}/loyalty-points', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getRetailerLoyaltyDetails']);
        Route::get('retailers/loyalty-points', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getRetailersLoyaltyPoints']);
        Route::get('loyalty-redemptions', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getLoyaltyRedemptions']);
        Route::post('retailers/{id}/approve', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'approveRetailer']);

        // Manage Retailer Orders
        Route::get('retailer-orders', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getRetailerOrders']);
        Route::put('retailer-orders/{id}', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'updateRetailerOrder']);

        // Distributor Hub
        Route::get('distributors', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getDistributors']);
        Route::get('distributor-insights', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getDistributorInsights']);
        Route::get('distributor-insights/{id}', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getDistributorDetailInsight']);
        Route::get('distributor-orders', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'getDistributorOrders']);
        Route::post('distributor-orders/{id}/approve', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'approveDistributorOrder']);
        Route::put('distributor-orders/{id}', [\App\Http\Controllers\Api\SalesManagerDashboardApiController::class, 'updateDistributorOrder']);
    });

    // Field Staff Dashboard & Orders
    Route::prefix('field-staff')->middleware(['device.binding'])->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Api\FieldStaffDashboardApiController::class, 'index']);
        Route::get('performance-trend', [\App\Http\Controllers\Api\FieldStaffDashboardApiController::class, 'performanceTrend']);
        Route::get('reports/sales-orders', [\App\Http\Controllers\Api\FieldStaffDashboardApiController::class, 'generateSalesOrdersReport']);
        Route::get('retailers', [\App\Http\Controllers\Api\FieldStaffDashboardApiController::class, 'getRetailers']);
        Route::post('retailers', [\App\Http\Controllers\Api\FieldStaffDashboardApiController::class, 'storeRetailer']);
        Route::get('retailers/{id}/loyalty-points', [\App\Http\Controllers\Api\FieldStaffDashboardApiController::class, 'getRetailerLoyaltyDetails']);
        Route::get('retailer-orders', [\App\Http\Controllers\Api\FieldStaffRetailerOrderController::class, 'index']);
        Route::get('retailer-orders/calculate-price', [\App\Http\Controllers\Api\FieldStaffRetailerOrderController::class, 'calculatePrice']);
        Route::get('retailer-orders/{id}', [\App\Http\Controllers\Api\FieldStaffRetailerOrderController::class, 'show']);
        Route::post('retailer-orders', [\App\Http\Controllers\Api\FieldStaffRetailerOrderController::class, 'store']);
        Route::post('retailer-orders/{id}/update-status', [\App\Http\Controllers\Api\FieldStaffRetailerOrderController::class, 'updateStatus']);
        Route::post('retailer-orders/{id}/accept', [\App\Http\Controllers\Api\FieldStaffRetailerOrderController::class, 'acceptOrder']);
        Route::put('retailer-orders/{id}', [\App\Http\Controllers\Api\FieldStaffRetailerOrderController::class, 'update']);

        // Tracking & Actions
        Route::get('punch', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'getPunchStatus']);
        Route::post('punch', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'punch']);
        Route::post('ping', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'pingLocation']);
        // Replaced by new Field Visits Module
        // Route::post('log-visit', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'logVisit']);
        // Route::get('retailers/{id}/last-visit', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'getLastVisitRemark']);
        Route::post('expenses', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'submitExpense']);
        Route::get('leaves', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'getLeaves']);
        Route::get('leave-types', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'getLeaveTypes']);
        Route::post('leaves', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'requestLeave']);
        Route::post('visits/report-location', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'reportLocation']);
        Route::post('sync-offline-logs', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'syncOfflineLogs']);
        
        Route::get('loyalty-redemptions', [\App\Http\Controllers\Api\LoyaltyApiController::class, 'getFieldstaffRedemptions']);
        Route::post('loyalty-redemptions/{id}/confirm', [\App\Http\Controllers\Api\LoyaltyApiController::class, 'confirmFieldstaffRedemption']);
    });

    // Manager Tracking & Actions
    Route::prefix('manager')->group(function () {
        Route::get('punch', [\App\Http\Controllers\Api\ManagerActionApiController::class, 'getPunchStatus']);
        Route::post('punch', [\App\Http\Controllers\Api\ManagerActionApiController::class, 'punch']);
        Route::post('ping', [\App\Http\Controllers\Api\ManagerActionApiController::class, 'pingLocation']);
        // Replaced by new Field Visits Module
        // Route::post('log-visit', [\App\Http\Controllers\Api\ManagerActionApiController::class, 'logVisit']);
        Route::post('expenses', [\App\Http\Controllers\Api\ManagerActionApiController::class, 'submitExpense']);
        Route::get('leaves', [\App\Http\Controllers\Api\ManagerActionApiController::class, 'getLeaves']);
        Route::get('leave-types', [\App\Http\Controllers\Api\ManagerActionApiController::class, 'getLeaveTypes']);
        Route::post('leaves', [\App\Http\Controllers\Api\ManagerActionApiController::class, 'requestLeave']);
    });

    // Retailer Dashboard & Loyalty
    Route::prefix('retailer')->group(function () {
        Route::get('dashboard/statistics', [\App\Http\Controllers\Api\RetailerDashboardApiController::class, 'getStatistics']);
        Route::get('loyalty-points', [\App\Http\Controllers\Api\RetailerDashboardApiController::class, 'getLoyaltyPoints']);
        Route::get('loyalty-rewards', [\App\Http\Controllers\Api\LoyaltyApiController::class, 'getRetailerRewards']);
        Route::post('loyalty-rewards/claim', [\App\Http\Controllers\Api\LoyaltyApiController::class, 'claimRetailerReward']);
        Route::post('rate-staff', [\App\Http\Controllers\Api\RatingApiController::class, 'rateStaff']);
        Route::get('my-ratings', [\App\Http\Controllers\Api\RatingApiController::class, 'getMyRatings']);
    });

    // Return Management APIs
    Route::prefix('returns')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\ReturnApiController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\ReturnApiController::class, 'store']);
        Route::get('/filters', [\App\Http\Controllers\Api\ReturnApiController::class, 'getFilters']);
        Route::get('/delivered-orders', [\App\Http\Controllers\Api\ReturnApiController::class, 'getDeliveredOrders']);
        Route::post('/{returnRequest}/approve', [\App\Http\Controllers\Api\ReturnApiController::class, 'approve']);
        Route::post('/{returnRequest}/reject', [\App\Http\Controllers\Api\ReturnApiController::class, 'reject']);
    });
    // Unified Orders API
    Route::prefix('orders')->group(function () {
        Route::post('/', [\App\Http\Controllers\Api\OrderApiController::class, 'store']);
        Route::post('/{id}/add-free-items', [\App\Http\Controllers\Api\OrderApiController::class, 'addFreeItems']);
    });

    // Field Staff Visits Module
    Route::prefix('field-visits')->group(function () {
        Route::get('parties', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'parties']);
        Route::get('purposes', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'purposes']);
        Route::post('start', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'start']);
        Route::post('stop', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'stop']);
        Route::get('status', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'status']);
        Route::get('history', [\App\Http\Controllers\Api\FieldStaffActionApiController::class, 'history']);
    });
});

