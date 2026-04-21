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
use App\Models\FieldStaff;
use App\Models\SalesTarget;
use App\Models\IncentiveSlab;
use App\Models\LocationLog;
use App\Models\VisitLog;

class FieldStaffDashboardApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/field-staff/dashboard",
     *     summary="Get Field Staff dashboard summary data",
     *     tags={"Field Staff Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         required=false,
     *         description="Stats period",
     *         @OA\Schema(type="string", enum={"weekly", "monthly", "yearly"}, default="monthly")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard data for the logged-in Field Staff"
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

        if (!$user->hasRole('fieldstaff')) {
            return response()->json(['error' => 'Only Field Staff can access this dashboard'], 403);
        }

        $fieldStaff = $user->fieldStaff;
        $fieldStaffId = $fieldStaff->id;

        // 1. Basic Stats
        $retailerOrderQuery = RetailerOrder::where('fieldstaff_id', $fieldStaffId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $orderStats = [
            'total' => (clone $retailerOrderQuery)->count(),
            'pending_approval' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_PENDING)->count(),
            'processing' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_PROCESSING)->count(),
            'accepted' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_APPROVED)->count(),
            'delivered' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_DELIVERED)->count(),
            'cancelled' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_CANCELLED)->count(),
            'rejected' => (clone $retailerOrderQuery)->where('status', RetailerOrder::STATUS_REJECTED)->count(),
        ];

        // 2. Target vs Achievement (Current Month - Based on TAXABLE VALUE i.e., Excl. GST)
        $month = now()->format('F');
        $year = now()->year;
        
        $target = SalesTarget::where('field_staff_id', $fieldStaffId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        // Achievement (Sum of unit_price * quantity for delivered orders this month)
        $achievementValue = DB::table('retailer_orders')
            ->join('retailer_order_items', 'retailer_orders.id', '=', 'retailer_order_items.retailer_order_id')
            ->where('retailer_orders.fieldstaff_id', $fieldStaffId)
            ->where('retailer_orders.status', RetailerOrder::STATUS_DELIVERED)
            ->whereMonth('retailer_orders.delivered_at', now()->month)
            ->whereYear('retailer_orders.delivered_at', now()->year)
            ->sum(DB::raw('retailer_order_items.unit_price * retailer_order_items.quantity'));

        $targetAmount = $target ? $target->amount : 0;
        $achievementPercent = $targetAmount > 0 ? ($achievementValue / $targetAmount) * 100 : 0;

        // 3. Global Ranking (Top Performers by Achievement %)
        $allStaffStats = FieldStaff::with(['user'])->get()->map(function ($staff) use ($month, $year) {
            $staffTarget = SalesTarget::where('field_staff_id', $staff->id)
                ->where('month', $month)
                ->where('year', $year)
                ->value('amount') ?? 0;

            $staffAchievementValue = DB::table('retailer_orders')
                ->join('retailer_order_items', 'retailer_orders.id', '=', 'retailer_order_items.retailer_order_id')
                ->where('retailer_orders.fieldstaff_id', $staff->id)
                ->where('retailer_orders.status', RetailerOrder::STATUS_DELIVERED)
                ->whereMonth('retailer_orders.delivered_at', now()->month)
                ->whereYear('retailer_orders.delivered_at', now()->year)
                ->sum(DB::raw('retailer_order_items.unit_price * retailer_order_items.quantity'));

            return [
                'id' => $staff->id,
                'name' => $staff->user->name ?? 'N/A',
                'achievement_percent' => $staffTarget > 0 ? ($staffAchievementValue / $staffTarget) * 100 : 0,
                'total_sales' => $staffAchievementValue
            ];
        })->sortByDesc('achievement_percent')->values();

        $myRank = $allStaffStats->search(fn($s) => $s['id'] === $fieldStaffId) + 1;

        // 4. Outstanding Alerts
        $outstandingAmount = RetailerOrder::where('fieldstaff_id', $fieldStaffId)
            ->where('status', RetailerOrder::STATUS_DELIVERED)
            ->where('payment_status', '!=', 'paid')
            ->sum('total_amount');

        // 5. Incentive Slabs Architecture
        $slab = IncentiveSlab::where('is_active', true)
            ->where('min_achievement_percent', '<=', $achievementPercent)
            ->where(function ($q) use ($achievementPercent) {
                $q->where('max_achievement_percent', '>=', $achievementPercent)
                  ->orWhereNull('max_achievement_percent');
            })
            ->first();

        return response()->json([
            'period' => $period,
            'summary' => [
                'target' => number_format($targetAmount, 2, '.', ''),
                'achievement' => number_format($achievementValue, 2, '.', ''),
                'achievement_percent' => round($achievementPercent, 2),
                'global_rank' => $myRank,
                'total_staff' => $allStaffStats->count(),
                'outstanding_dues' => number_format($outstandingAmount, 2, '.', ''),
                'incentive_rate' => $slab ? $slab->incentive_percent . '%' : '0%',
                'projected_incentive' => $slab ? number_format($achievementValue * ($slab->incentive_percent / 100), 2, '.', '') : '0.00'
            ],
            'order_stats' => $orderStats,
            'counts' => [
                'total_retailers' => Retailer::where('field_staff_id', $fieldStaffId)->count(),
                'actionable_orders' => $orderStats['pending_approval']
            ],
            'ranking_preview' => $allStaffStats->take(5) // Show top 5 for motivation
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/field-staff/retailers",
     *     summary="List all retailers under this Field Staff with full details and loyalty points",
     *     tags={"Field Staff Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of retailers with details")
     * )
     */
    public function getRetailers(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('fieldstaff')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $fieldStaffId = $user->fieldStaff->id;

        $retailers = Retailer::with(['user', 'district'])
            ->where('field_staff_id', $fieldStaffId)
            ->get()
            ->map(function ($retailer) {
                // Dynamically calculate loyalty points
                $points = RetailerOrder::where('retailer_id', $retailer->id)
                    ->where('status', RetailerOrder::STATUS_DELIVERED)
                    ->whereNotNull('loyalty_points_earned')
                    ->where('loyalty_points_earned', '>', 0)
                    ->sum('loyalty_points_earned');

                return [
                    'id' => $retailer->id,
                    'shop_name' => $retailer->shop_name,
                    'email' => $retailer->user->email ?? 'N/A',
                    'contact_no' => $retailer->contact_no,
                    'gst' => $retailer->gst,
                    'drug_license_no' => $retailer->drug_license_no,
                    'address' => $retailer->address,
                    'city' => $retailer->district->name ?? 'N/A',
                    'pincode' => $retailer->pincode,
                    'loyalty_points' => number_format($points, 2, '.', '')
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

    /**
     * @OA\Get(
     *     path="/api/field-staff/performance-trend",
     *     summary="Get performance trend chart data for mobile",
     *     tags={"Field Staff Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="period", in="query", required=false, @OA\Schema(type="string", enum={"weekly", "monthly", "yearly"})),
     *     @OA\Response(response=200, description="Chart labels and counts")
     * )
     */
    public function performanceTrend(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('fieldstaff')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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

        $fieldStaffId = $user->fieldStaff->id;
        $query = RetailerOrder::where(function ($q) use ($fieldStaffId) {
            $q->where('fieldstaff_id', $fieldStaffId)
                ->orWhereHas('retailer', function ($qr) use ($fieldStaffId) {
                    $qr->where('field_staff_id', $fieldStaffId);
                });
        })->whereBetween('created_at', [$startDate, $endDate]);

        // Aggregate trend data
        $trend = [];
        if ($period === 'weekly') {
            $orders = $query->clone()->select(
                DB::raw('count(id) as count'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as label"),
                DB::raw('DATE(created_at) as date')
            )->groupBy('date', 'label')->orderBy('date', 'asc')->get();
            
            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $lbl = $d->format('Y-m-d');
                $found = $orders->firstWhere('label', $lbl);
                $trend[] = ['label' => $d->format('D, M d'), 'count' => $found ? $found->count : 0];
            }
        } elseif ($period === 'yearly') {
            $orders = $query->clone()->select(
                DB::raw('count(id) as count'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as label")
            )->groupBy('label')->orderBy('label', 'asc')->get();
            
            for ($d = $startDate->copy()->startOfMonth(); $d->lte($endDate); $d->addMonth()) {
                $lbl = $d->format('Y-m');
                $found = $orders->firstWhere('label', $lbl);
                $trend[] = ['label' => $d->format('M Y'), 'count' => $found ? $found->count : 0];
            }
        } else {
            $orders = $query->clone()->select(
                DB::raw('count(id) as count'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as label"),
                DB::raw('DATE(created_at) as date')
            )->groupBy('date', 'label')->orderBy('date', 'asc')->get();
            
            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $lbl = $d->format('Y-m-d');
                $found = $orders->firstWhere('label', $lbl);
                $trend[] = ['label' => $d->format('d M'), 'count' => $found ? $found->count : 0];
            }
        }

        return response()->json([
            'period' => $period,
            'trend' => $trend
        ]);
    }
}
