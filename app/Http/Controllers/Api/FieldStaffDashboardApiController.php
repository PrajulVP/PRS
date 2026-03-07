<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RetailerOrder;
use App\Models\Retailer;

class FieldStaffDashboardApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/field-staff/dashboard",
     *     summary="Get Field Staff dashboard summary data",
     *     tags={"Field Staff Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard data for the logged-in Field Staff"
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->hasRole('fieldstaff')) {
            return response()->json(['error' => 'Only Field Staff can access this dashboard'], 403);
        }

        $fieldStaffId = $user->fieldStaff->id;

        // Retailer Order Stats
        $retailerOrderQuery = RetailerOrder::where(function ($q) use ($fieldStaffId) {
            $q->where('fieldstaff_id', $fieldStaffId)
                ->orWhereHas('retailer', function ($qr) use ($fieldStaffId) {
                    $qr->where('field_staff_id', $fieldStaffId);
                });
        });

        $orderStats = [
            'total' => (clone $retailerOrderQuery)->count(),
            'pending_approval' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_PENDING)->count(), // Orders they need to interact with
            'processing' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_PROCESSING)->count(),
            'accepted' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_ACCEPTED)->count(),
            'delivered' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_DELIVERED)->count(),
            'cancelled' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_CANCELLED)->count(),
            'rejected' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_REJECTED)->count(),
        ];

        // Counts
        $counts = [
            'total_retailers' => Retailer::where('field_staff_id', $fieldStaffId)->count(),
            'actionable_orders' => $orderStats['pending_approval']
        ];

        return response()->json([
            'order_stats' => $orderStats,
            'counts' => $counts,
        ]);
    }
}
