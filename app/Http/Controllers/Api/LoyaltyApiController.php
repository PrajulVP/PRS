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

        // We can reuse the logic from LoyaltyPointsController or duplicate the essential parts here
        $controller = new \App\Http\Controllers\LoyaltyPointsController();
        $rewards = $controller->calculateUpcomingRewards($retailer, 'brand');

        return response()->json([
            'status' => true,
            'data' => $rewards
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
        
        $controller = new \App\Http\Controllers\LoyaltyPointsController();
        $upcomingRewards = $controller->calculateUpcomingRewards($retailer, 'brand');
        $targetReward = collect($upcomingRewards)->firstWhere('brand', $slab->brand->name ?? '');
        
        if (!$targetReward || $targetReward['current_total'] < $slab->min_points) {
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
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
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
            ->join('loyalty_slabs', 'loyalty_redemptions.loyalty_slab_id', '=', 'loyalty_slabs.id')
            ->join('brands', 'loyalty_slabs.brand_id', '=', 'brands.id')
            ->where('retailers.field_staff_id', $fieldStaff->id);
            
        if ($request->status !== 'all') {
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
}
