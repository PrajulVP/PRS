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
    InventoryController,
    DistrictController,
    AreaController,
    SalesManagerController,
    DistributorController,
    FieldStaffController,
    RetailerController,
    RetailerOrderController,
    RetailerOrderManagementController,
    SettingsController,
    DistributorOrderController,
    LoyaltyPointsController,
    SystemController
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
    Route::get('products/download-template', [ProductController::class, 'downloadTemplate'])->name('products.download-template');
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::resource('products', ProductController::class);
    Route::post('inventories/{inventory}/adjust-stock', [InventoryController::class, 'adjustStock'])->name('inventories.adjust-stock');
    Route::resource('inventories', InventoryController::class);

    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('users/{user}/activate', [UserController::class, 'activateUser'])->name('admin.users.activate');

    Route::get('roles', [PermissionController::class, 'index'])->name('admin.permissions.index');
    Route::get('roles/{role}/permissions', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
    Route::put('roles/{role}/permissions', [PermissionController::class, 'update'])->name('admin.permissions.update');
    Route::post('roles/{role}/permissions/update-single', [PermissionController::class, 'updateSingle'])->name('admin.permissions.updateSingle');

    // Route::get('pending-approvals', [PendingApprovalController::class, 'index'])->name('pending-approvals'); // Deprecated generic route
    Route::get('approvals/retailers', [PendingApprovalController::class, 'index'])->defaults('type', 'retailer')->name('approvals.retailer');
    Route::get('approvals/distributors', [PendingApprovalController::class, 'index'])->defaults('type', 'distributor')->name('approvals.distributor');
    Route::get('users/pending-approval', [PendingApprovalController::class, 'index'])->name('admin.users.pending_approval');

    Route::name('admin.')->group(function () {
        Route::resource('sales-managers', SalesManagerController::class);
        Route::patch('sales-managers/{salesManager}/activate', [SalesManagerController::class, 'activate'])->name('sales-managers.activate');
        Route::patch('sales-managers/{salesManager}/deactivate', [SalesManagerController::class, 'deactivate'])->name('sales-managers.deactivate');

        Route::resource('distributors', DistributorController::class);
        Route::patch('distributors/{distributor}/activate', [DistributorController::class, 'activate'])->name('distributors.activate');
        Route::patch('distributors/{distributor}/deactivate', [DistributorController::class, 'deactivate'])->name('distributors.deactivate');

        Route::resource('field-staffs', FieldStaffController::class);
        Route::patch('field-staffs/{fieldstaff}/activate', [FieldStaffController::class, 'activate'])->name('field-staffs.activate');
        Route::patch('field-staffs/{fieldstaff}/deactivate', [FieldStaffController::class, 'deactivate'])->name('field-staffs.deactivate');

        Route::resource('retailers', RetailerController::class);
        Route::patch('retailers/{retailer}/activate', [RetailerController::class, 'activate'])->name('retailers.activate');
        Route::patch('retailers/{retailer}/deactivate', [RetailerController::class, 'deactivate'])->name('retailers.deactivate');

        Route::resource('retailer-orders', RetailerOrderManagementController::class)
            // ->except(['create']) // Removed to allow create route
            ->parameters(['retailer-orders' => 'retailerOrder']);

        Route::post('retailer-orders/{retailerOrder}/accept', [RetailerOrderManagementController::class, 'acceptOrder'])->name('retailer-orders.acceptOrder');
        Route::post('retailer-orders/{retailerOrder}/assign-fieldstaff', [RetailerOrderManagementController::class, 'assignFieldStaff'])->name('retailer-orders.assignFieldStaff');
        Route::post('orders/{order}/assign-distributor', [RetailerOrderManagementController::class, 'assignDistributor'])->name('orders.assign_distributor');
        Route::get('retailer-orders/product/{product}', [RetailerOrderManagementController::class, 'getProductDetails'])->name('retailer-orders.product-details');

        // Cancellation / Approval endpoints (parity with distributor orders)
        Route::post('retailer-orders/{retailerOrder}/request-cancellation', [RetailerOrderManagementController::class, 'requestCancellation'])->name('retailer-orders.request-cancellation');
        Route::post('retailer-orders/{retailerOrder}/approve-cancellation', [RetailerOrderManagementController::class, 'approveCancellation'])->name('retailer-orders.approve-cancellation');
        Route::post('retailer-orders/{retailerOrder}/cancel-order', [RetailerOrderManagementController::class, 'cancelOrder'])->name('retailer-orders.cancel-order');

        Route::post('retailer-orders/{retailerOrder}/update-status', [RetailerOrderManagementController::class, 'updateStatus'])->name('retailer-orders.update-status');
        Route::post('retailer-orders/{retailerOrder}/update-payment-status', [RetailerOrderManagementController::class, 'updatePaymentStatus'])->name('retailer-orders.update-payment-status');
        Route::get('retailer-orders/{retailerOrder}/invoice', [RetailerOrderManagementController::class, 'invoice'])->name('retailer-orders.invoice');
        Route::post('retailer-orders/{retailerOrder}/upload-invoice', [RetailerOrderManagementController::class, 'uploadInvoice'])->name('retailer-orders.upload-invoice');
        Route::post('retailer-orders/{retailerOrder}/remove-invoice', [RetailerOrderManagementController::class, 'removeInvoice'])->name('retailer-orders.remove-invoice');
        Route::post('retailer-orders/{retailerOrder}/confirm-receipt', [RetailerOrderManagementController::class, 'confirmReceipt'])->name('retailer-orders.confirm-receipt');


        Route::resource('distributor-orders', DistributorOrderController::class);
        Route::get('distributor-orders/product/{product}', [DistributorOrderController::class, 'getProductDetails'])->name('distributor-orders.product-details');

        Route::post('distributor-orders/{distributor_order}/accept-by-admin', [DistributorOrderController::class, 'acceptByAdmin'])
            ->name('distributor-orders.accept-by-admin');
        Route::post('distributor-orders/{distributor_order}/accept-by-sales-manager', [DistributorOrderController::class, 'acceptBySalesManager'])
            ->name('distributor-orders.accept-by-sales-manager');
        Route::post('distributor-orders/{distributor_order}/approve-cancellation', [DistributorOrderController::class, 'approveCancellation'])
            ->name('distributor-orders.approve-cancellation');
        Route::post('distributor-orders/{distributor_order}/request-cancellation', [DistributorOrderController::class, 'requestCancellation'])
            ->name('distributor-orders.request-cancellation');
        Route::post('distributor-orders/{distributor_order}/cancel-order', [DistributorOrderController::class, 'cancelOrder'])
            ->name('distributor-orders.cancel-order');
        Route::post('distributor-orders/{distributor_order}/update-status', [DistributorOrderController::class, 'updateStatus'])
            ->name('distributor-orders.update-status');
        Route::post('distributor-orders/{distributor_order}/update-payment-status', [DistributorOrderController::class, 'updatePaymentStatus'])
            ->name('distributor-orders.update-payment-status');
        Route::get('distributor-orders/{distributor_order}/invoice', [DistributorOrderController::class, 'invoice'])
            ->name('distributor-orders.invoice');
        Route::post('distributor-orders/{distributor_order}/upload-invoice', [DistributorOrderController::class, 'uploadInvoice'])
            ->name('distributor-orders.upload-invoice');
        Route::post('distributor-orders/{distributor_order}/approve', [DistributorOrderController::class, 'approveOrder'])
            ->name('distributor-orders.approve');
        Route::post('distributor-orders/{distributor_order}/remove-invoice', [DistributorOrderController::class, 'removeInvoice'])
            ->name('distributor-orders.remove-invoice');
        Route::post('distributor-orders/{distributor_order}/confirm-receipt', [DistributorOrderController::class, 'confirmReceipt'])
            ->name('distributor-orders.confirm-receipt');

        // Loyalty Points Dashboard
        Route::get('loyalty-points', [LoyaltyPointsController::class, 'index'])->name('loyalty-points.index');
        Route::get('loyalty-points/{retailer}/summary', [LoyaltyPointsController::class, 'getSummary'])->name('loyalty-points.summary');

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

    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::post('notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
});

Route::prefix('system')->name('system.')->group(function () {
    Route::get('swagger-generate', [SystemController::class, 'swaggerGenerate'])->name('swagger.generate');
    Route::get('migrate', [SystemController::class, 'migrate'])->name('migrate');
    Route::get('migrate-fresh', [SystemController::class, 'migrateFresh'])->name('migrate.fresh');
    Route::get('migrate-fresh-seed', [SystemController::class, 'migrateFreshSeed'])->name('migrate.fresh.seed');
    Route::get('optimize', [SystemController::class, 'optimize'])->name('optimize');
});
