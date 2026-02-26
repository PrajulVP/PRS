<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\RetailerOrder;
use App\Models\DistributorOrder;
# [ADD] use App\Models\Retailer;
use App\Models\Retailer;
use App\Models\Product;

class DistributorDashboardApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/distributor/dashboard",
     *     summary="Get distributor dashboard summary data",
     *     tags={"Distributor Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard data for the logged-in distributor",
     *         @OA\JsonContent(
     *             @OA\Property(property="counts", type="object"),
     *             @OA\Property(property="retailer_order_stats", type="object"),
     *             @OA\Property(property="distributor_order_stats", type="object"),
     *             @OA\Property(property="recent_retailer_orders", type="array", @OA\Items()),
     *             @OA\Property(property="chart_data", type="object"),
     *             @OA\Property(property="top_retailers", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function index()
    {
        $distributorId = $this->getDistributorId();
        if ($distributorId instanceof \Illuminate\Http\JsonResponse) return $distributorId;

        // 1. Counts
        $retailerCount = Retailer::where('distributor_id', $distributorId)->count();
        $productCount = DB::table('distributor_product')
            ->where('distributor_id', $distributorId)
            ->distinct('product_id')->count('product_id');

        $counts = [
            'retailers' => $retailerCount,
            'products' => $productCount,
        ];

        // 2. Retailer Order Stats
        $retailerOrderStats = $this->calculateOrderStatusDistribution($distributorId);

        // 3. Distributor Order Stats
        $distributorOrderQuery = DistributorOrder::where('distributor_id', $distributorId);
        $distributorOrderStats = [
            'total' => (clone $distributorOrderQuery)->count(),
            'pending' => (clone $distributorOrderQuery)->where('status', 'pending')->count(),
            'approved' => (clone $distributorOrderQuery)->where('status', 'approved')->count(),
            'delivered' => (clone $distributorOrderQuery)->where('status', 'delivered')->count(),
            'cancelled' => (clone $distributorOrderQuery)->where('status', 'cancelled')->count(),
        ];

        // 4. Recent Retailer Orders
        $recentRetailerOrders = RetailerOrder::where('distributor_id', $distributorId)
            ->with(['retailer.user'])
            ->latest()
            ->take(5)
            ->get();

        // 5. Chart Data
        $chartData = $this->calculateTotalOrdersOverTime($distributorId);

        // 6. Top Retailers
        $topRetailers = $this->calculateTopRetailers($distributorId);

        return response()->json([
            'counts' => $counts,
            'retailer_order_stats' => $retailerOrderStats,
            'distributor_order_stats' => $distributorOrderStats,
            'recent_retailer_orders' => $recentRetailerOrders,
            'chart_data' => $chartData,
            'top_retailers' => $topRetailers,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/distributor/dashboard/order-status-distribution",
     *     summary="Get retailer order status distribution",
     *     tags={"Distributor Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Status distribution")
     * )
     */
    public function getOrderStatusDistribution()
    {
        $distributorId = $this->getDistributorId();
        if ($distributorId instanceof \Illuminate\Http\JsonResponse) return $distributorId;

        return response()->json($this->calculateOrderStatusDistribution($distributorId));
    }

    /**
     * @OA\Get(
     *     path="/api/distributor/dashboard/total-orders-over-time",
     *     summary="Get total orders over time",
     *     tags={"Distributor Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Chart data")
     * )
     */
    public function getTotalOrdersOverTime()
    {
        $distributorId = $this->getDistributorId();
        if ($distributorId instanceof \Illuminate\Http\JsonResponse) return $distributorId;

        return response()->json($this->calculateTotalOrdersOverTime($distributorId));
    }

    /**
     * @OA\Get(
     *     path="/api/distributor/dashboard/orders-by-retailer",
     *     summary="Get orders grouped by retailer",
     *     tags={"Distributor Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Top retailers")
     * )
     */
    public function getOrdersByRetailer()
    {
        $distributorId = $this->getDistributorId();
        if ($distributorId instanceof \Illuminate\Http\JsonResponse) return $distributorId;

        return response()->json($this->calculateTopRetailers($distributorId));
    }

    /**
     * @OA\Get(
     *     path="/api/distributor/dashboard/top-products",
     *     summary="Get top products for this distributor",
     *     tags={"Distributor Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Top products")
     * )
     */
    public function getTopProducts()
    {
        $distributorId = $this->getDistributorId();
        if ($distributorId instanceof \Illuminate\Http\JsonResponse) return $distributorId;

        $topProducts = DB::table('retailer_order_items')
            ->join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
            ->join('products', 'retailer_order_items.product_id', '=', 'products.id')
            ->where('retailer_orders.distributor_id', $distributorId)
            ->select('products.product_name', DB::raw('SUM(retailer_order_items.quantity) as total_quantity'))
            ->groupBy('retailer_order_items.product_id', 'products.product_name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        return response()->json($topProducts);
    }

    // Helper Methods

    private function getDistributorId()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('distributor')) {
            return response()->json(['error' => 'Only distributors can access this dashboard'], 403);
        }

        $distributor = $user->distributor;
        if (!$distributor) {
            return response()->json(['error' => 'Distributor profile not found'], 404);
        }

        return $distributor->id;
    }

    private function calculateOrderStatusDistribution($distributorId)
    {
        $query = RetailerOrder::where('distributor_id', $distributorId);
        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'approved' => (clone $query)->whereIn('status', ['approved', 'approved_by_distributor', 'approved_by_admin'])->count(),
            'delivered' => (clone $query)->where('status', 'delivered')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];
    }

    private function calculateTotalOrdersOverTime($distributorId)
    {
        $monthlyOrders = RetailerOrder::where('distributor_id', $distributorId)
            ->select(
                DB::raw('count(id) as count'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_year"),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month')
            )->groupBy('year', 'month', 'month_year')
            ->orderBy('year', 'desc')->orderBy('month', 'desc')
            ->take(6)->get()->sortBy('month_year');

        return [
            'months' => $monthlyOrders->pluck('month_year')->values(),
            'counts' => $monthlyOrders->pluck('count')->values(),
        ];
    }

    private function calculateTopRetailers($distributorId)
    {
        return RetailerOrder::select('retailer_id', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
            ->where('distributor_id', $distributorId)
            ->groupBy('retailer_id')->orderByDesc('total_orders')->take(5)
            ->with('retailer.user')->get();
    }
}
