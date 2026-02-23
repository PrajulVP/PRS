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

        // Base Queries
        $retailerQuery = Retailer::query();
        $distributorQuery = \App\Models\Distributor::query();
        $productQuery = Product::query();
        $salesManagerQuery = \App\Models\SalesManager::query();
        $fieldStaffQuery = FieldStaff::query();

        $retailerOrderQuery = RetailerOrder::query();
        $distributorOrderQuery = DistributorOrder::query();

        // Role-Specific Metrics
        $topRetailers = collect();
        $topDistributors = collect();
        $topFieldStaff = collect();
        $totalLoyaltyPoints = 0;
        $monthlyDistributorOrdersChart = null;

        // Role-Based Filtering
        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            // See lists and graphs of both distributor and retailer orders, top users, loyalty points, and fieldstaffs
            $totalLoyaltyPoints = RetailerOrder::sum('loyalty_points_earned') ?? 0;

            $topRetailers = RetailerOrder::select('retailer_id', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
                ->groupBy('retailer_id')->orderByDesc('total_orders')->take(5)
                ->with('retailer.user')->get();

            $topDistributors = RetailerOrder::select('distributor_id', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
                ->groupBy('distributor_id')->orderByDesc('total_orders')->take(5)
                ->with('distributor.user')->get();

            $topFieldStaff = RetailerOrder::select('fieldstaff_id', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
                ->whereNotNull('fieldstaff_id')
                ->groupBy('fieldstaff_id')->orderByDesc('total_orders')->take(5)
                ->with('fieldStaff.user')->get();
        } elseif ($user->hasRole('salesmanager')) {
            // See distributor order and retailer order statistics, and the performance of fieldstaffs under them.
            $salesManager = $user->salesManager;
            if ($salesManager) {
                // Fieldstaff performance
                $fieldStaffIds = $salesManager->fieldStaffs->pluck('id');
                $retailerQuery->whereIn('field_staff_id', $fieldStaffIds);

                $retailerOrderQuery->whereHas('retailer', function ($q) use ($fieldStaffIds) {
                    $q->whereIn('field_staff_id', $fieldStaffIds);
                });

                $distributorQuery->where('sales_manager_id', $salesManager->id);
                $distributorOrderQuery->whereHas('distributor', function ($q) use ($salesManager) {
                    $q->where('sales_manager_id', $salesManager->id);
                });

                $topFieldStaff = RetailerOrder::select('fieldstaff_id', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
                    ->whereIn('fieldstaff_id', $fieldStaffIds)
                    ->groupBy('fieldstaff_id')->orderByDesc('total_orders')->take(5)
                    ->with('fieldStaff.user')->get();
            }
        } elseif ($user->hasRole('distributor')) {
            // See all orders and their mostly ordered retailers
            $distributor = $user->distributor;
            if ($distributor) {
                $retailerOrderQuery->where('distributor_id', $distributor->id);
                $distributorOrderQuery->where('distributor_id', $distributor->id);
                $retailerQuery->where('distributor_id', $distributor->id);

                $topRetailers = RetailerOrder::select('retailer_id', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
                    ->where('distributor_id', $distributor->id)
                    ->groupBy('retailer_id')->orderByDesc('total_orders')->take(5)
                    ->with('retailer.user')->get();
            }
        } elseif ($user->hasRole('fieldstaff')) {
            // See retailers orders and statistics of their orders, performance to understand if they are working better or not.
            $fieldStaff = $user->fieldStaff;
            if ($fieldStaff) {
                $retailerQuery->where('field_staff_id', $fieldStaff->id);
                $retailerOrderQuery->whereHas('retailer', function ($q) use ($fieldStaff) {
                    $q->where('field_staff_id', $fieldStaff->id);
                });
                $distributorOrderQuery->where('id', 0); // No access
            }
        } elseif ($user->hasRole('retailer')) {
            // attractive, showing their orders.
            $retailer = $user->retailer;
            if ($retailer) {
                $retailerOrderQuery->where('retailer_id', $retailer->id);
                $distributorOrderQuery->where('id', 0); // No access
                $retailerQuery->where('id', 0); // Self only, or no list

                $totalLoyaltyPoints = RetailerOrder::where('retailer_id', $retailer->id)->sum('loyalty_points_earned') ?? 0;
            }
        }

        // 3. Counts - Product Logic
        $productCount = Product::count(); // Default for Admin, Sales Manager, Field Staff
        if ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            if ($distributor) {
                $productCount = DB::table('distributor_product')
                    ->where('distributor_id', $distributor->id)
                    ->distinct('product_id')->count('product_id');
            }
        } elseif ($user->hasRole('retailer')) {
            $retailer = $user->retailer;
            if ($retailer && $retailer->distributor_id) {
                $productCount = DB::table('distributor_product')
                    ->where('distributor_id', $retailer->distributor_id)
                    ->distinct('product_id')->count('product_id');
            }
        }

        $counts = [
            'distributors' => $distributorQuery->count(),
            'retailers' => $retailerQuery->count(),
            'products' => $productCount,
            'sales_managers' => $salesManagerQuery->count(),
            'field_staff' => $fieldStaffQuery->count(),
        ];

        // 4. Order Stats (Retailer Orders)
        $retailerOrderStats = [
            'total' => $retailerOrderQuery->clone()->count(),
            'pending' => $retailerOrderQuery->clone()->where('status', 'pending')->count(),
            'approved' => $retailerOrderQuery->clone()->whereIn('status', ['approved', 'approved_by_distributor', 'approved_by_admin'])->count(),
            'delivered' => $retailerOrderQuery->clone()->where('status', 'delivered')->count(),
            'cancelled' => $retailerOrderQuery->clone()->where('status', 'cancelled')->count(),
        ];

        // 5. Order Stats (Distributor Orders)
        $distributorOrderStats = [
            'total' => $distributorOrderQuery->clone()->count(),
            'pending' => $distributorOrderQuery->clone()->where('status', 'pending')->count(),
            'approved' => $distributorOrderQuery->clone()->where('status', 'approved')->count(),
            'delivered' => $distributorOrderQuery->clone()->where('status', 'delivered')->count(),
            'cancelled' => $distributorOrderQuery->clone()->where('status', 'cancelled')->count(),
        ];

        // 6. Recent Orders
        $recentRetailerOrders = $retailerOrderQuery->clone()->with(['retailer.user'])->latest()->take(5)->get();
        $recentDistributorOrders = collect();
        if ($user->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            $recentDistributorOrders = $distributorOrderQuery->clone()->with(['distributor.user'])->latest()->take(5)->get();
        }

        // 7. Chart Data: Monthly Retailer Orders (Last 6 Months)
        $monthlyOrders = $retailerOrderQuery->clone()->select(
            DB::raw('count(id) as count'),
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_year"),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month')
        )->groupBy('year', 'month', 'month_year')
            ->orderBy('year', 'desc')->orderBy('month', 'desc')
            ->take(6)->get()->sortBy('month_year'); // Sort back to chronological

        $chartData = [
            'months' => $monthlyOrders->pluck('month_year')->values(),
            'counts' => $monthlyOrders->pluck('count')->values(),
        ];

        // 8. Chart Data: Monthly Distributor Orders (Admin/SM)
        if ($user->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            $monthlyDistributorOrders = $distributorOrderQuery->clone()->select(
                DB::raw('count(id) as count'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_year"),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month')
            )->groupBy('year', 'month', 'month_year')
                ->orderBy('year', 'desc')->orderBy('month', 'desc')
                ->take(6)->get()->sortBy('month_year');

            $monthlyDistributorOrdersChart = [
                'months' => $monthlyDistributorOrders->pluck('month_year')->values(),
                'counts' => $monthlyDistributorOrders->pluck('count')->values(),
            ];
        }

        return view('dashboard', compact(
            'counts',
            'retailerOrderStats',
            'distributorOrderStats',
            'recentRetailerOrders',
            'recentDistributorOrders',
            'chartData',
            'topRetailers',
            'topDistributors',
            'topFieldStaff',
            'totalLoyaltyPoints',
            'monthlyDistributorOrdersChart'
        ));
    }
}
