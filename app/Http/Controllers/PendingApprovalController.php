<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Distributor;
use App\Models\SalesManager;
use App\Models\FieldStaff;
use App\Models\Retailer;
use App\Models\DistributorOrder; // Added this line

class PendingApprovalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        // Retrieve 'type' from route parameter (defaults) or query string
        $reqType = $request->route('type') ?? $request->input('type');

        // Determine effective type or fallback to role-based default
        if ($reqType) {
            $viewType = $reqType;
        } else {
            // Default fallback if no type specified
            if ($user->hasRole('superadmin')) $viewType = 'distributor'; // Default view
            elseif ($user->hasRole('admin')) $viewType = 'field_staff';
            elseif ($user->hasRole('salesmanager')) $viewType = 'retailer';
            else $viewType = 'none';
        }

        if ($request->ajax()) {
            $data = [];
            $roleType = $viewType; // For context in frontend

            // Logic based on requested view type
            if ($viewType === 'distributor') {
                $query = \App\Models\DistributorOrder::with(['distributor.user', 'items.product', 'salesManager.user']);

                if ($user->hasRole('salesmanager') && $user->salesManager) {
                    $salesManagerId = $user->salesManager->id;
                    $query->where(function ($q) use ($salesManagerId) {
                        $q->where('sales_manager_id', $salesManagerId)
                            ->orWhereHas('distributor', function ($q) use ($salesManagerId) {
                                $q->where('sales_manager_id', $salesManagerId);
                            });
                    });
                }

                if ($request->input('status')) {
                    $query->where('status', $request->input('status'));
                }

                if ($request->input('payment_status')) {
                    $status = $request->input('payment_status');
                    if ($status === 'pending') {
                        $query->where(function ($q) {
                            $q->whereIn('payment_status', ['pending', 'unpaid'])
                                ->orWhereNull('payment_status');
                        });
                    } else {
                        $query->where('payment_status', $status);
                    }
                }

                $data = $query->latest()->get();
            } elseif ($viewType === 'retailer') {
                // Fetch Retailer Orders
                $query = \App\Models\RetailerOrder::with(['retailer.user', 'items.product', 'distributor.user', 'fieldStaff.user']);

                if ($user->hasRole('distributor') && $user->distributor) {
                    $query->where('distributor_id', $user->distributor->id);
                }

                if ($request->input('status')) {
                    $query->where('status', $request->input('status'));
                }

                $data = $query->latest()->get();
            }
            // ... (other types removed as we focus on Orders for approvals page in this context)

            // Format Data
            $formatted = collect($data)->map(function ($item) use ($viewType) {
                // Common Order Formatting
                $productSummary = $item->items->map(function ($i) {
                    return $i->product->product_name . ' (' . $i->quantity . ')';
                })->implode(', ');

                $res = [
                    'id' => $item->id,
                    'order_code' => $item->order_code,
                    'total_amount' => number_format($item->total_amount, 2),
                    'status' => ucfirst(str_replace('_', ' ', $item->status)),
                    'product_summary' => $productSummary,
                    'placed_at' => $item->placed_at ? $item->placed_at->format('Y-m-d H:i') : '-',
                    'role_type' => 'order',
                    'items' => $item->items->map(function ($i) {
                        return [
                            'product_name' => $i->product->product_name ?? 'N/A',
                            'quantity' => $i->quantity,
                            'unit_price' => number_format($i->unit_price, 2),
                            'total_amount' => number_format($i->total_amount, 2),
                        ];
                    }),
                    'delivery_notes' => $item->delivery_notes ?? '-',
                ];

                if ($viewType === 'distributor') {
                    $res['distributor_name'] = $item->distributor->user->name ?? 'N/A';
                    $res['payment_status'] = $item->payment_status ?? 'pending';
                    $res['invoice_url'] = $item->invoice_path ? asset('storage/' . $item->invoice_path) : null;
                } elseif ($viewType === 'retailer') {
                    $res['retailer_name'] = $item->retailer->user->name ?? 'N/A';
                    $res['payment_status'] = $item->payment_status ?? 'pending';
                    $res['invoice_url'] = $item->invoice_path ? asset('storage/' . $item->invoice_path) : null;
                }

                return $res;
            });

            return response()->json(['data' => $formatted]);
        }

        if ($viewType === 'retailer') {
            return view('admin.pending_approvals.retailers.index', ['type' => $viewType]);
        } elseif ($viewType === 'distributor') {
            return view('admin.pending_approvals.distributors.index', ['type' => $viewType]);
        }

        abort(404, 'View not found for type: ' . $viewType);
    }
}
