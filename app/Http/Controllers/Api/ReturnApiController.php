<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Models\RetailerOrder;
use App\Models\DistributorOrder;
use App\Models\Product;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReturnApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/returns",
     *     summary="List return requests",
     *     description="Returns a list of return requests filtered by user role and order type.",
     *     tags={"Returns"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="order_type", in="query", required=false, @OA\Schema(type="string", enum={"retailer", "distributor"}), description="Filter by order type"),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string"), description="Filter by status (pending, verified, completed, rejected)"),
     *     @OA\Parameter(name="brand", in="query", required=false, @OA\Schema(type="string"), description="Filter by product brand"),
     *     @OA\Parameter(name="product_id", in="query", required=false, @OA\Schema(type="integer"), description="Filter by product ID"),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date"), description="Start date (YYYY-MM-DD)"),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date"), description="End date (YYYY-MM-DD)"),
     *     @OA\Response(
     *         response=200,
     *         description="List of return requests",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = ReturnRequest::with(['user.retailer', 'product', 'verifiedByUser', 'distributorApprovedByUser', 'adminApprover', 'distributor.user', 'field_staff.user', 'sales_manager.user']);

        // Filter based on role
        if ($user->hasRole('retailer')) {
            $query->where('user_id', $user->id)->where('order_type', 'retailer');
        } elseif ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            if ($distributor) {
                $query->where(function($q) use ($user, $distributor) {
                    $q->where('user_id', $user->id)->where('order_type', 'distributor')
                      ->orWhere('distributor_id', $distributor->id);
                });
            }
            $query->with(['user.retailer']);
        } elseif ($user->hasRole('fieldstaff')) {
            $query->where('field_staff_id', $user->fieldStaff?->id)->with(['user.retailer']);
        } elseif ($user->hasRole('salesmanager')) {
            $query->where('sales_manager_id', $user->salesManager?->id)->with(['user.retailer']);
        } else {
            $query->with(['user.retailer']);
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('brand')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('brand', $request->brand);
            });
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $returns = $query->latest()->get()->map(function($r) {
            $shopName = null;
            if ($r->order_type === 'retailer') {
                $shopName = $r->user->retailer->shop_name ?? 'N/A';
            }
            
            $data = $r->toArray();
            $data['shop_name'] = $shopName;
            return $data;
        });

        return response()->json($returns);
    }

    /**
     * @OA\Post(
     *     path="/api/returns",
     *     summary="Submit a new return request",
     *     description="Creates a new return request for a delivered product. Requires images as evidence.",
     *     tags={"Returns"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"order_type", "order_id", "product_id", "quantity", "reason", "images[]"},
     *                 @OA\Property(property="order_type", type="string", enum={"retailer", "distributor"}),
     *                 @OA\Property(property="order_id", type="integer"),
     *                 @OA\Property(property="product_id", type="integer"),
     *                 @OA\Property(property="side", type="string", nullable=true),
     *                 @OA\Property(property="size", type="string", nullable=true),
     *                 @OA\Property(property="quantity", type="number", format="float"),
     *                 @OA\Property(property="reason", type="string"),
     *                 @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Return request submitted"),
     *     @OA\Response(response=422, description="Validation error or ineligible product")
     * )
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
            'images.*' => 'image|max:5120',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($request->product_id);
            if (!$product->is_returnable) {
                return response()->json(['error' => 'This product is not eligible for returns.'], 422);
            }

            $order = null;
            $item = null;
            $distributorId = null;
            $fieldStaffId = null;
            $salesManagerId = null;
            $price = 0;

            if ($request->order_type === 'retailer') {
                $order = RetailerOrder::with(['distributor', 'retailer'])->findOrFail($request->order_id);
                $item = $order->items()
                    ->where('product_id', $request->product_id)
                    ->where('side', $request->side)
                    ->where('size', $request->size)
                    ->firstOrFail();
                
                $price = $item->unit_price ?? 0;
                $distributorId = $order->distributor_id;
                $fieldStaffId = $order->fieldstaff_id ?? $order->retailer?->field_staff_id;
                $salesManagerId = $order->distributor?->sales_manager_id ?? $order->retailer?->sales_manager_id;
            } else {
                $order = DistributorOrder::with('distributor')->findOrFail($request->order_id);
                $item = $order->items()
                    ->where('product_id', $request->product_id)
                    ->where('side', $request->side)
                    ->where('size', $request->size)
                    ->firstOrFail();
                
                $price = $item->price ?? 0;
                $distributorId = $order->distributor_id;
                $salesManagerId = $order->sales_manager_id ?? $order->distributor?->sales_manager_id;
            }

            // Quantity Check
            $existingReturnQty = ReturnRequest::where('order_type', $request->order_type)
                ->where('order_id', $request->order_id)
                ->where('product_id', $request->product_id)
                ->where('side', $request->side)
                ->where('size', $request->size)
                ->where('status', '!=', 'rejected')
                ->sum('quantity');
            
            if ($request->quantity > ($item->quantity - $existingReturnQty)) {
                return response()->json(['error' => 'Requested quantity exceeds available return balance.'], 422);
            }

            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('returns', 'public');
            }

            $returnRequest = ReturnRequest::create([
                'return_code' => 'RET-' . strtoupper(Str::random(8)),
                'order_type' => $request->order_type,
                'order_id' => $request->order_id,
                'user_id' => $user->id,
                'distributor_id' => $distributorId,
                'field_staff_id' => $fieldStaffId,
                'sales_manager_id' => $salesManagerId,
                'product_id' => $request->product_id,
                'product_name' => $item->product_name ?? $product->product_name,
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
            return response()->json(['success' => 'Return request submitted successfully.', 'data' => $returnRequest]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/returns/{id}/approve",
     *     summary="Approve a return request (Unified Tiered Approval)",
     *     description="Approves a return request based on the authenticated user's role. This is a common endpoint for all roles (Field Staff, Sales Manager, Distributor, Admin). 
                The system automatically transitions the status through the following tiers:
                - RETAILER RETURN: pending (Wait for Field Staff) -> verified (Wait for Distributor) -> completed (Final).
                - DISTRIBUTOR RETURN: pending (Wait for Sales Manager) -> verified (Wait for Admin/Superadmin) -> completed (Final).
                Final approval triggers credit note generation and stock adjustment.",
     *     tags={"Returns"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="Return Request ID"),
     *     @OA\Response(
     *         response=200, 
     *         description="Return request approved successfully."
     *     ),
     *     @OA\Response(response=403, description="Unauthorized to approve at this stage for the current user's role.")
     * )
     */
    public function approve(ReturnRequest $returnRequest)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            DB::beginTransaction();

            if ($returnRequest->order_type === 'retailer') {
                if ($user->hasRole('fieldstaff') && $returnRequest->status === 'pending') {
                    $returnRequest->update([
                        'status' => 'verified',
                        'verified_at' => now(),
                        'verified_by' => $user->id,
                    ]);
                } elseif ($user->hasRole('distributor') && $returnRequest->status === 'verified') {
                    $returnRequest->update([
                        'status' => 'completed',
                        'distributor_approved_at' => now(),
                        'distributor_approved_by' => $user->id,
                    ]);
                    $this->generateCreditNote($returnRequest);
                    $this->adjustStockForReturn($returnRequest);
                } else {
                    return response()->json(['error' => 'Unauthorized or invalid status for approval.'], 403);
                }
            } else { // distributor return
                if ($user->hasRole('salesmanager') && $returnRequest->status === 'pending') {
                    $returnRequest->update([
                        'status' => 'verified',
                        'verified_at' => now(),
                        'verified_by' => $user->id,
                    ]);
                } elseif ($user->hasAnyRole(['admin', 'superadmin']) && $returnRequest->status === 'verified') {
                    $returnRequest->update([
                        'status' => 'completed',
                        'admin_approved_at' => now(),
                        'admin_approved_by' => $user->id,
                    ]);
                    $this->generateCreditNote($returnRequest);
                    $this->adjustStockForReturn($returnRequest);
                } else {
                    return response()->json(['error' => 'Unauthorized or invalid status for approval.'], 403);
                }
            }

            DB::commit();
            return response()->json(['success' => 'Return request approved.', 'data' => $returnRequest]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Internal method to generate Credit Note after final approval.
     */
    private function generateCreditNote(ReturnRequest $returnRequest)
    {
        $refundAmount = $returnRequest->refund_amount;
        $creditCode = 'CN-' . strtoupper(Str::random(10));
        
        \App\Models\CreditNote::create([
            'credit_code' => $creditCode,
            'user_id' => $returnRequest->user_id,
            'return_request_id' => $returnRequest->id,
            'amount' => $refundAmount,
            'balance' => $refundAmount,
            'status' => 'active',
            'notes' => 'Credit issued for return ' . $returnRequest->return_code,
        ]);
    }

    /**
     * Internal method to adjust stock when a return is completed.
     */
    private function adjustStockForReturn(ReturnRequest $returnRequest)
    {
        try {
            if ($returnRequest->order_type === 'distributor') {
                $inventory = \App\Models\Inventory::where('distributor_id', $returnRequest->distributor_id)
                    ->where('product_id', $returnRequest->product_id)
                    ->where('side', $returnRequest->side)
                    ->where('size', $returnRequest->size)
                    ->first();

                if ($inventory) {
                    $inventory->decrement('stock', $returnRequest->quantity);
                }
            } elseif ($returnRequest->order_type === 'retailer') {
                $inventory = \App\Models\Inventory::where('distributor_id', $returnRequest->distributor_id)
                    ->where('product_id', $returnRequest->product_id)
                    ->where('side', $returnRequest->side)
                    ->where('size', $returnRequest->size)
                    ->first();

                if ($inventory) {
                    $inventory->increment('stock', $returnRequest->quantity);
                } else {
                    \App\Models\Inventory::create([
                        'distributor_id' => $returnRequest->distributor_id,
                        'product_id' => $returnRequest->product_id,
                        'product_name' => $returnRequest->product_name,
                        'side' => $returnRequest->side,
                        'size' => $returnRequest->size,
                        'stock' => $returnRequest->quantity,
                        'batch_no' => 'RETURNED',
                        'expiry_date' => now()->addYear(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("API Stock Adjustment Error: " . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/returns/{id}/reject",
     *     summary="Reject a return request",
     *     description="Rejects a return request with a provided reason.",
     *     tags={"Returns"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reason"},
     *             @OA\Property(property="reason", type="string", minLength=5)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Return request rejected")
     * )
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

        return response()->json(['success' => 'Return request rejected.']);
    }

    /**
     * @OA\Get(
     *     path="/api/returns/filters",
     *     summary="Get filters for return requests",
     *     description="Returns available brands, products, and distributors for filtering purposes.",
     *     tags={"Returns"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="brand", in="query", required=false, @OA\Schema(type="string"), description="Filter products by brand"),
     *     @OA\Response(response=200, description="Filter options")
     * )
     */
    public function getFilters(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $brands = Product::whereNotNull('brand')->distinct()->pluck('brand')->sort()->values();
        
        $productQuery = Product::select('id', 'product_name');
        if ($request->filled('brand')) {
            $productQuery->where('brand', $request->brand);
        }
        $products = $productQuery->orderBy('product_name')->get();
        
        $distributors = [];
        if ($user->hasRole('retailer')) {
            $distributorIds = RetailerOrder::where('retailer_id', $user->retailer?->id)
                ->where('status', 'delivered')
                ->whereNotNull('distributor_id')
                ->distinct()
                ->pluck('distributor_id');
                
            $distributors = \App\Models\Distributor::whereIn('id', $distributorIds)
                ->with('user')
                ->get()
                ->map(fn($d) => ['id' => $d->id, 'name' => $d->user?->name ?? 'N/A']);
        }
        
        return response()->json(['brands' => $brands, 'products' => $products, 'distributors' => $distributors]);
    }

    /**
     * @OA\Get(
     *     path="/api/returns/delivered-orders",
     *     summary="Get delivered orders for returns",
     *     description="Returns a list of orders that are eligible for return requests based on the user's role.",
     *     tags={"Returns"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string"), description="Search by Order Code or Product Name"),
     *     @OA\Parameter(name="date", in="query", required=false, @OA\Schema(type="string", format="date"), description="Filter by specific order date (YYYY-MM-DD)"),
     *     @OA\Response(response=200, description="List of delivered orders")
     * )
     */
    public function getDeliveredOrders(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $search = $request->search;
        
        if ($user->hasRole('retailer')) {
            $query = RetailerOrder::where('retailer_id', $user->retailer?->id)
                ->where('status', 'delivered')
                ->with(['distributor.user']);
        } elseif ($user->hasRole('distributor')) {
            $query = DistributorOrder::where('distributor_id', $user->distributor?->id)
                ->where('status', 'delivered');
        } else {
            return response()->json(['data' => []]);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhereHas('items', function($sq) use ($search) {
                      $sq->where('product_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        
        $orders = $query->latest()->paginate(10);
        return response()->json($orders);
    }
}
