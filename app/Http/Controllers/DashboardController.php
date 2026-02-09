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

        // 1. Base Queries
        $retailerQuery = Retailer::query();
        $distributorQuery = \App\Models\Distributor::query();
        $productQuery = Product::query();
        $salesManagerQuery = \App\Models\SalesManager::query();
        $fieldStaffQuery = FieldStaff::query();

        $retailerOrderQuery = RetailerOrder::query();
        $distributorOrderQuery = DistributorOrder::query();

        // 2. Role-Based Filtering
        if ($user->hasRole(['superadmin', 'admin'])) {
            // No filtering needed
        } elseif ($user->hasRole('salesmanager')) {
            $salesManager = $user->salesManager;
            if ($salesManager) {
                // Retailers assigned to field staff under this sales manager
                $fieldStaffIds = $salesManager->fieldStaffs->pluck('id');
                $retailerQuery->whereIn('field_staff_id', $fieldStaffIds);

                // Retailer Orders
                $retailerOrderQuery->whereHas('retailer', function ($q) use ($fieldStaffIds) {
                    $q->whereIn('field_staff_id', $fieldStaffIds);
                });

                // Distributor Orders - Distributors assigned to this sales manager (if relationship exists, assuming logic similar to field staff or based on region)
                // Assuming sales managers manage distributors in their area/district or directly assigned?
                // Standard logic: Distributors might be assigned to a Sales Manager.
                // Checking Distributor model: belongsTo SalesManager? (Need to verify).
                // If not explicit, maybe restrict Distributor Orders to those approved by this Sales Manager?
                // For now, let's filter Distributor orders where the distributor is in the same district/area or accepted by this SM.
                // Keeping it open or basic for SM if logic unclear, but preventing seeing ALL.
                // Let's assume SM permissions on Distributor Orders are "view all" or "view assigned".
                // Based on `DistributorOrderController`, SM accepts orders. 
                // Let's filter dist orders where `sales_manager_id` matches? (Distributor table has sales_manager_id?)
                // Let's check schemas if possible, but safe bet:
                // If Distributor has sales_manager_id:
                $distributorQuery->where('sales_manager_id', $salesManager->id);
                $distributorOrderQuery->whereHas('distributor', function ($q) use ($salesManager) {
                    $q->where('sales_manager_id', $salesManager->id);
                });
            }
        } elseif ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            if ($distributor) {
                // Stats for Distributors
                $retailerOrderQuery->where('distributor_id', $distributor->id);
                $distributorOrderQuery->where('distributor_id', $distributor->id);

                // Can see retailers assigned to them?
                $retailerQuery->where('distributor_id', $distributor->id);
            }
        } elseif ($user->hasRole('fieldstaff')) {
            $fieldStaff = $user->fieldStaff;
            if ($fieldStaff) {
                $retailerQuery->where('field_staff_id', $fieldStaff->id);
                // Fix: Filter orders via retailer relationship
                $retailerOrderQuery->whereHas('retailer', function ($q) use ($fieldStaff) {
                    $q->where('field_staff_id', $fieldStaff->id);
                });
                // Field staff usually don't see distributor orders or manage them.
                $distributorOrderQuery->where('id', 0); // No access
            }
        } elseif ($user->hasRole('retailer')) {
            $retailer = $user->retailer;
            if ($retailer) {
                $retailerOrderQuery->where('retailer_id', $retailer->id);
                $distributorOrderQuery->where('id', 0); // No access
                $retailerQuery->where('id', 0); // Self only, or no list
            }
        }

        // 3. Counts
        // 3. Counts - Product Logic
        $productCount = Product::count(); // Default for Admin, Sales Manager, Field Staff

        if ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            if ($distributor) {
                // Count products in distributor's stock
                $productCount = DB::table('distributor_product')
                    ->where('distributor_id', $distributor->id)
                    ->distinct('product_id')
                    ->count('product_id');
            }
        } elseif ($user->hasRole('retailer')) {
            $retailer = $user->retailer;
            if ($retailer && $retailer->distributor_id) {
                // Count products available from their distributor
                $productCount = DB::table('distributor_product')
                    ->where('distributor_id', $retailer->distributor_id)
                    ->distinct('product_id')
                    ->count('product_id');
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

        // 7. Chart Data: Monthly Retailer Orders (Last 6 Months)
        $monthlyOrders = $retailerOrderQuery->clone()->select(
            DB::raw('count(id) as count'),
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_year"),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month')
        )
            ->groupBy('year', 'month', 'month_year')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(6)
            ->get()
            ->sortBy('month_year'); // Sort back to chronological

        $chartData = [
            'months' => $monthlyOrders->pluck('month_year')->values(),
            'counts' => $monthlyOrders->pluck('count')->values(),
        ];

        return view('dashboard', compact(
            'counts',
            'retailerOrderStats',
            'distributorOrderStats',
            'recentRetailerOrders',
            'chartData'
        ));
    }
}
