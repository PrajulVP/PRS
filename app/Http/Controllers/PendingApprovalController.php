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
                // Fetch Distributor Orders instead of Distributor Users
                // Logic: Orders pending approval (e.g., pending status)
                // Filter permissions (Sales Manager sees their pending orders? Admin sees all pending?)

                $query = \App\Models\DistributorOrder::with(['distributor.user', 'items.product', 'salesManager.user']);

                $statusFilter = $request->input('status');

                if ($user->hasRole('salesmanager') && $user->salesManager) {
                    $query->where('sales_manager_id', $user->salesManager->id);
                }

                if ($statusFilter) {
                    $query->where('status', $statusFilter);
                } else {
                    // If no filter, user said "by default it shows all". 
                    // Previously we restricted to pending. 
                    // Use the previous default list ONLY if you want to restrict initial view. 
                    // But user said "by default it shows all", which implies NO restriction.
                    // However, "Pending Approvals" page name suggests otherwise.
                    // I will assume "All" means All.
                }

                $data = $query->latest()->get();
            } elseif ($viewType === 'retailer') {
                // Admins/Superadmins can see all pending retailers? 
                // Or stick to SalesManager seeing their own? 
                // Assuming Admin can see all for now to support the menu item.
                if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
                    $data = Retailer::whereHas('user', function ($query) {
                        $query->where('status', 'inactive');
                    })->with(['user', 'fieldStaff.user'])->get();
                } elseif ($user->hasRole('salesmanager')) {
                    if ($user->salesManager) {
                        $sales_manager_id = $user->salesManager->id;
                        $fieldStaffIds = FieldStaff::where('sales_manager_id', $sales_manager_id)->pluck('id');
                        $data = Retailer::where(function ($query) use ($sales_manager_id, $fieldStaffIds) {
                            $query->where('sales_manager_id', $sales_manager_id)
                                ->orWhereIn('field_staff_id', $fieldStaffIds);
                        })->whereHas('user', function ($q) {
                            $q->where('status', 'inactive');
                        })->with(['user', 'fieldStaff.user'])->get();
                    }
                }
            } elseif ($viewType === 'sales_manager') {
                if ($user->hasRole('superadmin')) {
                    $data = SalesManager::whereHas('user', function ($query) {
                        $query->where('status', 'inactive');
                    })->with('user')->get();
                }
            } elseif ($viewType === 'field_staff') {
                if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
                    $data = FieldStaff::whereHas('user', function ($query) {
                        $query->where('status', 'inactive');
                    })->with(['user', 'salesManager.user'])->get();
                }
            }

            // Format Data for DataTable
            $formatted = collect($data)->map(function ($item) use ($viewType) {
                if ($viewType === 'distributor') {
                    // Format as Order
                    $productSummary = $item->items->map(function ($i) {
                        return $i->product->product_name . ' (' . $i->quantity . ')';
                    })->implode(', ');

                    return [
                        'id' => $item->id,
                        'order_code' => $item->order_code,
                        'distributor_name' => $item->distributor->user->name ?? 'N/A',
                        'total_amount' => number_format($item->total_amount, 2),
                        'status' => ucfirst(str_replace('_', ' ', $item->status)),
                        'payment_status' => $item->payment_status ?? 'pending',
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
                        'invoice_path' => $item->invoice_path,
                        'invoice_url' => $item->invoice_path ? asset('storage/' . $item->invoice_path) : null,
                        // We need actions url logic or handle in frontend
                    ];
                } else {
                    // Format as User
                    $res = [
                        'id' => $item->id,
                        'name' => $item->user->name,
                        'email' => $item->user->email,
                        'role_type' => $viewType,
                    ];

                    if ($viewType === 'sales_manager') {
                        $res['role'] = 'Sales Manager';
                        $res['activate_url'] = route('admin.users.activate', $item->user->id);
                    } elseif ($viewType === 'field_staff') {
                        $res['linked_manager'] = $item->salesManager->user->name ?? 'N/A';
                        $res['activate_url'] = route('admin.field-staffs.activate', $item->id);
                    } elseif ($viewType === 'retailer') {
                        $res['added_by'] = $item->fieldStaff->user->name ?? 'N/A';
                        $res['activate_url'] = route('admin.retailers.activate', $item->id);
                    }
                    return $res;
                }
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
