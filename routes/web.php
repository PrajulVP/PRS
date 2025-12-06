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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// =========================================================================
// PUBLIC / GUEST ROUTES
// =========================================================================
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');
    // Redundant naming kept for compatibility if needed, otherwise can be removed
    Route::post('/login-alt', [AuthController::class, 'login'])->name('login.post');
});

// =========================================================================
// AUTHENTICATED ROUTES
// =========================================================================
Route::middleware(['auth:web'])->group(function () {

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


    // =====================================================================
    // ROLE-BASED GROUPS
    // =====================================================================

    // --- SuperAdmin & Admin ---
    Route::middleware(['role:superadmin|admin'])->group(function () {
        // User CRUD
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
        Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::post('admin/users/{user}/activate', [UserController::class, 'activateUser'])->name('admin.users.activate');

        // Permissions / Master Settings
        Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
            Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
            Route::get('permissions/{role}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
            Route::put('permissions/{role}', [PermissionController::class, 'update'])->name('permissions.update');
        });
    });

    // --- Pending Approvals (Shared) ---
    // Accessible by SuperAdmin, Admin, SalesManager, FieldStaff (based on old group)
    // Note: The original code had specific middleware on 'admin.users' but pending approval was wider.
    // Keeping consistent with original logic.
    Route::get('pending-approvals', [PendingApprovalController::class, 'index'])->name('pending-approvals');


    // --- Management Routes (SuperAdmin, Admin, SalesManager, FieldStaff) ---
    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['role:superadmin|admin|salesmanager|fieldstaff']], function () {
        Route::get('users/pending-approval', [PendingApprovalController::class, 'index'])->name('users.pending_approval');

        // Role Management Resources
        Route::resource('salesmanagers', SalesManagerController::class);
        Route::resource('distributors', DistributorController::class);
        Route::resource('fieldstaffs', FieldStaffController::class);
        Route::patch('fieldstaffs/{fieldstaff}/activate', [FieldStaffController::class, 'activate'])->name('fieldstaffs.activate');
        Route::resource('retailers', RetailerController::class);
        Route::patch('retailers/{retailer}/activate', [RetailerController::class, 'activate'])->name('retailers.activate');

        // Order Assignment
        Route::post('orders/{order}/assign-distributor', [RetailerOrderManagementController::class, 'assignDistributor'])->name('orders.assign_distributor');
    });


    // =====================================================================
    // ORDER MANAGEMENT
    // /====================================================================

    // --- Retailer Orders (Management Side) ---
    Route::resource('retailer-orders-management', RetailerOrderManagementController::class)
        ->except(['create', 'store'])
        ->parameters(['retailer-orders-management' => 'retailerOrder']);

    Route::post('retailer-orders-management/{retailerOrder}/accept', [RetailerOrderManagementController::class, 'acceptOrder'])->name('retailer-orders-management.acceptOrder');
    Route::post('retailer-orders-management/{retailerOrder}/assign-fieldstaff', [RetailerOrderManagementController::class, 'assignFieldStaff'])->name('retailer-orders-management.assignFieldStaff');

    // Admin Creation of Retailer Orders
    Route::get('admin/retailer-orders/create', [RetailerOrderController::class, 'create'])->name('admin.retailer-orders.create');

    // --- Distributor Orders ---
    Route::resource('distributor-orders', distributorOrderController::class);

    // Status Updates / Actions for Distributor Orders
    Route::post('distributor-orders/{distributor_order}/accept-by-sales-manager', [distributorOrderController::class, 'acceptBySalesManager'])
        ->name('distributor-orders.accept-by-sales-manager')
        ->middleware('role:salesmanager');

    Route::post('distributor-orders/{distributor_order}/accept-by-admin', [distributorOrderController::class, 'acceptByAdmin'])
        ->name('distributor-orders.accept-by-admin')
        ->middleware('role:admin');

    Route::post('distributor-orders/{distributor_order}/request-cancellation', [distributorOrderController::class, 'requestCancellation'])
        ->name('distributor-orders.request-cancellation')
        ->middleware('role:distributor');

    Route::post('distributor-orders/{distributor_order}/approve-cancellation', [distributorOrderController::class, 'approveCancellation'])
        ->name('distributor-orders.approve-cancellation')
        ->middleware('role:salesmanager');

    Route::post('distributor-orders/{distributor_order}/cancel-order', [distributorOrderController::class, 'cancelOrder'])
        ->name('distributor-orders.cancel-order')
        ->middleware('role:distributor');


    // =====================================================================
    // ROLE SPECIFIC PORTALS
    // =====================================================================

    // --- Distributor Portal ---
    Route::prefix('distributor')->name('distributor.')->middleware('role:distributor')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'distributorIndex'])->name('orders.index');
    });

    // --- Field Staff Portal ---
    Route::prefix('fieldstaff')->name('fieldstaff.')->middleware('role:fieldstaff')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'fieldStaffIndex'])->name('orders.index');
        Route::post('/orders/{order}/update-delivery-status', [RetailerOrderManagementController::class, 'updateDeliveryStatus'])->name('orders.updateDeliveryStatus');
    });

    // --- Retailer Portal ---
    Route::prefix('retailer')->name('retailer.')->middleware('role:retailer')->group(function () {
        Route::get('/orders', [RetailerOrderController::class, 'retailerIndex'])->name('orders.index');
        Route::get('/orders/create', [RetailerOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [RetailerController::class, 'store'])->name('orders.store');
        Route::get('/orders/{retailerOrder}', [RetailerController::class, 'show'])->name('orders.show');
        Route::post('/orders/{retailerOrder}/confirm-delivery', [RetailerOrderController::class, 'confirmDelivery'])->name('orders.confirmDelivery');
    });
});
