<?php

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
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

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
    Route::resource('retailer-orders-management', RetailerOrderManagementController::class);
    Route::resource('distributor-bulk-orders', DistributorBulkOrderController::class);
    Route::resource('products', ProductController::class);

    Route::post('admin/orders/{order}/assign-distributor', [RetailerOrderManagementController::class, 'assignDistributor'])->name('admin.orders.assign_distributor')->middleware('role:superadmin|admin|manager');

    // Order Workflow Routes
    Route::prefix('manager')->name('manager.')->middleware('role:manager')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'managerIndex'])->name('orders.index');
        Route::post('/orders/{order}/assign-distributor', [RetailerOrderManagementController::class, 'assignDistributor'])->name('orders.assignDistributor');
    });

    Route::prefix('distributor')->name('distributor.')->middleware('role:distributor')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'distributorIndex'])->name('orders.index');
        Route::post('/orders/{order}/assign-fieldstaff', [RetailerOrderManagementController::class, 'assignFieldStaff'])->name('orders.assignFieldStaff');
    });

    Route::prefix('fieldstaff')->name('fieldstaff.')->middleware('role:fieldstaff')->group(function () {
        Route::get('/orders', [RetailerOrderManagementController::class, 'fieldStaffIndex'])->name('orders.index');
        Route::post('/orders/{order}/update-delivery-status', [RetailerOrderManagementController::class, 'updateDeliveryStatus'])->name('orders.updateDeliveryStatus');
    });

    Route::prefix('retailer')->name('retailer.')->middleware('role:retailer')->group(function () {
        Route::get('/orders', [RetailerOrderController::class, 'retailerIndex'])->name('orders.index');
        Route::get('/orders/{retailerOrder}', [RetailerOrderController::class, 'show'])->name('orders.show');
    });

    // AJAX: Get areas for selected district
    Route::get('/distributors/get-areas/{district}', [DistributorController::class, 'getAreas'])->name('distributors.getAreas');
    // AJAX: Get areas for selected district for Field Staff
    Route::get('/fieldstaffs/get-areas/{district}', [FieldStaffController::class, 'getAreas'])->name('fieldstaffs.getAreas');
    // AJAX: Get areas for selected district for Retailers
    Route::get('/retailers/get-areas/{district}', [RetailerController::class, 'getAreas'])->name('retailers.getAreas');

    // AJAX: Get distributors for selected district for Retailers
    Route::get('/retailers/get-distributors/{district}', [RetailerController::class, 'getDistributors'])->name('retailers.getDistributors');
    // AJAX: Get distributors for selected district for Field Staff
    Route::get('/fieldstaffs/get-distributors/{district}', [FieldStaffController::class, 'getDistributors'])->name('fieldstaffs.getDistributors');

    // Logout (session)
    Route::post('admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['role:superadmin']], function () {
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('permissions/{role}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('permissions/{role}', [PermissionController::class, 'update'])->name('permissions.update');
    });

});