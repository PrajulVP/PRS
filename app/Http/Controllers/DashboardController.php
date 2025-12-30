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

        // 1. Counts
        $counts = [
            'distributors' => \App\Models\Distributor::count(),
            'retailers' => \App\Models\Retailer::count(),
            'products' => \App\Models\Product::count(),
            'sales_managers' => \App\Models\SalesManager::count(),
            'field_staff' => \App\Models\FieldStaff::count(),
        ];

        // 2. Order Stats (Retailer Orders)
        $retailerOrderStats = [
            'total' => RetailerOrder::count(),
            'pending' => RetailerOrder::where('status', 'pending')->count(),
            'approved' => RetailerOrder::whereIn('status', ['approved', 'approved_by_distributor', 'approved_by_admin'])->count(),
            'delivered' => RetailerOrder::where('status', 'delivered')->count(),
            'cancelled' => RetailerOrder::where('status', 'cancelled')->count(),
        ];

        // 3. Order Stats (Distributor Orders)
        $distributorOrderStats = [
            'total' => DistributorOrder::count(),
            'pending' => DistributorOrder::where('status', 'pending')->count(),
            'approved' => DistributorOrder::where('status', 'approved')->count(), // Adjust status if needed
            'delivered' => DistributorOrder::where('status', 'delivered')->count(),
            'cancelled' => DistributorOrder::where('status', 'cancelled')->count(),
        ];

        // 4. Recent Orders (Combined or Separate)
        $recentRetailerOrders = RetailerOrder::with(['retailer.user'])->latest()->take(5)->get();

        // 5. Chart Data: Monthly Retailer Orders (Last 6 Months)
        $monthlyOrders = RetailerOrder::select(
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
            ->sortBy('month_year'); // Sort back to chronological for chart

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
