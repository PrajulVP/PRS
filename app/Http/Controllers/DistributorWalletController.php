<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\DistributorOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class DistributorWalletController extends Controller
{
    /**
     * Display a listing of distributor credits.
     */
    public function index(Request $request, Distributor $distributor = null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. If Distributor, show only their own points
        if ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            if (!$distributor) {
                return redirect()->back()->with('error', 'Distributor profile not found.');
            }

            $creditBalance = $distributor->credit_balance ?? 0;

            // --- COMBINED HISTORY (Orders + Credits) ---
            $orders = $distributor->distributorOrders()
                ->with('items.product')
                ->whereIn('status', ['approved', 'delivered'])
                ->get();

            $credits = \App\Models\CreditNote::where('user_id', $distributor->user_id)
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
                    'amount' => 0, // Distributors don't earn points for now
                    'status' => $order->status,
                    'type' => 'ORDER'
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

            $unifiedHistory = $history->concat($creditHistory)->sortByDesc('date');

            return view('admin.loyalty_points.distributor_view', [
                'distributor' => $distributor,
                'creditBalance' => $creditBalance,
                'unifiedHistory' => $unifiedHistory
            ]);
        }

        // 2. For other roles (Admin, Manager), show selector page
        
        // --- PREPARE BASE QUERY (Shared for AJAX and View) ---
        $distributorsQuery = \App\Models\Distributor::with(['user', 'salesManager.user', 'district', 'area'])
            ->select('distributors.*');

        // Role-based Access Control
        if ($user->hasRole('salesmanager')) {
            $distributorsQuery->where('sales_manager_id', $user->salesManager->id);
        }

        // Request-based Region Filters (Sync with selects)
        if ($request->filled('sales_manager_id')) {
            $distributorsQuery->where('sales_manager_id', $request->sales_manager_id);
        }

        // Add Aggregates
        $distributorsQuery->withCount(['distributorOrders as total_orders' => function ($query) {
            $query->whereIn('status', ['approved', 'delivered']);
        }])->withMax(['distributorOrders as last_order_date' => function ($query) {
            $query->whereIn('status', ['approved', 'delivered']);
        }], 'updated_at');

        // --- HANDLE AJAX REQUESTS ---
        if ($request->ajax()) {
            
            // Case A: Detail Transaction Table for a specific distributor
            if ($request->has('distributor_id')) {
                $distributorId = $request->input('distributor_id');
                $targetDistributor = Distributor::findOrFail($distributorId);

                // Permission check
                if ($user->hasRole('salesmanager') && $targetDistributor->sales_manager_id !== $user->salesManager->id) {
                    return response()->json(['error' => 'Unauthorized access.'], 403);
                }

                $orders = $targetDistributor->distributorOrders()
                    ->with('items.product')
                    ->whereIn('status', ['approved', 'delivered'])
                    ->get()
                    ->map(function($o) {
                        $o->type = 'order';
                        return $o;
                    });

                $credits = \App\Models\CreditNote::where('user_id', $targetDistributor->user_id)
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

                $merged = $orders->concat($credits)->sortByDesc('updated_at');

                return DataTables::of($merged)
                    ->addIndexColumn()
                    ->addColumn('product_summary', function ($row) {
                        if (isset($row->type) && $row->type === 'credit') {
                            $notes = !empty($row->notes) ? '<div class="small text-muted mt-1">Notes: '.$row->notes.'</div>' : '';
                            return '<div class="text-info fw-bold"><i class="fa fa-receipt me-1"></i> Credit Note Issued</div>' . ($row->product_summary ?? '') . $notes;
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
                    ->editColumn('loyalty_points_earned', function ($row) {
                        $prefix = (isset($row->type) && $row->type === 'credit') ? '+' : '';
                        $pts = (isset($row->type) && $row->type === 'credit') ? ($row->loyalty_points_earned ?? 0) : 0;
                        return $prefix . number_format($pts, 2);
                    })
                    ->editColumn('status', function ($row) {
                        if (isset($row->type) && $row->type === 'credit') {
                            return '<span class="badge bg-info text-white">CREDIT</span>';
                        }
                        $statusClass = $row->status === 'delivered' ? 'success' : 'primary';
                        return '<span class="badge bg-'.$statusClass.' text-white">'.strtoupper($row->status).'</span>';
                    })
                    ->rawColumns(['product_summary', 'status'])
                    ->make(true);
            }

            // Case B: Overview Table (List of Distributors)
            return DataTables::of($distributorsQuery)
                ->addIndexColumn()
                ->addColumn('distributor_name', function ($row) {
                    return '<div class="d-flex align-items-center">
                                <div class="fw-bold heading-theme">' . ($row->name ?? $row->user->name ?? 'N/A') . '</div>
                            </div>';
                })
                ->addColumn('sales_manager', function ($row) {
                    return '<span class="small sub-heading-theme">'.($row->salesManager->user->name ?? 'N/A').'</span>';
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
                    $credits = number_format($row->credit_balance ?? 0, 2);
                    return '<div class="text-center">
                                <span class="badge bg-info text-white px-2 py-1" style="font-size: 0.7rem;" title="Credit Balance">₹'.$credits.' Cr</span>
                            </div>';
                })
                ->addColumn('action', function ($row) {
                    $url = route('admin.distributor-wallet.detail', $row->id);
                    return '<div class="text-center">
                                <a href="'.$url.'" class="btn btn-primary rounded-pill px-4 fw-bold" style="padding-top: 7px; padding-bottom: 7px; font-size: 0.85rem; box-shadow: 0 4px 10px rgba(0,73,122,0.2);">
                                    View
                                </a>
                            </div>';
                })
                ->filterColumn('distributor_name', function($query, $keyword) {
                    $query->where('distributors.name', 'like', "%{$keyword}%")
                          ->orWhereHas('user', function($q) use ($keyword) {
                              $q->where('name', 'like', "%{$keyword}%");
                          });
                })
                ->filterColumn('sales_manager', function($query, $keyword) {
                    $query->whereHas('salesManager.user', function($q) use ($keyword) {
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
                ->rawColumns(['distributor_name', 'sales_manager', 'region_area', 'total_orders', 'last_order', 'dynamic_loyalty_points', 'action'])
                ->make(true);
        }

        // --- HANDLE NON-AJAX REQUEST (Initial View Load) ---
        $distributors = (clone $distributorsQuery)->get();
        $globalCreditBalance = $distributors->sum('credit_balance');

        $salesManagers = collect();

        if ($user->hasAnyRole(['admin', 'superadmin'])) {
            $salesManagers = \App\Models\SalesManager::with('user')->get();
        }

        $selectedDistributor = $distributor;
        return view('admin.loyalty_points.distributor_index', compact('distributors', 'globalCreditBalance', 'salesManagers', 'selectedDistributor'));
    }
}
