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
        if ($request->ajax()) {
            // Return logic for DataTables if we want to list ALL retailers with points summary
            // But the requirement is "select box".
            // So we might return JSON for select2 or handle search.

            // If it's a request for the summary TABLE of a selected retailer
            if ($request->has('retailer_id')) {
                $retailerId = $request->input('retailer_id');
                // Permission check: ensure user can view this retailer
                $retailer = Retailer::findOrFail($retailerId);

                // Add scoped permission checks here if needed (e.g. Field Staff can only see own retailers)
                if ($user->hasRole('fieldstaff') && $retailer->field_staff_id !== $user->fieldStaff->id) {
                    return response()->json(['error' => 'Unauthorized access to this retailer.'], 403);
                }

                $orders = $retailer->retailerOrders()
                    ->with('items.product')
                    ->whereIn('status', ['approved', 'delivered'])
                    ->orderBy('updated_at', 'desc');

                return DataTables::of($orders)
                    ->addIndexColumn()
                    ->addColumn('product_summary', function ($row) {
                        return $row->items->map(function ($item) {
                            $product = $item->product;
                            if (!$product) {
                                // Fallback: Check if we can find the name in inventories (orphaned data)
                                $inventory = \App\Models\Inventory::where('product_id', $item->product_id)->first();
                                $prodName = $inventory ? $inventory->product_name : 'Unknown Product #' . $item->product_id;
                                $prodGeneric = $inventory ? $inventory->distributor_product_code : 'N/A';
                            } else {
                                $prodName = $product->product_name;
                                $prodGeneric = $product->generic_name ?? 'N/A';
                            }
                            return '<div class="mb-1"><span class="fw-bold">'.$prodName.'</span> <span class="text-muted small">('.$prodGeneric.')</span><br><span class="small">'.$item->quantity.' '.$item->unit.'</span></div>';
                        })->implode('');
                    })
                    ->editColumn('updated_at', function ($row) {
                        return $row->updated_at->format('d M Y, h:i A');
                    })
                    ->editColumn('loyalty_points_earned', function ($row) {
                        return number_format($row->loyalty_points_earned, 2);
                    })
                    ->rawColumns(['product_summary', 'loyalty_points_earned'])
                    ->make(true);
            }
        }

        // Fetch list of retailers based on role and filters
        $retailersQuery = Retailer::with(['user', 'fieldStaff.user', 'salesManager.user']);

        if ($user->hasRole('fieldstaff')) {
            $retailersQuery->where('field_staff_id', $user->fieldStaff->id);
        } elseif ($user->hasRole('salesmanager')) {
            $retailersQuery->where('sales_manager_id', $user->salesManager->id);
            if ($request->filled('field_staff_id')) {
                $retailersQuery->where('field_staff_id', $request->field_staff_id);
            }
        } elseif ($user->hasRole('distributor')) {
            $retailersQuery->where('distributor_id', $user->distributor->id);
        } else {
            // Admin/Superadmin - can filter by Sales Manager or Field Staff
            if ($request->filled('sales_manager_id')) {
                $retailersQuery->where('sales_manager_id', $request->sales_manager_id);
            }
            if ($request->filled('field_staff_id')) {
                $retailersQuery->where('field_staff_id', $request->field_staff_id);
            }
        }

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

        if ($request->ajax() && !$request->has('retailer_id')) {
            return DataTables::of($retailersQuery)
                ->addIndexColumn()
                ->addColumn('shop_name', function ($row) {
                    return '<div class="d-flex align-items-center">
                                <div class="avatar-xs bg-glass-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fa fa-shopping-bag small"></i>
                                </div>
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
                ->rawColumns(['shop_name', 'owner_name', 'sales_manager', 'field_staff', 'region_area', 'total_orders', 'last_order', 'dynamic_loyalty_points', 'action'])
                ->make(true);
        }

        $retailers = $retailersQuery->get();

        // Calculate global summary using the loaded collection
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

        // Strict Role-based Permission Check
        if ($user->hasRole('fieldstaff') && $retailer->field_staff_id !== $user->fieldStaff->id) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }
        
        if ($user->hasRole('salesmanager') && $retailer->sales_manager_id !== $user->salesManager->id) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }

        if ($user->hasRole('distributor') && $retailer->distributor_id !== $user->distributor->id) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }

        // Logic check for non-admin roles without specific profile but with permissions
        if (!$user->hasAnyRole(['admin', 'superadmin', 'fieldstaff', 'salesmanager', 'distributor', 'retailer'])) {
             return response()->json(['error' => 'Unauthorized Access'], 403);
        }

        $totalPoints = $retailer->retailerOrders()
            ->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED)
            ->sum('loyalty_points_earned');

        // Check if this retailer is the top performer globally
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
        
        $fieldStaffs = $query->get()->map(function($fs) {
            return [
                'id' => $fs->id,
                'name' => $fs->user->name ?? 'N/A'
            ];
        });

        return response()->json($fieldStaffs);
    }
}
