<?php

use Illuminate\Support\Facades\Route;

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
    RetailerOrderManagementController,    SettingsController,    distributorOrderController
};

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
// Route::post('/login-alt', [AuthController::class, 'login'])->name('login.post');

Route::middleware(['auth'])->group(function () {

    Route::post('admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('dashboard-api')->name('dashboard.api.')->group(function () {
        Route::get('order-status-distribution', [DashboardController::class, 'getOrderStatusDistribution'])->name('orderStatusDistribution');
        Route::get('total-orders-over-time', [DashboardController::class, 'getTotalOrdersOverTime'])->name('totalOrdersOverTime');
        Route::get('orders-by-distributor', [DashboardController::class, 'getOrdersByDistributor'])->name('ordersByDistributor');
        Route::get('orders-by-retailer', [DashboardController::class, 'getOrdersByRetailer'])->name('ordersByRetailer');
        Route::get('top-products', [DashboardController::class, 'getTopProducts'])->name('topProducts');
    });

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('districts', DistrictController::class);
    Route::resource('areas', AreaController::class);
    Route::resource('products', ProductController::class);

    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('users/{user}/activate', [UserController::class, 'activateUser'])->name('admin.users.activate');

    Route::get('roles', [PermissionController::class, 'index'])->name('admin.permissions.index');
    Route::get('roles/{role}/permissions', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
    Route::put('roles/{role}/permissions', [PermissionController::class, 'update'])->name('admin.permissions.update');

    Route::get('pending-approvals', [PendingApprovalController::class, 'index'])->name('pending-approvals');
    Route::get('users/pending-approval', [PendingApprovalController::class, 'index'])->name('admin.users.pending_approval');

    Route::name('admin.')->group(function () {
        Route::resource('sales-managers', SalesManagerController::class);

        Route::resource('distributors', DistributorController::class);

        Route::resource('field-staffs', FieldStaffController::class);
        Route::patch('field-staffs/{fieldstaff}/activate', [FieldStaffController::class, 'activate'])->name('field-staffs.activate');

        Route::resource('retailers', RetailerController::class);
        Route::patch('retailers/{retailer}/activate', [RetailerController::class, 'activate'])->name('retailers.activate');

        Route::resource('retailer-orders', RetailerOrderManagementController::class)
            ->except(['create', 'store'])
            ->parameters(['retailer-orders' => 'retailerOrder']);

        Route::post('retailer-orders/{retailerOrder}/accept', [RetailerOrderManagementController::class, 'acceptOrder'])->name('retailer-orders.acceptOrder');
        Route::post('retailer-orders/{retailerOrder}/assign-fieldstaff', [RetailerOrderManagementController::class, 'assignFieldStaff'])->name('retailer-orders.assignFieldStaff');
        Route::post('orders/{order}/assign-distributor', [RetailerOrderManagementController::class, 'assignDistributor'])->name('orders.assign_distributor');
        Route::get('retailer-orders/create', [RetailerOrderController::class, 'create'])->name('retailer-orders.create');

        Route::resource('distributor-orders', distributorOrderController::class);

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

        // Master settings
        Route::get('settings/general', [SettingsController::class, 'general'])->name('settings.general');
        Route::post('settings', [SettingsController::class, 'save'])->name('settings.save');
    });

    Route::get('/distributors/get-areas/{district}', [DistributorController::class, 'getAreas'])->name('distributors.getAreas');
    Route::get('/retailers/get-areas/{district}', [RetailerController::class, 'getAreas'])->name('retailers.getAreas');
    Route::get('/retailers/get-distributors-by-district-and-area/{district}/{area}', [RetailerController::class, 'getDistributorsByDistrictAndArea'])->name('retailers.getDistributorsByDistrictAndArea');
    Route::get('/get-products/{distributor}', [RetailerOrderManagementController::class, 'getProductsByDistributor'])->name('get-products-by-distributor');

    Route::prefix('distributor')->name('distributor.')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'distributorIndex'])->name('orders.index');
    });

    Route::prefix('fieldstaff')->name('fieldstaff.')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'fieldStaffIndex'])->name('orders.index');
        Route::post('/orders/{order}/update-delivery-status', [RetailerOrderManagementController::class, 'updateDeliveryStatus'])->name('orders.updateDeliveryStatus');
    });

    Route::prefix('retailer')->name('retailer.')->group(function () {
        Route::get('/orders', [RetailerOrderController::class, 'retailerIndex'])->name('orders.index');
        Route::post('/orders', [RetailerOrderController::class, 'store'])->name('orders.store');
        Route::post('/orders/{retailerOrder}/confirm-delivery', [RetailerOrderController::class, 'confirmDelivery'])->name('orders.confirmDelivery');
    });
});
