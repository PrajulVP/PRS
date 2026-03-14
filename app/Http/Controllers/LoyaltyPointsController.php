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
        $user = Auth::user();

        // 1. If Retailer, show only their own points
        if ($user->hasRole('retailer')) {
            $retailer = $user->retailer;
            if (!$retailer) {
                return redirect()->back()->with('error', 'Retailer profile not found.');
            }

            // Get history of points earned (show all finalized orders)
            $orders = $retailer->retailerOrders()
                ->whereIn('status', ['approved', 'delivered'])
                ->orderBy('updated_at', 'desc')
                ->get();

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
                            $prodName = $item->product ? $item->product->product_name : 'Unknown';
                            $prodBrand = $item->product ? $item->product->brand : 'N/A';
                            return '<div class="mb-1"><span class="fw-bold">'.$prodName.'</span> <span class="text-muted small">('.$prodBrand.')</span><br><span class="small">'.$item->quantity.' '.$item->unit.'</span></div>';
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
        $retailersQuery = Retailer::query()
            ->with(['user', 'fieldStaff.user', 'salesManager.user'])
            ->withSum(['retailerOrders as dynamic_loyalty_points' => function ($query) {
                $query->whereNotNull('loyalty_points_earned')
                    ->where('loyalty_points_earned', '>', 0)
                    ->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED);
            }], 'loyalty_points_earned');

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

        if ($request->ajax() && !$request->has('retailer_id')) {
            return DataTables::of(clone $retailersQuery)
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
                ->addColumn('order_summary', function ($row) {
                    $lastOrder = $row->retailerOrders()
                        ->with('items.product')
                        ->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED)
                        ->latest('updated_at')
                        ->first();
                    
                    if (!$lastOrder) return '<span class="text-muted small">No delivered orders</span>';
                    
                    return $lastOrder->items->take(2)->map(function ($item) {
                        $pName = $item->product ? $item->product->product_name : 'Unknown';
                        return '<div class="small fw-bold"> &bull; ' . $pName . ' (' . $item->quantity . ')</div>';
                    })->implode('') . ($lastOrder->items->count() > 2 ? '<div class="small text-muted ps-2">...and more</div>' : '');
                })
                ->editColumn('dynamic_loyalty_points', function ($row) {
                    $pts = number_format($row->dynamic_loyalty_points ?? 0, 2);
                    return '<div class="text-center">
                                <span class="badge-points px-3 py-2" style="font-size: 0.9rem;">'.$pts.'</span>
                            </div>';
                })
                ->orderColumn('dynamic_loyalty_points', function ($query, $order) {
                    $query->orderBy('dynamic_loyalty_points', $order);
                })
                ->addColumn('action', function ($row) {
                    return '<div class="text-center">
                                <button class="btn btn-primary btn-xs rounded-pill px-3 fw-bold detail-btn" data-id="'.$row->id.'">
                                    <i class="fa fa-eye me-2"></i>View History
                                </button>
                            </div>';
                })
                ->rawColumns(['shop_name', 'owner_name', 'sales_manager', 'field_staff', 'region_area', 'order_summary', 'dynamic_loyalty_points', 'action'])
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
}
