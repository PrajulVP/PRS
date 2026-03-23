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
use App\Models\FieldStaff;
use Illuminate\Support\Facades\Hash;
use App\Traits\OneSignalNotifications;
use App\Traits\HandlesNotifications;

class SalesManagerDashboardApiController extends Controller
{
    use HandlesNotifications, OneSignalNotifications;
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
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
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
        })->whereBetween('created_at', [$startDate, $endDate]);

        $retailerOrderStats = [
            'total' => (clone $retailerOrderQuery)->count(),
            'pending' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_PENDING)->count(),
            'processing' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_PROCESSING)->count(),
            'accepted' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_APPROVED)->count(),
            'delivered' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_DELIVERED)->count(),
            'cancelled' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_CANCELLED)->count(),
        ];

        // 2. Distributor Order Stats (Orders by distributors assigned to this SM)
        $distributorOrderQuery = DistributorOrder::whereHas('distributor', function ($q) use ($salesManager) {
            $q->where('sales_manager_id', $salesManager->id);
        })->whereBetween('created_at', [$startDate, $endDate]);

        $distributorOrderStats = [
            'total' => (clone $distributorOrderQuery)->count(),
            'pending' => (clone $distributorOrderQuery)->where('status', DistributorOrder::STATUS_PENDING)->count(),
            'processing' => (clone $distributorOrderQuery)->where('status', DistributorOrder::STATUS_PROCESSING)->count(),
            'accepted' => (clone $distributorOrderQuery)->where('status', DistributorOrder::STATUS_APPROVED)->count(),
            'delivered' => (clone $distributorOrderQuery)->where('status', DistributorOrder::STATUS_DELIVERED)->count(),
            'cancelled' => (clone $distributorOrderQuery)->where('status', DistributorOrder::STATUS_CANCELLED)->count(),
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
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('fieldstaff_id')->orderByDesc('total_orders')->take(5)
            ->with('fieldStaff.user')->get();

        return response()->json([
            'period' => $period,
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

        // OneSignal Push
        $this->sendOneSignalPush(
            [$retailer->user->id],
            "Your retailer account has been approved and activated. Welcome!",
            ['user_id' => $retailer->user->id, 'type' => 'user_approval'],
            'Account Activated'
        );

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

        $retailers = Retailer::with(['user', 'district', 'area'])
            ->whereIn('field_staff_id', $fieldStaffIds)
            ->get();

        return response()->json($retailers);
    }

    /**
     * @OA\Get(
     *     path="/api/sales-manager/retailers/loyalty-points",
     *     summary="List all retailers under this Sales Manager with their loyalty points",
     *     tags={"Sales Manager Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of retailers' loyalty points")
     * )
     */
    public function getRetailersLoyaltyPoints()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('salesmanager')) return response()->json(['error' => 'Unauthorized'], 403);

        $salesManager = $user->salesManager;
        $fieldStaffIds = $salesManager->fieldStaffs->pluck('id');

        $retailers = Retailer::with('fieldStaff.user')
            ->whereIn('field_staff_id', $fieldStaffIds)
            ->get(['id', 'shop_name', 'contact_no', 'field_staff_id'])
            ->map(function ($retailer) {
                // dynamically sum points and stats
                $orderQuery = RetailerOrder::where('retailer_id', $retailer->id)
                    ->where('status', RetailerOrder::STATUS_DELIVERED);

                $points = (clone $orderQuery)
                    ->whereNotNull('loyalty_points_earned')
                    ->where('loyalty_points_earned', '>', 0)
                    ->sum('loyalty_points_earned');

                $totalOrders = (clone $orderQuery)->count();
                $lastOrderDate = (clone $orderQuery)->latest('updated_at')->value('updated_at')?->format('Y-m-d');

                return [
                    'id' => $retailer->id,
                    'shop_name' => $retailer->shop_name,
                    'contact_no' => $retailer->contact_no,
                    'loyalty_points' => $points,
                    'field_staff_name' => $retailer->fieldStaff->user->name ?? 'N/A'
                ];
            });

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
     * @OA\Get(
     *     path="/api/sales-manager/retailer-orders",
     *     summary="List all retailer orders under this Sales Manager",
     *     tags={"Sales Manager Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"pending","processing","accepted","delivered","cancelled","rejected"})),
     *     @OA\Parameter(name="field_staff_id", in="query", required=false, @OA\Schema(type="integer"), description="Filter by Field Staff ID"),
     *     @OA\Response(response=200, description="List of retailer orders")
     * )
     */
    public function getRetailerOrders(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('salesmanager')) return response()->json(['error' => 'Unauthorized'], 403);

        $salesManager = $user->salesManager;
        $fieldStaffIds = $salesManager->fieldStaffs->pluck('id');

        $query = RetailerOrder::with(['retailer.user', 'fieldStaff.user', 'items.product', 'distributor.user'])
            ->whereHas('retailer', function ($q) use ($fieldStaffIds) {
                $q->whereIn('field_staff_id', $fieldStaffIds);
            })->orWhereIn('fieldstaff_id', $fieldStaffIds);

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('field_staff_id') && !empty($request->field_staff_id)) {
            $query->where('fieldstaff_id', $request->field_staff_id);
        }

        $orders = $query->latest()->get();

        return response()->json($orders->map(function ($order) {
            return [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'retailer_name' => $order->retailer->user->name ?? 'N/A',
                'field_staff_name' => $order->fieldStaff->user->name ?? 'N/A',
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'placed_at' => $order->placed_at,
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product->product_name ?? 'N/A',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal
                    ];
                })
            ];
        }));
    }

    /**
     * @OA\Get(
     *     path="/api/sales-manager/distributor-insights",
     *     summary="List all distributors with summary stats for Sales Manager",
     *     tags={"Sales Manager Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of distributors with insights")
     * )
     */
    public function getDistributorInsights()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('salesmanager')) return response()->json(['error' => 'Unauthorized'], 403);

        $salesManager = $user->salesManager;
        $distributors = \App\Models\Distributor::with('user')
            ->where('sales_manager_id', $salesManager->id)
            ->get()
            ->map(function ($distributor) {
                return [
                    'id' => $distributor->id,
                    'name' => $distributor->user->name ?? 'N/A',
                    'shop_name' => $distributor->shop_name,
                    'contact_no' => $distributor->contact_no,
                    'total_own_orders' => \App\Models\DistributorOrder::where('distributor_id', $distributor->id)->count(),
                    'total_retailer_orders_received' => \App\Models\RetailerOrder::where('distributor_id', $distributor->id)->count(),
                ];
            });

        return response()->json($distributors);
    }

    /**
     * @OA\Get(
     *     path="/api/sales-manager/distributor-insights/{id}",
     *     summary="Detailed insights for a specific distributor",
     *     tags={"Sales Manager Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detailed distributor insights")
     * )
     */
    public function getDistributorDetailInsight($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('salesmanager')) return response()->json(['error' => 'Unauthorized'], 403);

        $salesManager = $user->salesManager;
        $distributor = \App\Models\Distributor::with('user')
            ->where('sales_manager_id', $salesManager->id)
            ->findOrFail($id);

        $fieldStaffIds = $salesManager->fieldStaffs->pluck('id');

        // Their own orders (Distributor -> Company)
        $ownOrders = \App\Models\DistributorOrder::where('distributor_id', $distributor->id)
            ->latest()
            ->get();

        // Retailer orders assigned to this distributor
        $retailerOrders = \App\Models\RetailerOrder::with(['retailer.user', 'fieldStaff.user'])
            ->where('distributor_id', $distributor->id)
            ->latest()
            ->get()
            ->map(function ($order) use ($fieldStaffIds) {
                // Determine if retailer is under this SM's fieldstaff
                $retailerFieldStaffId = $order->retailer->field_staff_id ?? null;
                $isUnderMyFieldStaff = $fieldStaffIds->contains($retailerFieldStaffId);

                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'retailer_name' => $order->retailer->user->name ?? 'N/A',
                    'field_staff_name' => $order->fieldStaff->user->name ?? 'N/A',
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'placed_at' => $order->placed_at,
                    'categorization' => $isUnderMyFieldStaff ? 'Internal (Under My Field Staff)' : 'External (Other Sales Manager)',
                ];
            });

        return response()->json([
            'distributor' => [
                'id' => $distributor->id,
                'name' => $distributor->user->name ?? 'N/A',
                'shop_name' => $distributor->shop_name,
            ],
            'own_orders' => $ownOrders,
            'retailer_orders_received' => $retailerOrders
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/sales-manager/fieldstaffs",
     *     summary="Create a new Field Staff",
     *     tags={"Sales Manager Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation","contact_no","pincode"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string"),
     *             @OA\Property(property="contact_no", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="pincode", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Field Staff created successfully")
     * )
     */
    public function storeFieldStaff(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('salesmanager')) {
            return response()->json(['error' => 'Unauthorized. Only Sales Managers can create Field Staff.'], 403);
        }

        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4|confirmed',
        ]);

        $fieldstaffData = $request->validate([
            'pincode' => 'required|string',
            'contact_no' => 'required|digits:10',
            'address' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $newUser = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'role' => 'fieldstaff',
                'status' => 'inactive',
                'contact_no' => $fieldstaffData['contact_no'],
                'address' => $fieldstaffData['address'] ?? null,
                'pincode' => $fieldstaffData['pincode'],
            ]);
            $newUser->assignRole('fieldstaff');

            $fieldstaff = new FieldStaff($fieldstaffData);
            $fieldstaff->user_id = $newUser->id;
            $fieldstaff->sales_manager_id = $user->salesManager->id;
            $fieldstaff->save();

            // Notify Admins for approval
            $admins = User::role(['admin', 'superadmin'])->get();
            foreach ($admins as $admin) {
                if (method_exists($this, 'notifyUnique')) {
                    $this->notifyUnique($admin, new \App\Notifications\UserApprovalRequired(
                        $newUser,
                        "New Field Staff {$newUser->name} has been created by Sales Manager {$user->name} and requires activation.",
                        url('/admin/field-staffs')
                    ));
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Field Staff created successfully and is pending admin approval.',
                'field_staff' => $fieldstaff->load('user')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create field staff. ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/sales-manager/distributors",
     *     summary="List all distributors assigned to this Sales Manager",
     *     tags={"Sales Manager Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of distributors")
     * )
     */
    public function getDistributors()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('salesmanager')) return response()->json(['error' => 'Unauthorized'], 403);

        $salesManager = $user->salesManager;
        $distributors = \App\Models\Distributor::with('user')
            ->where('sales_manager_id', $salesManager->id)
            ->get();

        return response()->json($distributors);
    }

    /**
     * @OA\Post(
     *     path="/api/sales-manager/distributor-orders/{id}/approve",
     *     summary="Approve (process) a distributor order",
     *     description="Changes order status to 'processing'. Only for orders assigned to this Sales Manager.",
     *     tags={"Sales Manager Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Order approved successfully")
     * )
     */
    public function approveDistributorOrder($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('salesmanager')) return response()->json(['error' => 'Unauthorized'], 403);

        $salesManager = $user->salesManager;
        $order = DistributorOrder::whereHas('distributor', function ($q) use ($salesManager) {
            $q->where('sales_manager_id', $salesManager->id);
        })->findOrFail($id);

        if ($order->status !== DistributorOrder::STATUS_PENDING) {
            return response()->json(['error' => 'Order is not in pending status'], 400);
        }

        $order->update([
            'status' => DistributorOrder::STATUS_PROCESSING,
            'sales_manager_id' => $salesManager->id
        ]);

        // Notify Admins
        $admins = User::role(['admin', 'superadmin'])->get();
        foreach ($admins as $admin) {
            $this->notifyUnique($admin, new \App\Notifications\OrderActionRequired(
                $order,
                "Distributor Order #{$order->order_code} has been processed by {$user->name} and is ready for your approval.",
                route('admin.approvals.distributor'),
                'distributor_order'
            ));
        }

        return response()->json(['message' => "Order #{$order->order_code} approved successfully."]);
    }
}
