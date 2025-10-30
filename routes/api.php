<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RetailerController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\ManagerController;
use \App\Http\Controllers\FieldStaffController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;


// // Web Authentication (Session-based)
// Route::middleware(['web', 'guest:admin'])->group(function () {
//     Route::get('login', [AuthController::class, 'showLogin'])->name('login');
//     Route::post('/login', [AuthController::class, 'login'])->name('login.post');
// });

// Route::middleware(['web', 'auth.admin'])->group(function () {
//     Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
//     Route::post('/admin/logout', [AdminController::class, 'logout'])->name('logout');

//     Route::resource('districts', DistrictController::class);

//     Route::resource('areas', AreaController::class);

//     Route::resource('distributors', DistributorController::class)->middleware('role:superadmin,admin,manager');
//     // AJAX route to get areas by district
//     Route::get('/get-areas/{district}', [DistributorController::class, 'getAreas']);

//     Route::resource('retailers', RetailerController::class)->middleware('role:superadmin,admin,manager');

//     Route::resource('managers', ManagerController::class)->middleware('role:superadmin,admin');

//     Route::resource('fieldstaffs', FieldStaffController::class)->middleware('role:superadmin,admin,manager');
//     Route::get('/get-distributors/{district}', [FieldStaffController::class, 'getDistributors']);

//     Route::resource('orders', OrderController::class);
// });

// Route::middleware(['web','auth:distributor'])->prefix('distributor')->name('distributor.')->group(function(){
//     // Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');
//     Route::get('/orders', [OrderController::class,'index'])->name('orders.index');
//     Route::post('/invoices/upload', [InvoiceController::class,'upload'])->name('invoices.upload');
//     // ... more web pages for charts, outstanding etc
// });



// Route::post('/login', [AuthController::class, 'apiLogin'])->name('api.login');

// Route::middleware(['guest'])->group(function () {
//     Route::get('/login', [AdminController::class, 'showLogin'])->name('admin.login');
//     Route::post('/login', [AdminController::class, 'login'])->name('login.post');
// });

// Route::middleware(['auth'])->group(function () {

//     // ✅ ROLE-BASED DASHBOARD ROUTES
//     Route::get('/dashboard/superadmin', fn() => view('dashboard'))->name('dashboard.superadmin');
//     Route::get('/dashboard/admin', fn() => view('dashboard'))->name('dashboard.admin');
//     Route::get('/dashboard/manager', fn() => view('dashboard'))->name('dashboard.manager');
//     Route::get('/dashboard/distributor', fn() => view('dashboard'))->name('dashboard.distributor');
//     Route::get('/dashboard/fieldstaff', fn() => view('dashboard'))->name('dashboard.fieldstaff');
//     Route::get('/dashboard/retailer', fn() => view('dashboard'))->name('dashboard.retailer');

//     Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
// });


