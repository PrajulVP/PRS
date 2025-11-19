<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\FieldStaffController;
use App\Http\Controllers\RetailerController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\RetailerOrderManagementController; // New
use App\Http\Controllers\DistributorBulkOrderController;   // New
use App\Http\Controllers\RetailerOrderController;
use App\Http\Controllers\PermissionController;



// Public / guest
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');

    // If you also want a general user login later:
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login'); // optional
    Route::post('/login', [AuthController::class, 'login'])->name('login.post'); // optional
});

// Protected session routes
Route::middleware(['auth:web'])->group(function () {
   
    // Generic dashboard entrypoint — controller will redirect or show based on role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard API routes for charts and statistics
    Route::prefix('dashboard-api')->name('dashboard.api.')->group(function () {
        Route::get('order-status-distribution', [DashboardController::class, 'getOrderStatusDistribution'])->name('orderStatusDistribution');
        Route::get('orders-by-district', [DashboardController::class, 'getOrdersByDistrict'])->name('ordersByDistrict');
        Route::get('total-orders-over-time', [DashboardController::class, 'getTotalOrdersOverTime'])->name('totalOrdersOverTime');
        Route::get('orders-by-distributor', [DashboardController::class, 'getOrdersByDistributor'])->name('ordersByDistributor');
        Route::get('orders-by-fieldstaff', [DashboardController::class, 'getOrdersByFieldStaff'])->name('ordersByFieldStaff');
        Route::get('top-retailers', [DashboardController::class, 'getTopRetailers'])->name('topRetailers');
        Route::get('top-distributors', [DashboardController::class, 'getTopDistributors'])->name('topDistributors');
        Route::get('users-by-credit', [DashboardController::class, 'getUsersByCredit'])->name('usersByCredit');
        Route::get('users-by-loyalty-points', [DashboardController::class, 'getUsersByLoyaltyPoints'])->name('usersByLoyaltyPoints');
        Route::get('orders-by-retailer', [DashboardController::class, 'getOrdersByRetailer'])->name('ordersByRetailer');
        Route::get('sales-target', [DashboardController::class, 'getSalesTarget'])->name('salesTarget');
        Route::get('top-products', [DashboardController::class, 'getTopProducts'])->name('topProducts');
    });

    // User Management
    Route::get('/admin/users', [App\Http\Controllers\UserController::class, 'index'])->name('admin.users')->middleware('role:superadmin|admin');
    Route::get('/admin/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('admin.users.create')->middleware('role:superadmin|admin');
    Route::post('/admin/users', [App\Http\Controllers\UserController::class, 'store'])->name('admin.users.store')->middleware('role:superadmin|admin');
    Route::get('/admin/users/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('admin.users.destroy');

    Route::get('/admin/users/get-by-role', [App\Http\Controllers\UserController::class, 'getUsersByRole'])->name('admin.users.getByRole');

    Route::group(['middleware' => ['role:superadmin|admin']], function () {
        Route::resource('managers', ManagerController::class);
    });
    Route::resource('distributors', DistributorController::class);
    Route::resource('fieldstaffs', FieldStaffController::class);
    Route::resource('retailers', RetailerController::class);
    Route::resource('districts', DistrictController::class);
    Route::resource('areas', AreaController::class);
    Route::post('retailer-orders-management/{retailerOrder}/accept', [RetailerOrderManagementController::class, 'acceptOrder'])->name('retailer-orders-management.acceptOrder');
    Route::post('retailer-orders-management/{retailerOrder}/assign-fieldstaff', [RetailerOrderManagementController::class, 'assignFieldStaff'])->name('retailer-orders-management.assignFieldStaff');
    Route::resource('retailer-orders-management', RetailerOrderManagementController::class)->except(['create', 'store'])->parameters([
        'retailer-orders-management' => 'retailerOrder'
    ]);
    Route::post('distributor-bulk-orders/{distributor_bulk_order}/confirm-delivery', [DistributorBulkOrderController::class, 'confirmDelivery'])->name('distributor-bulk-orders.confirmDelivery');
    Route::post('distributor-bulk-orders/{distributor_bulk_order}/accept-order', [DistributorBulkOrderController::class, 'acceptOrder'])->name('distributor-bulk-orders.acceptOrder');
    Route::post('distributor-bulk-orders/{distributor_bulk_order}/cancel-order', [DistributorBulkOrderController::class, 'cancelOrder'])->name('distributor-bulk-orders.cancelOrder');

    Route::resource('distributor-bulk-orders', DistributorBulkOrderController::class);
    Route::get('admin/retailer-orders/create', [RetailerOrderController::class, 'create'])->name('admin.retailer-orders.create');
    Route::resource('products', ProductController::class);

    Route::post('admin/orders/{order}/assign-distributor', [RetailerOrderManagementController::class, 'assignDistributor'])->name('admin.orders.assign_distributor')->middleware('role:superadmin|admin|manager');

    // Order Workflow Routes
    Route::prefix('manager')->name('manager.')->middleware('role:manager')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'managerIndex'])->name('orders.index');
        Route::post('/orders/{order}/assign-distributor', [RetailerOrderManagementController::class, 'assignDistributor'])->name('orders.assignDistributor');
    });

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
        Route::post('/orders', [RetailerOrderController::class, 'store'])->name('orders.store');
        Route::post('/orders/{retailerOrder}/confirm-delivery', [RetailerOrderController::class, 'confirmDelivery'])->name('orders.confirmDelivery');
        Route::get('/orders/{retailerOrder}', [RetailerOrderController::class, 'show'])->name('orders.show');
    });

    // AJAX: Get areas for selected district
    Route::get('/distributors/get-areas/{district}', [DistributorController::class, 'getAreas'])->name('distributors.getAreas');
    // AJAX: Get areas for selected district for Field Staff
    Route::get('/fieldstaffs/get-areas/{district}', [FieldStaffController::class, 'getAreas'])->name('fieldstaffs.getAreas');
    // AJAX: Get areas for selected district for Retailers
    Route::get('/retailers/get-areas/{district}', [RetailerController::class, 'getAreas'])->name('retailers.getAreas');

    // AJAX: Get distributors for selected district for Retailers
    Route::get('/retailers/get-distributors-by-district-and-area/{district}/{area}', [RetailerController::class, 'getDistributorsByDistrictAndArea'])->name('retailers.getDistributorsByDistrictAndArea');
    // AJAX: Get distributors for selected district for Field Staff
    Route::get('/fieldstaffs/get-distributors-by-district-and-area/{district}/{area}', [FieldStaffController::class, 'getDistributorsByDistrictAndArea'])->name('fieldstaffs.getDistributorsByDistrictAndArea');

    Route::get('/get-products/{distributor}', [RetailerOrderManagementController::class, 'getProductsByDistributor'])->name('get-products-by-distributor');

    // Logout (session)
    Route::post('admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['role:superadmin']], function () {
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('permissions/{role}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('permissions/{role}', [PermissionController::class, 'update'])->name('permissions.update');
    });

});