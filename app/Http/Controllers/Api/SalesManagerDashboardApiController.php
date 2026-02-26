<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\RetailerOrder;
use App\Models\DistributorOrder;
use App\Models\Retailer;
use App\Models\User;

class SalesManagerDashboardApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/sales-manager/dashboard",
     *     summary="Get Sales Manager dashboard summary data",
     *     tags={"Sales Manager Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard data for the logged-in Sales Manager",
     *         @OA\JsonContent(
     *             @OA\Property(property="retailer_order_stats", type="object"),
     *             @OA\Property(property="distributor_order_stats", type="object"),
     *             @OA\Property(property="counts", type="object"),
     *             @OA\Property(property="top_fieldstaff", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('salesmanager')) {
            return response()->json(['error' => 'Only Sales Managers can access this dashboard'], 403);
        }

        $salesManager = $user->salesManager;
        if (!$salesManager) {
            return response()->json(['error' => 'Sales Manager profile not found'], 404);
        }

        $fieldStaffIds = $salesManager->fieldStaffs->pluck('id');

        // 1. Retailer Order Stats (Orders under this SM's fieldstaff)
        $retailerOrderQuery = RetailerOrder::whereHas('retailer', function ($q) use ($fieldStaffIds) {
            $q->whereIn('field_staff_id', $fieldStaffIds);
        });

        $retailerOrderStats = [
            'total' => (clone $retailerOrderQuery)->count(),
            'pending' => (clone $retailerOrderQuery)->where('status', 'pending')->count(),
            'approved' => (clone $retailerOrderQuery)->whereIn('status', ['approved', 'approved_by_distributor', 'approved_by_admin'])->count(),
            'delivered' => (clone $retailerOrderQuery)->where('status', 'delivered')->count(),
            'cancelled' => (clone $retailerOrderQuery)->where('status', 'cancelled')->count(),
        ];

        // 2. Distributor Order Stats (Orders by distributors assigned to this SM)
        $distributorOrderQuery = DistributorOrder::whereHas('distributor', function ($q) use ($salesManager) {
            $q->where('sales_manager_id', $salesManager->id);
        });

        $distributorOrderStats = [
            'total' => (clone $distributorOrderQuery)->count(),
            'pending' => (clone $distributorOrderQuery)->where('status', 'pending')->count(),
            'approved' => (clone $distributorOrderQuery)->where('status', 'approved')->count(),
            'delivered' => (clone $distributorOrderQuery)->where('status', 'delivered')->count(),
            'cancelled' => (clone $distributorOrderQuery)->where('status', 'cancelled')->count(),
        ];

        // 3. Counts
        $counts = [
            'distributors' => \App\Models\Distributor::where('sales_manager_id', $salesManager->id)->count(),
            'field_staff' => $fieldStaffIds->count(),
            'retailers' => Retailer::whereIn('field_staff_id', $fieldStaffIds)->count(),
            'pending_retailer_approvals' => Retailer::whereIn('field_staff_id', $fieldStaffIds)
                ->whereHas('user', function ($q) {
                    $q->where('status', 'inactive');
                })->count(),
        ];

        // 4. Top FieldStaff performance
        $topFieldStaff = RetailerOrder::select('fieldstaff_id', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
            ->whereIn('fieldstaff_id', $fieldStaffIds)
            ->groupBy('fieldstaff_id')->orderByDesc('total_orders')->take(5)
            ->with('fieldStaff.user')->get();

        return response()->json([
            'retailer_order_stats' => $retailerOrderStats,
            'distributor_order_stats' => $distributorOrderStats,
            'counts' => $counts,
            'top_fieldstaff' => $topFieldStaff,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/sales-manager/pending-retailers",
     *     summary="List retailers waiting for approval",
     *     tags={"Sales Manager Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of pending retailers")
     * )
     */
    public function getPendingRetailers()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('salesmanager')) return response()->json(['error' => 'Unauthorized'], 403);

        $salesManager = $user->salesManager;
        $fieldStaffIds = $salesManager->fieldStaffs->pluck('id');

        $pendingRetailers = Retailer::with(['user', 'fieldStaff.user', 'district', 'area'])
            ->whereIn('field_staff_id', $fieldStaffIds)
            ->whereHas('user', function ($q) {
                $q->where('status', 'inactive');
            })
            ->latest()
            ->get();

        return response()->json($pendingRetailers);
    }

    /**
     * @OA\Post(
     *     path="/api/sales-manager/retailers/{id}/approve",
     *     summary="Approve (activate) a retailer account",
     *     tags={"Sales Manager Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Retailer approved successfully")
     * )
     */
    public function approveRetailer($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('salesmanager')) return response()->json(['error' => 'Unauthorized'], 403);

        $salesManager = $user->salesManager;
        $fieldStaffIds = $salesManager->fieldStaffs->pluck('id');

        $retailer = Retailer::whereIn('field_staff_id', $fieldStaffIds)->findOrFail($id);

        $retailer->user->update(['status' => 'active']);

        return response()->json(['message' => "Retailer {$retailer->user->name} approved successfully."]);
    }

    /**
     * @OA\Get(
     *     path="/api/sales-manager/fieldstaffs",
     *     summary="List all field staff assigned to this Sales Manager",
     *     tags={"Sales Manager Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of field staff")
     * )
     */
    public function getFieldStaffs()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('salesmanager')) return response()->json(['error' => 'Unauthorized'], 403);

        $salesManager = $user->salesManager;
        $fieldStaffs = \App\Models\FieldStaff::with('user')
            ->where('sales_manager_id', $salesManager->id)
            ->get();

        return response()->json($fieldStaffs);
    }

    /**
     * @OA\Get(
     *     path="/api/sales-manager/retailers",
     *     summary="List all retailers under this Sales Manager (via field staff)",
     *     tags={"Sales Manager Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of retailers")
     * )
     */
    public function getRetailers()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('salesmanager')) return response()->json(['error' => 'Unauthorized'], 403);

        $salesManager = $user->salesManager;
        $fieldStaffIds = $salesManager->fieldStaffs->pluck('id');

        $retailers = Retailer::with(['user', 'fieldStaff.user', 'district', 'area'])
            ->whereIn('field_staff_id', $fieldStaffIds)
            ->get();

        return response()->json($retailers);
    }

    /**
     * @OA\Get(
     *     path="/api/sales-manager/retailers/{id}/loyalty-points",
     *     summary="Get loyalty points summary and history for a retailer",
     *     tags={"Sales Manager Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Loyalty points data")
     * )
     */
    public function getRetailerLoyaltyDetails($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('salesmanager')) return response()->json(['error' => 'Unauthorized'], 403);

        $salesManager = $user->salesManager;
        $fieldStaffIds = $salesManager->fieldStaffs->pluck('id');

        $retailer = Retailer::whereIn('field_staff_id', $fieldStaffIds)->findOrFail($id);

        $history = $retailer->retailerOrders()
            ->with('items.product')
            ->whereIn('status', ['accepted_by_distributor', 'delivered'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'retailer' => [
                'id' => $retailer->id,
                'shop_name' => $retailer->shop_name,
                'loyalty_points' => $retailer->loyalty_points,
            ],
            'history' => $history->map(function ($order) {
                return [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'points_earned' => $order->loyalty_points_earned,
                    'date' => $order->updated_at->format('Y-m-d H:i:s'),
                    'items_summary' => $order->items->map(function ($item) {
                        return ($item->product->product_name ?? 'N/A') . ' (' . $item->quantity . ')';
                    })->implode(', ')
                ];
            })
        ]);
    }
}
