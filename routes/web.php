<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\{
    AuthController,
    DashboardController,
    UserController,
    PendingApprovalController,
    ProfileController,
    PermissionController,
    ProductController,
    DistrictController,
    AreaController,
    SalesManagerController,
    DistributorController,
    FieldStaffController,
    RetailerController,
    RetailerOrderController,
    RetailerOrderManagementController,
    distributorOrderController
};


Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');
    // Redundant naming kept for compatibility if needed, otherwise can be removed
    Route::post('/login-alt', [AuthController::class, 'login'])->name('login.post');
});


Route::middleware(['auth'])->group(function () {

    // --- Authentication ---
    Route::post('admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

    // --- Dashboard ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard API for Charts/Widgets
    Route::prefix('dashboard-api')->name('dashboard.api.')->group(function () {
        Route::get('order-status-distribution', [DashboardController::class, 'getOrderStatusDistribution'])->name('orderStatusDistribution');
        Route::get('total-orders-over-time', [DashboardController::class, 'getTotalOrdersOverTime'])->name('totalOrdersOverTime');
        Route::get('orders-by-distributor', [DashboardController::class, 'getOrdersByDistributor'])->name('ordersByDistributor');
        Route::get('orders-by-retailer', [DashboardController::class, 'getOrdersByRetailer'])->name('ordersByRetailer');
        Route::get('top-products', [DashboardController::class, 'getTopProducts'])->name('topProducts');
    });

    // --- Profile Management ---
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // --- Common Resources ---
    Route::resource('districts', DistrictController::class);
    Route::resource('areas', AreaController::class);
    Route::resource('products', ProductController::class);

    // --- AJAX / Helper Data Fetching ---
    Route::get('/distributors/get-areas/{district}', [DistributorController::class, 'getAreas'])->name('distributors.getAreas');
    Route::get('/retailers/get-areas/{district}', [RetailerController::class, 'getAreas'])->name('retailers.getAreas');
    Route::get('/retailers/get-distributors-by-district-and-area/{district}/{area}', [RetailerController::class, 'getDistributorsByDistrictAndArea'])->name('retailers.getDistributorsByDistrictAndArea');
    Route::get('/get-products/{distributor}', [RetailerOrderManagementController::class, 'getProductsByDistributor'])->name('get-products-by-distributor');


    // --- SuperAdmin & Admin ---
    // User CRUD
    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::post('users/{user}/activate', [UserController::class, 'activateUser'])->name('admin.users.activate');

    // Permissions / Master Settings
    Route::get('roles', [PermissionController::class, 'index'])->name('admin.permissions.index');
    Route::get('roles/{role}/edit', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
    Route::put('roles/{role}', [PermissionController::class, 'update'])->name('admin.permissions.update');
    // Permissions / Master Settings
    Route::get('roles', [PermissionController::class, 'index'])->name('admin.permissions.index');
    Route::get('roles/{role}/edit', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
    Route::put('roles/{role}', [PermissionController::class, 'update'])->name('admin.permissions.update');

    Route::get('pending-approvals', [PendingApprovalController::class, 'index'])->name('pending-approvals');


    // --- Management Routes (SuperAdmin, Admin, SalesManager, FieldStaff) ---
    Route::get('users/pending-approval', [PendingApprovalController::class, 'index'])->name('admin.users.pending_approval');

    // Role Management Resources
    Route::resource('salesmanagers', SalesManagerController::class, ['as' => 'admin']);
    Route::resource('distributors', DistributorController::class, ['as' => 'admin']);
    Route::resource('fieldstaffs', FieldStaffController::class, ['as' => 'admin']);
    Route::patch('fieldstaffs/{fieldstaff}/activate', [FieldStaffController::class, 'activate'])->name('admin.fieldstaffs.activate');
    Route::resource('retailers', RetailerController::class, ['as' => 'admin']);
    Route::patch('retailers/{retailer}/activate', [RetailerController::class, 'activate'])->name('admin.retailers.activate');

    // Order Assignment
    Route::post('orders/{order}/assign-distributor', [RetailerOrderManagementController::class, 'assignDistributor'])->name('admin.orders.assign_distributor');


    // --- Retailer Orders (Management Side) ---
    Route::resource('retailer-orders-management', RetailerOrderManagementController::class)
        ->except(['create', 'store'])
        ->parameters(['retailer-orders-management' => 'retailerOrder']);

    Route::post('retailer-orders-management/{retailerOrder}/accept', [RetailerOrderManagementController::class, 'acceptOrder'])->name('retailer-orders-management.acceptOrder');
    Route::post('retailer-orders-management/{retailerOrder}/assign-fieldstaff', [RetailerOrderManagementController::class, 'assignFieldStaff'])->name('retailer-orders-management.assignFieldStaff');

    // Admin Creation of Retailer Orders
    Route::get('retailer-orders/create', [RetailerOrderController::class, 'create'])->name('admin.retailer-orders.create');

    // --- Distributor Orders ---
    Route::resource('distributor-orders', distributorOrderController::class);

    // Status Updates / Actions for Distributor Orders
    // Status Updates / Actions for Distributor Orders
    Route::post('distributor-orders/{distributor_order}/accept-by-admin', [distributorOrderController::class, 'acceptByAdmin'])
        ->name('distributor-orders.accept-by-admin');

    Route::post('distributor-orders/{distributor_order}/accept-by-sales-manager', [distributorOrderController::class, 'acceptBySalesManager'])
        ->name('distributor-orders.accept-by-sales-manager');
    Route::post('distributor-orders/{distributor_order}/approve-cancellation', [distributorOrderController::class, 'approveCancellation'])
        ->name('distributor-orders.approve-cancellation');

    Route::post('distributor-orders/{distributor_order}/request-cancellation', [distributorOrderController::class, 'requestCancellation'])
        ->name('distributor-orders.request-cancellation');
    Route::post('distributor-orders/{distributor_order}/cancel-order', [distributorOrderController::class, 'cancelOrder'])
        ->name('distributor-orders.cancel-order');

    // --- Distributor Portal ---
    Route::prefix('distributor')->name('distributor.')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'distributorIndex'])->name('orders.index');
    });

    // --- Field Staff Portal ---
    Route::prefix('fieldstaff')->name('fieldstaff.')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'fieldStaffIndex'])->name('orders.index');
        Route::post('/orders/{order}/update-delivery-status', [RetailerOrderManagementController::class, 'updateDeliveryStatus'])->name('orders.updateDeliveryStatus');
    });

    // --- Retailer Portal ---
    Route::prefix('retailer')->name('retailer.')->group(function () {
        Route::get('/orders', [RetailerOrderController::class, 'retailerIndex'])->name('orders.index');
        Route::get('/orders/create', [RetailerOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [RetailerController::class, 'store'])->name('orders.store');
        Route::get('/orders/{retailerOrder}', [RetailerController::class, 'show'])->name('orders.show');
        Route::post('/orders/{retailerOrder}/confirm-delivery', [RetailerOrderController::class, 'confirmDelivery'])->name('orders.confirmDelivery');
    });
});
