<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Distributor;
use App\Models\SalesManager;
use App\Models\FieldStaff;
use App\Models\Retailer;
use App\Models\DistributorOrder; // Added this line
use Illuminate\Support\Facades\Storage;

class PendingApprovalController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $reqType = $request->route('type') ?? $request->input('type');

        // Determine effective type or fallback to role-based default
        if ($reqType) {
            $viewType = $reqType;
        } else {
            // Default fallback if no type specified
            if ($user->hasPermissionToCategory('distributor_approvals', 'view')) $viewType = 'distributor';
            elseif ($user->hasPermissionToCategory('retailer_approvals', 'view')) $viewType = 'retailer';
            elseif ($user->hasRole('superadmin')) $viewType = 'distributor';
            elseif ($user->hasRole('admin')) $viewType = 'distributor';
            elseif ($user->hasRole('salesmanager')) $viewType = 'distributor'; // SM handles distributor approvals
            elseif ($user->hasRole('retailer')) $viewType = 'retailer';
            elseif ($user->hasRole('fieldstaff')) $viewType = 'retailer';
            else $viewType = 'none';
        }

        // Final Permission Check
        if ($viewType === 'retailer') {
            if (!$user->hasPermissionToCategory('retailer_approvals', 'view') && !$user->hasAnyRole(['superadmin', 'admin', 'distributor', 'fieldstaff', 'retailer'])) {
                abort(403, 'Unauthorized access to retailer approvals.');
            }
        } elseif ($viewType === 'distributor') {
            if (!$user->hasPermissionToCategory('distributor_approvals', 'view') && !$user->hasRole(['superadmin', 'admin', 'salesmanager', 'distributor'])) {
                abort(403, 'Unauthorized access to distributor approvals.');
            }
        }

        if ($request->ajax()) {
            $data = [];
            $roleType = $viewType; // For context in frontend

            // Logic based on requested view type
            if ($viewType === 'distributor') {
                $query = \App\Models\DistributorOrder::with(['distributor.user', 'items.product', 'items.batches', 'distributor.salesManager.user', 'salesManager.user', 'returnRequests']);

                if ($user->hasRole('salesmanager') && $user->salesManager) {
                    $salesManagerId = $user->salesManager->id;
                    $query->where(function ($q) use ($salesManagerId) {
                        $q->where('sales_manager_id', $salesManagerId)
                            ->orWhereHas('distributor', function ($q) use ($salesManagerId) {
                                $q->where('sales_manager_id', $salesManagerId);
                            });
                    });
                }

                if ($user->hasRole('distributor') && $user->distributor) {
                    $query->where('distributor_id', $user->distributor->id);
                }

                if ($request->input('status')) {
                    $query->where('status', $request->input('status'));
                }

                if ($request->input('payment_status')) {
                    $status = $request->input('payment_status');
                    if ($status === 'pending') {
                        $query->where(function ($q) {
                            $q->where('payment_status', 'pending')
                                ->orWhereNull('payment_status');
                        });
                    } else {
                        $query->where('payment_status', $status);
                    }
                }

                $data = $query->latest()->get();

                // Calculate Counts for Tabs
                $countsQuery = \App\Models\DistributorOrder::query();
                if ($user->hasRole('salesmanager') && $user->salesManager) {
                    $salesManagerId = $user->salesManager->id;
                    $countsQuery->where(function ($q) use ($salesManagerId) {
                        $q->where('sales_manager_id', $salesManagerId)
                            ->orWhereHas('distributor', function ($q) use ($salesManagerId) {
                                $q->where('sales_manager_id', $salesManagerId);
                            });
                    });
                }
                if ($user->hasRole('distributor') && $user->distributor) {
                    $countsQuery->where('distributor_id', $user->distributor->id);
                }

                $statusCounts = $countsQuery->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')->toArray();
                $totalCount = array_sum($statusCounts);
            } elseif ($viewType === 'retailer') {
                // Fetch Retailer Orders
                $query = \App\Models\RetailerOrder::with(['retailer.user', 'retailer.area', 'retailer.district', 'retailer.salesManager.user', 'retailer.fieldStaff.user', 'items.product', 'items.batches', 'distributor.user', 'fieldStaff.user', 'returnRequests']);

                if ($user->hasRole('distributor') && $user->distributor) {
                    $query->where('distributor_id', $user->distributor->id);
                }

                if ($user->hasRole('retailer') && $user->retailer) {
                    $query->where('retailer_id', $user->retailer->id);
                }

                if ($user->hasRole('fieldstaff') && $user->fieldStaff) {
                    $fieldStaffId = $user->fieldStaff->id;
                    $query->where(function ($q) use ($fieldStaffId) {
                        $q->where('fieldstaff_id', $fieldStaffId)
                            ->orWhereHas('retailer', function ($qr) use ($fieldStaffId) {
                                $qr->where('field_staff_id', $fieldStaffId);
                            });
                    });
                }

                if ($user->hasRole('salesmanager') && $user->salesManager) {
                    $salesManagerId = $user->salesManager->id;
                    $query->where(function ($q) use ($salesManagerId) {
                        $q->whereHas('retailer', function ($subQ) use ($salesManagerId) {
                            $subQ->whereHas('fieldStaff', function ($fsQ) use ($salesManagerId) {
                                $fsQ->where('sales_manager_id', $salesManagerId);
                            });
                        })->orWhereHas('fieldStaff', function ($fsQ) use ($salesManagerId) {
                            $fsQ->where('sales_manager_id', $salesManagerId);
                        });
                    });
                }

                if ($request->input('status')) {
                    $query->where('status', $request->input('status'));
                }

                if ($request->input('payment_status')) {
                    $pStatus = $request->input('payment_status');
                    if ($pStatus === 'pending') {
                        $query->where(function ($q) {
                            $q->where('payment_status', 'pending')->orWhereNull('payment_status');
                        });
                    } else {
                        $query->where('payment_status', $pStatus);
                    }
                }

                $data = $query->latest()->get();

                // Calculate Counts for Tabs
                $countsQuery = \App\Models\RetailerOrder::query();
                if ($user->hasRole('distributor') && $user->distributor) {
                    $countsQuery->where('distributor_id', $user->distributor->id);
                }
                if ($user->hasRole('retailer') && $user->retailer) {
                    $countsQuery->where('retailer_id', $user->retailer->id);
                }
                if ($user->hasRole('fieldstaff') && $user->fieldStaff) {
                    $fieldStaffId = $user->fieldStaff->id;
                    $countsQuery->where(function ($q) use ($fieldStaffId) {
                        $q->where('fieldstaff_id', $fieldStaffId)
                            ->orWhereHas('retailer', function ($qr) use ($fieldStaffId) {
                                $qr->where('field_staff_id', $fieldStaffId);
                            });
                    });
                }
                if ($user->hasRole('salesmanager') && $user->salesManager) {
                    $salesManagerId = $user->salesManager->id;
                    $countsQuery->where(function ($q) use ($salesManagerId) {
                        $q->whereHas('retailer', function ($subQ) use ($salesManagerId) {
                            $subQ->whereHas('fieldStaff', function ($fsQ) use ($salesManagerId) {
                                $fsQ->where('sales_manager_id', $salesManagerId);
                            });
                        })->orWhereHas('fieldStaff', function ($fsQ) use ($salesManagerId) {
                            $fsQ->where('sales_manager_id', $salesManagerId);
                        });
                    });
                }

                $statusCounts = $countsQuery->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')->toArray();
                $totalCount = array_sum($statusCounts);
            }
            // ... (other types removed as we focus on Orders for approvals page in this context)

            // Format Data
            $formatted = collect($data)->map(function ($item) use ($viewType) {
                // Common Order Formatting
                $productSummary = $item->items->map(function ($i) {
                    $pName = $i->product?->product_name ?? 'N/A';
                    
                    // Clean up product name from any existing brackets to prevent duplication
                    if (str_contains($pName, '[')) {
                        $pName = trim(explode('[', $pName)[0]);
                    }
                    
                    $vLabel = array_filter([$i->side, $i->size]);
                    $pBrand = $i->product?->brand ?? null;
                    
                    $pPack = $i->product?->pack ?? null;
                    
                    $summary = '<div class="product-summary-item mb-2" style="line-height: 1.35; width: 100%; white-space: normal; word-break: break-word; overflow-wrap: break-word;">';
                    $summary .= '<div style="display: block; margin-bottom: 2px;">';
                    $summary .= '<span class="fw-bold" style="color: #334155; font-size: 0.85rem; word-break: break-word;">'.$pName.'</span>';
                    if (!empty(trim($pPack)) && strtoupper(trim($pPack)) !== 'N/A') {
                        $summary .= '<span class="small fw-semibold" style="color: #94a3b8; font-size: 0.75rem; white-space: nowrap; margin-left: 3px;">['.$pPack.']</span>';
                    }
                    if (!empty($vLabel)) {
                        $summary .= ' <span class="badge rounded-pill align-middle" style="background: #e0f2fe; color: #0369a1; font-size: 0.65rem; padding: 2px 6.5px; font-weight: 700; letter-spacing: 0.3px; white-space: nowrap; margin-left: 4px; display: inline-block;">' . strtoupper(implode(' / ', $vLabel)) . '</span>';
                    }
                    $summary .= '</div>';
                    
                    $meta = [];
                    $qtyStr = $i->quantity . ' ' . ($i->unit ?? 'Nos');
                    
                    $meta[] = '<span class="text-primary fw-bold" style="font-size: 0.75rem;">' . $qtyStr . '</span>';
                    
                    if (!empty($meta)) {
                        $summary .= '<div class="d-flex flex-wrap align-items-center gap-1 mt-1" style="word-break: break-word;">' . implode(' <span class="text-muted" style="font-size: 0.75rem; margin: 0 2px;">•</span> ', $meta) . '</div>';
                    }
                    $summary .= '</div>';
                    return $summary;
                })->implode('|||');

                $brandSummary = $item->items->map(function ($i) {
                    return $i->product?->brand ?? 'N/A';
                })->implode('|||');

                $res = [
                    'id' => $item->id,
                    'order_code' => $item->order_code,
                    'total_amount' => $item->total_amount,
                    'metadata' => $item->metadata,
                    'status' => ucfirst(str_replace('_', ' ', $item->status)),
                    'product_summary' => $productSummary,
                    'brand_summary' => $brandSummary,
                    'placed_at' => $item->placed_at ? $item->placed_at->format('Y-m-d H:i') : '-',
                    'role_type' => 'order',
                    'items' => $item->items->map(function ($i) use ($viewType) {
                        $itemData = [
                            'order_item_id' => $i->id,
                            'product_id' => $i->product_id,
                            'product_name' => $i->product?->product_name ?? 'N/A',
                            'product_code' => $i->product?->product_code,
                            'generic_name' => $i->product?->generic_name,
                            'quantity' => $i->quantity,
                            'free_quantity' => $i->free_quantity ?? 0,
                            'unit' => $i->unit ?? 'Strips',
                            'pack' => $i->product?->pack,
                            'strip_size' => $i->product?->strip_size,
                            'box_size' => $i->product?->box_size,
                            'carton_size' => $i->product?->carton_size,
                            'side' => $i->side,
                            'size' => $i->size,
                            'is_returnable' => $i->product?->is_returnable ?? true,
                            'gst' => $i->product?->gst ?? 0,
                        ];

                        // Find corresponding return request if any
                        $retReq = ($item->returnRequests ?? collect())->where('product_id', $i->product_id)
                            ->where('side', $i->side)
                            ->where('size', $i->size)
                            ->first();
                        
                        $itemData['return_status'] = $retReq ? $retReq->status : null;
                        $itemData['return_code'] = $retReq ? $retReq->return_code : null;

                        if ($viewType === 'distributor') {
                            $itemData['unit_price'] = $i->price ?? 0;
                            $itemData['total_amount'] = $i->subtotal ?? 0;
                        } else {
                            $itemData['unit_price'] = $i->unit_price ?? 0;
                            $itemData['total_amount'] = $i->total_amount ?? 0;
                        }

                        $itemData['batches'] = ($i->batches ?? collect())->map(function ($b) {
                            return [
                                'id' => $b->id,
                                'batch_no' => $b->batch_no,
                                'expiry_date' => $b->expiry_date ? (function ($date) {
                                    try {
                                        $parsed = \Carbon\Carbon::parse($date);
                                        if ($parsed->copy()->endOfMonth()->isSameDay($parsed)) {
                                            return $parsed->format('m/Y');
                                        }
                                        return $parsed->format('d/m/Y');
                                    } catch (\Exception $e) {
                                        return $date;
                                    }
                                })($b->expiry_date) : '-',
                                'quantity' => $b->quantity,
                            ];
                        });

                        return $itemData;
                    }),
                    'delivery_notes' => $item->delivery_notes ?? '-',
                ];

                if ($viewType === 'distributor') {
                    $res['distributor_id'] = $item->distributor_id;
                    $res['distributor_name'] = $item->distributor->user->name ?? ($item->distributor->name ?? 'N/A');
                    $res['distributor_phone'] = $item->distributor->contact_no ?? '--';
                    $res['distributor_email'] = $item->distributor->user->email ?? '--';
                    $res['distributor_address'] = $item->distributor->address ?? '--';
                    $res['distributor_gst'] = $item->distributor->gst ?? '--';
                    $res['distributor_dl'] = $item->distributor->drug_license_no ?? '--';
                    $res['payment_status'] = $item->payment_status ?? 'pending';
                    $res['invoice_url'] = $item->invoice_path ? asset('storage/' . $item->invoice_path) : null;
                    $res['sales_manager_id'] = $item->sales_manager_id;
                    $res['distributor_sm_id'] = $item->distributor->sales_manager_id ?? null;
                } elseif ($viewType === 'retailer') {
                    $res['retailer_id'] = $item->retailer_id;
                    $res['distributor_id'] = $item->distributor_id;
                    $res['retailer_name'] = $item->retailer->shop_name ?? ($item->retailer->user->name ?? 'N/A');
                    $res['retailer_sm_name'] = $item->retailer->salesManager->user->name ?? 'N/A';
                    $res['retailer_fs_name'] = $item->retailer->fieldStaff->user->name ?? 'N/A';
                    $res['retailer_phone'] = $item->retailer->contact_no ?? '--';
                    $res['retailer_area'] = $item->retailer->area->name ?? 'N/A';
                    $res['retailer_district'] = $item->retailer->district->name ?? 'N/A';
                    $res['retailer_gst'] = $item->retailer->gst ?? '--';
                    $res['retailer_dl'] = $item->retailer->drug_license_no ?? '--';
                    $res['retailer_location'] = $item->retailer->address ?? '--';
                    $res['payment_status'] = $item->payment_status ?? 'pending';
                    $res['invoice_url'] = $item->invoice_path ? asset('storage/' . $item->invoice_path) : null;
                    $res['fieldstaff_id'] = $item->fieldstaff_id;
                    $res['retailer_fs_id'] = $item->retailer->field_staff_id ?? null;
                    $res['sales_manager_id'] = $item->retailer->sales_manager_id ?? ($item->retailer->fieldStaff->sales_manager_id ?? null);
                    $res['retailer_sm_id'] = $item->retailer->sales_manager_id ?? ($item->retailer->fieldStaff->sales_manager_id ?? null);
                    
                    // Added distributor details for popover as well
                    $res['distributor_name'] = $item->distributor->user->name ?? ($item->distributor->name ?? 'N/A');
                    $res['distributor_phone'] = $item->distributor->contact_no ?? '--';
                    $res['distributor_gst'] = $item->distributor->gst ?? '--';
                    $res['distributor_dl'] = $item->distributor->drug_license_no ?? '--';
                }

                return $res;
            });

            return response()->json([
                'data' => $formatted,
                'counts' => $viewType === 'retailer' || $viewType === 'distributor' ? [
                    'all' => $totalCount ?? 0,
                    'pending' => $statusCounts['pending'] ?? 0,
                    'processing' => $statusCounts['processing'] ?? 0,
                    'approved' => $statusCounts['approved'] ?? 0,
                    'delivered' => $statusCounts['delivered'] ?? 0,
                    'cancelled' => $statusCounts['cancelled'] ?? 0,
                    'rejected' => $statusCounts['rejected'] ?? 0,
                ] : []
            ]);
        }

        if ($viewType === 'retailer') {
            return view('admin.pending_approvals.retailers.index', ['type' => $viewType]);
        } elseif ($viewType === 'distributor') {
            return view('admin.pending_approvals.distributors.index', ['type' => $viewType]);
        }

        abort(404, 'View not found for type: ' . $viewType);
    }
}
