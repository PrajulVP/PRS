<?php

namespace App\Http\Controllers;

use App\Models\ReturnRequest;
use App\Models\CreditNote;
use App\Models\RetailerOrder;
use App\Models\DistributorOrder;
use App\Models\Retailer;
use App\Models\Distributor;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReturnController extends Controller
{
    /**
     * Display a listing of return requests.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = ReturnRequest::with(['user', 'product', 'tier1Approver', 'tier2Approver', 'adminApprover', 'distributor', 'field_staff.user', 'sales_manager.user']);

        // Filter based on role
        if ($user->hasRole('retailer')) {
            $query->where('user_id', $user->id)->where('order_type', 'retailer');
        } elseif ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            if ($distributor) {
                $query->where(function($q) use ($user, $distributor) {
                    // Returns from this distributor to admin
                    $q->where('user_id', $user->id)->where('order_type', 'distributor')
                    // OR Returns from retailers assigned to this distributor
                    ->orWhere('distributor_id', $distributor->id);
                });
            }
        } elseif ($user->hasRole('fieldstaff')) {
            $query->where('field_staff_id', $user->field_staff_id ?? ($user->fieldStaff->id ?? null));
        } elseif ($user->hasRole('salesmanager')) {
            $query->where('sales_manager_id', $user->sales_manager_id ?? ($user->salesManager->id ?? null));
        }

        if ($request->ajax()) {
            return response()->json([
                'data' => $query->latest()->get()
            ]);
        }

        $deliveredOrders = [];
        if ($user->hasRole('retailer')) {
            $retailer = $user->retailer;
            if ($retailer) {
                $deliveredOrders = RetailerOrder::where('retailer_id', $retailer->id)
                    ->where('status', 'delivered')
                    ->latest()
                    ->take(10)
                    ->get();
            }
        } elseif ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            if ($distributor) {
                $deliveredOrders = DistributorOrder::where('distributor_id', $distributor->id)
                    ->where('status', 'delivered')
                    ->latest()
                    ->take(10)
                    ->get();
            }
        }

        return view('admin.returns.index', compact('deliveredOrders'));
    }

    /**
     * Store a new return request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_type' => 'required|in:retailer,distributor',
            'order_id' => 'required|integer',
            'product_id' => 'required|integer',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|min:5',
            'images' => 'required|array|min:1',
            'images.*' => 'image|max:5120', // 5MB limit per image
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            DB::beginTransaction();

            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePaths[] = $image->store('returns', 'public');
                }
            }

            // Find the order item to get product name, side, size etc
            $order = null;
            $item = null;
            $product = Product::findOrFail($request->product_id);

            if (!$product->is_returnable) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product is not eligible for returns.'
                ], 422);
            }

            // Determine order type and fetch order/item
            if ($request->order_type === 'retailer') {
                $order = RetailerOrder::with(['distributor', 'retailer'])->findOrFail($request->order_id);
                $item = $order->items()->where('product_id', $request->product_id)
                    ->where('side', $request->side)
                    ->where('size', $request->size)
                    ->firstOrFail();
                $price = $item->unit_price ?? 0;
                
                $distributorId = $order->distributor_id;
                // If order doesn't have it (orphaned), fallback to retailer's current distributor as last resort
                if (!$distributorId) {
                    $distributorId = $order->retailer?->distributor_id;
                }

                $fieldStaffId = $order->fieldstaff_id ?? $order->retailer?->field_staff_id;
                
                // Sales Manager should be from the distributor first, then retailer
                $salesManagerId = null;
                if ($distributorId) {
                    $salesManagerId = \App\Models\Distributor::find($distributorId)?->sales_manager_id;
                }
                if (!$salesManagerId) {
                    $salesManagerId = $order->retailer?->sales_manager_id;
                }
            } else {
                $order = DistributorOrder::with('distributor')->findOrFail($request->order_id);
                $item = $order->items()->where('product_id', $request->product_id)
                    ->where('side', $request->side)
                    ->where('size', $request->size)
                    ->firstOrFail();
                $price = $item->price ?? 0;

                $distributorId = $order->distributor_id;
                $fieldStaffId = null;
                $salesManagerId = $order->sales_manager_id ?? $order->distributor?->sales_manager_id;
            }

            // Check remaining quantity
            $existingReturnQty = ReturnRequest::where('order_type', $request->order_type)
                ->where('order_id', $request->order_id)
                ->where('product_id', $request->product_id)
                ->where('side', $request->side)
                ->where('size', $request->size)
                ->where('status', '!=', 'rejected')
                ->sum('quantity');
            
            $remainingQty = $item->quantity - $existingReturnQty;

            if ($request->quantity > $remainingQty) {
                return response()->json([
                    'success' => false,
                    'message' => "Requested quantity exceeds the available return balance. Max available: " . number_format($remainingQty, 2)
                ], 422);
            }

            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('returns', 'public');
                    $imagePaths[] = $path;
                }
            }

            // Create return request
            $returnRequest = ReturnRequest::create([
                'return_code' => 'RET-' . strtoupper(Str::random(8)),
                'order_type' => $request->order_type,
                'order_id' => $request->order_id,
                'user_id' => $user->id,
                'distributor_id' => $distributorId,
                'field_staff_id' => $fieldStaffId,
                'sales_manager_id' => $salesManagerId,
                'product_id' => $request->product_id,
                'product_name' => $item->product_name ?? ($item->product ? $item->product->product_name : 'N/A'),
                'side' => $item->side,
                'size' => $item->size,
                'quantity' => $request->quantity,
                'unit' => $item->unit ?? 'Nos',
                'reason' => $request->reason,
                'image_path' => $imagePaths[0] ?? null,
                'image_paths' => $imagePaths,
                'status' => 'pending',
                'refund_amount' => $request->quantity * $price,
            ]);

            DB::commit();

            // Notify respective manager/staff
            if ($returnRequest->order_type === 'retailer') {
                // Always notify Field Staff (Tier 1)
                if ($returnRequest->field_staff_id) {
                    $fsUser = \App\Models\FieldStaff::find($returnRequest->field_staff_id)?->user;
                    if ($fsUser) {
                        $fsUser->notify(new \App\Notifications\ReturnRequestNotification($returnRequest, 'created', $user));
                    }
                }
                
                // Also notify Distributor (Tier 2/Stakeholder)
                if ($returnRequest->distributor_id) {
                    $distUser = \App\Models\Distributor::find($returnRequest->distributor_id)?->user;
                    if ($distUser) {
                        $distUser->notify(new \App\Notifications\ReturnRequestNotification($returnRequest, 'created', $user));
                    }
                }
            } elseif ($returnRequest->order_type === 'distributor') {
                // Notify Sales Manager (Tier 1)
                if ($returnRequest->sales_manager_id) {
                    $smUser = \App\Models\SalesManager::find($returnRequest->sales_manager_id)?->user;
                    if ($smUser) {
                        $smUser->notify(new \App\Notifications\ReturnRequestNotification($returnRequest, 'created', $user));
                    }
                }
            }

            return response()->json(['success' => 'Return request submitted successfully.', 'data' => $returnRequest]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Return Request Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to submit return request: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Approve a return request (Multi-tier logic).
     */
    public function approve(Request $request, ReturnRequest $returnRequest)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            DB::beginTransaction();

            if ($returnRequest->order_type === 'retailer') {
                // Tier 1: Field Staff
                if ($user->hasRole('fieldstaff') && $returnRequest->status === 'pending') {
                    $returnRequest->update([
                        'status' => 'approved_tier1',
                        'tier1_approved_at' => now(),
                        'tier1_approved_by' => $user->id,
                    ]);
                }
                // Tier 2: Distributor
                elseif ($user->hasRole('distributor') && $returnRequest->status === 'approved_tier1') {
                    $returnRequest->update([
                        'status' => 'completed',
                        'tier2_approved_at' => now(),
                        'tier2_approved_by' => $user->id,
                    ]);
                    $this->generateCreditNote($returnRequest);
                }
                else {
                    throw new \Exception('Unauthorized or invalid status for approval.');
                }
            } else { // distributor return
                // Tier 1: Sales Manager
                if ($user->hasRole('salesmanager') && $returnRequest->status === 'pending') {
                    $returnRequest->update([
                        'status' => 'approved_tier1',
                        'tier1_approved_at' => now(),
                        'tier1_approved_by' => $user->id,
                    ]);
                }
                // Tier 2: Admin
                elseif ($user->hasAnyRole(['admin', 'superadmin']) && $returnRequest->status === 'approved_tier1') {
                    $returnRequest->update([
                        'status' => 'completed',
                        'admin_approved_at' => now(),
                        'admin_approved_by' => $user->id,
                    ]);
                    $this->generateCreditNote($returnRequest);
                }
                else {
                    throw new \Exception('Unauthorized or invalid status for approval.');
                }
            }

            DB::commit();

            // Send Notifications after successful state change
            if ($returnRequest->status === 'approved_tier1') {
                $nextRecipient = null;
                if ($returnRequest->order_type === 'retailer' && $returnRequest->distributor_id) {
                    $nextRecipient = \App\Models\User::where('id', function($q) use ($returnRequest) {
                        $q->select('user_id')->from('distributors')->where('id', $returnRequest->distributor_id);
                    })->first();
                } elseif ($returnRequest->order_type === 'distributor') {
                    $nextRecipients = \App\Models\User::role(['admin', 'superadmin'])->get();
                    foreach ($nextRecipients as $recipient) {
                        $recipient->notify(new \App\Notifications\ReturnRequestNotification($returnRequest, 'approved', $user));
                    }
                    $nextRecipient = null; // Prevent double notify below
                }

                if ($nextRecipient) {
                    $nextRecipient->notify(new \App\Notifications\ReturnRequestNotification($returnRequest, 'approved', $user));
                }
            } elseif ($returnRequest->status === 'completed') {
                // Notify the original requester
                $returnRequest->user->notify(new \App\Notifications\ReturnRequestNotification($returnRequest, 'completed', $user));
            }

            return response()->json(['success' => 'Return request approved.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Return Approval Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reject a return request.
     */
    public function reject(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate(['reason' => 'required|string|min:5']);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $returnRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'rejected_by' => $user->id,
        ]);

        // Notify the original requester
        $returnRequest->user->notify(new \App\Notifications\ReturnRequestNotification($returnRequest, 'rejected', $user));

        return response()->json(['success' => 'Return request rejected.']);
    }

    /**
     * Internal method to generate Credit Note after final approval.
     */
    private function generateCreditNote(ReturnRequest $returnRequest)
    {
        $refundAmount = $returnRequest->refund_amount;
        $creditCode = 'CN-' . strtoupper(Str::random(10));
        
        $creditNote = CreditNote::create([
            'credit_code' => $creditCode,
            'user_id' => $returnRequest->user_id,
            'return_request_id' => $returnRequest->id,
            'amount' => $refundAmount,
            'balance' => $refundAmount,
            'status' => 'active',
            'notes' => 'Credit issued for return ' . $returnRequest->return_code,
        ]);

        // Update profile balance (optional, if credit_balance exists on models)
        $requester = $returnRequest->user;
        if ($returnRequest->order_type === 'retailer') {
            $retailer = $requester->retailer;
            if ($retailer && \Schema::hasColumn('retailers', 'credit_balance')) {
                $retailer->increment('credit_balance', $refundAmount);
            }
        } else {
            $distributor = $requester->distributor;
            if ($distributor && \Schema::hasColumn('distributors', 'credit_balance')) {
                $distributor->increment('credit_balance', $refundAmount);
            }
        }

        Log::info("Credit Note {$creditCode} generated for return {$returnRequest->return_code}");
        return $creditNote;
    }

    /**
     * Search for a delivered order by code to initiate a return.
     */
    public function searchOrder(Request $request)
    {
        $code = trim($request->code);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (empty($code)) {
            return response()->json(['success' => false, 'message' => 'Please enter an order code.']);
        }

        // Search Retailer Orders
        $retailerOrder = RetailerOrder::where('order_code', $code)
            ->where('status', 'delivered')
            ->with(['items.product', 'returnRequests', 'distributor.user'])
            ->first();
        
        if ($retailerOrder) {
            $isRetailer = $user->hasRole('retailer') && $retailerOrder->retailer_id === $user->retailer?->id;
            $isDistributor = $user->hasRole('distributor') && $retailerOrder->distributor_id === $user->distributor?->id;
            
            if ($isRetailer || $isDistributor) {
                return response()->json([
                    'success' => true,
                    'type' => 'retailer',
                    'order' => $this->formatOrderForSearch($retailerOrder)
                ]);
            }
        }

        // Search Distributor Orders
        $distributorOrder = DistributorOrder::where('order_code', $code)
            ->where('status', 'delivered')
            ->with(['items.product', 'returnRequests', 'distributor.user'])
            ->first();

        if ($distributorOrder) {
            $isDistributor = $user->hasRole('distributor') && $distributorOrder->distributor_id === $user->distributor?->id;
            if ($isDistributor) {
                return response()->json([
                    'success' => true,
                    'type' => 'distributor',
                    'order' => $this->formatOrderForSearch($distributorOrder)
                ]);
            }
        }

        return response()->json(['success' => false, 'message' => 'Delivered order not found or access denied.']);
    }

    /**
     * Helper to format order items for search results.
     */
    private function formatOrderForSearch($order)
    {
        return [
            'id' => $order->id,
            'order_code' => $order->order_code,
            'distributor_name' => $order->distributor?->user?->name ?? ($order->distributor?->name ?? 'Self/Admin'),
            'delivered_at' => $order->delivered_at ? $order->delivered_at->format('d M, Y') : ($order->updated_at ? $order->updated_at->format('d M, Y') : 'N/A'),
            'items' => $order->items->map(function ($item) use ($order) {
                $itemReturns = $order->returnRequests
                    ->where('product_id', $item->product_id)
                    ->where('side', $item->side)
                    ->where('size', $item->size);

                $returnedQty = $itemReturns->where('status', 'completed')->sum('quantity');
                $pendingQty = $itemReturns->whereIn('status', ['pending', 'approved_tier1'])->sum('quantity');

                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name ?? $item->product?->product_name,
                    'side' => $item->side,
                    'size' => $item->size,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit ?? 'Nos',
                    'is_returnable' => $item->product?->is_returnable ?? true,
                    'returned_qty' => (float)$returnedQty,
                    'pending_return_qty' => (float)$pendingQty,
                ];
            })
        ];
    }

    /**
     * Get paginated/searchable delivered orders for the current user.
     */
    public function getDeliveredOrders(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $search = $request->search;
        $brand = $request->brand;
        $productId = $request->product_id;
        $distributorId = $request->distributor_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        
        if ($user->hasRole('retailer')) {
            $retailer = $user->retailer;
            if (!$retailer) return response()->json(['data' => []]);
            
            $query = RetailerOrder::where('retailer_id', $retailer->id)
                ->where('status', 'delivered')
                ->with(['items.product', 'distributor.user']);
                
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('order_code', 'like', "%{$search}%")
                      ->orWhereHas('items.product', function($pq) use ($search) {
                          $pq->where('product_name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                      })
                      ->orWhereHas('distributor.user', function($dq) use ($search) {
                          $dq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            if ($brand) {
                $query->whereHas('items.product', function($q) use ($brand) {
                    $q->where('brand', $brand);
                });
            }

            if ($productId) {
                $query->whereHas('items', function($q) use ($productId) {
                    $q->where('product_id', $productId);
                });
            }

            if ($distributorId) {
                $query->where('distributor_id', $distributorId);
            }

            if ($startDate) {
                $query->whereDate('delivered_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('delivered_at', '<=', $endDate);
            }
            
            $orders = $query->latest()->paginate(10);
            
            $formatted = $orders->getCollection()->map(function($o) {
                return [
                    'id' => $o->id,
                    'order_code' => $o->order_code,
                    'date' => $o->delivered_at ? $o->delivered_at->format('d M, Y') : $o->updated_at->format('d M, Y'),
                    'total_amount' => $o->total_amount,
                    'item_count' => $o->items->count(),
                    'distributor' => $o->distributor?->user?->name ?? 'N/A'
                ];
            });
            
            return response()->json([
                'data' => $formatted,
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'total' => $orders->total()
                ]
            ]);
            
        } elseif ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            if (!$distributor) return response()->json(['data' => []]);
            
            $query = DistributorOrder::where('distributor_id', $distributor->id)
                ->where('status', 'delivered')
                ->with(['items.product']);
                
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('order_code', 'like', "%{$search}%")
                      ->orWhereHas('items.product', function($pq) use ($search) {
                          $pq->where('product_name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                      });
                });
            }

            if ($brand) {
                $query->whereHas('items.product', function($q) use ($brand) {
                    $q->where('brand', $brand);
                });
            }

            if ($productId) {
                $query->whereHas('items', function($q) use ($productId) {
                    $q->where('product_id', $productId);
                });
            }

            if ($startDate) {
                $query->whereDate('delivered_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('delivered_at', '<=', $endDate);
            }
            
            $orders = $query->latest()->paginate(10);
            
            $formatted = $orders->getCollection()->map(function($o) {
                return [
                    'id' => $o->id,
                    'order_code' => $o->order_code,
                    'date' => $o->delivered_at ? $o->delivered_at->format('d M, Y') : $o->updated_at->format('d M, Y'),
                    'total_amount' => $o->total_amount,
                    'item_count' => $o->items->count(),
                    'distributor' => 'Self'
                ];
            });
            
            return response()->json([
                'data' => $formatted,
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'total' => $orders->total()
                ]
            ]);
        }
        
        return response()->json(['data' => []]);
    }

    /**
     * Get unique filters (brands, products, distributors) for return requests.
     */
    public function getFilters(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $brands = Product::whereNotNull('brand')->distinct()->pluck('brand')->sort()->values();
        $products = Product::select('id', 'product_name')->orderBy('product_name')->get();
        
        $distributors = [];
        if ($user->hasRole('retailer')) {
            // Get distributors that have delivered orders to this retailer
            $distributorIds = RetailerOrder::where('retailer_id', $user->retailer?->id)
                ->where('status', 'delivered')
                ->whereNotNull('distributor_id')
                ->distinct()
                ->pluck('distributor_id');
                
            $distributors = Distributor::whereIn('id', $distributorIds)
                ->with('user')
                ->get()
                ->map(function($d) {
                    return [
                        'id' => $d->id,
                        'name' => $d->user?->name ?? 'N/A'
                    ];
                });
        }
        
        return response()->json([
            'brands' => $brands,
            'products' => $products,
            'distributors' => $distributors
        ]);
    }
}
