<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\RetailerOrder;
use App\Models\Retailer;
use App\Models\User;

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
    public function index(Request $request)
    {
        $user = Auth::user();
        
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
        })->whereBetween('created_at', [$startDate, $endDate]);

        $orderStats = [
            'total' => (clone $retailerOrderQuery)->count(),
            'pending_approval' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_PENDING)->count(), // Orders they need to interact with
            'processing' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_PROCESSING)->count(),
            'accepted' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_APPROVED)->count(),
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
            'period' => $period,
            'order_stats' => $orderStats,
            'counts' => $counts,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/field-staff/retailers/loyalty-points",
     *     summary="List all retailers under this Field Staff with their loyalty points",
     *     tags={"Field Staff Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of retailers' loyalty points")
     * )
     */
    public function getRetailersLoyaltyPoints(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('fieldstaff')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $fieldStaffId = $user->fieldStaff->id;

        $retailers = Retailer::where('field_staff_id', $fieldStaffId)
            ->get(['id', 'shop_name', 'contact_no'])
            ->map(function ($retailer) {
                // Dynamically calculate loyalty points and order stats
                $orderQuery = RetailerOrder::where('retailer_id', $retailer->id)
                    ->where('status', RetailerOrder::STATUS_DELIVERED);
                
                $points = (clone $orderQuery)
                    ->whereNotNull('loyalty_points_earned')
                    ->where('loyalty_points_earned', '>', 0)
                    ->sum('loyalty_points_earned');

                $totalOrders = (clone $orderQuery)->count();
                $lastOrder = (clone $orderQuery)->latest('updated_at')->first();

                return [
                    'id' => $retailer->id,
                    'shop_name' => $retailer->shop_name,
                    'contact_no' => $retailer->contact_no,
                    'loyalty_points' => $points
                ];
            });

        return response()->json($retailers);
    }

    /**
     * @OA\Get(
     *     path="/api/field-staff/retailers/{id}/loyalty-points",
     *     summary="Get loyalty points summary and history for a retailer under this Field Staff",
     *     tags={"Field Staff Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Loyalty points data")
     * )
     */
    public function getRetailerLoyaltyDetails($id)
    {
        $user = Auth::user();
        if (!$user->hasRole('fieldstaff')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $fieldStaffId = $user->fieldStaff->id;

        $retailer = Retailer::where('field_staff_id', $fieldStaffId)->findOrFail($id);

        $history = $retailer->retailerOrders()
            ->with('items.product')
            ->whereIn('status', [RetailerOrder::STATUS_APPROVED, RetailerOrder::STATUS_DELIVERED])
            ->whereNotNull('loyalty_points_earned')
            ->where('loyalty_points_earned', '>', 0)
            ->orderBy('updated_at', 'desc')
            ->get();

        $totalLoyaltyPoints = $retailer->retailerOrders()
            ->whereNotNull('loyalty_points_earned')
            ->where('loyalty_points_earned', '>', 0)
            ->where('status', RetailerOrder::STATUS_DELIVERED)
            ->sum('loyalty_points_earned');

        return response()->json([
            'retailer' => [
                'id' => $retailer->id,
                'shop_name' => $retailer->shop_name,
                'loyalty_points' => $totalLoyaltyPoints,
            ],
            'history' => $history->map(function ($order) {
                return [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'points_earned' => $order->loyalty_points_earned,
                    'order_value' => $order->total_amount,
                    'date' => $order->updated_at->format('Y-m-d H:i:s'),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->product_name ?? 'Unknown',
                            'brand' => $item->product->brand ?? 'N/A',
                            'quantity' => $item->quantity,
                            'unit' => $item->unit ?? 'N/A',
                            'unit_price' => $item->unit_price,
                            'total_amount' => $item->total_amount
                        ];
                    })
                ];
            })
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/field-staff/retailers",
     *     summary="Create a new Retailer",
     *     tags={"Field Staff Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation","shop_name","contact_no","pincode","gst","district_id","area_id"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string"),
     *             @OA\Property(property="shop_name", type="string"),
     *             @OA\Property(property="contact_no", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="pincode", type="string"),
     *             @OA\Property(property="gst", type="string"),
     *             @OA\Property(property="drug_license_no", type="string"),
     *             @OA\Property(property="district_id", type="integer"),
     *             @OA\Property(property="area_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Retailer created successfully")
     * )
     */
    public function storeRetailer(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('fieldstaff')) {
            return response()->json(['error' => 'Unauthorized. Only Field Staff can create Retailers.'], 403);
        }

        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4|confirmed',
        ]);

        $retailerData = $request->validate([
            'shop_name' => 'required|string|max:255',
            'pincode' => 'required',
            'gst' => 'required|unique:retailers',
            'drug_license_no' => 'nullable|string|max:255',
            'contact_no' => 'required|digits:10',
            'address' => 'required',
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
        ]);

        try {
            DB::beginTransaction();

            $newUser = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'role' => 'retailer',
                'status' => 'inactive',
                'contact_no' => $retailerData['contact_no'],
                'address' => $retailerData['address'],
                'pincode' => $retailerData['pincode'],
            ]);
            $newUser->assignRole('retailer');

            $fieldstaff = $user->fieldStaff;

            $retailer = new Retailer($retailerData);
            $retailer->user_id = $newUser->id;
            $retailer->field_staff_id = $fieldstaff->id;
            $retailer->sales_manager_id = $fieldstaff->sales_manager_id;
            $retailer->save();

            // Notify the assigned Sales Manager
            if ($retailer->sales_manager_id) {
                $salesManagerUser = User::whereHas('salesManager', function ($q) use ($retailer) {
                    $q->where('id', $retailer->sales_manager_id);
                })->first();

                if ($salesManagerUser && method_exists($this, 'notifyUnique')) {
                    $this->notifyUnique($salesManagerUser, new \App\Notifications\UserApprovalRequired(
                        $newUser,
                        "New Retailer {$newUser->name} from {$retailer->shop_name} has registered and requires your approval.",
                        url('/admin/retailers')
                    ));
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Retailer created successfully and is pending approval.',
                'retailer' => $retailer->load('user')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create retailer. ' . $e->getMessage()], 500);
        }
    }
}
