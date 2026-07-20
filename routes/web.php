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
    SystemController,
    ReportController,
    PrescriptionAnalysisController,
    SidebarController,
    ReturnController
};

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Add Laravel UI password reset routes
Auth::routes(['login' => false, 'register' => false, 'verify' => false, 'reset' => true]);

Route::middleware(['auth'])->group(function () {

    Route::post('admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/retailers', [DashboardController::class, 'getDistributorRetailers'])->name('dashboard.retailers');

    Route::prefix('dashboard-api')->name('dashboard.api.')->group(function () {
        Route::get('order-status-distribution', [DashboardController::class, 'getOrderStatusDistribution'])->name('orderStatusDistribution');
        Route::get('total-orders-over-time', [DashboardController::class, 'getTotalOrdersOverTime'])->name('totalOrdersOverTime');
        Route::get('orders-by-distributor', [DashboardController::class, 'getOrdersByDistributor'])->name('ordersByDistributor');
        Route::get('orders-by-retailer', [DashboardController::class, 'getOrdersByRetailer'])->name('ordersByRetailer');
        Route::get('top-products', [DashboardController::class, 'getTopProducts'])->name('topProducts');
    });

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/check-password', [ProfileController::class, 'checkPassword'])->name('profile.check-password');
    Route::post('/profile/upload-photo', [ProfileController::class, 'uploadPhoto'])->name('profile.upload-photo');
    Route::post('/profile/remove-photo', [ProfileController::class, 'removePhoto'])->name('profile.remove-photo');

    Route::resource('districts', DistrictController::class);
    Route::resource('areas', AreaController::class);
    Route::get('products/download-template', [ProductController::class, 'downloadTemplate'])->name('products.download-template');
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::post('products/{product}/toggle-returnable', [ProductController::class, 'toggleReturnable'])->name('products.toggle-returnable');
    Route::post('products/bulk-brand-returnable', [ProductController::class, 'bulkBrandReturnable'])->name('products.bulk-brand-returnable');
    Route::get('products/get-by-brand/{brand}', [ProductController::class, 'getByBrand'])->name('products.get-by-brand');
    Route::resource('products', ProductController::class);
    Route::post('inventories/{inventory}/adjust-stock', [InventoryController::class, 'adjustStock'])->name('inventories.adjust-stock');
    Route::resource('inventories', InventoryController::class);

    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('users/{user}/activate', [UserController::class, 'activateUser'])->name('admin.users.activate');
    Route::post('users/{user}/deactivate', [UserController::class, 'deactivateUser'])->name('admin.users.deactivate');

    Route::get('roles', [PermissionController::class, 'index'])->name('admin.permissions.index');
    Route::get('roles/{role}/permissions', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
    Route::put('roles/{role}/permissions', [PermissionController::class, 'update'])->name('admin.permissions.update');
    Route::post('roles/{role}/permissions/update-single', [PermissionController::class, 'updateSingle'])->name('admin.permissions.updateSingle');

    // Route::get('pending-approvals', [PendingApprovalController::class, 'index'])->name('pending-approvals'); // Deprecated generic route

    // External OCR API Route
    Route::post('ocr/process', [App\Http\Controllers\OcrController::class, 'process'])->name('ocr.process');

    Route::name('admin.')->prefix('admin')->group(function () {
        Route::resource('sales-managers', SalesManagerController::class);
        Route::patch('sales-managers/{sales_manager}/activate', [SalesManagerController::class, 'activate'])->name('sales-managers.activate');
        Route::patch('sales-managers/{sales_manager}/deactivate', [SalesManagerController::class, 'deactivate'])->name('sales-managers.deactivate');

        Route::get('approvals/retailers', [PendingApprovalController::class, 'index'])->defaults('type', 'retailer')->name('approvals.retailer');
        Route::get('approvals/distributors', [PendingApprovalController::class, 'index'])->defaults('type', 'distributor')->name('approvals.distributor');
        Route::get('users/pending-approval', [PendingApprovalController::class, 'index'])->name('users.pending_approval');
        Route::get('sidebar-counts', [SidebarController::class, 'getCounts'])->name('sidebar-counts');

        Route::resource('distributors', DistributorController::class);
        Route::patch('distributors/{distributor}/activate', [DistributorController::class, 'activate'])->name('distributors.activate');
        Route::patch('distributors/{distributor}/deactivate', [DistributorController::class, 'deactivate'])->name('distributors.deactivate');

        Route::resource('field-staffs', FieldStaffController::class);
        Route::patch('field-staffs/{field_staff}/activate', [FieldStaffController::class, 'activate'])->name('field-staffs.activate');
        Route::patch('field-staffs/{field_staff}/deactivate', [FieldStaffController::class, 'deactivate'])->name('field-staffs.deactivate');
        Route::post('field-staffs/{field_staff}/grant-clock-in', [FieldStaffController::class, 'grantClockInPermission'])->name('field-staffs.grant-clock-in');

        // Field Staff Specialized Management (Tracking, Expenses & Leaves)
        Route::get('field-staff/tracking', [ReportController::class, 'fieldStaffReports'])->name('field-staff.tracking');
        Route::get('field-staff/tracking-map', [ReportController::class, 'fieldStaffTracking'])->name('field-staff.tracking-map');
        Route::get('field-staff/tracking/export', [ReportController::class, 'fieldStaffTrackingExport'])->name('field-staff.tracking.export');
        
        // Manager Specialized Management (Tracking)
        Route::get('manager/tracking', [ReportController::class, 'managerReports'])->name('manager.tracking');
        Route::get('manager/tracking-map', [ReportController::class, 'managerTracking'])->name('manager.tracking-map');
        Route::get('manager/tracking/export', [ReportController::class, 'managerTrackingExport'])->name('manager.tracking.export');
        Route::get('field-staff/expenses', [\App\Http\Controllers\FieldStaffManagementController::class, 'expensesIndex'])->name('field-staff.expenses');
        Route::post('field-staff/expenses/{expense}/status', [\App\Http\Controllers\FieldStaffManagementController::class, 'updateExpenseStatus'])->name('field-staff.expenses.status');
        Route::get('field-staff/leaves', [\App\Http\Controllers\FieldStaffManagementController::class, 'leavesIndex'])->name('field-staff.leaves');
        Route::post('field-staff/leaves/{leave}/status', [\App\Http\Controllers\FieldStaffManagementController::class, 'updateLeaveStatus'])->name('field-staff.leaves.status');

        Route::resource('retailers', RetailerController::class);
        Route::patch('retailers/{retailer}/activate', [RetailerController::class, 'activate'])->name('retailers.activate');
        Route::patch('retailers/{retailer}/deactivate', [RetailerController::class, 'deactivate'])->name('retailers.deactivate');

        Route::get('retailer-orders/get-products', [RetailerOrderManagementController::class, 'getProducts'])->name('retailer-orders.get-products');

        Route::resource('retailer', RetailerOrderManagementController::class)
            // ->except(['create']) // Removed to allow create route
            ->parameters(['retailer' => 'retailerOrder']);

        Route::post('retailer/{retailerOrder}/accept', [RetailerOrderManagementController::class, 'acceptOrder'])->name('retailer.accept');
        Route::post('retailer/{retailerOrder}/assign-fieldstaff', [RetailerOrderManagementController::class, 'assignFieldStaff'])->name('retailer.assignFieldStaff');
        Route::post('orders/{order}/assign-distributor', [RetailerOrderManagementController::class, 'assignDistributor'])->name('orders.assign_distributor');
        Route::get('retailer/product/{product}', [RetailerOrderManagementController::class, 'getProductDetails'])->name('retailer.product-details');
        Route::get('retailer/product/{product}/distributor-variants', [RetailerOrderManagementController::class, 'getDistributorProductVariants'])->name('retailer.distributor-variants');
        Route::get('retailer/get-field-staffs-by-manager', [RetailerOrderManagementController::class, 'getFieldStaffsByManager'])->name('retailer.get-field-staffs-by-manager');
        Route::get('retailer/get-retailers-by-field-staff', [RetailerOrderManagementController::class, 'getRetailersByFieldStaff'])->name('retailer.get-retailers-by-field-staff');

        // Cancellation / Approval endpoints (parity with distributor orders)
        Route::post('retailer/{retailerOrder}/request-cancellation', [RetailerOrderManagementController::class, 'requestCancellation'])->name('retailer.request-cancellation');
        Route::post('retailer/{retailerOrder}/approve-cancellation', [RetailerOrderManagementController::class, 'approveCancellation'])->name('retailer.approve-cancellation');
        Route::post('retailer/{retailerOrder}/cancel-order', [RetailerOrderManagementController::class, 'cancelOrder'])->name('retailer.cancel-order');

        Route::post('retailer/{retailerOrder}/update-status', [RetailerOrderManagementController::class, 'updateStatus'])->name('retailer.update-status');
        Route::post('retailer/{retailerOrder}/update-payment-status', [RetailerOrderManagementController::class, 'updatePaymentStatus'])->name('retailer.update-payment-status');
        Route::get('retailer/{retailerOrder}/invoice', [RetailerOrderManagementController::class, 'invoice'])->name('retailer.invoice');
        Route::post('retailer/{retailerOrder}/upload-invoice', [RetailerOrderManagementController::class, 'uploadInvoice'])->name('retailer.upload-invoice');
        Route::post('retailer/{retailerOrder}/remove-invoice', [RetailerOrderManagementController::class, 'removeInvoice'])->name('retailer.remove-invoice');
        Route::post('retailer/{retailerOrder}/confirm-receipt', [RetailerOrderManagementController::class, 'confirmReceipt'])->name('retailer.confirm-receipt');
        Route::post('retailer/validate-batches', [RetailerOrderManagementController::class, 'validateBatches'])->name('retailer.validate-batches');

        
        Route::post('retailer/{retailerOrder}/reject', [RetailerOrderManagementController::class, 'rejectOrder'])->name('retailer.reject');


        Route::get('distributor-orders/get-products', [DistributorOrderController::class, 'getProducts'])->name('distributor-orders.get-products');
        Route::resource('distributor-orders', DistributorOrderController::class);
        Route::get('distributor-orders/product/{product}', [DistributorOrderController::class, 'getProductDetails'])->name('distributor-orders.product-details');
        Route::get('distributor-orders/product/{product}/distributor-variants', [DistributorOrderController::class, 'getDistributorProductVariants'])->name('distributor-orders.distributor-variants');

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
        Route::post('distributor-orders/{distributor_order}/reject', [DistributorOrderController::class, 'rejectOrder'])
            ->name('distributor-orders.reject');
        Route::post('distributor-orders/{distributor_order}/remove-invoice', [DistributorOrderController::class, 'removeInvoice'])
            ->name('distributor-orders.remove-invoice');

        Route::post('distributor-orders/{distributor_order}/confirm-receipt', [DistributorOrderController::class, 'confirmReceipt'])
            ->name('distributor-orders.confirm-receipt');

        // Loyalty Points Dashboard
        Route::get('loyalty-points', [LoyaltyPointsController::class, 'index'])->name('loyalty-points.index');
        Route::get('loyalty-points/{retailer}', [LoyaltyPointsController::class, 'index'])->name('loyalty-points.detail');
        
        // Distributor Wallet Dashboard
        Route::get('distributor-wallet', [\App\Http\Controllers\DistributorWalletController::class, 'index'])->name('distributor-wallet.index');
        Route::get('distributor-wallet/{distributor}', [\App\Http\Controllers\DistributorWalletController::class, 'index'])->name('distributor-wallet.detail');
        Route::get('distributor-wallet/{distributor}/summary', [\App\Http\Controllers\DistributorWalletController::class, 'getSummary'])->name('distributor-wallet.summary');
        // Staff Ratings for Admin & Sales Managers
        Route::get('staff-ratings', [\App\Http\Controllers\AdminRatingController::class, 'index'])->name('staff-ratings.index');
        Route::get('loyalty-points/{retailer}/summary', [LoyaltyPointsController::class, 'getSummary'])->name('loyalty-points.summary');
        Route::get('loyalty-points/get-field-staffs-by-manager', [LoyaltyPointsController::class, 'getFieldStaffByManager'])->name('loyalty-points.field-staffs-by-manager');

        // Returns & Credits
        Route::prefix('returns')->name('returns.')->group(function () {
            Route::get('/', [ReturnController::class, 'index'])->name('index');
            Route::post('/', [ReturnController::class, 'store'])->name('store');
            Route::post('/{returnRequest}/approve', [ReturnController::class, 'approve'])->name('approve');
            Route::post('/{returnRequest}/reject', [ReturnController::class, 'reject'])->name('reject');
            Route::get('/search-order', [ReturnController::class, 'searchOrder'])->name('search-order');
            Route::get('/delivered-orders', [ReturnController::class, 'getDeliveredOrders'])->name('delivered-orders');
            Route::get('/get-filters', [ReturnController::class, 'getFilters'])->name('get-filters');
        });

        // Master settings
        Route::get('settings/general', [SettingsController::class, 'general'])->name('settings.general');
        Route::post('settings', [SettingsController::class, 'save'])->name('settings.save');
        Route::post('settings/brands/save', [SettingsController::class, 'saveBrand'])->name('settings.brands.save');
        Route::post('settings/brands/delete', [SettingsController::class, 'deleteBrand'])->name('settings.brands.delete');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/executive', [ReportController::class, 'index'])->name('index');
    Route::get('/', function() { return redirect()->route('admin.reports.index'); });
            Route::get('/orders', [ReportController::class, 'orderReports'])->name('orders');
            Route::get('/distributors', [ReportController::class, 'distributorReports'])->name('distributors');
            Route::get('/retailers', [ReportController::class, 'retailerReports'])->name('retailers');
            Route::get('/products', [ReportController::class, 'productReports'])->name('products');
            Route::get('/brands', [ReportController::class, 'brandReports'])->name('brands');
            Route::get('/areas', [ReportController::class, 'areaReports'])->name('areas');
            Route::get('/monitoring', [ReportController::class, 'monitoring'])->name('monitoring');
            Route::get('/monitoring/data', [ReportController::class, 'getMonitoringData'])->name('monitoring.data');
            Route::get('/targets', [ReportController::class, 'targetReports'])->name('targets');
            Route::get('/visits', [ReportController::class, 'visitReports'])->name('visits');
            Route::get('/outstanding', [ReportController::class, 'outstandingReports'])->name('outstanding');
            Route::get('/performance', [ReportController::class, 'performanceReports'])->name('performance');
            Route::get('/get-staff-by-manager', [ReportController::class, 'getStaffByManager'])->name('get-staff');
            Route::get('/export/{format}', [ReportController::class, 'downloadExport'])->name('export');
            
            // Prescription & Molecule Analysis
            Route::get('/prescribed-salts', [PrescriptionAnalysisController::class, 'prescribedSalts'])->name('prescribed-salts');
            Route::get('/fastest-molecules', [PrescriptionAnalysisController::class, 'fastestMovingMolecules'])->name('fastest-molecules');
            Route::get('/molecule-analytics', [PrescriptionAnalysisController::class, 'moleculeAnalytics'])->name('molecule-analytics');
            Route::get('/molecule-analytics/export', [PrescriptionAnalysisController::class, 'exportMoleculeAnalytics'])->name('molecule-analytics.export');
        });
    });

    Route::get('/distributors/get-areas/{district}', [DistributorController::class, 'getAreas'])->name('distributors.getAreas');
    Route::get('/retailers/get-areas/{district}', [RetailerController::class, 'getAreas'])->name('retailers.getAreas');
    Route::get('/retailers/get-distributors-by-district-and-area/{district}/{area}', [RetailerController::class, 'getDistributorsByDistrictAndArea'])->name('retailers.getDistributorsByDistrictAndArea');
    Route::get('/get-products/{distributor}', [RetailerOrderManagementController::class, 'getProductsByDistributor'])->name('get-products-by-distributor');

    Route::prefix('distributor')->name('distributor.')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'distributorIndex'])->name('orders.index');
        Route::get('/staff-ratings', [\App\Http\Controllers\DistributorRatingController::class, 'index'])->name('staff-ratings.index');
        Route::post('/staff-ratings', [\App\Http\Controllers\DistributorRatingController::class, 'store'])->name('staff-ratings.store');
    });

    Route::prefix('fieldstaff')->name('fieldstaff.')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'fieldStaffIndex'])->name('orders.index');
        Route::post('/orders/{order}/update-delivery-status', [RetailerOrderManagementController::class, 'updateDeliveryStatus'])->name('orders.updateDeliveryStatus');
    });

    Route::prefix('retailer')->name('retailer.')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'index'])->name('orders.index');
        Route::post('/orders', [RetailerOrderController::class, 'store'])->name('orders.store');
        Route::post('/orders/{retailerOrder}/confirm-delivery', [RetailerOrderController::class, 'confirmDelivery'])->name('orders.confirmDelivery');
    });

    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/fetch', [\App\Http\Controllers\NotificationController::class, 'fetchLatest'])->name('notifications.fetch');
    Route::post('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::post('notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Prescription AI Route
    Route::post('ai/extract-prescription', [App\Http\Controllers\PrescriptionController::class, 'extract'])->name('ai.extract-prescription');
});

Route::prefix('system')->name('system.')->group(function () {
    Route::get('/swagger-generate', [SystemController::class, 'swaggerGenerate'])->name('swagger-generate');
    Route::get('/migrate', [SystemController::class, 'migrate'])->name('migrate');
    Route::get('/migrate-fresh', [SystemController::class, 'migrateFresh'])->name('migrate-fresh');
    Route::get('/migrate-fresh-seed', [SystemController::class, 'migrateFreshSeed'])->name('migrate-fresh-seed');
    Route::get('/optimize', [SystemController::class, 'optimize'])->name('optimize');
    Route::get('/ocr-logs', [SystemController::class, 'getOcrLogs'])->name('ocr-logs');
    Route::get('/logs', [SystemController::class, 'logs'])->name('logs');
    Route::get('/db-status', [SystemController::class, 'dbStatus'])->name('db-status');
});
