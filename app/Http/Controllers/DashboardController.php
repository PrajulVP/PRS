<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Product;
use App\Models\PermissionCategory;

use App\Models\RetailerOrder;
use App\Models\DistributorOrder;

use App\Models\FieldStaff;
use App\Models\Retailer;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('superadmin')) {
            $totalUsers = User::count();
            $totalRoles = Role::count();
            $totalProducts = Product::count();
            $totalPermissions = PermissionCategory::count();
            $totalOrders = RetailerOrder::count() + DistributorOrder::count();

            $overallTargetAmount = \App\Models\SalesTarget::sum('amount');
            $overallAchievedAmount = RetailerOrder::sum('total_amount');

            return view('layouts.dashboards.superadmin', compact('totalUsers', 'totalRoles', 'totalProducts', 'totalPermissions', 'totalOrders', 'overallTargetAmount', 'overallAchievedAmount'));
        }

        if ($user->hasRole('admin')) {
            $totalUsers = User::count();
            $totalProducts = Product::count();
            $totalOrders = RetailerOrder::count() + DistributorOrder::count();

            $overallTargetAmount = \App\Models\SalesTarget::sum('amount');
            $overallAchievedAmount = RetailerOrder::sum('total_amount');

            return view('layouts.dashboards.admin', compact('totalUsers', 'totalProducts', 'totalOrders', 'overallTargetAmount', 'overallAchievedAmount'));
        }

        if ($user->hasRole('salesmanager')) {
            $salesManager = $user->salesManager;
            $totalFieldStaff = $salesManager ? $salesManager->fieldStaffs()->count() : 0;
            $fieldStaffIds = $salesManager ? $salesManager->fieldStaffs()->pluck('id') : [];
            $totalRetailers = Retailer::whereIn('field_staff_id', $fieldStaffIds)->count();
            $overallTargetAmount = \App\Models\SalesTarget::whereIn('field_staff_id', $fieldStaffIds)->sum('amount');
            $overallAchievedAmount = RetailerOrder::whereIn('retailer_id', Retailer::whereIn('field_staff_id', $fieldStaffIds)->pluck('id'))->sum('total_amount');

            return view('layouts.dashboards.salesmanager', compact('totalFieldStaff', 'totalRetailers', 'overallTargetAmount', 'overallAchievedAmount'));
        }

        if ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            $totalOrders = $distributor ? $distributor->retailerOrders()->count() : 0;
            return view('layouts.dashboards.distributor', compact('totalOrders'));
        }

        if ($user->hasRole('retailer')) {
            $retailer = $user->retailer;
            $totalOrders = $retailer ? $retailer->retailerOrders()->count() : 0;
            return view('layouts.dashboards.retailer', compact('totalOrders'));
        }

        if ($user->hasRole('fieldstaff')) {
            $fieldStaff = $user->fieldStaff;
            $totalAssignedRetailers = $fieldStaff ? $fieldStaff->retailers()->count() : 0;
            $totalOrders = $fieldStaff ? RetailerOrder::whereIn('retailer_id', $fieldStaff->retailers->pluck('id'))->count() : 0;
            $salesTarget = $fieldStaff ? $fieldStaff->salesTargets()->latest()->first() : null;
            $targetAmount = $salesTarget ? $salesTarget->amount : 0;
            $achievedAmount = $fieldStaff ? RetailerOrder::whereIn('retailer_id', $fieldStaff->retailers->pluck('id'))->sum('total_amount') : 0;

            return view('layouts.dashboards.fieldstaff', compact('totalAssignedRetailers', 'totalOrders', 'targetAmount', 'achievedAmount'));
        }

        return view('welcome');
    }

    public function getOrderStatusDistribution(Request $request)
    {
        $user = Auth::user();
        $retailerOrders = RetailerOrder::query();
        $distributorOrders = DistributorOrder::query();

        if ($user->hasRole('salesmanager')) {
            $managerFieldStaffIds = $user->salesManager->fieldStaffs->pluck('id');
            $retailerOrders->whereIn('retailer_id', Retailer::whereIn('field_staff_id', $managerFieldStaffIds)->pluck('id'));
        } elseif ($user->hasRole('fieldstaff')) {
            $fieldStaff = $user->fieldStaff;
            if ($fieldStaff) {
                $assignedRetailerIds = $fieldStaff->retailers->pluck('id');
                $retailerOrders->whereIn('retailer_id', $assignedRetailerIds);
            }
        }

        $retailerStatus = $retailerOrders->select('status', DB::raw('count(*) as count'))
                                        ->groupBy('status')
                                        ->get();

        $distributorStatus = $distributorOrders->select('status', DB::raw('count(*) as count'))
                                            ->groupBy('status')
                                            ->get();

        $combinedStatus = $retailerStatus->concat($distributorStatus)->groupBy('status')->map(function ($row) {
            return $row->sum('count');
        });

        return response()->json($combinedStatus);
    }

    public function getTotalOrdersOverTime(Request $request)
    {
        $user = Auth::user();
        $retailerOrders = RetailerOrder::query();
        $distributorOrders = DistributorOrder::query();

        if ($user->hasRole('salesmanager')) {
            $managerFieldStaffIds = $user->salesManager->fieldStaffs->pluck('id');
             $retailerOrders->whereIn('retailer_id', Retailer::whereIn('field_staff_id', $managerFieldStaffIds)->pluck('id'));
        }

        $retailerOrdersOverTime = $retailerOrders->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                                                ->groupBy('date')
                                                ->orderBy('date')
                                                ->get();

        $distributorOrdersOverTime = $distributorOrders->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                                                    ->groupBy('date')
                                                    ->orderBy('date')
                                                    ->get();

        $combinedOrdersOverTime = $retailerOrdersOverTime->concat($distributorOrdersOverTime)->groupBy('date')->map(function ($row) {
            return $row->sum('count');
        })->sortKeys();

        return response()->json($combinedOrdersOverTime);
    }

    public function getOrdersByDistributor(Request $request)
    {
        $user = Auth::user();
        $retailerOrders = RetailerOrder::query();
        $distributorOrders = DistributorOrder::query();

        if ($user->hasRole('salesmanager')) {
            $managerFieldStaffIds = $user->salesManager->fieldStaffs->pluck('id');
            $retailerOrders->whereIn('retailer_id', Retailer::whereIn('field_staff_id', $managerFieldStaffIds)->pluck('id'));
        }

        $retailerOrdersByDistributor = $retailerOrders->join('distributors', 'retailer_orders.distributor_id', '=', 'distributors.id')
                                                    ->select('distributors.name as distributor_name', DB::raw('count(retailer_orders.id) as count'))
                                                    ->groupBy('distributor_name')
                                                    ->get();

        $distributorOrdersByDistributor = $distributorOrders->join('distributors', 'distributor_orders.distributor_id', '=', 'distributors.id')
                                                            ->select('distributors.name as distributor_name', DB::raw('count(distributor_orders.id) as count'))
                                                            ->groupBy('distributor_name')
                                                            ->get();

        $combinedOrdersByDistributor = $retailerOrdersByDistributor->concat($distributorOrdersByDistributor)->groupBy('distributor_name')->map(function ($row) {
            return $row->sum('count');
        });

        return response()->json($combinedOrdersByDistributor);
    }

    public function getOrdersByRetailer(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('fieldstaff')) {
            return response()->json([]);
        }

        $fieldStaff = $user->fieldStaff;
        if (!$fieldStaff) {
            return response()->json([]);
        }

        $assignedRetailerIds = $fieldStaff->retailers->pluck('id');

        $ordersByRetailer = RetailerOrder::whereIn('retailer_id', $assignedRetailerIds)
                                        ->join('retailers', 'retailer_orders.retailer_id', '=', 'retailers.id')
                                        ->join('users', 'retailers.user_id', '=', 'users.id')
                                        ->select('users.name as retailer_name', DB::raw('count(retailer_orders.id) as count'))
                                        ->groupBy('retailer_name')
                                        ->get();

        return response()->json($ordersByRetailer->pluck('count', 'retailer_name'));
    }

    public function getTopProducts(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('fieldstaff')) {
            return response()->json([]);
        }

        $fieldStaff = $user->fieldStaff;
        if (!$fieldStaff) {
            return response()->json([]);
        }

        $assignedRetailerIds = $fieldStaff->retailers->pluck('id');

        $topProducts = RetailerOrder::whereIn('retailer_id', $assignedRetailerIds)
                                    ->join('retailer_order_items', 'retailer_orders.id', '=', 'retailer_order_items.retailer_order_id')
                                    ->join('products', 'retailer_order_items.product_id', '=', 'products.id')
                                    ->select('products.name as product_name', DB::raw('SUM(retailer_order_items.quantity) as total_quantity_ordered'))
                                    ->groupBy('products.name')
                                    ->orderByDesc('total_quantity_ordered')
                                    ->limit(10)
                                    ->get();

        return response()->json($topProducts);
    }
}
