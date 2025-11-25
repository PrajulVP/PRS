<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SalesManagerController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\FieldStaffController;
use App\Http\Controllers\RetailerController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\RetailerOrderManagementController;
use App\Http\Controllers\DistributorBulkOrderController;
use App\Http\Controllers\RetailerOrderController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PendingApprovalController;
use App\Http\Controllers\UserController;


// Public / guest
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Protected session routes
Route::middleware(['auth:web'])->group(function () {
   
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('dashboard-api')->name('dashboard.api.')->group(function () {
        Route::get('order-status-distribution', [DashboardController::class, 'getOrderStatusDistribution'])->name('orderStatusDistribution');
        Route::get('total-orders-over-time', [DashboardController::class, 'getTotalOrdersOverTime'])->name('totalOrdersOverTime');
        Route::get('orders-by-distributor', [DashboardController::class, 'getOrdersByDistributor'])->name('ordersByDistributor');
        Route::get('orders-by-retailer', [DashboardController::class, 'getOrdersByRetailer'])->name('ordersByRetailer');
        Route::get('top-products', [DashboardController::class, 'getTopProducts'])->name('topProducts');
    });

    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users')->middleware('role:superadmin|admin');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create')->middleware('role:superadmin|admin');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store')->middleware('role:superadmin|admin');
    
    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['role:superadmin|admin|salesmanager|fieldstaff']], function () {
        Route::get('users/pending-approval', [PendingApprovalController::class, 'index'])->name('users.pending_approval');
        Route::post('users/{user}/activate', [UserController::class, 'activateUser'])->name('users.activate');
        Route::resource('salesmanagers', SalesManagerController::class);
        Route::resource('distributors', DistributorController::class);
        Route::resource('fieldstaffs', FieldStaffController::class);
        Route::patch('fieldstaffs/{fieldstaff}/activate', [FieldStaffController::class, 'activate'])->name('fieldstaffs.activate');
        Route::resource('retailers', RetailerController::class);
        Route::patch('retailers/{retailer}/activate', [RetailerController::class, 'activate'])->name('retailers.activate');
    });
    Route::resource('districts', DistrictController::class);
    Route::resource('areas', AreaController::class);
    Route::post('retailer-orders-management/{retailerOrder}/accept', [RetailerOrderManagementController::class, 'acceptOrder'])->name('retailer-orders-management.acceptOrder');
    Route::post('retailer-orders-management/{retailerOrder}/assign-fieldstaff', [RetailerOrderManagementController::class, 'assignFieldStaff'])->name('retailer-orders-management.assignFieldStaff');
    Route::resource('retailer-orders-management', RetailerOrderManagementController::class)->except(['create', 'store'])->parameters([
        'retailer-orders-management' => 'retailerOrder'
    ]);
    Route::post('distributor-bulk-orders/{distributor_bulk_order}/confirm-delivery', [DistributorBulkOrderController::class, 'confirmDelivery'])->name('distributor-bulk-orders.confirmDelivery');
    Route::post('distributor-bulk-orders/{distributor_bulk_order}/accept-order', [DistributorBulkOrderController::class, 'acceptOrder'])->name('distributor-bulk-orders.accept-order');
    Route::post('distributor-bulk-orders/{distributor_bulk_order}/cancel-order', [DistributorBulkOrderController::class, 'cancelOrder'])->name('distributor-bulk-orders.cancelOrder');

    Route::resource('distributor-bulk-orders', DistributorBulkOrderController::class);
    Route::get('admin/retailer-orders/create', [RetailerOrderController::class, 'create'])->name('admin.retailer-orders.create');
    Route::resource('products', ProductController::class);

    Route::post('admin/orders/{order}/assign-distributor', [RetailerOrderManagementController::class, 'assignDistributor'])->name('admin.orders.assign_distributor')->middleware('role:superadmin|admin|salesmanager');

    Route::prefix('distributor')->name('distributor.')->middleware('role:distributor')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'distributorIndex'])->name('orders.index');
    });

    Route::prefix('fieldstaff')->name('fieldstaff.')->middleware('role:fieldstaff')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'fieldStaffIndex'])->name('orders.index');
        Route::post('/orders/{order}/update-delivery-status', [RetailerOrderManagementController::class, 'updateDeliveryStatus'])->name('orders.updateDeliveryStatus');
    });

    Route::prefix('retailer')->name('retailer.')->middleware('role:retailer')->group(function () {
        Route::get('/orders', [RetailerOrderController::class, 'retailerIndex'])->name('orders.index');
        Route::get('/orders/create', [RetailerOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [RetailerController::class, 'store'])->name('orders.store');
        Route::post('/orders/{retailerOrder}/confirm-delivery', [RetailerOrderController::class, 'confirmDelivery'])->name('orders.confirmDelivery');
        Route::get('/orders/{retailerOrder}', [RetailerController::class, 'show'])->name('orders.show');
    });

    Route::get('/distributors/get-areas/{district}', [DistributorController::class, 'getAreas'])->name('distributors.getAreas');
    Route::get('/retailers/get-areas/{district}', [RetailerController::class, 'getAreas'])->name('retailers.getAreas');
    Route::get('/retailers/get-distributors-by-district-and-area/{district}/{area}', [RetailerController::class, 'getDistributorsByDistrictAndArea'])->name('retailers.getDistributorsByDistrictAndArea');

    Route::get('/get-products/{distributor}', [RetailerOrderManagementController::class, 'getProductsByDistributor'])->name('get-products-by-distributor');

    Route::post('admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::get('pending-approvals', [PendingApprovalController::class, 'index'])->name('pending-approvals');

    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['role:superadmin|admin']], function () {
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('permissions/{role}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('permissions/{role}', [PermissionController::class, 'update'])->name('permissions.update');
        
        Route::post('users/{user}/activate', [UserController::class, 'activateUser'])->name('users.activate');
    });

});