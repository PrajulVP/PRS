<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Product;
use App\Models\PermissionCategory;

use App\Models\RetailerOrder;
use App\Models\DistributorOrder;

use App\Models\FieldStaff;
use App\Models\Retailer;
use Illuminate\Support\Facades\DB;
use App\Models\Offer;
use App\Models\LoyaltySlab;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $monthParam = $request->get('month', date('Y-m'));
        $data = $this->calculateDashboardData($monthParam);
        
        if ($request->ajax()) {
            return view('partials.dashboard_content', $data)->render();
        }
        
        return view('dashboard', $data);
    }

    public function getStats(Request $request)
    {
        $monthParam = $request->get('month', date('Y-m'));
        $data = $this->calculateDashboardData($monthParam);
        
        // Return JSON with specific subsets needed for AJAX updates
        return response()->json([
            'monthParam' => $data['monthParam'],
            'counts' => $data['counts'],
            'retailerOrderStats' => $data['retailerOrderStats'],
            'distributorOrderStats' => $data['distributorOrderStats'],
            'chartData' => $data['chartData'],
            'monthlyDistributorOrdersChart' => $data['monthlyDistributorOrdersChart'] ?? null,
            'brandSalesDistribution' => $data['brandSalesDistribution'] ?? null,
            // Add other data if needed for dynamic updates
        ]);
    }

    public function getDistributorRetailers(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole('distributor')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $distributorId = $user->distributor->id;
        
        $retailers = Retailer::with('user')
            ->whereHas('retailerOrders', function ($orderQuery) use ($distributorId) {
                $orderQuery->where('distributor_id', $distributorId);
            })
            ->latest()
            ->get(); // For the dashboard offcanvas, getting all might be fine, or paginate if needed. Let's use get() to load all in offcanvas list.

        $data = $retailers->map(function ($retailer) use ($distributorId) {
            $stats = RetailerOrder::select(
                DB::raw('COUNT(id) as total_orders'),
                DB::raw('SUM(total_amount) as total_revenue')
            )
                ->where('retailer_id', $retailer->id)
                ->where('distributor_id', $distributorId)
                ->first();

            $statusCounts = RetailerOrder::select('status', DB::raw('COUNT(id) as count'))
                ->where('retailer_id', $retailer->id)
                ->where('distributor_id', $distributorId)
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $ordersNeedingAction = $statusCounts[RetailerOrder::STATUS_PROCESSING] ?? 0;

            return [
                'id'                    => $retailer->id,
                'name'                  => $retailer->user?->name ?? 'N/A',
                'shop_name'             => $retailer->shop_name ?? 'N/A',
                'email'                 => $retailer->user?->email ?? 'N/A',
                'phone'                 => $retailer->user?->contact_no ?? 'N/A',
                'address'               => $retailer->address ?? 'N/A',
                'total_orders'          => $stats->total_orders ?? 0,
                'total_revenue'         => number_format($stats->total_revenue ?? 0, 2),
                'orders_needing_action' => $ordersNeedingAction,
                'status_counts'         => [
                    'pending'    => $statusCounts['pending'] ?? 0,
                    'processing' => $statusCounts['processing'] ?? 0,
                    'approved'   => $statusCounts['approved'] ?? 0,
                    'delivered'  => $statusCounts['delivered'] ?? 0,
                ]
            ];
        });

        return response()->json(['data' => $data]);
    }

    private function calculateDashboardData($monthParam)
    {
        $user = Auth::user();
        
        try {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $monthParam);
        } catch (\Exception $e) {
            $date = now();
            $monthParam = $date->format('Y-m');
        }
        
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();
        $period = 'monthly';
        $fieldStaffIds = null;

        // Base Queries
        $retailerQuery = Retailer::query();
        $distributorQuery = \App\Models\Distributor::query();
        $productQuery = Product::query();
        $salesManagerQuery = \App\Models\SalesManager::query();
        $fieldStaffQuery = FieldStaff::query();

        $retailerOrderQuery = RetailerOrder::query();
        $distributorOrderQuery = DistributorOrder::query();

        // Role-Specific Metrics
        $topRetailers = collect();
        $topDistributors = collect();
        $topFieldStaff = collect();
        $topProducts = collect();
        $topAreas = collect();
        $totalLoyaltyPoints = 0;
        $monthlyDistributorOrdersChart = null;
        $activeOffers = collect();
        $myRank = null;
        $totalInLocality = 0;
        $isTopRetailer = false;
        $data_extra = [];
        $brandSalesDistribution = $this->getBrandSalesDistribution($startDate, $endDate, $user);
        $upcomingRewards = [];

        // Role-Based Filtering
        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            // See lists and graphs of both distributor and retailer orders, top users, loyalty points, and fieldstaffs
            $topRetailers = RetailerOrder::select('retailer_id', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
                ->groupBy('retailer_id')->orderByDesc('total_orders')->take(5)
                ->with('retailer.user')->get();

            $topDistributors = RetailerOrder::select('distributor_id', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
                ->groupBy('distributor_id')->orderByDesc('total_orders')->take(5)
                ->with('distributor.user')->get();

            $topFieldStaff = RetailerOrder::select('fieldstaff_id', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
                ->whereNotNull('fieldstaff_id')
                ->groupBy('fieldstaff_id')->orderByDesc('total_orders')->take(5)
                ->with('fieldStaff.user')->get();

            // Target Calculations for Admin/Superadmin
            $monthStr = $startDate->format('m');
            $yearStr = $startDate->format('Y');
            
            $globalTotalTarget = 0;
            $globalTotalAchieved = 0;
            
            $fieldStaffs = FieldStaff::with(['user', 'salesManager.user'])->get();
            $fsPerformance = [];
            $managerPerformanceData = [];

            foreach ($fieldStaffs as $fs) {
                $fsAchieved = $fs->getAchievedAmountForMonth($monthStr, $yearStr);
                $fsTargets = $fs->salesTargets()
                    ->where('year', $yearStr)
                    ->where('month', $startDate->format('F'))
                    ->get();
                $fsTargetSum = $fsTargets->sum('amount');
                
                $globalTotalTarget += $fsTargetSum;
                $globalTotalAchieved += $fsAchieved;
                
                $percent = $fsTargetSum > 0 ? round(($fsAchieved / $fsTargetSum) * 100, 1) : 0;
                
                if ($fsTargetSum > 0) {
                    $fsPerformance[] = [
                        'id' => $fs->id,
                        'name' => $fs->user ? $fs->user->name : 'Unknown',
                        'manager' => ($fs->salesManager && $fs->salesManager->user) ? $fs->salesManager->user->name : 'No Manager',
                        'target' => $fsTargetSum,
                        'achieved' => $fsAchieved,
                        'remaining' => max(0, $fsTargetSum - $fsAchieved),
                        'achievement_percent' => $percent
                    ];
                }
                
                // Aggregate by manager
                $managerId = $fs->sales_manager_id ?? 0;
                if (!isset($managerPerformanceData[$managerId])) {
                    $managerName = ($fs->salesManager && $fs->salesManager->user) ? $fs->salesManager->user->name : 'No Manager assigned';
                    $managerPerformanceData[$managerId] = [
                        'id' => $managerId,
                        'name' => $managerName,
                        'target' => 0,
                        'achieved' => 0,
                    ];
                }
                $managerPerformanceData[$managerId]['target'] += $fsTargetSum;
                $managerPerformanceData[$managerId]['achieved'] += $fsAchieved;
            }

            // Process Manager Data
            $managerPerformance = [];
            foreach ($managerPerformanceData as $mId => $mData) {
                if ($mId == 0) continue; // skip "No Manager"
                $mData['achievement_percent'] = $mData['target'] > 0 ? round(($mData['achieved'] / $mData['target']) * 100, 1) : 0;
                $mData['remaining'] = max(0, $mData['target'] - $mData['achieved']);
                $managerPerformance[] = $mData;
            }
            usort($managerPerformance, function($a, $b) {
                return $b['achievement_percent'] <=> $a['achievement_percent']; // Highest first
            });
            $topManagers = array_slice($managerPerformance, 0, 10);
            
            // Process Field Staff Data (already filtered for > 0 targets)
            usort($fsPerformance, function($a, $b) {
                return $b['achievement_percent'] <=> $a['achievement_percent'];
            });
            
            $top5FieldStaffs = array_slice($fsPerformance, 0, 5);
            $bottom5FieldStaffs = array_slice(array_reverse($fsPerformance), 0, 5);

            $data_extra['global_target'] = $globalTotalTarget;
            $data_extra['global_achieved'] = $globalTotalAchieved;
            $data_extra['global_remaining'] = max(0, $globalTotalTarget - $globalTotalAchieved);
            $data_extra['global_percent'] = $globalTotalTarget > 0 ? round(($globalTotalAchieved / $globalTotalTarget) * 100, 1) : 0;
            
            $data_extra['top_managers'] = $topManagers;
            $data_extra['top_5_fieldstaff'] = $top5FieldStaffs;
            $data_extra['bottom_5_fieldstaff'] = $bottom5FieldStaffs;

            $topAreas = \App\Models\Area::withCount('retailers')
                ->with(['district'])
                ->get()
                ->map(function($area) use ($startDate, $endDate, $fieldStaffIds) {
                    $area->total_revenue = RetailerOrder::whereHas('retailer', function($q) use ($area, $fieldStaffIds) {
                        $q->where('area_id', $area->id);
                        if ($fieldStaffIds) {
                            $q->whereIn('field_staff_id', $fieldStaffIds);
                        }
                    })->where('status', 'delivered')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('total_amount');
                    return $area;
                })->filter(fn($a) => $a->total_revenue > 0)
                ->sortByDesc('total_revenue')->take(5);
        } elseif ($user->hasRole('salesmanager')) {
            // See distributor order and retailer order statistics, and the performance of fieldstaffs under them.
            $salesManager = $user->salesManager;
            if ($salesManager) {
                // Fieldstaff performance
                $fieldStaffIds = $salesManager->fieldStaffs->pluck('id');
                $retailerQuery->whereIn('field_staff_id', $fieldStaffIds);

                $retailerOrderQuery->whereHas('retailer', function ($q) use ($fieldStaffIds) {
                    $q->whereIn('field_staff_id', $fieldStaffIds);
                });

                $distributorQuery->where('sales_manager_id', $salesManager->id);
                $fieldStaffQuery->where('sales_manager_id', $salesManager->id);
                $distributorOrderQuery->whereHas('distributor', function ($q) use ($salesManager) {
                    $q->where('sales_manager_id', $salesManager->id);
                });

                $topFieldStaff = RetailerOrder::select('fieldstaff_id', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
                    ->whereIn('fieldstaff_id', $fieldStaffIds)
                    ->groupBy('fieldstaff_id')->orderByDesc('total_orders')->take(5)
                    ->with('fieldStaff.user')->get();

                // Targets Logic
                $monthStr = $startDate->format('m');
                $yearStr = $startDate->format('Y');

                $totalTarget = 0;
                $totalAchieved = 0;
                $brand_targets = [];
                $uniqueBrands = \App\Models\Brand::pluck('name');
                
                foreach ($uniqueBrands as $brand) {
                    $brand_targets[$brand] = [
                        'brand' => $brand,
                        'target' => 0,
                        'achieved' => 0,
                    ];
                }
                
                $fieldStaffs = FieldStaff::whereIn('id', $fieldStaffIds)->get();
                $fieldStaffPerformance = [];
                
                foreach ($fieldStaffs as $fs) {
                    // Achievement
                    $fsAchieved = $fs->getAchievedAmountForMonth($monthStr, $yearStr);
                    $totalAchieved += $fsAchieved;
                    
                    // Target
                    $fsTargets = $fs->salesTargets()
                        ->where('year', $yearStr)
                        ->where('month', $startDate->format('F'))
                        ->get();
                    $fsTargetSum = $fsTargets->sum('amount');
                    $totalTarget += $fsTargetSum;
                    
                    $fsOrders = \App\Models\RetailerOrder::where('fieldstaff_id', $fs->id)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->count();
                        
                    $fieldStaffPerformance[] = [
                        'id' => $fs->id,
                        'name' => $fs->user ? $fs->user->name : 'Unknown',
                        'target' => $fsTargetSum,
                        'achieved' => $fsAchieved,
                        'orders' => $fsOrders,
                        'remaining' => max(0, $fsTargetSum - $fsAchieved),
                        'achievement_percent' => $fsTargetSum > 0 ? round(($fsAchieved / $fsTargetSum) * 100, 1) : 0
                    ];
                    
                    foreach ($uniqueBrands as $brand) {
                        $bTarget = $fsTargets->where('brand', $brand)->first();
                        $bTargetAmount = $bTarget ? $bTarget->amount : 0;
                        $bAchieved = $fs->getAchievedAmountForMonth($monthStr, $yearStr, $brand);
                        
                        $brand_targets[$brand]['target'] += $bTargetAmount;
                        $brand_targets[$brand]['achieved'] += $bAchieved;
                    }
                }
                
                // Sort fieldstaff performance by highest achievement percent
                usort($fieldStaffPerformance, function($a, $b) {
                    return $b['achievement_percent'] <=> $a['achievement_percent'];
                });
                
                $data_extra['target'] = $totalTarget;
                $data_extra['achieved'] = $totalAchieved;
                $data_extra['remaining'] = max(0, $totalTarget - $totalAchieved);
                $data_extra['achievement_percent'] = $totalTarget > 0 ? round(($totalAchieved / $totalTarget) * 100, 1) : 0;
                $data_extra['brand_targets'] = $brand_targets;
                $data_extra['field_staff_performance'] = $fieldStaffPerformance;

                // Top Areas for Sales Manager
                $topAreas = \App\Models\Area::withCount('retailers')
                    ->with(['district'])
                    ->whereHas('retailers', function($q) use ($fieldStaffIds) {
                        $q->whereIn('field_staff_id', $fieldStaffIds);
                    })
                    ->get()
                    ->map(function($area) use ($startDate, $endDate, $fieldStaffIds) {
                        $area->total_revenue = RetailerOrder::whereHas('retailer', function($q) use ($area, $fieldStaffIds) {
                            $q->where('area_id', $area->id)
                              ->whereIn('field_staff_id', $fieldStaffIds);
                        })->where('status', 'delivered')
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->sum('total_amount');
                        return $area;
                    })->filter(fn($a) => $a->total_revenue > 0)
                    ->sortByDesc('total_revenue')->take(5);
            }
        } elseif ($user->hasRole('distributor')) {
            // See all orders and their mostly ordered retailers
            $distributor = $user->distributor;
            if ($distributor) {
                $retailerOrderQuery->where('distributor_id', $distributor->id);
                $distributorOrderQuery->where('distributor_id', $distributor->id);
                
                // Scope related counts
                $retailerQuery->where('distributor_id', $distributor->id);
                $fieldStaffQuery->whereHas('retailers', function($q) use ($distributor) {
                    $q->where('distributor_id', $distributor->id);
                });
                $salesManagerQuery->where('id', $distributor->sales_manager_id);
                $distributorQuery->where('id', $distributor->id);

                $topRetailers = RetailerOrder::select('retailer_id', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
                    ->where('distributor_id', $distributor->id)
                    ->groupBy('retailer_id')->orderByDesc('total_orders')->take(5)
                    ->with('retailer.user')->get();

                foreach ($topRetailers as $tr) {
                    $topProd = DB::table('retailer_order_items')
                        ->join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
                        ->join('products', 'retailer_order_items.product_id', '=', 'products.id')
                        ->where('retailer_orders.retailer_id', $tr->retailer_id)
                        ->where('retailer_orders.distributor_id', $distributor->id)
                        ->select('products.product_name', DB::raw('SUM(retailer_order_items.quantity) as total_qty'))
                        ->groupBy('products.product_name')
                        ->orderByDesc('total_qty')
                        ->first();
                    $tr->top_product_name = $topProd ? $topProd->product_name : 'N/A';
                }

                // Top products overall ordered by retailers from this distributor
                $topProducts = DB::table('retailer_order_items')
                    ->join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
                    ->join('products', 'retailer_order_items.product_id', '=', 'products.id')
                    ->where('retailer_orders.distributor_id', $distributor->id)
                    ->select('products.product_name', DB::raw('SUM(retailer_order_items.quantity) as total_quantity_ordered'), DB::raw('SUM(retailer_order_items.total_amount) as total_revenue'))
                    ->groupBy('products.id', 'products.product_name')
                    ->orderByDesc('total_quantity_ordered')
                    ->take(5)
                    ->get();

                // [ADD] Portal Enhancement Stats
                $targetAchievement = $distributor->distributorTargets()
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->first();
                
                $achievedAmount = RetailerOrder::where('distributor_id', $distributor->id)
                    ->where('status', RetailerOrder::STATUS_DELIVERED)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total_amount');

                $avgMinutes = RetailerOrder::where('distributor_id', $distributor->id)
                    ->where('status', RetailerOrder::STATUS_DELIVERED)
                    ->whereNotNull('placed_at')
                    ->whereNotNull('delivered_at')
                    ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, placed_at, delivered_at)) as avg_time'))
                    ->first()->avg_time;

                $data_extra = [
                    'target' => $targetAchievement ? (float)$targetAchievement->target_amount : 0,
                    'achieved' => (float)$achievedAmount,
                    'loyalty_points' => (float)$distributor->loyalty_points,
                    'outstanding' => (float)$distributor->outstanding_amount,
                    'credit_days' => (int)$distributor->credit_days,
                    'avg_turnaround' => $this->formatMinutes($avgMinutes)
                ];
                
                $tr_list = $topRetailers; // Re-assign for clarity if needed
            }
        } elseif ($user->hasRole('fieldstaff')) {
            // See retailers orders and statistics of their orders, performance to understand if they are working better or not.
            $fieldStaff = $user->fieldStaff;
            if ($fieldStaff) {
                $retailerQuery->where('field_staff_id', $fieldStaff->id);
                $retailerOrderQuery->whereHas('retailer', function ($q) use ($fieldStaff) {
                    $q->where('field_staff_id', $fieldStaff->id);
                });
                $distributorOrderQuery->where('id', 0); // No access
            }
        } elseif ($user->hasRole('retailer')) {
            // attractive, showing their orders.
            $retailer = $user->retailer;
            if ($retailer) {
                $retailerOrderQuery->where('retailer_id', $retailer->id);
                $distributorOrderQuery->where('id', 0); // No access
                $retailerQuery->where('id', 0); // Self only, or no list

                $totalLoyaltyPoints = RetailerOrder::where('retailer_id', $retailer->id)
                    ->where('status', RetailerOrder::STATUS_DELIVERED)
                    ->whereNotNull('loyalty_points_earned')
                    ->where('loyalty_points_earned', '>', 0)
                    ->sum('loyalty_points_earned');

                // Check if this retailer is the top performer globally
                $topR = \App\Models\Retailer::withSum(['retailerOrders as pts' => function($q){ $q->where('status', 'delivered'); }], 'loyalty_points_earned')->orderByDesc('pts')->first();
                $isTopRetailer = ($topR && $topR->id === $retailer->id);

                // Sales Rep details
                $retailer->load('fieldStaff.user');
                
                // Active Offers
                $activeOffers = Offer::active()->latest()->take(3)->get();

                // Locality Ranking (Rank within the same Area based on Delivered Revenue)
                $areaId = $retailer->area_id;
                $localityRank = \App\Models\Retailer::where('area_id', $areaId)
                    ->withSum(['retailerOrders as revenue' => function($q) { $q->where('status', 'delivered'); }], 'total_amount')
                    ->get()
                    ->sortByDesc('revenue')
                    ->values();
                
                $myRank = $localityRank->search(fn($r) => $r->id === $retailer->id) + 1;
                $totalInLocality = $localityRank->count();

                // Upcoming Rewards Logic based on brand totals (PTR amount)
                $slabs = \App\Models\LoyaltySlab::with('brand')->orderBy('min_points')->get();
                $loyaltyRules = [];
                foreach ($slabs as $slab) {
                    if (!$slab->brand) continue;
                    $brand = $slab->brand->name;
                    if (!isset($loyaltyRules[$brand])) {
                        $loyaltyRules[$brand] = [];
                    }
                    $loyaltyRules[$brand][] = [
                        'threshold' => $slab->min_points,
                        'reward' => $slab->gift_name,
                        'reward_options' => json_decode($slab->reward_options, true) ?: [$slab->gift_name]
                    ];
                }

                $upcomingRewards = [];
                if (!empty($loyaltyRules)) {
                    $brandTotals = \Illuminate\Support\Facades\DB::table('retailer_order_items')
                        ->join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
                        ->join('products', 'retailer_order_items.product_id', '=', 'products.id')
                        ->join('brands', 'products.brand_id', '=', 'brands.id')
                        ->where('retailer_orders.retailer_id', $retailer->id)
                        ->where('retailer_orders.status', \App\Models\RetailerOrder::STATUS_DELIVERED)
                        ->select('brands.name as brand', \Illuminate\Support\Facades\DB::raw('SUM(retailer_order_items.unit_price * retailer_order_items.quantity) as total_ptr'))
                        ->groupBy('brands.name')
                        ->pluck('total_ptr', 'brand')
                        ->toArray();

                    foreach ($loyaltyRules as $brand => $rules) {
                        if (empty($rules)) continue;
                        
                        usort($rules, function($a, $b) { return $a['threshold'] <=> $b['threshold']; });
                        $currentTotal = $brandTotals[$brand] ?? 0;
                        
                        $nextRule = null;
                        $achievedRules = [];
                        foreach ($rules as $rule) {
                            if ($currentTotal < $rule['threshold']) {
                                $nextRule = $rule;
                                break;
                            } else {
                                $achievedRules[] = $rule;
                            }
                        }
                        
                        $upcomingRewards[] = [
                            'brand' => $brand,
                            'current_total' => $currentTotal,
                            'next_target' => $nextRule ? $nextRule['threshold'] : null,
                            'next_reward' => $nextRule ? $nextRule['reward'] : null,
                            'next_reward_options' => $nextRule ? $nextRule['reward_options'] : null,
                            'achieved_rewards' => $achievedRules
                        ];
                    }
                }
            }
        }



        // 3. Counts - Product Logic
        $productCount = Product::count(); // Default for Admin, Sales Manager, Field Staff
        if ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            if ($distributor) {
                $productCount = DB::table('distributor_product')
                    ->where('distributor_id', $distributor->id)
                    ->distinct('product_id')->count('product_id');
            }
        } elseif ($user->hasRole('retailer')) {
            $retailer = $user->retailer;
            if ($retailer && $retailer->distributor_id) {
                $productCount = DB::table('distributor_product')
                    ->where('distributor_id', $retailer->distributor_id)
                    ->distinct('product_id')->count('product_id');
            }
        }

        $counts = [
            'distributors' => $distributorQuery->count(),
            'retailers' => $retailerQuery->count(),
            'products' => $productCount,
            'sales_managers' => $salesManagerQuery->count(),
            'field_staff' => $fieldStaffQuery->count(),
            'areas' => \App\Models\Area::count(),
        ];

        // 4. Order Stats (Retailer Orders)
        $retailerOrderStats = [
            'total' => $retailerOrderQuery->clone()->count(),
            'pending' => $retailerOrderQuery->clone()->where('status', 'pending')->count(),
            'processing' => $retailerOrderQuery->clone()->where('status', 'processing')->count(),
            'approved' => $retailerOrderQuery->clone()->where('status', 'approved')->count(),
            'delivered' => $retailerOrderQuery->clone()->where('status', 'delivered')->count(),
            'cancelled' => $retailerOrderQuery->clone()->where('status', 'cancelled')->count(),
        ];

        // 5. Order Stats (Distributor Orders)
        $distributorOrderStats = [
            'total' => $distributorOrderQuery->clone()->count(),
            'pending' => $distributorOrderQuery->clone()->where('status', 'pending')->count(),
            'processing' => $distributorOrderQuery->clone()->where('status', 'processing')->count(),
            'approved' => $distributorOrderQuery->clone()->where('status', 'approved')->count(),
            'delivered' => $distributorOrderQuery->clone()->where('status', 'delivered')->count(),
            'cancelled' => $distributorOrderQuery->clone()->where('status', 'cancelled')->count(),
        ];

        // 6. Recent Orders
        $recentRetailerOrders = $retailerOrderQuery->clone()
            ->with(['retailer.user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($order) {
                $placedAt = $order->placed_at ?? $order->created_at;
                $deliveredAt = $order->delivered_at;
                
                if ($order->status === 'delivered' && $deliveredAt) {
                    $days = (int)$placedAt->diffInDays($deliveredAt);
                    $order->supply_chain_track = [
                        'label' => $days . ' days to deliver',
                        'color' => 'success'
                    ];
                } else {
                    $days = (int)$placedAt->diffInDays(now());
                    $order->supply_chain_track = [
                        'label' => $days . ' days ' . ($order->status === 'cancelled' ? 'at cancellation' : 'since ordered'),
                        'color' => $order->status === 'cancelled' ? 'danger' : 'info'
                    ];
                }
                return $order;
            });
        $recentDistributorOrders = collect();
        if ($user->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            $recentDistributorOrders = $distributorOrderQuery->clone()->with(['distributor.user'])->latest()->take(5)->get();
        }

        // 7. Chart Data: Dynamic Orders based on period
        $chartData = $this->generateChartData($retailerOrderQuery->clone()->whereBetween('created_at', [$startDate, $endDate]), $period, $startDate, $endDate);
        
        // 8. Chart Data: Monthly Distributor Orders (Admin/SM)
        if ($user->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            $monthlyDistributorOrdersChart = $this->generateChartData($distributorOrderQuery->clone()->whereBetween('created_at', [$startDate, $endDate]), $period, $startDate, $endDate);
        }

        // 9. Live Pulse Stats
        $pulseStats = ['active' => 0, 'visits' => 0, 'alerts' => 0];
        if ($user->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            $today = now()->toDateString();
            
            $activeFsQuery = \App\Models\AttendanceLog::whereDate('timestamp', $today)
                ->where('type', 'punch_in')
                ->whereHas('user', function($q) { $q->whereHas('roles', function($r) { $r->where('name', 'fieldstaff'); }); });
                
            $visitsQuery = \App\Models\VisitLog::whereDate('check_in_at', $today)
                ->whereHas('user', function($q) { $q->whereHas('roles', function($r) { $r->where('name', 'fieldstaff'); }); });
                
            if ($user->hasRole('salesmanager') && $user->salesManager) {
                $managerId = $user->salesManager->id;
                $activeFsQuery->whereHas('user.fieldStaff', function($q) use ($managerId) {
                    $q->where('sales_manager_id', $managerId);
                });
                $visitsQuery->whereHas('user.fieldStaff', function($q) use ($managerId) {
                    $q->where('sales_manager_id', $managerId);
                });
            }
            
            $pulseStats['active'] = $activeFsQuery->distinct('user_id')->count();
            $pulseStats['visits'] = $visitsQuery->count();
            // Optional: calculate alerts, e.g. mock gps usage today
            $pulseStats['alerts'] = \App\Models\LocationLog::whereDate('timestamp', $today)
                ->where('is_mock_location', true)
                ->when($user->hasRole('salesmanager') && $user->salesManager, function($query) use ($user) {
                    $query->whereHas('user.fieldStaff', function($q) use ($user) {
                        $q->where('sales_manager_id', $user->salesManager->id);
                    });
                })
                ->count();
        }

        return compact(
            'counts',
            'retailerOrderStats',
            'distributorOrderStats',
            'recentRetailerOrders',
            'recentDistributorOrders',
            'chartData',
            'topRetailers',
            'topFieldStaff',
            'topProducts',
            'topDistributors',
            'isTopRetailer',
            'monthlyDistributorOrdersChart',
            'period',
            'monthParam',
            'activeOffers',
            'myRank',
            'totalInLocality',
            'totalLoyaltyPoints',
            'data_extra',
            'brandSalesDistribution',
            'topAreas',
            'upcomingRewards',
            'pulseStats'
        );
    }

    private function getBrandSalesDistribution($startDate, $endDate, $user = null)
    {
        // Get all unique brands in the system
        $allBrands = \Illuminate\Support\Facades\DB::table('brands')->pluck('name')
            ->map(fn($b) => $b ?: 'Standard')
            ->unique()
            ->values();

        $query = \App\Models\RetailerOrderItem::join('products', 'retailer_order_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
            ->where('retailer_orders.status', 'delivered')
            ->whereBetween('retailer_orders.created_at', [$startDate, $endDate]);

        if ($user && $user->hasRole('salesmanager')) {
            $fieldStaffIds = $user->salesManager->fieldStaffs->pluck('id');
            $query->whereIn('retailer_orders.fieldstaff_id', $fieldStaffIds);
        }

        $distribution = $query->select('brands.name as brand', DB::raw('SUM(retailer_order_items.total_amount) as revenue'))
            ->groupBy('brands.name')
            ->get()
            ->keyBy(fn($item) => $item->brand ?: 'Standard');

        $finalData = [];
        foreach ($allBrands as $brand) {
            $revenue = isset($distribution[$brand]) ? (float)$distribution[$brand]->revenue : 0.0;
            $finalData[$brand] = $revenue;
        }

        // Sort by revenue descending to keep the chart meaningful
        arsort($finalData);

        return [
            'labels' => array_keys($finalData),
            'values' => array_values($finalData),
        ];
    }

    private function generateChartData($query, $period, $startDate, $endDate)
    {
        $q = $query->clone();
        
        if ($period === 'daily') {
            // Group by Hour (Single day)
            $orders = $q->select(
                DB::raw('count(id) as count'),
                DB::raw('SUM(total_amount) as valuation'),
                DB::raw("DATE_FORMAT(created_at, '%H:00') as label"),
                DB::raw('HOUR(created_at) as hour')
            )->groupBy('hour', 'label')
                ->orderBy('hour', 'asc')
                ->get();

            $chartLabels = [];
            $chartCounts = [];
            $chartValuations = [];
            for ($i = 0; $i < 24; $i++) {
                $label = sprintf("%02d:00", $i);
                $chartLabels[] = $label;
                $found = $orders->firstWhere('hour', $i);
                $chartCounts[] = $found ? $found->count : 0;
                $chartValuations[] = $found ? (float)$found->valuation : 0;
            }
        } elseif ($period === 'weekly') {
            // Group by Day
            $orders = $q->select(
                DB::raw('count(id) as count'),
                DB::raw('SUM(total_amount) as valuation'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as label"),
                DB::raw('DATE(created_at) as date')
            )->groupBy('date', 'label')
                ->orderBy('date', 'asc')
                ->get();
                
            // Fill missing days
            $chartLabels = [];
            $chartCounts = [];
            $chartValuations = [];
            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $label = $d->format('Y-m-d');
                $displayLabel = $d->format('D, M d');
                $chartLabels[] = $displayLabel;
                $found = $orders->firstWhere('label', $label);
                $chartCounts[] = $found ? $found->count : 0;
                $chartValuations[] = $found ? (float)$found->valuation : 0;
            }
        } elseif ($period === 'yearly') {
            // Group by Month (12 months)
            $orders = $q->select(
                DB::raw('count(id) as count'),
                DB::raw('SUM(total_amount) as valuation'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as label")
            )->groupBy('label')
                ->orderBy('label', 'asc')
                ->get();
                
            $chartLabels = [];
            $chartCounts = [];
            $chartValuations = [];
            for ($d = $startDate->copy()->startOfMonth(); $d->lte($endDate); $d->addMonth()) {
                $label = $d->format('Y-m');
                $displayLabel = $d->format('M Y');
                $chartLabels[] = $displayLabel;
                $found = $orders->firstWhere('label', $label);
                $chartCounts[] = $found ? $found->count : 0;
                $chartValuations[] = $found ? (float)$found->valuation : 0;
            }
        } else {
            // default monthly (group by day in current month)
            $orders = $q->select(
                DB::raw('count(id) as count'),
                DB::raw('SUM(total_amount) as valuation'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as label"),
                DB::raw('DATE(created_at) as date')
            )->groupBy('date', 'label')
                ->orderBy('date', 'asc')
                ->get();
                
            $chartLabels = [];
            $chartCounts = [];
            $chartValuations = [];
            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $label = $d->format('Y-m-d');
                $displayLabel = $d->format('d M');
                $chartLabels[] = $displayLabel;
                $found = $orders->firstWhere('label', $label);
                $chartCounts[] = $found ? $found->count : 0;
                $chartValuations[] = $found ? (float)$found->valuation : 0;
            }
        }

        return [
            'labels' => $chartLabels,
            'counts' => $chartCounts,
            'valuations' => $chartValuations,
        ];
    }

    private function formatMinutes($avgMinutes)
    {
        if (!$avgMinutes) return 'N/A';

        $hours = floor($avgMinutes / 60);
        $minutes = $avgMinutes % 60;

        if ($hours > 24) {
            $days = floor($hours / 24);
            $remainingHours = $hours % 24;
            return "{$days}d {$remainingHours}h";
        }

        return "{$hours}h {$minutes}m";
    }
}
