<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\RetailerOrder;
use App\Models\RetailerOrderItem;
use App\Models\Retailer;
use App\Models\User;
use App\Models\FieldStaff;
use App\Models\SalesTarget;
use App\Models\IncentiveSlab;
use App\Models\LocationLog;
use App\Models\VisitLog;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $retailerOrderQuery = RetailerOrder::where(function ($q) use ($fieldStaffId) {
            $q->where('fieldstaff_id', $fieldStaffId)
                ->orWhereHas('retailer', function ($qr) use ($fieldStaffId) {
                    $qr->where('field_staff_id', $fieldStaffId);
                });
        })->whereBetween('created_at', [$startDate, $endDate]);

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
        
        $targets = $fieldStaff->getCurrentMonthTargets();
        $targetAmount = $targets->sum('amount');
        $achievementValue = $fieldStaff->getCurrentMonthAchieved();
        $achievementPercent = $targetAmount > 0 ? ($achievementValue / $targetAmount) * 100 : 0;

        $brand_targets = [];
        $uniqueBrands = \App\Models\Product::select('brand')->distinct()->pluck('brand');
        foreach ($uniqueBrands as $brand) {
            $bTarget = $targets->where('brand', $brand)->first();
            $bTargetAmount = $bTarget ? $bTarget->amount : 0;
            $bAchieved = $fieldStaff->getCurrentMonthAchieved($brand);
            $brand_targets[] = [
                'brand' => $brand,
                'target' => number_format($bTargetAmount, 2, '.', ''),
                'achievement' => number_format($bAchieved, 2, '.', ''),
                'remaining' => number_format(max(0, $bTargetAmount - $bAchieved), 2, '.', ''),
                'achievement_percent' => $bTargetAmount > 0 ? round(($bAchieved / $bTargetAmount) * 100, 2) : 0
            ];
        }

        // 3. Global Ranking (Top Performers by Achievement %)
        $allStaffStats = FieldStaff::with(['user'])->get()->map(function ($staff) use ($month, $year) {
            $staffTarget = SalesTarget::where('field_staff_id', $staff->id)
                ->where('month', $month)
                ->where('year', $year)
                ->sum('amount');

            $staffAchievementValue = $staff->getAchievedAmountForMonth(
                \Carbon\Carbon::parse("1 {$month} {$year}")->month, 
                $year
            );

            return [
                'id' => $staff->id,
                'name' => $staff->user->name ?? 'N/A',
                'achievement_percent' => $staffTarget > 0 ? ($staffAchievementValue / $staffTarget) * 100 : 0,
                'total_sales' => $staffAchievementValue
            ];
        })->sortByDesc('achievement_percent')->values();

        $myRank = $allStaffStats->search(fn($s) => $s['id'] === $fieldStaffId) + 1;

        // 4. Outstanding Alerts
        $outstandingAmount = RetailerOrder::where(function ($q) use ($fieldStaffId) {
                $q->where('fieldstaff_id', $fieldStaffId)
                    ->orWhereHas('retailer', function ($qr) use ($fieldStaffId) {
                        $qr->where('field_staff_id', $fieldStaffId);
                    });
            })
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
                'remaining' => number_format(max(0, $targetAmount - $achievementValue), 2, '.', ''),
                'achievement_percent' => round($achievementPercent, 2),
                'brand_targets' => $brand_targets,
                'global_rank' => $myRank,
                'is_top_5' => $myRank > 0 && $myRank <= 5,
                'total_staff' => $allStaffStats->count(),
                'outstanding_dues' => number_format($outstandingAmount, 2, '.', ''),
                'incentive_rate' => $slab ? $slab->incentive_percent . '%' : '0%',
                'projected_incentive' => $slab ? number_format($achievementValue * ($slab->incentive_percent / 100), 2, '.', '') : '0.00'
            ],
            'order_stats' => $orderStats,
            'counts' => [
                'total_retailers' => Retailer::where('field_staff_id', $fieldStaffId)->count(),
                'actionable_orders' => $orderStats['pending_approval']
            ]
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

        $retailers = Retailer::with(['user', 'district', 'area'])
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
                    'loyalty_points' => number_format($points, 2, '.', ''),
                    'status' => $retailer->user->status ?? 'inactive',
                    'district_id' => $retailer->district_id,
                    'area_id' => $retailer->area_id,
                    'area_name' => $retailer->area->name ?? 'N/A'
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
                        $isFreeItem = $item->unit_price == 0;
                        $baseName = $item->product->product_name ?? 'Unknown';
                        
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $isFreeItem ? $baseName . ' (Free)' : $baseName,
                            'brand' => $item->product->brand ?? 'N/A',
                            'quantity' => $isFreeItem ? $item->free_quantity : $item->quantity,
                            'free_quantity' => $item->free_quantity,
                            'is_free' => $isFreeItem,
                            'price' => (float)$item->unit_price,
                            'unit' => $item->unit ?? 'N/A',
                            'side' => $item->side,
                            'size' => $item->size,
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
     *             @OA\Property(property="area_id", type="integer"),
     *             @OA\Property(property="latitude", type="number", format="float", description="Latitude of the retailer"),
     *             @OA\Property(property="longitude", type="number", format="float", description="Longitude of the retailer")
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
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
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

    /**
     * @OA\Get(
     *     path="/api/field-staff/reports/sales-orders",
     *     summary="Generate a Sales Orders Report (Excel/CSV or PDF) for the logged-in Field Staff",
     *     tags={"Field Staff Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="format", in="query", required=false, description="Output format: 'excel' (CSV) or 'pdf'", @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")),
     *     @OA\Parameter(name="start_date", in="query", required=false, description="From date (YYYY-MM-DD)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="end_date", in="query", required=false, description="To date (YYYY-MM-DD)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="retailer_ids", in="query", required=false, description="Comma-separated retailer IDs", @OA\Schema(type="string")),
     *     @OA\Parameter(name="area_ids", in="query", required=false, description="Comma-separated area IDs", @OA\Schema(type="string")),
     *     @OA\Parameter(name="brands", in="query", required=false, description="Comma-separated brand names", @OA\Schema(type="string")),
     *     @OA\Parameter(name="product_ids", in="query", required=false, description="Comma-separated product IDs", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, description="Order status filter", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Report file download")
     * )
     */
    public function generateSalesOrdersReport(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('fieldstaff')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $fieldStaff   = $user->fieldStaff;
        $fieldStaffId = $fieldStaff->id;

        // --- Build base query scoped to this field staff ---
        $query = RetailerOrder::with([
            'retailer.user',
            'retailer.area',
            'retailer.district',
            'items.product',
            'distributor.user',
        ])->where(function ($q) use ($fieldStaffId) {
            $q->where('fieldstaff_id', $fieldStaffId)
              ->orWhereHas('retailer', function ($qr) use ($fieldStaffId) {
                  $qr->where('field_staff_id', $fieldStaffId);
              });
        });

        // --- Apply optional filters ---
        if ($request->filled('start_date')) {
            $query->where('placed_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }
        if ($request->filled('end_date')) {
            $query->where('placed_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }
        if ($request->filled('retailer_ids')) {
            $ids = array_filter(array_map('intval', explode(',', $request->retailer_ids)));
            if (!empty($ids)) $query->whereIn('retailer_id', $ids);
        }
        if ($request->filled('area_ids')) {
            $areaIds = array_filter(array_map('intval', explode(',', $request->area_ids)));
            if (!empty($areaIds)) {
                $query->whereHas('retailer', fn($q) => $q->whereIn('area_id', $areaIds));
            }
        }
        if ($request->filled('brands')) {
            $brands = array_filter(array_map('trim', explode(',', $request->brands)));
            if (!empty($brands)) {
                $query->whereHas('items.product', fn($q) => $q->whereIn('brand', $brands));
            }
        }
        if ($request->filled('product_ids')) {
            $pIds = array_filter(array_map('intval', explode(',', $request->product_ids)));
            if (!empty($pIds)) {
                $query->whereHas('items', fn($q) => $q->whereIn('product_id', $pIds));
            }
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('placed_at', 'desc')->get();

        // --- Compute summary metrics ---
        $totalRevenue    = $orders->sum('total_amount');
        $totalOrderCount = $orders->count();
        $totalQty        = $orders->sum(fn($o) => $o->items->sum('quantity'));
        $uniqueRetailers = $orders->pluck('retailer_id')->unique()->count();

        $format = strtolower($request->get('format', 'excel'));
        $reportTitle = 'Sales Orders Report';
        $staffName   = $user->name ?? 'Field Staff';
        $dateRange   = ($request->start_date ?? 'All') . ' to ' . ($request->end_date ?? 'Now');

        // ======================= PDF FORMAT =======================
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.fieldstaff_sales_orders', [
                'orders'          => $orders,
                'staffName'       => $staffName,
                'dateRange'       => $dateRange,
                'totalRevenue'    => $totalRevenue,
                'totalOrderCount' => $totalOrderCount,
                'totalQty'        => $totalQty,
                'uniqueRetailers' => $uniqueRetailers,
                'generatedAt'     => now()->format('d M Y, h:i A'),
                'filters'         => array_filter([
                    'Start Date'  => $request->start_date,
                    'End Date'    => $request->end_date,
                    'Brands'      => $request->brands,
                    'Status'      => $request->status,
                ]),
            ])->setPaper('a4', 'landscape');

            $filename = 'fieldstaff_sales_orders_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        }

        // ==================== EXCEL / CSV FORMAT ====================
        $filename = 'fieldstaff_sales_orders_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        $callback = function () use ($orders, $staffName, $dateRange, $totalRevenue, $totalOrderCount, $totalQty, $uniqueRetailers) {
            $file = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Report Header
            fputcsv($file, ['Field Staff Sales Orders Report']);
            fputcsv($file, ['Staff:', $staffName]);
            fputcsv($file, ['Date Range:', $dateRange]);
            fputcsv($file, ['Generated At:', now()->format('d M Y, h:i A')]);
            fputcsv($file, []);

            // Summary Row
            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Total Orders', 'Total Revenue (₹)', 'Total Qty Sold', 'Unique Retailers']);
            fputcsv($file, [
                $totalOrderCount,
                number_format($totalRevenue, 2),
                $totalQty,
                $uniqueRetailers,
            ]);
            fputcsv($file, []);

            // Column Headers
            fputcsv($file, [
                'Order Code',
                'Order Date',
                'Retailer Name',
                'Shop Name',
                'Area',
                'District',
                'Distributor',
                'Product Code',
                'Product Name',
                'Brand',
                'Qty',
                'Free Qty',
                'Unit',
                'Side',
                'Size',
                'Unit Price (₹)',
                'Line Total (₹)',
                'MRP (₹)',
                'Loyalty Points',
                'Order Total (₹)',
                'Status',
                'Payment Status',
                'Placed At',
                'Delivered At',
            ]);

            // Data Rows — one row per order item
            foreach ($orders as $order) {
                $retailerName = $order->retailer->user->name ?? 'N/A';
                $shopName     = $order->retailer->shop_name ?? 'N/A';
                $area         = $order->retailer->area->name ?? 'N/A';
                $district     = $order->retailer->district->name ?? 'N/A';
                $distributor  = $order->distributor->user->name ?? 'N/A';

                if ($order->items->isEmpty()) {
                    // Write an empty order row
                    fputcsv($file, [
                        $order->order_code,
                        $order->placed_at ? $order->placed_at->format('d M Y H:i') : 'N/A',
                        $retailerName, $shopName, $area, $district, $distributor,
                        '', '', '', '', '', '', '', '',
                        '', '', '', '',
                        number_format($order->total_amount, 2),
                        $order->status,
                        $order->payment_status ?? 'N/A',
                        $order->placed_at ? $order->placed_at->format('d M Y H:i') : 'N/A',
                        $order->delivered_at ? $order->delivered_at->format('d M Y H:i') : 'Pending',
                    ]);
                } else {
                    $firstItem = true;
                    foreach ($order->items as $item) {
                        $product = $item->product;
                        fputcsv($file, [
                            $firstItem ? $order->order_code : '',
                            $firstItem ? ($order->placed_at ? $order->placed_at->format('d M Y H:i') : 'N/A') : '',
                            $firstItem ? $retailerName : '',
                            $firstItem ? $shopName : '',
                            $firstItem ? $area : '',
                            $firstItem ? $district : '',
                            $firstItem ? $distributor : '',
                            $product->product_code ?? 'N/A',
                            $product->product_name ?? 'Unknown',
                            $product->brand ?? 'N/A',
                            $item->quantity,
                            $item->free_quantity ?? 0,
                            $item->unit ?? 'Nos',
                            $item->side ?? '',
                            $item->size ?? '',
                            number_format($item->unit_price, 2),
                            number_format($item->total_amount, 2),
                            number_format($product->mrp ?? 0, 2),
                            $firstItem ? ($order->loyalty_points_earned ?? 0) : '',
                            $firstItem ? number_format($order->total_amount, 2) : '',
                            $firstItem ? $order->status : '',
                            $firstItem ? ($order->payment_status ?? 'N/A') : '',
                            $firstItem ? ($order->placed_at ? $order->placed_at->format('d M Y H:i') : 'N/A') : '',
                            $firstItem ? ($order->delivered_at ? $order->delivered_at->format('d M Y H:i') : 'Pending') : '',
                        ]);
                        $firstItem = false;
                    }
                }
            }

            fputcsv($file, []);
            fputcsv($file, ['--- End of Report ---']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
