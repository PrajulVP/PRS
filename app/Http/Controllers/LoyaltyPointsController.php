<?php

namespace App\Http\Controllers;

use App\Models\Retailer;
use App\Models\RetailerOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class LoyaltyPointsController extends Controller
{
    public function calculateUpcomingRewards($retailer, $type = 'global')
    {
        if ($type === 'brand') {
            $loyaltyRulesCollection = \App\Models\LoyaltySlab::join('brands', 'loyalty_slabs.brand_id', '=', 'brands.id')
                ->select('loyalty_slabs.*', 'brands.name as type')
                ->orderBy('brands.name')
                ->orderBy('min_points')
                ->get()
                ->groupBy('type');
            
            $brandTotalsQuery = \App\Models\RetailerOrderItem::join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
                ->join('products', 'retailer_order_items.product_id', '=', 'products.id')
                ->join('brands', 'products.brand_id', '=', 'brands.id')
                ->where('retailer_orders.retailer_id', $retailer->id)
                ->where('retailer_orders.status', \App\Models\RetailerOrder::STATUS_DELIVERED)
                ->selectRaw('brands.name as brand, SUM(retailer_order_items.quantity * retailer_order_items.unit_price) as total_amount')
                ->groupBy('brands.name')
                ->get();
                
            // Make the array keys fully uppercase and trimmed to prevent case-sensitivity bugs
            $brandTotals = [];
            foreach ($brandTotalsQuery as $row) {
                $cleanBrand = strtoupper(trim($row->brand));
                $brandTotals[$cleanBrand] = ($brandTotals[$cleanBrand] ?? 0) + $row->total_amount;
            }
            
            $redemptions = \Illuminate\Support\Facades\DB::table('loyalty_redemptions')
                ->join('loyalty_slabs', 'loyalty_redemptions.loyalty_slab_id', '=', 'loyalty_slabs.id')
                ->join('brands', 'loyalty_slabs.brand_id', '=', 'brands.id')
                ->where('loyalty_redemptions.retailer_id', $retailer->id)
                ->select('loyalty_slabs.id', 'brands.name as type', 'loyalty_slabs.min_points')
                ->get();

            $upcomingRewards = [];
            foreach ($loyaltyRulesCollection as $rawBrand => $rules) {
                $brand = strtoupper(trim($rawBrand));
                
                $brandRedemptions = $redemptions->filter(function($item) use ($brand) {
                    return strtoupper(trim($item->type)) === $brand;
                });
                
                $redeemedSlabIds = $brandRedemptions->pluck('id')->toArray();
                $totalSpent = $brandRedemptions->sum('min_points');
                
                $currentTotal = ($brandTotals[$brand] ?? 0) - $totalSpent;
                if ($currentTotal < 0) $currentTotal = 0;
                
                $achievedRules = [];
                $nextRule = null;
                
                foreach ($rules as $rule) {
                    if ($currentTotal >= $rule->min_points) {
                        $achievedRules[] = [
                            'slab_id' => $rule->id,
                            'threshold' => $rule->min_points, 
                            'reward' => $rule->gift_name,
                            'reward_options' => json_decode($rule->reward_options, true) ?: [$rule->gift_name],
                            'is_redeemed' => false // Since points are deducted, any rule they can still reach is achievable again
                        ];
                    } else {
                        if (!$nextRule) {
                            $nextRule = $rule;
                        }
                    }
                }
                
                $upcomingRewards[] = [
                    'brand' => $rawBrand,
                    'current_total' => $currentTotal,
                    'next_target' => $nextRule ? $nextRule->min_points : null,
                    'next_reward' => $nextRule ? $nextRule->gift_name : null,
                    'next_reward_options' => $nextRule ? (json_decode($nextRule->reward_options, true) ?: [$nextRule->gift_name]) : null,
                    'achieved_rewards' => collect($achievedRules)->where('is_redeemed', false)->values()->toArray()
                ];
            }
            return $upcomingRewards;
        }

        $loyaltyRules = \App\Models\LoyaltySlab::orderBy('min_points')->get();
        
        $totalEarned = \App\Models\RetailerOrder::where('retailer_id', $retailer->id)
            ->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED)
            ->sum('loyalty_points_earned');
            
        $totalSpent = \Illuminate\Support\Facades\DB::table('loyalty_redemptions')
            ->join('loyalty_slabs', 'loyalty_redemptions.loyalty_slab_id', '=', 'loyalty_slabs.id')
            ->where('loyalty_redemptions.retailer_id', $retailer->id)
            ->sum('loyalty_slabs.min_points');

        $availablePoints = $totalEarned - $totalSpent;
        
        $achievedRewards = [];
        $nextRule = null;
        
        foreach ($loyaltyRules as $rule) {
            if ($availablePoints >= $rule->min_points) {
                $achievedRewards[] = [
                    'slab_id' => $rule->id,
                    'threshold' => $rule->min_points, 
                    'reward' => $rule->gift_name,
                    'reward_options' => json_decode($rule->reward_options, true) ?: [$rule->gift_name],
                ];
            } else {
                if (!$nextRule) {
                    $nextRule = $rule;
                }
            }
        }
        
        return [
            [
                'brand' => 'Global Rewards',
                'current_total' => $availablePoints,
                'next_target' => $nextRule ? $nextRule->min_points : null,
                'next_reward' => $nextRule ? $nextRule->gift_name : null,
                'next_reward_options' => $nextRule ? (json_decode($nextRule->reward_options, true) ?: [$nextRule->gift_name]) : null,
                'achieved_rewards' => $achievedRewards,
            ]
        ];
    }

    /**
     * Display a listing of loyalty points.
     */
    public function index(Request $request, Retailer $retailer = null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. If Retailer, show only their own points
        if ($user->hasRole('retailer')) {
            $retailer = $user->retailer;
            if (!$retailer) {
                return redirect()->back()->with('error', 'Retailer profile not found.');
            }

            // Use persistent loyalty points balance
            $totalPoints = $retailer->loyalty_points ?? 0;
            $creditBalance = $retailer->credit_balance ?? 0;

            // --- COMBINED HISTORY (Orders + Credits) ---
            $orders = $retailer->retailerOrders()
                ->with('items.product')
                ->whereIn('status', ['approved', 'delivered'])
                ->get();

            $credits = \App\Models\CreditNote::where('user_id', $retailer->user_id)
                ->with('returnRequest')
                ->where('status', 'active')
                ->get();

            // Transform both to a unified history format
            $history = $orders->map(function ($order) {
                return (object)[
                    'date' => $order->updated_at,
                    'reference' => '#' . $order->order_code,
                    'details' => $order->items->map(function ($item) {
                        $name = $item->product ? $item->product->product_name : ($item->missing_product_name ?? 'Unknown Product');
                        return "{$item->quantity}x {$name}";
                    })->implode(', '),
                    'amount' => $order->loyalty_points_earned,
                    'status' => $order->status,
                    'type' => 'LP'
                ];
            });

            $creditHistory = $credits->map(function ($credit) {
                $details = 'Refund Credit';
                if ($credit->returnRequest) {
                    $rr = $credit->returnRequest;
                    $details .= ": " . ($rr->product_name ?? 'Product');
                    $details .= " ({$rr->quantity} {$rr->unit})";
                    $details .= " [Return ID: #{$rr->id}]";
                }
                if ($credit->notes) {
                    $details .= " | Notes: " . $credit->notes;
                }

                return (object)[
                    'date' => $credit->created_at,
                    'reference' => 'CN-' . ($credit->credit_code ?? $credit->id),
                    'details' => $details,
                    'amount' => $credit->amount,
                    'status' => 'CREDIT',
                    'type' => 'CR'
                ];
            });

            $redemptions = \Illuminate\Support\Facades\DB::table('loyalty_redemptions')
                ->join('loyalty_slabs', 'loyalty_redemptions.loyalty_slab_id', '=', 'loyalty_slabs.id')
                ->where('loyalty_redemptions.retailer_id', $retailer->id)
                ->select('loyalty_redemptions.*', 'loyalty_slabs.gift_name', 'loyalty_slabs.min_points')
                ->get();
            
            $redemptionHistory = $redemptions->map(function ($r) {
                return (object)[
                    'id' => $r->id,
                    'date' => $r->created_at,
                    'reference' => 'REW-' . $r->id,
                    'details' => 'Redeemed: ' . $r->gift_name,
                    'amount' => -$r->min_points,
                    'status' => $r->status,
                    'type' => 'REWARD'
                ];
            });

            $unifiedHistory = $creditHistory->concat($redemptionHistory)->sortByDesc('date');

            $upcomingRewards = $this->calculateUpcomingRewards($retailer, 'brand');

            return view('retailer.loyalty_points.index', [
                'retailer' => $retailer,
                'totalPoints' => $totalPoints,
                'creditBalance' => $creditBalance,
                'unifiedHistory' => $unifiedHistory,
                'upcomingRewards' => $upcomingRewards
            ]);
        }

        // 2. For other roles (Admin, Manager, Field Staff), show selector page
        
        // --- PREPARE BASE QUERY (Shared for AJAX and View) ---
        $retailersQuery = \App\Models\Retailer::with(['user', 'salesManager.user', 'fieldStaff.user', 'district', 'area'])
            ->select('retailers.*');

        // Role-based Access Control
        if ($user->hasRole('fieldstaff')) {
            $retailersQuery->where('field_staff_id', $user->fieldStaff->id);
        } elseif ($user->hasRole('salesmanager')) {
            $retailersQuery->where('sales_manager_id', $user->salesManager->id);
        } elseif ($user->hasRole('distributor')) {
            $retailersQuery->where('distributor_id', $user->distributor->id);
        }

        // Request-based Region Filters (Sync with selects)
        if ($request->filled('sales_manager_id')) {
            $retailersQuery->where('sales_manager_id', $request->sales_manager_id);
        }
        if ($request->filled('field_staff_id')) {
            $retailersQuery->where('field_staff_id', $request->field_staff_id);
        }

        // Add Aggregates
        $retailersQuery->withCount(['retailerOrders as total_orders' => function ($query) {
            $query->whereIn('status', ['approved', 'delivered']);
        }])->withMax(['retailerOrders as last_order_date' => function ($query) {
            $query->whereIn('status', ['approved', 'delivered']);
        }], 'updated_at');

        // --- HANDLE AJAX REQUESTS ---
        if ($request->ajax()) {
            
            // Case A: Detail Transaction Table for a specific retailer
            if ($request->has('retailer_id')) {
                $retailerId = $request->input('retailer_id');
                $targetRetailer = Retailer::findOrFail($retailerId);

                // Permission check
                if ($user->hasRole('fieldstaff') && $targetRetailer->field_staff_id !== $user->fieldStaff->id) {
                    return response()->json(['error' => 'Unauthorized access.'], 403);
                }

                // Orders removed as per request

                $credits = \App\Models\CreditNote::where('user_id', $targetRetailer->user_id)
                    ->with('returnRequest')
                    ->get()
                    ->map(function($c) {
                        $summary = '';
                        $returnId = null;
                        if ($c->returnRequest) {
                            $rr = $c->returnRequest;
                            $productName = $rr->product_name ?? 'Unknown Product';
                            $summary = '<div class="mb-1"><span class="fw-bold">' . $productName . '</span>';
                            if (!empty($rr->side) && strtoupper(trim($rr->side)) !== 'N/A') {
                                $summary .= ' <span class="text-muted small">(' . $rr->side . ')</span>';
                            }
                            if (!empty($rr->size) && strtoupper(trim($rr->size)) !== 'N/A') {
                                $summary .= ' <span class="text-muted small">(' . $rr->size . ')</span>';
                            }
                            $summary .= '<br><span class="small">' . $rr->quantity . ' ' . $rr->unit . '</span>';
                            $summary .= '<br><span class="small">Return ID: #' . $rr->id . '</span>';
                            $summary .= '</div>';
                            $returnId = $rr->id;
                        }
                        return (object)[
                            'id' => $c->id,
                            'order_code' => $c->credit_code,
                            'updated_at' => $c->updated_at,
                            'loyalty_points_earned' => $c->amount,
                            'status' => 'credit',
                            'type' => 'credit',
                            'notes' => $c->notes,
                            'product_summary' => $summary,
                            'return_id' => $returnId
                        ];
                    });

                $redemptions = \Illuminate\Support\Facades\DB::table('loyalty_redemptions')
                    ->join('loyalty_slabs', 'loyalty_redemptions.loyalty_slab_id', '=', 'loyalty_slabs.id')
                    ->where('loyalty_redemptions.retailer_id', $targetRetailer->id)
                    ->select(
                        'loyalty_redemptions.id',
                        'loyalty_redemptions.updated_at',
                        'loyalty_redemptions.status',
                        'loyalty_slabs.gift_name',
                        'loyalty_slabs.min_points as amount'
                    )
                    ->get()
                    ->map(function($r) {
                        return (object)[
                            'id' => $r->id,
                            'order_code' => 'REWARD-' . str_pad($r->id, 5, '0', STR_PAD_LEFT),
                            'updated_at' => \Carbon\Carbon::parse($r->updated_at),
                            'loyalty_points_earned' => -($r->amount),
                            'status' => $r->status,
                            'type' => 'reward',
                            'product_summary' => '<div class="text-success fw-bold"><i class="fa fa-gift me-1"></i> Reward Redeemed</div><div class="small mt-1">' . $r->gift_name . '</div>'
                        ];
                    });

                $merged = $credits->concat($redemptions)->sortByDesc('updated_at');

                return DataTables::of($merged)
                    ->addIndexColumn()
                    ->addColumn('type_label', function($row) {
                        if (isset($row->type) && $row->type === 'credit') {
                            return '<span class="badge bg-info text-dark px-3 py-2 fs-6 shadow-sm"><i class="fa fa-undo me-1"></i>Return / Credit</span>';
                        }
                        if (isset($row->type) && $row->type === 'reward') {
                            return '<span class="badge bg-warning text-dark px-3 py-2 fs-6 shadow-sm"><i class="fa fa-gift me-1"></i>Reward Claim</span>';
                        }
                        return '';
                    })
                    ->addColumn('product_summary', function ($row) {
                        if (isset($row->type) && $row->type === 'credit') {
                            $notes = !empty($row->notes) ? '<div class="small text-muted mt-1">Notes: '.$row->notes.'</div>' : '';
                            return '<div class="text-info fw-bold"><i class="fa fa-receipt me-1"></i> Credit Note Issued</div>' . ($row->product_summary ?? '') . $notes;
                        }
                        if (isset($row->type) && $row->type === 'reward') {
                            return $row->product_summary;
                        }
                        return $row->items->map(function ($item) {
                            $pName = $item->product->product_name ?? 'Product';
                            $pGeneric = $item->product->generic_name ?? null;

                            // Handle missing product case
                            if (!$item->product) {
                                $inventory = \App\Models\Inventory::where('product_id', $item->product_id)->first();
                                $pName = $inventory ? $inventory->product_name : 'Unknown Product #' . $item->product_id;
                                $pGeneric = $inventory ? $inventory->distributor_product_code : null;
                            }

                            $summary = '<div class="mb-1"><span class="fw-bold">'.$pName.'</span>';
                            if (!empty(trim($pGeneric)) && strtoupper(trim($pGeneric)) !== 'N/A') {
                                $summary .= ' <span class="text-muted small">('.$pGeneric.')</span>';
                            }
                            $summary .= '<br><span class="small">'.$item->quantity.' '.$item->unit.'</span></div>';
                            return $summary;
                        })->implode("\n");
                    })
                    ->editColumn('updated_at', function ($row) {
                        return $row->updated_at->format('d M Y, h:i A');
                    })
                    ->editColumn('status', function ($row) {
                        if (isset($row->type) && $row->type === 'credit') {
                            return '<span class="badge bg-info text-white">CREDIT</span>';
                        }
                        if (isset($row->type) && $row->type === 'reward') {
                            $statusClass = $row->status === 'delivered' ? 'success' : 'warning';
                            return '<span class="badge bg-'.$statusClass.' text-white">'.strtoupper($row->status).'</span>';
                        }
                        $statusClass = $row->status === 'delivered' ? 'success' : 'primary';
                        return '<span class="badge bg-'.$statusClass.' text-white">'.strtoupper($row->status).'</span>';
                    })
                    ->rawColumns(['type_label', 'product_summary', 'status'])
                    ->make(true);
            }

            // Case B: Overview Table (List of Retailers)
            return DataTables::of($retailersQuery)
                ->addIndexColumn()
                ->addColumn('shop_name', function ($row) {
                    return '<div class="d-flex align-items-center">
                                <div class="fw-bold heading-theme">' . ($row->shop_name ?? 'N/A') . '</div>
                            </div>';
                })
                ->addColumn('owner_name', function ($row) {
                    return '<span class="sub-heading-theme">'.($row->user->name ?? 'N/A').'</span>';
                })
                ->addColumn('sales_manager', function ($row) {
                    return '<span class="small sub-heading-theme">'.($row->salesManager->user->name ?? 'N/A').'</span>';
                })
                ->addColumn('field_staff', function ($row) {
                    return '<span class="small sub-heading-theme">'.($row->fieldStaff->user->name ?? 'N/A').'</span>';
                })
                ->addColumn('region_area', function ($row) {
                    return '<span class="small sub-heading-theme">'.($row->district->name ?? 'N/A').', '.($row->area->name ?? 'N/A').'</span>';
                })
                ->addColumn('total_points', function ($row) {
                    $points = number_format($row->loyalty_points ?? 0, 2);
                    return '<div class="text-center fw-bold text-dark" style="font-size: 0.95rem;">'.$points.' <span class="small text-muted fw-normal">pts</span></div>';
                })
                ->addColumn('wallet_credits', function ($row) {
                    $credits = number_format($row->credit_balance ?? 0, 2);
                    return '<div class="text-center fw-bold text-dark" style="font-size: 0.95rem;">₹'.$credits.' Cr</div>';
                })
                ->addColumn('upcoming_reward', function ($row) {
                    $upcomingRewards = $this->calculateUpcomingRewards($row);
                    $globalReward = $upcomingRewards[0] ?? null;
                    if ($globalReward && count($globalReward['achieved_rewards']) > 0) {
                        return '<div class="text-center"><span class="badge bg-light text-danger border border-danger px-2 py-1"><i class="fa fa-exclamation-circle me-1"></i>Action Required</span></div>';
                    } elseif ($globalReward && $globalReward['next_target']) {
                        $progress = min(100, ($globalReward['current_total'] / $globalReward['next_target']) * 100);
                        return '<div class="text-start d-inline-block" style="min-width: 140px;">
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem;"><i class="fa fa-gift text-muted me-1"></i>'.$globalReward['next_reward'].'</div>
                                    <div class="d-flex justify-content-between align-items-center mt-1" style="font-size: 0.75rem;">
                                        <span class="text-muted">'.number_format($globalReward['current_total'], 0).' / '.number_format($globalReward['next_target'], 0).'</span>
                                        <span class="fw-bold text-dark">'.round($progress).'%</span>
                                    </div>
                                    <div class="progress mt-1" style="height: 4px; border-radius: 2px; background-color: #f1f5f9;">
                                        <div class="progress-bar bg-dark" role="progressbar" style="width: '.$progress.'%;"></div>
                                    </div>
                                </div>';
                    } elseif ($globalReward && !$globalReward['next_target']) {
                        return '<div class="text-center"><span class="badge bg-light text-success border border-success px-2 py-1"><i class="fa fa-star me-1"></i>Max Level</span></div>';
                    }
                    return '<div class="text-center text-muted small">N/A</div>';
                })
                ->addColumn('action', function ($row) {
                    $url = route('admin.loyalty-points.detail', $row->id);
                    return '<div class="text-center">
                                <a href="'.$url.'" class="btn btn-outline-dark btn-sm rounded-pill px-4 fw-semibold" style="padding-top: 5px; padding-bottom: 5px; font-size: 0.8rem;">
                                    View
                                </a>
                            </div>';
                })
                ->filterColumn('shop_name', function($query, $keyword) {
                    $query->where('retailers.shop_name', 'like', "%{$keyword}%");
                })
                ->filterColumn('owner_name', function($query, $keyword) {
                    $query->whereHas('user', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('sales_manager', function($query, $keyword) {
                    $query->whereHas('salesManager.user', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('field_staff', function($query, $keyword) {
                    $query->whereHas('fieldStaff.user', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('region_area', function($query, $keyword) {
                    $query->whereHas('district', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    })->orWhereHas('area', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['shop_name', 'owner_name', 'sales_manager', 'field_staff', 'region_area', 'total_points', 'upcoming_reward', 'action'])
                ->make(true);
        }

        // --- HANDLE NON-AJAX REQUEST (Initial View Load) ---
        $retailers = (clone $retailersQuery)->get();
        $globalLoyaltyPoints = $retailers->sum('loyalty_points');

        $salesManagers = collect();
        $fieldStaffs = collect();

        if ($user->hasAnyRole(['admin', 'superadmin'])) {
            $salesManagers = \App\Models\SalesManager::with('user')->get();
            $fsQuery = \App\Models\FieldStaff::with('user');
            if ($request->filled('sales_manager_id')) {
                $fsQuery->where('sales_manager_id', $request->sales_manager_id);
            }
            $fieldStaffs = $fsQuery->get();
        } elseif ($user->hasRole('salesmanager')) {
            $fieldStaffs = \App\Models\FieldStaff::with('user')
                ->where('sales_manager_id', $user->salesManager->id)
                ->get();
        }

        // 1. Fetch Top Achievers
        $topAchievers = (clone $retailersQuery)
            ->orderBy('loyalty_points', 'desc')
            ->take(4)
            ->get();

        $selectedRetailer = $retailer;
        $upcomingRewards = [];
        $retailerPendingRedemptions = collect();

        // 2. If a retailer is selected, calculate their rewards directly for the inline view
        if ($selectedRetailer) {
            $upcomingRewards = $this->calculateUpcomingRewards($selectedRetailer, 'brand');
            
            $retailerPendingRedemptions = \Illuminate\Support\Facades\DB::table('loyalty_redemptions')
                ->join('loyalty_slabs', 'loyalty_redemptions.loyalty_slab_id', '=', 'loyalty_slabs.id')
                ->join('brands', 'loyalty_slabs.brand_id', '=', 'brands.id')
                ->where('loyalty_redemptions.retailer_id', $selectedRetailer->id)
                ->where('loyalty_redemptions.status', 'pending')
                ->select(
                    'loyalty_redemptions.id as redemption_id',
                    'loyalty_slabs.gift_name',
                    'loyalty_slabs.min_points as threshold',
                    'brands.name as brand',
                    'loyalty_redemptions.created_at'
                )
                ->get();
        }

        // 3. Fetch Priority Actions: Pending Rewards
        $pendingRedemptions = [];
        $completedRedemptions = [];
        if ($user->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            $pendingQuery = \Illuminate\Support\Facades\DB::table('loyalty_redemptions')
                ->join('retailers', 'loyalty_redemptions.retailer_id', '=', 'retailers.id')
                ->leftJoin('users', 'retailers.user_id', '=', 'users.id')
                ->join('loyalty_slabs', 'loyalty_redemptions.loyalty_slab_id', '=', 'loyalty_slabs.id')
                ->join('brands', 'loyalty_slabs.brand_id', '=', 'brands.id')
                ->where('loyalty_redemptions.status', 'pending')
                ->select(
                    'loyalty_redemptions.id as redemption_id',
                    'loyalty_redemptions.created_at',
                    'retailers.id as retailer_id',
                    'retailers.shop_name',
                    'users.name as owner_name',
                    'loyalty_slabs.gift_name',
                    'brands.name as brand',
                    'loyalty_slabs.min_points as threshold',
                    'loyalty_redemptions.selected_reward'
                )
                ->orderBy('loyalty_redemptions.created_at', 'asc');
                
            if ($user->hasRole('salesmanager')) {
                $pendingQuery->where('retailers.sales_manager_id', $user->salesManager->id);
            }
            
            $pendingRedemptions = $pendingQuery->get();

            // Fetch Latest Completed Redemptions
            $completedQuery = \Illuminate\Support\Facades\DB::table('loyalty_redemptions')
                ->join('retailers', 'loyalty_redemptions.retailer_id', '=', 'retailers.id')
                ->leftJoin('users', 'retailers.user_id', '=', 'users.id')
                ->join('loyalty_slabs', 'loyalty_redemptions.loyalty_slab_id', '=', 'loyalty_slabs.id')
                ->join('brands', 'loyalty_slabs.brand_id', '=', 'brands.id')
                ->whereIn('loyalty_redemptions.status', ['approved', 'delivered'])
                ->select(
                    'loyalty_redemptions.id as redemption_id',
                    'loyalty_redemptions.updated_at',
                    'loyalty_redemptions.status',
                    'retailers.id as retailer_id',
                    'retailers.shop_name',
                    'users.name as owner_name',
                    'loyalty_slabs.gift_name',
                    'brands.name as brand',
                    'loyalty_slabs.min_points as threshold',
                    'loyalty_redemptions.selected_reward'
                )
                ->orderBy('loyalty_redemptions.updated_at', 'desc')
                ->limit(20);
                
            if ($user->hasRole('salesmanager')) {
                $completedQuery->where('retailers.sales_manager_id', $user->salesManager->id);
            }
            
            $completedRedemptions = $completedQuery->get();
        }

        return view('admin.loyalty_points.index', compact('retailers', 'globalLoyaltyPoints', 'salesManagers', 'fieldStaffs', 'selectedRetailer', 'topAchievers', 'upcomingRewards', 'pendingRedemptions', 'completedRedemptions', 'retailerPendingRedemptions'));
    }

    /**
     * Get summary stats for a selected retailer via AJAX
     */
    public function getSummary(Request $request, Retailer $retailer)
    {
        $user = Auth::user();

        // Permissions
        if ($user->hasRole('fieldstaff') && $retailer->field_staff_id !== $user->fieldStaff->id) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }
        if ($user->hasRole('salesmanager') && $retailer->sales_manager_id !== $user->salesManager->id) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }

        $totalPoints = $retailer->retailerOrders()
            ->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED)
            ->sum('loyalty_points_earned');

        $isTop = Retailer::withSum(['retailerOrders as points' => function($q){
                $q->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED);
            }], 'loyalty_points_earned')
            ->orderByDesc('points')
            ->first();

        return response()->json([
            'total_points' => $retailer->loyalty_points ?? 0,
            'credit_balance' => $retailer->credit_balance ?? 0,
            'is_top_retailer' => ($isTop && $isTop->id === $retailer->id),
            'redeemed_points' => 0,
            'shop_name' => $retailer->shop_name,
            'owner_name' => $retailer->user->name ?? 'N/A',
            'email' => $retailer->user->email ?? 'N/A',
            'phone' => $retailer->contact_no ?? 'N/A',
            'area' => $retailer->area->name ?? 'N/A',
            'district' => $retailer->district->name ?? 'N/A',
            'joined_date' => $retailer->created_at->format('d M Y')
        ]);
    }

    /**
     * Get field staff list by manager (AJAX dropdown)
     */
    public function getFieldStaffByManager(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'superadmin'])) {
             return response()->json(['error' => 'Unauthorized Access'], 403);
        }

        $query = \App\Models\FieldStaff::with('user');
        if ($request->filled('sales_manager_id')) {
            $query->where('sales_manager_id', $request->sales_manager_id);
        }
        
        return response()->json($query->get()->map(function($fs) {
            return ['id' => $fs->id, 'name' => $fs->user->name ?? 'N/A'];
        }));
    }

    /**
     * Mark a loyalty reward as given for a retailer
     */
    public function markRewardGiven(Request $request, Retailer $retailer)
    {
        $redemptionId = null;
        $rewardName = null;

        if ($request->filled('redemption_id')) {
            $request->validate([
                'redemption_id' => 'required|exists:loyalty_redemptions,id'
            ]);
            
            $redemptionId = $request->redemption_id;
            
            \Illuminate\Support\Facades\DB::table('loyalty_redemptions')
                ->where('id', $redemptionId)
                ->update([
                    'status' => 'approved',
                    'updated_at' => now(),
                ]);
                
            $redemption = \Illuminate\Support\Facades\DB::table('loyalty_redemptions')
                ->where('id', $redemptionId)
                ->first();
            $slab = \Illuminate\Support\Facades\DB::table('loyalty_slabs')->where('id', $redemption->loyalty_slab_id)->first();
            $rewardName = $redemption->selected_reward ?: $slab->gift_name;
            
        } else {
            $request->validate([
                'slab_id' => 'required|exists:loyalty_slabs,id'
            ]);

            $slab = \Illuminate\Support\Facades\DB::table('loyalty_slabs')->where('id', $request->slab_id)->first();
            $rewardName = $request->selected_reward ?: $slab->gift_name;

            $redemptionId = \Illuminate\Support\Facades\DB::table('loyalty_redemptions')->insertGetId([
                'retailer_id' => $retailer->id,
                'loyalty_slab_id' => $request->slab_id,
                'selected_reward' => $request->selected_reward,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Notify Field Staff
        if ($retailer->field_staff_id) {
            $fieldStaff = \App\Models\FieldStaff::with('user')->find($retailer->field_staff_id);
            if ($fieldStaff && $fieldStaff->user) {
                $fieldStaff->user->notify(new \App\Notifications\LoyaltyRewardApproved($redemptionId, $retailer->shop_name, $rewardName));
            }
        }

        return redirect()->back()->with('success', 'Reward marked as given successfully.');
    }

    /**
     * Retailer claims a reward
     */
    public function claimReward(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('retailer')) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }

        $request->validate([
            'slab_id' => 'required|exists:loyalty_slabs,id',
            'selected_reward' => 'required|string|max:255'
        ]);

        $retailer = $user->retailer;
        
        $slab = \App\Models\LoyaltySlab::with('brand')->find($request->slab_id);
        
        $upcomingRewards = $this->calculateUpcomingRewards($retailer, 'brand');
        $targetReward = collect($upcomingRewards)->firstWhere('brand', $slab->brand->name ?? '');
        
        if (!$targetReward || $targetReward['current_total'] < $slab->min_points) {
            return redirect()->back()->with('error', 'Not enough points to claim this reward.');
        }

        \Illuminate\Support\Facades\DB::table('loyalty_redemptions')->insert([
            'retailer_id' => $retailer->id,
            'loyalty_slab_id' => $slab->id,
            'selected_reward' => $request->selected_reward,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Reward claimed successfully! Pending approval.');
    }
}
