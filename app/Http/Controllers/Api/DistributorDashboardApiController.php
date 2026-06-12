<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\RetailerOrder;
use App\Models\DistributorOrder;
use App\Models\Retailer;
use App\Models\Product;
use App\Models\DistributorTarget;
use Carbon\Carbon;

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
     *             @OA\Property(property="top_retailers", type="array", @OA\Items()),
     *             @OA\Property(property="credit_balance", type="string", example="5000.00")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function index(Request $request)
    {
        $distributorId = $this->getDistributorId();
        if ($distributorId instanceof \Illuminate\Http\JsonResponse) return $distributorId;

        $period = $request->get('period', 'monthly');
        $endDate = now();
        $startDate = now();

        switch ($period) {
            case 'weekly':
                $startDate = now()->subDays(6)->startOfDay();
                break;
            case 'yearly':
                $startDate = now()->startOfYear();
                break;
            case 'monthly':
            default:
                $period = 'monthly';
                $startDate = now()->startOfMonth();
                break;
        }

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
        $retailerOrderStats = $this->calculateOrderStatusDistribution($distributorId, $startDate, $endDate);

        // 3. Distributor Order Stats
        $distributorOrderQuery = DistributorOrder::where('distributor_id', $distributorId);
        $distributorOrderStats = [
            'total' => (clone $distributorOrderQuery)->count(),
            'pending' => (clone $distributorOrderQuery)->where('status', DistributorOrder::STATUS_PENDING)->count(),
            'processing' => (clone $distributorOrderQuery)->where('status', DistributorOrder::STATUS_PROCESSING)->count(),
            'accepted' => (clone $distributorOrderQuery)->where('status', DistributorOrder::STATUS_APPROVED)->count(),
            'delivered' => (clone $distributorOrderQuery)->where('status', DistributorOrder::STATUS_DELIVERED)->count(),
            'cancelled' => (clone $distributorOrderQuery)->where('status', DistributorOrder::STATUS_CANCELLED)->count(),
        ];

        // 4. Recent Retailer Orders
        $recentRetailerOrders = RetailerOrder::where('distributor_id', $distributorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['retailer.user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($order) {
                $orderArray = $order->toArray();
                $placedAt = $order->placed_at ?? $order->created_at;
                $deliveredAt = $order->delivered_at;
                
                if ($deliveredAt) {
                    $days = $placedAt->diffInDays($deliveredAt);
                    $orderArray['supply_chain_track'] = [
                        'status' => 'Completed',
                        'days' => $days,
                        'label' => $days . ' days to deliver'
                    ];
                } else {
                    $days = $placedAt->diffInDays(now());
                    $orderArray['supply_chain_track'] = [
                        'status' => 'In Progress',
                        'days' => $days,
                        'label' => $days . ' days since ordered'
                    ];
                }
                return $orderArray;
            });

        // 5. Chart Data
        $chartData = $this->calculateTotalOrdersOverTime($distributorId); // We leave chart data logic intact for backwards compatibility or fix as needed

        // 6. Top Retailers
        $topRetailers = $this->calculateTopRetailers($distributorId, $startDate, $endDate);

        // 7. Target vs Achievement
        $targetAchievement = $this->calculateTargetAchievement($distributorId);

        // 8. Turnaround Time
        $avgTurnaroundTime = $this->calculateAvgTurnaroundTime($distributorId);

        $distributorUser = Auth::user()->distributor;
        $creditBalance = (string)($distributorUser->credit_balance ?? 0);

        return response()->json([
            'period' => $period,
            'counts' => $counts,
            'retailer_order_stats' => $retailerOrderStats,
            'distributor_order_stats' => $distributorOrderStats,
            'recent_retailer_orders' => $recentRetailerOrders,
            'chart_data' => $chartData,
            'top_retailers' => $topRetailers,
            'target_achievement' => $targetAchievement,
            'avg_turnaround_time' => $avgTurnaroundTime,
            'credit_balance' => $creditBalance,
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
     *     path="/api/distributor/dashboard/actionable-orders-count",
     *     summary="Get the total count of retailer orders awaiting distributor approval",
     *     tags={"Distributor Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Total count of actionable orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="count", type="integer")
     *         )
     *     )
     * )
     */
    public function getActionableOrdersCount()
    {
        $distributorId = $this->getDistributorId();
        if ($distributorId instanceof \Illuminate\Http\JsonResponse) return $distributorId;

        $count = RetailerOrder::where('distributor_id', $distributorId)
            ->where('status', RetailerOrder::STATUS_PROCESSING)
            ->count();

        return response()->json(['count' => $count]);
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

    private function calculateOrderStatusDistribution($distributorId, $startDate = null, $endDate = null)
    {
        $query = RetailerOrder::where('distributor_id', $distributorId);
        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', RetailerOrder::STATUS_PENDING)->count(),
            'processing' => (clone $query)->where('status', RetailerOrder::STATUS_PROCESSING)->count(),
            'accepted' => (clone $query)->where('status', RetailerOrder::STATUS_APPROVED)->count(),
            'delivered' => (clone $query)->where('status', RetailerOrder::STATUS_DELIVERED)->count(),
            'cancelled' => (clone $query)->where('status', RetailerOrder::STATUS_CANCELLED)->count(),
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

    private function calculateTopRetailers($distributorId, $startDate = null, $endDate = null)
    {
        $query = RetailerOrder::select('retailer_id', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
            ->where('distributor_id', $distributorId);
            
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
            
        return $query->groupBy('retailer_id')->orderByDesc('total_orders')->take(5)
            ->with('retailer.user')->get();
    }

    private function calculateTargetAchievement($distributorId)
    {
        $now = now();
        $target = DistributorTarget::where('distributor_id', $distributorId)
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->first();

        if (!$target) {
            return [
                'target_amount' => 0,
                'achieved_amount' => 0,
                'percentage' => 0,
                'message' => 'No target set for this month'
            ];
        }

        // Calculate achievement dynamically if needed, or use stored value
        // For now, let's calculate from delivered orders in current month
        $achieved = RetailerOrder::where('distributor_id', $distributorId)
            ->where('status', RetailerOrder::STATUS_DELIVERED)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('total_amount');

        return [
            'target_amount' => (float)$target->target_amount,
            'achieved_amount' => (float)$achieved,
            'percentage' => $target->target_amount > 0 ? round(($achieved / $target->target_amount) * 100, 2) : 0
        ];
    }

    private function calculateAvgTurnaroundTime($distributorId)
    {
        $avgMinutes = RetailerOrder::where('distributor_id', $distributorId)
            ->where('status', RetailerOrder::STATUS_DELIVERED)
            ->whereNotNull('placed_at')
            ->whereNotNull('delivered_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, placed_at, delivered_at)) as avg_time'))
            ->first()->avg_time;

        if (!$avgMinutes) return 'N/A';

        $hours = floor($avgMinutes / 60);
        $minutes = $avgMinutes % 60;

        if ($hours > 24) {
            $days = floor($hours / 24);
            $remainingHours = $hours % 24;
            return "{$days}d {$remainingHours}h";
        }

        return "{$hours}h {$minutes}m";
    }
}
