<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RetailerOrder;
use Illuminate\Http\Request;

class RetailerDashboardApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/retailer/dashboard/statistics",
     *     summary="Get retailer dashboard statistics",
     *     tags={"Retailer Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_orders", type="integer", example=150),
     *             @OA\Property(property="pending_orders", type="integer", example=10),
     *             @OA\Property(property="processing_orders", type="integer", example=5),
     *             @OA\Property(property="approved_orders", type="integer", example=20),
     *             @OA\Property(property="delivered_orders", type="integer", example=110),
     *             @OA\Property(property="cancelled_orders", type="integer", example=3),
     *             @OA\Property(property="rejected_orders", type="integer", example=2),
     *             @OA\Property(property="credit_balance", type="string", example="1500.50"),
     *             @OA\Property(property="credit_limit", type="string", example="5000.00")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function getStatistics(Request $request)
    {
        $user = auth('api')->user();

        if (!$user->retailer) {
            return response()->json(['message' => 'User is not a retailer'], 403);
        }

        $retailerId = $user->retailer->id;

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

        $baseQuery = RetailerOrder::where('retailer_id', $retailerId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalOrders = (clone $baseQuery)->count();
        $pendingOrders = (clone $baseQuery)->where('status', RetailerOrder::STATUS_PENDING)->count();
        $processingOrders = (clone $baseQuery)->where('status', RetailerOrder::STATUS_PROCESSING)->count();
        $approvedOrders = (clone $baseQuery)->where('status', RetailerOrder::STATUS_APPROVED)->count();
        $deliveredOrders = (clone $baseQuery)->where('status', RetailerOrder::STATUS_DELIVERED)->count();
        $cancelledOrders = (clone $baseQuery)->where('status', RetailerOrder::STATUS_CANCELLED)->count();
        $rejectedOrders = (clone $baseQuery)->where('status', RetailerOrder::STATUS_REJECTED)->count();


        return response()->json([
            'period' => $period,
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'processing_orders' => $processingOrders,
            'approved_orders' => $approvedOrders,
            'delivered_orders' => $deliveredOrders,
            'cancelled_orders' => $cancelledOrders,
            'rejected_orders' => $rejectedOrders,
            'credit_balance' => (string)($user->retailer->credit_balance ?? 0),
            'credit_limit' => (string)($user->retailer->credit_limit ?? 0),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/retailer/loyalty-points",
     *     summary="Get retailer loyalty points and history",
     *     tags={"Retailer Loyalty"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Loyalty points details",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_points", type="string", example="500"),
     *             @OA\Property(property="credit_balance", type="string", example="1500.50"),
     *             @OA\Property(property="credit_limit", type="string", example="5000.00"),
     *             @OA\Property(property="history", type="array", @OA\Items(
     *                 @OA\Property(property="order_code", type="string", example="RO-ABC123"),
     *                 @OA\Property(property="points_earned", type="integer", example=50),
     *                 @OA\Property(property="date", type="string", format="date-time")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function getLoyaltyPoints(Request $request)
    {
        $user = auth('api')->user();

        if (!$user->retailer) {
            return response()->json(['message' => 'User is not a retailer'], 403);
        }

        $retailer = $user->retailer;

        // Get delivered orders where loyalty points were earned
        $pointsHistory = RetailerOrder::where('retailer_id', $retailer->id)
            ->whereNotNull('loyalty_points_earned')
            ->where('loyalty_points_earned', '>', 0)
            ->where('status', RetailerOrder::STATUS_DELIVERED)
            ->orderBy('delivered_at', 'desc')
            ->get(['order_code', 'loyalty_points_earned', 'delivered_at'])
            ->map(function ($order) {
                return [
                    'order_code' => $order->order_code,
                    'points_earned' => $order->loyalty_points_earned,
                    'date' => $order->delivered_at ? $order->delivered_at->format('Y-m-d H:i:s') : null,
                ];
            });

        $totalLoyaltyPoints = RetailerOrder::where('retailer_id', $retailer->id)
            ->whereNotNull('loyalty_points_earned')
            ->where('loyalty_points_earned', '>', 0)
            ->where('status', RetailerOrder::STATUS_DELIVERED)
            ->sum('loyalty_points_earned');

        // Upcoming Rewards Logic based on brand totals
        $loyaltyRulesCollection = \App\Models\LoyaltySlab::orderBy('type')
            ->orderBy('min_points')
            ->get()
            ->groupBy('type');

        // Get total PTR per brand for this retailer
        $brandTotals = \Illuminate\Support\Facades\DB::table('retailer_order_items')
            ->join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
            ->join('products', 'retailer_order_items.product_id', '=', 'products.id')
            ->where('retailer_orders.retailer_id', $retailer->id)
            ->where('retailer_orders.status', RetailerOrder::STATUS_DELIVERED)
            ->select('products.brand', \Illuminate\Support\Facades\DB::raw('SUM(retailer_order_items.unit_price * retailer_order_items.quantity) as total_ptr'))
            ->groupBy('products.brand')
            ->pluck('total_ptr', 'brand')
            ->toArray();

        // Get already redeemed slab ids for this retailer
        $redeemedSlabIds = \Illuminate\Support\Facades\DB::table('loyalty_redemptions')
            ->where('retailer_id', $retailer->id)
            ->pluck('loyalty_slab_id')
            ->toArray();

        $upcomingRewards = [];
        foreach ($loyaltyRulesCollection as $brand => $rules) {
            $currentTotal = $brandTotals[$brand] ?? 0;
            $achievedRules = [];
            $nextRule = null;

            foreach ($rules as $rule) {
                if ($currentTotal >= $rule->min_points) {
                    $achievedRules[] = [
                        'slab_id' => $rule->id,
                        'threshold' => $rule->min_points,
                        'reward' => $rule->gift_name,
                        'is_redeemed' => in_array($rule->id, $redeemedSlabIds),
                    ];
                } else {
                    if (!$nextRule) {
                        $nextRule = $rule;
                    }
                }
            }

            $upcomingRewards[] = [
                'brand' => $brand,
                'current_total' => $currentTotal,
                'next_target' => $nextRule ? $nextRule->min_points : null,
                'next_reward' => $nextRule ? $nextRule->gift_name : null,
                'achieved_rewards' => $achievedRules,
            ];
        }

        return response()->json([
            'total_points' => (string)$totalLoyaltyPoints,
            'credit_balance' => (string)($retailer->credit_balance ?? 0),
            'credit_limit' => (string)($retailer->credit_limit ?? 0),
            'upcoming_rewards' => $upcomingRewards,
            'history' => $pointsHistory
        ]);
    }
}
