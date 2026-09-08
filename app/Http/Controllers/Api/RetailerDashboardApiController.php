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
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         required=false,
     *         description="Statistics period: weekly, monthly, yearly",
     *         @OA\Schema(type="string", enum={"weekly", "monthly", "yearly"}, default="monthly")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics with overall summary and brand breakdown",
     *         @OA\JsonContent(
     *             @OA\Property(property="period", type="string", example="monthly"),
     *             @OA\Property(property="total_orders", type="integer", example=150),
     *             @OA\Property(property="pending_orders", type="integer", example=10),
     *             @OA\Property(property="processing_orders", type="integer", example=5),
     *             @OA\Property(property="approved_orders", type="integer", example=20),
     *             @OA\Property(property="delivered_orders", type="integer", example=110),
     *             @OA\Property(property="cancelled_orders", type="integer", example=3),
     *             @OA\Property(property="rejected_orders", type="integer", example=2),
     *             @OA\Property(property="credit_balance", type="string", example="1500.50"),
     *             @OA\Property(property="credit_limit", type="string", example="5000.00"),
     *             @OA\Property(
     *                 property="overall_summary",
     *                 type="object",
     *                 @OA\Property(property="earned_points", type="string", example="5220.00"),
     *                 @OA\Property(property="claimed_points", type="string", example="1500.00"),
     *                 @OA\Property(property="remaining_points", type="string", example="3720.00"),
     *                 @OA\Property(
     *                     property="brand_breakdown",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="brand", type="string", example="ATOMSHIELD"),
     *                         @OA\Property(property="earned_points", type="string", example="5220.00"),
     *                         @OA\Property(property="claimed_points", type="string", example="1000.00"),
     *                         @OA\Property(property="remaining_points", type="string", example="4220.00")
     *                     )
     *                 )
     *             )
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

        $retailer = $user->retailer;
        $retailerId = $retailer->id;

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

        // 1. Calculate overall earned points across all brands
        $earnedPoints = (float) RetailerOrder::where('retailer_id', $retailerId)
            ->where('status', RetailerOrder::STATUS_DELIVERED)
            ->selectRaw('COALESCE(SUM(CASE WHEN loyalty_points_earned > 0 THEN loyalty_points_earned ELSE total_amount END), 0) as total_pts')
            ->value('total_pts');

        // 2. Calculate overall claimed points across all brands
        $claimedRedemptions = \Illuminate\Support\Facades\DB::table('loyalty_redemptions')
            ->join('loyalty_slabs', 'loyalty_redemptions.loyalty_slab_id', '=', 'loyalty_slabs.id')
            ->join('brands', 'loyalty_slabs.brand_id', '=', 'brands.id')
            ->where('loyalty_redemptions.retailer_id', $retailerId)
            ->whereIn('loyalty_redemptions.status', ['pending', 'approved', 'delivered'])
            ->select('loyalty_redemptions.id', 'loyalty_redemptions.loyalty_slab_id', 'loyalty_slabs.min_points', 'brands.name as brand')
            ->get();

        $claimedPoints = (float) $claimedRedemptions->sum('min_points');
        $remainingPoints = max(0, $earnedPoints - $claimedPoints);

        // 3. Brand Breakdown
        $loyaltyRulesCollection = \App\Models\LoyaltySlab::join('brands', 'loyalty_slabs.brand_id', '=', 'brands.id')
            ->select('loyalty_slabs.*', 'brands.name as type')
            ->orderBy('brands.name')
            ->orderBy('min_points')
            ->get()
            ->groupBy('type');

        $brandTotals = \Illuminate\Support\Facades\DB::table('retailer_order_items')
            ->join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
            ->join('products', 'retailer_order_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->where('retailer_orders.retailer_id', $retailerId)
            ->where('retailer_orders.status', RetailerOrder::STATUS_DELIVERED)
            ->select('brands.name as brand', \Illuminate\Support\Facades\DB::raw('SUM(retailer_order_items.unit_price * retailer_order_items.quantity) as total_ptr'))
            ->groupBy('brands.name')
            ->pluck('total_ptr', 'brand')
            ->toArray();

        $redemptionsBySlab = \Illuminate\Support\Facades\DB::table('loyalty_redemptions')
            ->where('retailer_id', $retailerId)
            ->get()
            ->keyBy('loyalty_slab_id');

        $brandBreakdown = [];
        foreach ($loyaltyRulesCollection as $brand => $rules) {
            $brandEarned = (float)($brandTotals[$brand] ?? 0);
            $brandClaimed = 0;
            foreach ($rules as $rule) {
                if (isset($redemptionsBySlab[$rule->id])) {
                    $brandClaimed += (float)$rule->min_points;
                }
            }
            $brandRemaining = max(0, $brandEarned - $brandClaimed);

            $brandBreakdown[] = [
                'brand' => $brand,
                'earned_points' => number_format($brandEarned, 2, '.', ''),
                'claimed_points' => number_format($brandClaimed, 2, '.', ''),
                'remaining_points' => number_format($brandRemaining, 2, '.', '')
            ];
        }

        return response()->json([
            'period' => $period,
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'processing_orders' => $processingOrders,
            'approved_orders' => $approvedOrders,
            'delivered_orders' => $deliveredOrders,
            'cancelled_orders' => $cancelledOrders,
            'rejected_orders' => $rejectedOrders,
            'credit_balance' => (string)($retailer->credit_balance ?? 0),
            'credit_limit' => (string)($retailer->credit_limit ?? 0),
            'overall_summary' => [
                'earned_points' => number_format($earnedPoints, 2, '.', ''),
                'claimed_points' => number_format($claimedPoints, 2, '.', ''),
                'remaining_points' => number_format($remainingPoints, 2, '.', ''),
                'brand_breakdown' => $brandBreakdown,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/retailer/loyalty-points",
     *     summary="Get retailer total loyalty points and credit balances",
     *     tags={"Retailer Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Retailer loyalty points summary",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_points", type="string", example="3720.00"),
     *             @OA\Property(property="credit_balance", type="string", example="1500.50"),
     *             @OA\Property(property="credit_limit", type="string", example="5000.00"),
     *             @OA\Property(
     *                 property="history",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="order_code", type="string", example="ORD-2026-001"),
     *                     @OA\Property(property="points_earned", type="number", example=5220.00),
     *                     @OA\Property(property="date", type="string", example="2026-09-01 14:30:00")
     *                 )
     *             )
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

        return response()->json([
            'total_points' => (string)$totalLoyaltyPoints,
            'credit_balance' => (string)($retailer->credit_balance ?? 0),
            'credit_limit' => (string)($retailer->credit_limit ?? 0),
            'history' => $pointsHistory
        ]);
    }
}
