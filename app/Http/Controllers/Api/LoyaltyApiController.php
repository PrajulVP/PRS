<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\LoyaltySlab;

/**
 * @OA\Tag(
 *     name="Loyalty",
 *     description="API Endpoints for Loyalty Programme"
 * )
 */
class LoyaltyApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/retailer/loyalty-rewards",
     *     tags={"Retailer Loyalty"},
     *     summary="Get available loyalty rewards for the retailer",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getRetailerRewards(Request $request)
    {
        $user = Auth::user();
        $retailer = $user->retailer;

        if (!$retailer) {
            return response()->json(['status' => false, 'message' => 'Retailer not found'], 404);
        }

        $controller = new \App\Http\Controllers\LoyaltyPointsController();
        $rawRewards = $controller->calculateUpcomingRewards($retailer, 'brand');

        $formattedRewards = collect($rawRewards)->map(function ($b) {
            return [
                'brand' => $b['brand'],
                'current_total' => (float)$b['current_total'],
                'next_target' => $b['next_target'] ? (float)$b['next_target'] : null,
                'next_reward' => $b['next_reward'],
                'next_reward_options' => $b['next_reward_options'] ?: ($b['next_reward'] ? [$b['next_reward']] : []),
                'achieved_rewards' => collect($b['achieved_rewards'] ?? [])->map(function ($ar) {
                    return [
                        'slab_id' => $ar['slab_id'],
                        'threshold' => (float)$ar['threshold'],
                        'reward' => $ar['reward'],
                        'reward_options' => $ar['reward_options'] ?: [$ar['reward']],
                        'is_redeemed' => $ar['is_redeemed'] ?? false
                    ];
                })->values()->all(),
                'all_targets' => collect($b['all_targets'] ?? [])->map(function ($t) {
                    return [
                        'target' => (float)$t['target'],
                        'options' => $t['options']
                    ];
                })->values()->all()
            ];
        })->values()->all();

        return response()->json([
            'status' => true,
            'data' => $formattedRewards
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/retailer/loyalty-rewards/claim",
     *     tags={"Retailer Loyalty"},
     *     summary="Claim a loyalty reward",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"slab_id", "selected_reward"},
     *             @OA\Property(property="slab_id", type="integer", example=1),
     *             @OA\Property(property="selected_reward", type="string", example="1 Gold Coin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reward claimed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reward claimed successfully! Pending approval.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request or not enough points")
     * )
     */
    public function claimRetailerReward(Request $request)
    {
        $user = Auth::user();
        $retailer = $user->retailer;

        if (!$retailer) {
            return response()->json(['status' => false, 'message' => 'Retailer not found'], 404);
        }

        $request->validate([
            'slab_id' => 'required|exists:loyalty_slabs,id',
            'selected_reward' => 'required|string|max:255'
        ]);

        $slab = LoyaltySlab::with('brand')->find($request->slab_id);
        
        $alreadyClaimed = DB::table('loyalty_redemptions')
            ->where('retailer_id', $retailer->id)
            ->where('loyalty_slab_id', $slab->id)
            ->exists();
            
        if ($alreadyClaimed) {
            return response()->json(['status' => false, 'message' => 'You have already claimed this reward milestone.'], 400);
        }
        
        $controller = new \App\Http\Controllers\LoyaltyPointsController();
        $upcomingRewards = $controller->calculateUpcomingRewards($retailer, 'brand');
        $targetReward = collect($upcomingRewards)->firstWhere('brand', $slab->brand->name ?? '');
        
        $currentTotal = $targetReward['current_total'] ?? 0;
        
        if ($currentTotal < $slab->min_points) {
            return response()->json(['status' => false, 'message' => 'Not enough points to claim this reward.'], 400);
        }

        DB::table('loyalty_redemptions')->insert([
            'retailer_id' => $retailer->id,
            'loyalty_slab_id' => $slab->id,
            'selected_reward' => $request->selected_reward,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Reward claimed successfully! Pending approval.'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/fieldstaff/loyalty-redemptions",
     *     tags={"Field Staff Dashboard"},
     *     summary="Get approved loyalty redemptions to deliver",
     *     security={{"bearerAuth":{}}, {"deviceIdAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"approved", "all"}),
     *         description="Filter redemptions by status. Default is 'approved'. Pass 'all' to see history."
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="redemption_id", type="integer", example=12),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2026-09-09 14:20:00"),
     *                     @OA\Property(property="status", type="string", example="approved"),
     *                     @OA\Property(property="retailer_id", type="integer", example=42),
     *                     @OA\Property(property="shop_name", type="string", example="Metro Medicals"),
     *                     @OA\Property(property="owner_name", type="string", example="John Doe"),
     *                     @OA\Property(property="selected_reward", type="string", example="Smart Watch"),
     *                     @OA\Property(property="fallback_reward", type="string", example="Fitness Band"),
     *                     @OA\Property(property="brand", type="string", example="Atomeds"),
     *                     @OA\Property(property="threshold", type="number", format="float", example=500.00),
     *                     @OA\Property(property="device_id", type="string", example="uuid-1234-5678"),
     *                     @OA\Property(property="player_id", type="string", example="onesignal-player-id")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getFieldstaffRedemptions(Request $request)
    {
        $user = Auth::user();
        $fieldStaff = $user->fieldStaff;

        if (!$fieldStaff) {
            return response()->json(['status' => false, 'message' => 'Field staff not found'], 404);
        }

        $query = DB::table('loyalty_redemptions')
            ->join('retailers', 'loyalty_redemptions.retailer_id', '=', 'retailers.id')
            ->join('users', 'retailers.user_id', '=', 'users.id')
            ->leftJoin('loyalty_slabs', 'loyalty_redemptions.loyalty_slab_id', '=', 'loyalty_slabs.id')
            ->leftJoin('brands', 'loyalty_slabs.brand_id', '=', 'brands.id')
            ->where('retailers.field_staff_id', $fieldStaff->id);
            
        if ($request->has('status')) {
            $statusParam = strtolower(trim((string)$request->query('status')));
            if ($statusParam !== 'all' && $statusParam !== '') {
                $query->where('loyalty_redemptions.status', $statusParam);
            }
        } else {
            $query->where('loyalty_redemptions.status', 'approved');
        }
            
        $redemptions = $query->select(
                'loyalty_redemptions.id as redemption_id',
                'loyalty_redemptions.created_at',
                'loyalty_redemptions.status',
                'retailers.id as retailer_id',
                'retailers.shop_name',
                'users.name as owner_name',
                'loyalty_redemptions.selected_reward',
                'loyalty_slabs.gift_name as fallback_reward',
                'brands.name as brand',
                'loyalty_slabs.min_points as threshold',
                'users.device_uuid as device_id',
                'users.player_id'
            )
            ->orderBy('loyalty_redemptions.created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $redemptions
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/fieldstaff/loyalty-redemptions/{id}/confirm",
     *     tags={"Field Staff Dashboard"},
     *     summary="Confirm delivery of a loyalty reward",
     *     security={{"bearerAuth":{}}, {"deviceIdAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reward delivery confirmed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reward delivery confirmed successfully.")
     *         )
     *     )
     * )
     */
    public function confirmFieldstaffRedemption(Request $request, $id)
    {
        $user = Auth::user();
        $fieldStaff = $user->fieldStaff;

        if (!$fieldStaff) {
            return response()->json(['status' => false, 'message' => 'Field staff not found'], 404);
        }

        $redemption = DB::table('loyalty_redemptions')
            ->join('retailers', 'loyalty_redemptions.retailer_id', '=', 'retailers.id')
            ->where('loyalty_redemptions.id', $id)
            ->where('retailers.field_staff_id', $fieldStaff->id)
            ->where('loyalty_redemptions.status', 'approved')
            ->select('loyalty_redemptions.id')
            ->first();

        if (!$redemption) {
            return response()->json(['status' => false, 'message' => 'Invalid redemption or not authorized.'], 404);
        }

        DB::table('loyalty_redemptions')
            ->where('id', $id)
            ->update([
                'status' => 'delivered',
                'updated_at' => now(),
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Reward delivery confirmed successfully.'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/retailer/points-history",
     *     tags={"Retailer Loyalty"},
     *     summary="Get detailed earned points and claimed rewards history",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Earned points and claimed rewards history",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="earned_history",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="type", type="string", example="EARNED"),
     *                     @OA\Property(property="order_code", type="string", example="ORD-2026-001"),
     *                     @OA\Property(property="points", type="number", example=5220.00),
     *                     @OA\Property(property="date", type="string", example="2026-09-01 14:30:00")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="claimed_history",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="type", type="string", example="CLAIMED"),
     *                     @OA\Property(property="redemption_id", type="integer", example=12),
     *                     @OA\Property(property="brand", type="string", example="ATOMSHIELD"),
     *                     @OA\Property(property="reward_name", type="string", example="Smart Watch"),
     *                     @OA\Property(property="points_deducted", type="number", example=1000.00),
     *                     @OA\Property(property="status", type="string", example="approved"),
     *                     @OA\Property(property="claimed_at", type="string", example="2026-09-03 10:15:00")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function getPointsHistory(Request $request)
    {
        $user = Auth::user();
        $retailer = $user->retailer;

        if (!$retailer) {
            return response()->json(['message' => 'User is not a retailer'], 403);
        }

        // Earned points history from delivered orders
        $earnedHistory = \App\Models\RetailerOrder::where('retailer_id', $retailer->id)
            ->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED)
            ->orderBy('delivered_at', 'desc')
            ->get(['order_code', 'loyalty_points_earned', 'total_amount', 'delivered_at'])
            ->map(function ($order) {
                $pts = (float)$order->loyalty_points_earned > 0 ? (float)$order->loyalty_points_earned : (float)$order->total_amount;
                return [
                    'type' => 'EARNED',
                    'order_code' => $order->order_code,
                    'points' => $pts,
                    'date' => $order->delivered_at ? $order->delivered_at->format('Y-m-d H:i:s') : null,
                ];
            });

        // Claimed rewards history
        $claimedRedemptions = DB::table('loyalty_redemptions')
            ->join('loyalty_slabs', 'loyalty_redemptions.loyalty_slab_id', '=', 'loyalty_slabs.id')
            ->join('brands', 'loyalty_slabs.brand_id', '=', 'brands.id')
            ->where('loyalty_redemptions.retailer_id', $retailer->id)
            ->select(
                'loyalty_redemptions.id as redemption_id',
                'loyalty_redemptions.status',
                'loyalty_redemptions.created_at',
                'loyalty_redemptions.selected_reward',
                'loyalty_slabs.gift_name',
                'loyalty_slabs.min_points as threshold',
                'brands.name as brand'
            )
            ->orderBy('loyalty_redemptions.created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'type' => 'CLAIMED',
                    'redemption_id' => $r->redemption_id,
                    'brand' => $r->brand,
                    'reward_name' => $r->selected_reward ?: $r->gift_name,
                    'points_deducted' => (float)$r->threshold,
                    'status' => $r->status,
                    'claimed_at' => \Carbon\Carbon::parse($r->created_at)->format('Y-m-d H:i:s')
                ];
            });

        return response()->json([
            'status' => true,
            'earned_history' => $earnedHistory,
            'claimed_history' => $claimedRedemptions
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/retailer/loyalty-roadmap",
     *     tags={"Retailer Loyalty"},
     *     summary="Get brand milestone roadmap with claimed/unlocked status and reward options",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Brand milestone roadmaps",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="brand", type="string", example="ATOMSHIELD"),
     *                     @OA\Property(property="current_earned_points", type="number", example=5220.00),
     *                     @OA\Property(property="next_target", type="number", example=10000.00),
     *                     @OA\Property(property="next_reward", type="string", example="Gold Coin 5g"),
     *                     @OA\Property(
     *                         property="next_reward_options",
     *                         type="array",
     *                         @OA\Items(type="string", example="24K Gold Coin")
     *                     ),
     *                     @OA\Property(
     *                         property="milestone_roadmap",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="slab_id", type="integer", example=1),
     *                             @OA\Property(property="threshold", type="number", example=5000.00),
     *                             @OA\Property(property="default_reward", type="string", example="Smart Watch"),
     *                             @OA\Property(
     *                                 property="available_options",
     *                                 type="array",
     *                                 @OA\Items(type="string", example="Black Color")
     *                             ),
     *                             @OA\Property(property="is_unlocked", type="boolean", example=true),
     *                             @OA\Property(property="is_claimed", type="boolean", example=true),
     *                             @OA\Property(
     *                                 property="claim_details",
     *                                 type="object",
     *                                 nullable=true,
     *                                 @OA\Property(property="redemption_id", type="integer", example=12),
     *                                 @OA\Property(property="selected_option", type="string", example="Black Color"),
     *                                 @OA\Property(property="status", type="string", example="approved"),
     *                                 @OA\Property(property="claimed_at", type="string", example="2026-09-03 10:15:00")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function getLoyaltyRoadmap(Request $request)
    {
        $user = Auth::user();
        $retailer = $user->retailer;

        if (!$retailer) {
            return response()->json(['message' => 'User is not a retailer'], 403);
        }

        $loyaltyRulesCollection = LoyaltySlab::join('brands', 'loyalty_slabs.brand_id', '=', 'brands.id')
            ->select('loyalty_slabs.*', 'brands.name as type')
            ->orderBy('brands.name')
            ->orderBy('min_points')
            ->get()
            ->groupBy('type');

        $brandTotals = DB::table('retailer_order_items')
            ->join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
            ->join('products', 'retailer_order_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->where('retailer_orders.retailer_id', $retailer->id)
            ->where('retailer_orders.status', \App\Models\RetailerOrder::STATUS_DELIVERED)
            ->select('brands.name as brand', DB::raw('SUM(retailer_order_items.unit_price * retailer_order_items.quantity) as total_ptr'))
            ->groupBy('brands.name')
            ->pluck('total_ptr', 'brand')
            ->toArray();

        $redemptionsBySlab = DB::table('loyalty_redemptions')
            ->where('retailer_id', $retailer->id)
            ->get()
            ->keyBy('loyalty_slab_id');

        $brandRoadmaps = [];

        foreach ($loyaltyRulesCollection as $brand => $rules) {
            $brandEarned = (float)($brandTotals[$brand] ?? 0);
            $milestones = [];
            $nextRule = null;

            foreach ($rules as $rule) {
                $isRedeemed = isset($redemptionsBySlab[$rule->id]);
                $redemption = $isRedeemed ? $redemptionsBySlab[$rule->id] : null;
                $options = json_decode($rule->reward_options, true) ?: [$rule->gift_name];
                $isUnlocked = $brandEarned >= $rule->min_points;

                if (!$isUnlocked && !$nextRule) {
                    $nextRule = $rule;
                }

                $milestones[] = [
                    'slab_id' => $rule->id,
                    'threshold' => (float)$rule->min_points,
                    'default_reward' => $rule->gift_name,
                    'available_options' => $options,
                    'is_unlocked' => $isUnlocked,
                    'is_claimed' => $isRedeemed,
                    'claim_details' => $isRedeemed ? [
                        'redemption_id' => $redemption->id,
                        'selected_option' => $redemption->selected_reward ?: $rule->gift_name,
                        'status' => $redemption->status,
                        'claimed_at' => \Carbon\Carbon::parse($redemption->created_at)->format('Y-m-d H:i:s')
                    ] : null
                ];
            }

            $brandRoadmaps[] = [
                'brand' => $brand,
                'current_earned_points' => $brandEarned,
                'next_target' => $nextRule ? (float)$nextRule->min_points : null,
                'next_reward' => $nextRule ? $nextRule->gift_name : null,
                'next_reward_options' => $nextRule ? (json_decode($nextRule->reward_options, true) ?: [$nextRule->gift_name]) : [],
                'milestone_roadmap' => $milestones
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $brandRoadmaps
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/loyalty/retailer-details/{retailer_id}",
     *     tags={"Loyalty"},
     *     summary="Get loyalty rewards, claims, and points history for a specific retailer (for Field Staff and Sales Managers)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="retailer_id",
     *         in="path",
     *         required=true,
     *         description="Retailer ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Retailer loyalty details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="retailer",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=45),
     *                 @OA\Property(property="shop_name", type="string", example="Apollo Pharmacy"),
     *                 @OA\Property(property="contact_no", type="string", example="9876543210")
     *             ),
     *             @OA\Property(
     *                 property="summary",
     *                 type="object",
     *                 @OA\Property(property="earned_points", type="string", example="5220.00"),
     *                 @OA\Property(property="claimed_points", type="string", example="1500.00"),
     *                 @OA\Property(property="remaining_points", type="string", example="3720.00")
     *             ),
     *             @OA\Property(
     *                 property="earned_history",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             ),
     *             @OA\Property(
     *                 property="claimed_history",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             ),
     *             @OA\Property(
     *                 property="roadmap",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Retailer not found"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function getRetailerLoyaltyDetailsForStaff(Request $request, $retailer_id)
    {
        $user = Auth::user();

        // 1. Verify retailer exists
        $retailer = \App\Models\Retailer::with('user')->find($retailer_id);
        if (!$retailer) {
            return response()->json(['status' => false, 'message' => 'Retailer not found.'], 404);
        }

        // 2. Role-based authorization
        if ($user->hasRole('fieldstaff')) {
            $fieldStaff = $user->fieldStaff;
            if (!$fieldStaff || $retailer->field_staff_id != $fieldStaff->id) {
                return response()->json(['status' => false, 'message' => 'Unauthorized access to this retailer.'], 403);
            }
        } elseif ($user->hasRole('salesmanager')) {
            $salesManager = $user->salesManager;
            if (!$salesManager) {
                return response()->json(['status' => false, 'message' => 'Sales Manager profile not found.'], 403);
            }
            $fieldStaffIds = \App\Models\FieldStaff::where('sales_manager_id', $salesManager->id)
                ->orWhere('sales_manager_id', $salesManager->user_id)
                ->pluck('id')
                ->toArray();
            if (!in_array($retailer->field_staff_id, $fieldStaffIds)) {
                return response()->json(['status' => false, 'message' => 'Unauthorized access to this retailer.'], 403);
            }
        } elseif (!$user->hasRole('admin')) {
            return response()->json(['status' => false, 'message' => 'Unauthorized role.'], 403);
        }

        // 3. Earned points history from delivered orders
        $earnedHistory = \App\Models\RetailerOrder::where('retailer_id', $retailer->id)
            ->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED)
            ->orderBy('delivered_at', 'desc')
            ->get(['order_code', 'loyalty_points_earned', 'total_amount', 'delivered_at'])
            ->map(function ($order) {
                $pts = (float)$order->loyalty_points_earned > 0 ? (float)$order->loyalty_points_earned : (float)$order->total_amount;
                return [
                    'type' => 'EARNED',
                    'order_code' => $order->order_code,
                    'points' => $pts,
                    'date' => $order->delivered_at ? $order->delivered_at->format('Y-m-d H:i:s') : null,
                ];
            });

        // 4. Claimed rewards history
        $claimedRedemptions = DB::table('loyalty_redemptions')
            ->join('loyalty_slabs', 'loyalty_redemptions.loyalty_slab_id', '=', 'loyalty_slabs.id')
            ->join('brands', 'loyalty_slabs.brand_id', '=', 'brands.id')
            ->where('loyalty_redemptions.retailer_id', $retailer->id)
            ->select(
                'loyalty_redemptions.id as redemption_id',
                'loyalty_redemptions.status',
                'loyalty_redemptions.created_at',
                'loyalty_redemptions.selected_reward',
                'loyalty_slabs.gift_name',
                'loyalty_slabs.min_points as threshold',
                'brands.name as brand'
            )
            ->orderBy('loyalty_redemptions.created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'type' => 'CLAIMED',
                    'redemption_id' => $r->redemption_id,
                    'brand' => $r->brand,
                    'reward_name' => $r->selected_reward ?: $r->gift_name,
                    'points_deducted' => (float)$r->threshold,
                    'status' => $r->status,
                    'claimed_at' => \Carbon\Carbon::parse($r->created_at)->format('Y-m-d H:i:s')
                ];
            });

        // 5. Total calculations
        $earnedPoints = (float)\App\Models\RetailerOrder::where('retailer_id', $retailer->id)
            ->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED)
            ->selectRaw('COALESCE(SUM(CASE WHEN loyalty_points_earned > 0 THEN loyalty_points_earned ELSE total_amount END), 0) as total_pts')
            ->value('total_pts');

        $claimedPoints = (float)DB::table('loyalty_redemptions')
            ->join('loyalty_slabs', 'loyalty_redemptions.loyalty_slab_id', '=', 'loyalty_slabs.id')
            ->where('loyalty_redemptions.retailer_id', $retailer->id)
            ->whereIn('loyalty_redemptions.status', ['pending', 'approved', 'delivered'])
            ->sum('loyalty_slabs.min_points');

        $remainingPoints = max(0, $earnedPoints - $claimedPoints);

        // 6. Brand Milestone Roadmap
        $loyaltyRulesCollection = LoyaltySlab::join('brands', 'loyalty_slabs.brand_id', '=', 'brands.id')
            ->select('loyalty_slabs.*', 'brands.name as type')
            ->orderBy('brands.name')
            ->orderBy('min_points')
            ->get()
            ->groupBy('type');

        $brandTotals = DB::table('retailer_order_items')
            ->join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
            ->join('products', 'retailer_order_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->where('retailer_orders.retailer_id', $retailer->id)
            ->where('retailer_orders.status', \App\Models\RetailerOrder::STATUS_DELIVERED)
            ->select('brands.name as brand', DB::raw('SUM(retailer_order_items.unit_price * retailer_order_items.quantity) as total_ptr'))
            ->groupBy('brands.name')
            ->pluck('total_ptr', 'brand')
            ->toArray();

        $redemptionsBySlab = DB::table('loyalty_redemptions')
            ->where('retailer_id', $retailer->id)
            ->get()
            ->keyBy('loyalty_slab_id');

        $brandRoadmaps = [];
        foreach ($loyaltyRulesCollection as $brand => $rules) {
            $brandEarned = (float)($brandTotals[$brand] ?? 0);
            $milestones = [];
            $nextRule = null;

            foreach ($rules as $rule) {
                $isRedeemed = isset($redemptionsBySlab[$rule->id]);
                $redemption = $isRedeemed ? $redemptionsBySlab[$rule->id] : null;
                $options = json_decode($rule->reward_options, true) ?: [$rule->gift_name];
                $isUnlocked = $brandEarned >= $rule->min_points;

                if (!$isUnlocked && !$nextRule) {
                    $nextRule = $rule;
                }

                $milestones[] = [
                    'slab_id' => $rule->id,
                    'threshold' => (float)$rule->min_points,
                    'default_reward' => $rule->gift_name,
                    'available_options' => $options,
                    'is_unlocked' => $isUnlocked,
                    'is_claimed' => $isRedeemed,
                    'claim_details' => $isRedeemed ? [
                        'redemption_id' => $redemption->id,
                        'selected_option' => $redemption->selected_reward ?: $rule->gift_name,
                        'status' => $redemption->status,
                        'claimed_at' => \Carbon\Carbon::parse($redemption->created_at)->format('Y-m-d H:i:s')
                    ] : null
                ];
            }

            $brandRoadmaps[] = [
                'brand' => $brand,
                'current_earned_points' => $brandEarned,
                'next_target' => $nextRule ? (float)$nextRule->min_points : null,
                'next_reward' => $nextRule ? $nextRule->gift_name : null,
                'milestone_roadmap' => $milestones
            ];
        }

        return response()->json([
            'status' => true,
            'retailer' => [
                'id' => $retailer->id,
                'shop_name' => $retailer->shop_name,
                'contact_no' => $retailer->contact_no,
                'owner_name' => $retailer->user->name ?? 'N/A'
            ],
            'summary' => [
                'earned_points' => number_format($earnedPoints, 2, '.', ''),
                'claimed_points' => number_format($claimedPoints, 2, '.', ''),
                'remaining_points' => number_format($remainingPoints, 2, '.', ''),
            ],
            'earned_history' => $earnedHistory,
            'claimed_history' => $claimedRedemptions,
            'roadmap' => $brandRoadmaps
        ]);
    }
}
