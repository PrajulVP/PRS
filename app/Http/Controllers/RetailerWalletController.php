<?php

namespace App\Http\Controllers;

use App\Models\Retailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class RetailerWalletController extends Controller
{
    /**
     * Display a listing of retailer wallets.
     */
    public function index(Request $request, Retailer $retailer = null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // If retailer, redirect to their own loyalty page (wallet is shown there too for now or redirect if we split it for them)
        if ($user->hasRole('retailer')) {
            return redirect()->route('retailer.loyalty-points.index');
        }

        // --- PREPARE BASE QUERY ---
        $retailersQuery = Retailer::with(['user', 'salesManager.user', 'fieldStaff.user', 'district', 'area'])
            ->select('retailers.*');

        // Role-based Access Control
        if ($user->hasRole('salesmanager')) {
            $retailersQuery->where('sales_manager_id', $user->salesManager->id);
        }

        // Request-based Filters
        if ($request->filled('sales_manager_id')) {
            $retailersQuery->where('sales_manager_id', $request->sales_manager_id);
        }
        if ($request->filled('field_staff_id')) {
            $retailersQuery->where('field_staff_id', $request->field_staff_id);
        }

        // --- HANDLE AJAX REQUESTS ---
        if ($request->ajax()) {
            
            // Detail Transaction Table for a specific retailer
            if ($request->has('retailer_id')) {
                $retailerId = $request->input('retailer_id');
                $targetRetailer = Retailer::findOrFail($retailerId);

                // Permission check
                if ($user->hasRole('salesmanager') && $targetRetailer->sales_manager_id !== $user->salesManager->id) {
                    return response()->json(['error' => 'Unauthorized access.'], 403);
                }

                $credits = \App\Models\CreditNote::where('user_id', $targetRetailer->user_id)
                    ->with('returnRequest')
                    ->where('status', 'active')
                    ->get()
                    ->map(function($c) {
                        $summary = '';
                        if ($c->returnRequest) {
                            $rr = $c->returnRequest;
                            $productName = $rr->product_name ?? 'Unknown Product';
                            $summary = '<div class="mb-1"><span class="fw-bold">' . $productName . '</span>';
                            $summary .= '<br><span class="small">' . $rr->quantity . ' ' . ($rr->unit ?? '') . '</span>';
                            $summary .= '<br><span class="small">Return ID: #' . $rr->id . '</span>';
                            $summary .= '</div>';
                        }
                        return (object)[
                            'id' => $c->id,
                            'reference' => 'CN-' . ($c->credit_code ?? $c->id),
                            'updated_at' => $c->updated_at,
                            'amount' => $c->amount,
                            'status' => 'credit',
                            'type' => 'credit',
                            'notes' => $c->notes,
                            'product_summary' => $summary,
                        ];
                    });

                // Get some recent orders for context
                $orders = $targetRetailer->retailerOrders()
                    ->with('items.product')
                    ->whereIn('status', ['approved', 'delivered'])
                    ->get()
                    ->map(function($o) {
                        return (object)[
                            'id' => $o->id,
                            'reference' => $o->order_code,
                            'updated_at' => $o->updated_at,
                            'amount' => 0, // Orders don't add wallet credits in this context
                            'status' => $o->status,
                            'type' => 'order',
                            'notes' => '',
                            'product_summary' => '<div class="text-secondary"><i class="fa fa-shopping-cart"></i> Order Placed</div>'
                        ];
                    });

                $merged = $credits->concat($orders)->sortByDesc('updated_at');

                return DataTables::of($merged)
                    ->addIndexColumn()
                    ->addColumn('type_label', function($row) {
                        if (isset($row->type) && $row->type === 'credit') {
                            return '<span class="badge bg-info text-dark px-3 py-2 fs-6 shadow-sm"><i class="fa fa-undo me-1"></i>Credit Note</span>';
                        }
                        return '<span class="badge bg-light text-dark border px-3 py-2 fs-6"><i class="fa fa-box me-1"></i>Order</span>';
                    })
                    ->addColumn('product_summary', function ($row) {
                        if (isset($row->type) && $row->type === 'credit') {
                            $notes = !empty($row->notes) ? '<div class="small text-muted mt-1">Notes: '.$row->notes.'</div>' : '';
                            return '<div class="text-info fw-bold"><i class="fa fa-receipt me-1"></i> Refund/Credit</div>' . ($row->product_summary ?? '') . $notes;
                        }
                        return $row->product_summary;
                    })
                    ->editColumn('updated_at', function ($row) {
                        return \Carbon\Carbon::parse($row->updated_at)->format('d M Y, h:i A');
                    })
                    ->editColumn('status', function ($row) {
                        return ucfirst($row->status);
                    })
                    ->rawColumns(['type_label', 'product_summary', 'status'])
                    ->make(true);
            }

            // Overview Table (List of Retailers)
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
                ->addColumn('wallet_credits', function ($row) {
                    $credits = $row->credit_balance ?? 0;
                    if($credits > 0) {
                        return '<span class="badge rounded-pill shadow-sm" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); color: #0369a1; padding: 6px 14px; font-weight: 700; border: 1px solid #7dd3fc; letter-spacing: 0.3px;">₹' . number_format($credits, 2) . ' Cr</span>';
                    }
                    return '<span class="badge rounded-pill text-muted bg-light border" style="padding: 6px 14px; font-weight: 600;">₹0.00</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-primary btn-sm rounded-pill px-3 fw-bold detail-btn shadow-sm" data-id="' . $row->id . '">View</button>';
                })
                ->rawColumns(['shop_name', 'owner_name', 'sales_manager', 'field_staff', 'region_area', 'wallet_credits', 'action'])
                ->make(true);
        }

        // --- NON-AJAX RENDER ---
        $retailers = $retailersQuery->get();
        
        $salesManagers = \App\Models\SalesManager::with('user')->get();
        $fieldStaffs = \App\Models\FieldStaff::with('user');
        
        if ($user->hasRole('salesmanager')) {
            $fieldStaffs->where('sales_manager_id', $user->salesManager->id);
        }
        $fieldStaffs = $fieldStaffs->get();

        $selectedRetailer = $retailer;
        return view('admin.retailer_wallets.index', compact('retailers', 'salesManagers', 'fieldStaffs', 'selectedRetailer'));
    }

    /**
     * Get summary stats for a selected retailer via AJAX
     */
    public function getSummary(Request $request, Retailer $retailer)
    {
        $user = Auth::user();

        if ($user->hasRole('salesmanager') && $retailer->sales_manager_id !== $user->salesManager->id) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }

        return response()->json([
            'credit_balance' => $retailer->credit_balance ?? 0,
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
