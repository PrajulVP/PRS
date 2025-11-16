<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Product;
use App\Models\PermissionCategory; // Added this line

use App\Models\RetailerOrder;
use App\Models\DistributorOrder;

use App\Models\FieldStaff;
use App\Models\Retailer;
use Illuminate\Support\Facades\DB; // Added for database queries

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('superadmin')) {
            $totalUsers = User::count();
            $totalRoles = Role::count();
            $totalProducts = Product::count();
            $totalPermissions = PermissionCategory::count(); // Calculated total permissions from categories
            $totalOrders = RetailerOrder::count() + DistributorOrder::count();

            // Calculate overall sales target and achieved sales
            $overallTargetAmount = \App\Models\SalesTarget::sum('amount');
            $overallAchievedAmount = RetailerOrder::sum('total_amount'); // Assuming all retailer orders contribute to overall achieved sales

            return view('layouts.dashboards.superadmin', compact('totalUsers', 'totalRoles', 'totalProducts', 'totalPermissions', 'totalOrders', 'overallTargetAmount', 'overallAchievedAmount'));
        }

        if ($user->hasRole('admin')) {
            $totalUsers = User::count();
            $totalProducts = Product::count();
            $totalOrders = RetailerOrder::count() + DistributorOrder::count();

            // Calculate overall sales target and achieved sales
            $overallTargetAmount = \App\Models\SalesTarget::sum('amount');
            $overallAchievedAmount = RetailerOrder::sum('total_amount'); // Assuming all retailer orders contribute to overall achieved sales

            return view('layouts.dashboards.admin', compact('totalUsers', 'totalProducts', 'totalOrders', 'overallTargetAmount', 'overallAchievedAmount'));
        }

        if ($user->hasRole('manager')) {
            $totalFieldStaff = FieldStaff::count();
            $totalRetailers = Retailer::count();

            // Calculate overall sales target and achieved sales
            $overallTargetAmount = \App\Models\SalesTarget::sum('amount');
            $overallAchievedAmount = RetailerOrder::sum('total_amount'); // Assuming all retailer orders contribute to overall achieved sales

            return view('layouts.dashboards.manager', compact('totalFieldStaff', 'totalRetailers', 'overallTargetAmount', 'overallAchievedAmount'));
        }

        if ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            $totalOrders = $distributor ? $distributor->retailerOrders()->count() : 0;
            $totalFieldStaff = $distributor ? $distributor->fieldStaffs()->count() : 0;
            return view('layouts.dashboards.distributor', compact('totalOrders', 'totalFieldStaff'));
        }

        if ($user->hasRole('retailer')) {
            $retailer = $user->retailer;
            $totalOrders = $retailer ? $retailer->retailerOrders()->count() : 0;
            return view('layouts.dashboards.retailer', compact('totalOrders'));
        }

        if ($user->hasRole('fieldstaff')) {
            $fieldStaff = $user->fieldStaff; // Assuming a fieldStaff relationship on User model
            $totalAssignedRetailers = $fieldStaff ? $fieldStaff->retailers()->count() : 0;
            $totalOrders = $fieldStaff ? RetailerOrder::whereIn('retailer_id', $fieldStaff->retailers->pluck('id'))->count() : 0;
            // Assuming sales targets are linked to field staff
            $salesTarget = $fieldStaff ? $fieldStaff->salesTargets()->latest()->first() : null;
            $targetAmount = $salesTarget ? $salesTarget->amount : 0; // Changed from target_amount to amount
            $achievedAmount = $fieldStaff ? RetailerOrder::whereIn('retailer_id', $fieldStaff->retailers->pluck('id'))->sum('total_amount') : 0;

            return view('layouts.dashboards.fieldstaff', compact('totalAssignedRetailers', 'totalOrders', 'targetAmount', 'achievedAmount'));
        }

        return view('welcome');
    }

    // API for Order Status Distribution (Pie Chart)
    public function getOrderStatusDistribution(Request $request)
    {
        $user = Auth::user();
        $retailerOrders = RetailerOrder::query();
        $distributorOrders = DistributorOrder::query();

        if ($user->hasRole('manager')) {
            $managerFieldStaffIds = $user->fieldStaffs->pluck('id');
            $retailerOrders->whereIn('field_staff_id', $managerFieldStaffIds);
            $distributorOrders->whereIn('field_staff_id', $managerFieldStaffIds);
        } elseif ($user->hasRole('fieldstaff')) {
            $fieldStaff = $user->fieldStaff;
            if ($fieldStaff) {
                $assignedRetailerIds = $fieldStaff->retailers->pluck('id');
                $retailerOrders->whereIn('retailer_id', $assignedRetailerIds);
                // Field staff typically don't manage distributor orders directly, so no filter for distributorOrders
                $distributorOrders->where('field_staff_id', $fieldStaff->id); // Assuming distributor orders can be linked to field staff
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

    // API for Orders by District (Bar Chart)
    public function getOrdersByDistrict(Request $request)
    {
        $user = Auth::user();
        $retailerOrders = RetailerOrder::query();
        $distributorOrders = DistributorOrder::query();

        if ($user->hasRole('manager')) {
            $managerFieldStaffIds = $user->fieldStaffs->pluck('id');
            $retailerOrders->whereIn('field_staff_id', $managerFieldStaffIds);
            $distributorOrders->whereIn('field_staff_id', $managerFieldStaffIds);
        }

        $retailerOrdersByDistrict = $retailerOrders->join('users', 'retailer_orders.retailer_id', '=', 'users.id')
                                                ->join('districts', 'users.district_id', '=', 'districts.id')
                                                ->select('districts.name as district_name', DB::raw('count(retailer_orders.id) as count'))
                                                ->groupBy('districts.name')
                                                ->get();

        $distributorOrdersByDistrict = $distributorOrders->join('distributors', 'distributor_orders.distributor_id', '=', 'distributors.id')
                                                        ->join('users', 'distributors.user_id', '=', 'users.id')
                                                        ->join('districts', 'users.district_id', '=', 'districts.id')
                                                        ->select('districts.name as district_name', DB::raw('count(distributor_orders.id) as count'))
                                                        ->groupBy('districts.name')
                                                        ->get();

        $combinedOrdersByDistrict = $retailerOrdersByDistrict->concat($distributorOrdersByDistrict)->groupBy('district_name')->map(function ($row) {
            return $row->sum('count');
        });

        return response()->json($combinedOrdersByDistrict);
    }

    // API for Total Orders Over Time (Line Chart)
    public function getTotalOrdersOverTime(Request $request)
    {
        $user = Auth::user();
        $retailerOrders = RetailerOrder::query();
        $distributorOrders = DistributorOrder::query();

        if ($user->hasRole('manager')) {
            $managerFieldStaffIds = $user->fieldStaffs->pluck('id');
            $retailerOrders->whereIn('field_staff_id', $managerFieldStaffIds);
            $distributorOrders->whereIn('field_staff_id', $managerFieldStaffIds);
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
        })->sortKeys(); // Sort by date

        return response()->json($combinedOrdersOverTime);
    }

    // API for Orders by Distributor (Bar Chart)
    public function getOrdersByDistributor(Request $request)
    {
        $user = Auth::user();
        $retailerOrders = RetailerOrder::query();
        $distributorOrders = DistributorOrder::query();

        if ($user->hasRole('manager')) {
            $managerFieldStaffIds = $user->fieldStaffs->pluck('id');
            $retailerOrders->whereIn('field_staff_id', $managerFieldStaffIds);
            $distributorOrders->whereIn('field_staff_id', $managerFieldStaffIds);
        }

        $retailerOrdersByDistributor = $retailerOrders->join('distributors', 'retailer_orders.distributor_id', '=', 'distributors.id')
                                                    ->select('distributors.company_name as distributor_name', DB::raw('count(retailer_orders.id) as count'))
                                                    ->groupBy('distributor_name')
                                                    ->get();

        $distributorOrdersByDistributor = $distributorOrders->join('distributors', 'distributor_orders.distributor_id', '=', 'distributors.id')
                                                            ->select('distributors.company_name as distributor_name', DB::raw('count(distributor_orders.id) as count'))
                                                            ->groupBy('distributor_name')
                                                            ->get();

        $combinedOrdersByDistributor = $retailerOrdersByDistributor->concat($distributorOrdersByDistributor)->groupBy('distributor_name')->map(function ($row) {
            return $row->sum('count');
        });

        return response()->json($combinedOrdersByDistributor);
    }

    // API for Orders by Field Staff (Bar Chart)
    public function getOrdersByFieldStaff(Request $request)
    {
        $user = Auth::user();
        $retailerOrders = RetailerOrder::query();
        $distributorOrders = DistributorOrder::query();

        if ($user->hasRole('manager')) {
            $managerFieldStaffIds = $user->fieldStaffs->pluck('id');
            $retailerOrders->whereIn('field_staff_id', $managerFieldStaffIds);
            $distributorOrders->whereIn('field_staff_id', $managerFieldStaffIds);
        }

        $retailerOrdersByFieldStaff = $retailerOrders->join('field_staffs', 'retailer_orders.field_staff_id', '=', 'field_staffs.id')
                                                    ->join('users', 'field_staffs.user_id', '=', 'users.id')
                                                    ->select('users.name as field_staff_name', DB::raw('count(retailer_orders.id) as count'))
                                                    ->groupBy('field_staff_name')
                                                    ->get();

        $distributorOrdersByFieldStaff = $distributorOrders->join('field_staffs', 'distributor_orders.field_staff_id', '=', 'field_staffs.id')
                                                            ->join('users', 'field_staffs.user_id', '=', 'users.id')
                                                            ->select('users.name as field_staff_name', DB::raw('count(distributor_orders.id) as count'))
                                                            ->groupBy('field_staff_name')
                                                            ->get();

        $combinedOrdersByFieldStaff = $retailerOrdersByFieldStaff->concat($distributorOrdersByFieldStaff)->groupBy('field_staff_name')->map(function ($row) {
            return $row->sum('count');
        });

        return response()->json($combinedOrdersByFieldStaff);
    }

    // API for Top Retailers
    public function getTopRetailers(Request $request)
    {
        $user = Auth::user();
        $retailerOrders = RetailerOrder::query();

        if ($user->hasRole('manager')) {
            $managerFieldStaffIds = $user->fieldStaffs->pluck('id');
            $retailerOrders->whereIn('field_staff_id', $managerFieldStaffIds);
        }

        $topRetailers = $retailerOrders->join('retailers', 'retailer_orders.retailer_id', '=', 'retailers.id')
                                       ->join('users', 'retailers.user_id', '=', 'users.id')
                                       ->select('users.name as retailer_name', DB::raw('SUM(retailer_orders.total_amount) as total_order_value'))
                                       ->groupBy('retailer_name')
                                       ->orderByDesc('total_order_value')
                                       ->limit(10)
                                       ->get();

        return response()->json($topRetailers);
    }

    // API for Top Distributors
    public function getTopDistributors(Request $request)
    {
        $user = Auth::user();
        $retailerOrders = RetailerOrder::query();
        $distributorOrders = DistributorOrder::query();

        if ($user->hasRole('manager')) {
            $managerFieldStaffIds = $user->fieldStaffs->pluck('id');
            $retailerOrders->whereIn('field_staff_id', $managerFieldStaffIds);
            $distributorOrders->whereIn('field_staff_id', $managerFieldStaffIds);
        }

        $retailerOrdersByDistributor = $retailerOrders->join('distributors', 'retailer_orders.distributor_id', '=', 'distributors.id')
                                                    ->select('distributors.company_name as distributor_name', DB::raw('SUM(retailer_orders.total_amount) as total_order_value'))
                                                    ->groupBy('distributor_name');

        $distributorOrdersByDistributor = $distributorOrders->join('distributors', 'distributor_orders.distributor_id', '=', 'distributors.id')
                                                            ->select('distributors.company_name as distributor_name', DB::raw('SUM(distributor_orders.total_amount) as total_order_value'))
                                                            ->groupBy('distributor_name');

        $combinedOrdersByDistributor = $retailerOrdersByDistributor->unionAll($distributorOrdersByDistributor)
                                                                    ->get()
                                                                    ->groupBy('distributor_name')
                                                                    ->map(function ($row) {
                                                                        return $row->sum('total_order_value');
                                                                    })
                                                                    ->sortDesc()
                                                                    ->take(10);

        return response()->json($combinedOrdersByDistributor);
    }

    // API for Orders by Retailer (Bar Chart for Field Staff)
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

    // API for Sales Target vs. Achieved (for Field Staff)
    public function getSalesTarget(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('fieldstaff')) {
            return response()->json(['target' => 0, 'achieved' => 0]);
        }

        $fieldStaff = $user->fieldStaff;
        if (!$fieldStaff) {
            return response()->json(['target' => 0, 'achieved' => 0]);
        }

        $salesTarget = $fieldStaff->salesTargets()->latest()->first(); // Assuming latest target is current
        $targetAmount = $salesTarget ? $salesTarget->target_amount : 0;

        $achievedAmount = RetailerOrder::whereIn('retailer_id', $fieldStaff->retailers->pluck('id'))
                                        ->sum('total_amount');

        return response()->json(['target' => $targetAmount, 'achieved' => $achievedAmount]);
    }

    // API for Top Products Ordered (by assigned retailers for Field Staff)
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
                                    ->join('order_items', 'retailer_orders.id', '=', 'order_items.order_id')
                                    ->join('products', 'order_items.product_id', '=', 'products.id')
                                    ->select('products.name as product_name', DB::raw('SUM(order_items.quantity) as total_quantity_ordered'))
                                    ->groupBy('products.name')
                                    ->orderByDesc('total_quantity_ordered')
                                    ->limit(10)
                                    ->get();

        return response()->json($topProducts);
    }

    // API for Users by Credit (assuming 'credit' column on users table)
    public function getUsersByCredit(Request $request)
    {
        $user = Auth::user();
        $query = User::query();

        if ($user->hasRole('manager')) {
            // Assuming manager can only see credit of retailers/field staff under them
            // This needs to be refined based on how credit is managed and linked to users
            // For now, let's assume we only show users directly managed by the manager
            $managedUserIds = $user->fieldStaffs->pluck('user_id')->push($user->id); // Manager's own ID and their field staff's user IDs
            $query->whereIn('id', $managedUserIds);
        }

        $usersByCredit = $query->orderByDesc('credit') // Assuming 'credit' column exists
                               ->limit(10)
                               ->get(['name', 'credit']);

        return response()->json($usersByCredit);
    }

    // API for Users by Loyalty Points (assuming 'loyalty_points' column on users table)
    public function getUsersByLoyaltyPoints(Request $request)
    {
        $user = Auth::user();
        $query = User::query();

        if ($user->hasRole('manager')) {
            // Assuming manager can only see loyalty points of retailers/field staff under them
            $managedUserIds = $user->fieldStaffs->pluck('user_id')->push($user->id);
            $query->whereIn('id', $managedUserIds);
        }

        $usersByLoyaltyPoints = $query->orderByDesc('loyalty_points') // Assuming 'loyalty_points' column exists
                                      ->limit(10)
                                      ->get(['name', 'loyalty_points']);

        return response()->json($usersByLoyaltyPoints);
    }
}
