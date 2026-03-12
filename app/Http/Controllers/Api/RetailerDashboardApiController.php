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
     *             @OA\Property(property="total_loyalty_points", type="integer", example=500)
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

        // Calculate loyalty points dynamically from delivered orders that earned points
        $totalLoyaltyPoints = RetailerOrder::where('retailer_id', $retailerId)
            ->whereNotNull('loyalty_points_earned')
            ->where('loyalty_points_earned', '>', 0)
            ->where('status', RetailerOrder::STATUS_DELIVERED)
            ->sum('loyalty_points_earned');

        return response()->json([
            'period' => $period,
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'processing_orders' => $processingOrders,
            'approved_orders' => $approvedOrders,
            'delivered_orders' => $deliveredOrders,
            'cancelled_orders' => $cancelledOrders,
            'rejected_orders' => $rejectedOrders,
            'total_loyalty_points' => $totalLoyaltyPoints,
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
     *             @OA\Property(property="total_points", type="integer", example=500),
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

        return response()->json([
            'total_points' => $totalLoyaltyPoints,
            'history' => $pointsHistory
        ]);
    }
}
