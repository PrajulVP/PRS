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
    /**
     * Display a listing of loyalty points.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. If Retailer, show only their own points
        if ($user->hasRole('retailer')) {
            $retailer = $user->retailer;
            if (!$retailer) {
                return redirect()->back()->with('error', 'Retailer profile not found.');
            }

            // Get history of points earned (show all finalized orders)
            $orders = $retailer->retailerOrders()
                ->with('items.product')
                ->whereIn('status', ['approved', 'delivered'])
                ->orderBy('updated_at', 'desc')
                ->get();

            // Handle missing products gracefully
            $orders->each(function($order) {
                $order->items->each(function($item) {
                    if (!$item->product) {
                        $inventory = \App\Models\Inventory::where('product_id', $item->product_id)->first();
                        $item->missing_product_name = $inventory ? $inventory->product_name : 'Unknown Product #' . $item->product_id;
                        $item->missing_product_code = $inventory ? $inventory->distributor_product_code : 'N/A';
                    }
                });
            });

            $totalPoints = $retailer->retailerOrders()
                ->whereNotNull('loyalty_points_earned')
                ->where('loyalty_points_earned', '>', 0)
                ->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED)
                ->sum('loyalty_points_earned');

            return view('admin.loyalty_points.retailer_view', compact('retailer', 'orders', 'totalPoints'));
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
        }])->withSum(['retailerOrders as dynamic_loyalty_points' => function ($query) {
            $query->whereNotNull('loyalty_points_earned')
                ->where('loyalty_points_earned', '>', 0)
                ->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED);
        }], 'loyalty_points_earned')
        ->withMax(['retailerOrders as last_order_date' => function ($query) {
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

                $orders = $targetRetailer->retailerOrders()
                    ->with('items.product')
                    ->whereIn('status', ['approved', 'delivered'])
                    ->orderBy('updated_at', 'desc');

                return DataTables::of($orders)
                    ->addIndexColumn()
                    ->addColumn('product_summary', function ($row) {
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
                            if ($pGeneric && $pGeneric !== 'N/A') {
                                $summary .= ' <span class="text-muted small">('.$pGeneric.')</span>';
                            }
                            $summary .= '<br><span class="small">'.$item->quantity.' '.$item->unit.'</span></div>';
                            return $summary;
                        })->implode('');
                    })
                    ->editColumn('updated_at', function ($row) {
                        return $row->updated_at->format('d M Y, h:i A');
                    })
                    ->editColumn('loyalty_points_earned', function ($row) {
                        return number_format($row->loyalty_points_earned ?? 0, 2);
                    })
                    ->rawColumns(['product_summary'])
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
                ->addColumn('total_orders', function ($row) {
                    return '<div class="text-center fw-bold">'.$row->total_orders.'</div>';
                })
                ->addColumn('last_order', function ($row) {
                    return $row->last_order_date ? \Carbon\Carbon::parse($row->last_order_date)->format('d M Y') : '<span class="text-muted">N/A</span>';
                })
                ->editColumn('dynamic_loyalty_points', function ($row) {
                    $pts = number_format($row->dynamic_loyalty_points ?? 0, 2);
                    return '<div class="text-center">
                                <span class="badge-points px-3 py-2" style="font-size: 0.9rem;">'.$pts.'</span>
                            </div>';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="text-center">
                                <button class="btn btn-primary btn-xs rounded-pill px-3 fw-bold detail-btn" data-id="'.$row->id.'">
                                    View
                                </button>
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
                ->rawColumns(['shop_name', 'owner_name', 'sales_manager', 'field_staff', 'region_area', 'total_orders', 'last_order', 'dynamic_loyalty_points', 'action'])
                ->make(true);
        }

        // --- HANDLE NON-AJAX REQUEST (Initial View Load) ---
        $retailers = (clone $retailersQuery)->get();
        $globalLoyaltyPoints = $retailers->sum('dynamic_loyalty_points');

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

        return view('admin.loyalty_points.index', compact('retailers', 'globalLoyaltyPoints', 'salesManagers', 'fieldStaffs'));
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
            'total_points' => $totalPoints,
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
}
