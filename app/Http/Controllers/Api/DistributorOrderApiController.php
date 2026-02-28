<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributorOrder;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DistributorOrderApiController extends Controller
{
    use \App\Traits\HandlesNotifications;
    /**
     * @OA\Get(
     *     path="/api/distributor-orders",
     *     summary="List distributor orders (Unified Endpoint)",
     *     description="Returns orders for the logged-in user. If a Sales Manager, can optionally filter by distributor_id.",
     *     tags={"Distributor Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="distributor_id", in="query", required=false, @OA\Schema(type="integer"), description="Filter by specific distributor (Manager/Admin only)"),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="payment_status", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="from_date", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="to_date", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="List of orders")
     * )
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = DistributorOrder::with(['distributor.user', 'items.product', 'salesManager.user']);

        if ($user->hasRole('distributor')) {
            // Distributors always see only their own orders
            $query->where('distributor_id', $user->distributor->id);
        } elseif ($user->hasRole('salesmanager')) {
            // Managers see all their distributors' orders by default
            // If they provide a specific distributor_id, we filter to that one (if it belongs to them)
            if ($request->filled('distributor_id')) {
                $query->where('distributor_id', $request->distributor_id)
                    ->whereHas('distributor', function ($q) use ($user) {
                        $q->where('sales_manager_id', $user->salesManager->id);
                    });
            } else {
                $query->whereHas('distributor', function ($q) use ($user) {
                    $q->where('sales_manager_id', $user->salesManager->id);
                });
            }
        } elseif ($user->hasAnyRole(['admin', 'superadmin'])) {
            // Admins see everything, or filter if requested
            if ($request->filled('distributor_id')) {
                $query->where('distributor_id', $request->distributor_id);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // History Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhereHas('distributor.user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('placed_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('placed_at', '<=', $request->to_date);
        }

        $orders = $query->latest()->paginate($request->input('per_page', 15));

        $orders->getCollection()->transform(function ($order) {
            return $this->formatOrder($order);
        });

        return response()->json($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/distributor-orders/{id}",
     *     summary="Get details of a single distributor order",
     *     tags={"Distributor Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Order details"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $order = DistributorOrder::with(['distributor.user', 'items.product', 'salesManager.user'])->findOrFail($id);

        if ($user->hasRole('distributor') && $order->distributor_id !== $user->distributor->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->hasRole('salesmanager') && $order->distributor->sales_manager_id !== $user->salesManager->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($this->formatOrder($order));
    }

    /**
     * @OA\Post(
     *     path="/api/distributor-orders",
     *     summary="Place a new distributor order",
     *     tags={"Distributor Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="product_id", type="integer"),
     *                 @OA\Property(property="quantity", type="integer"),
     *                 @OA\Property(property="unit", type="string", example="Box")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Order placed successfully")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string|in:Box,Carton,Strip',
            'distributor_id' => 'sometimes|exists:distributors,id'
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $distributorId = null;
        $salesManagerId = null;

        if ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            $distributorId = $distributor->id;
            $salesManagerId = $distributor->sales_manager_id;
        } elseif ($user->hasAnyRole(['admin', 'superadmin'])) {
            if (!$request->has('distributor_id')) {
                return response()->json(['error' => 'distributor_id is required for admin'], 422);
            }
            $distributor = Distributor::findOrFail($request->distributor_id);
            $distributorId = $distributor->id;
            $salesManagerId = $distributor->sales_manager_id;
        } else {
            return response()->json(['error' => 'Only distributors or admins can place orders'], 403);
        }

        DB::beginTransaction();
        try {
            $order = DistributorOrder::create([
                'distributor_id' => $distributorId,
                'sales_manager_id' => $salesManagerId,
                'status' => DistributorOrder::STATUS_PENDING,
                'placed_at' => now(),
                'total_amount' => 0,
                'total_items' => 0,
                'total_quantity' => 0,
            ]);

            $totalAmount = 0;
            $totalItems = 0;
            $totalQuantity = 0;

            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $unitPrice = $product->pts;
                $subtotal = $itemData['quantity'] * $unitPrice;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'] ?? 'Box',
                    'price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
                $totalItems++;
                $totalQuantity += $itemData['quantity'];
            }

            $order->update([
                'total_amount' => $totalAmount,
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity,
            ]);

            // Notifications logic...
            if ($order->salesManager && $order->salesManager->user) {
                try {
                    $this->notifyUnique($order->salesManager->user, new \App\Notifications\OrderActionRequired(
                        $order,
                        "New Distributor Order #{$order->order_code} is ready for your approval.",
                        url('/approvals/distributors'),
                        'distributor_order'
                    ));
                } catch (\Exception $e) {
                    Log::error('Notification failed: ' . $e->getMessage());
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $this->formatOrder($order->load('items.product'))
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Order creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Order placement failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/distributor-orders/{id}/update-status",
     *     summary="Update order status (Unified Action Endpoint)",
     *     description="Handles Manager Acceptance, Admin Approval, Receipt Confirmation, and Cancellations.",
     *     tags={"Distributor Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="action", type="string", enum={"accept_manager", "approve_admin", "confirm_receipt", "request_cancellation", "approve_cancellation"}),
     *                 @OA\Property(property="payment_status", type="string", enum={"pending","paid","failed"}, description="Required for approve_admin"),
     *                 @OA\Property(property="invoice", type="string", format="binary", description="Optional for approve_admin"),
     *                 @OA\Property(property="cancellation_reason", type="string", description="Required for request_cancellation")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Status updated successfully")
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:accept_manager,approve_admin,confirm_receipt,request_cancellation,approve_cancellation',
            'payment_status' => 'required_if:action,approve_admin|in:pending,paid,failed',
            'invoice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'cancellation_reason' => 'required_if:action,request_cancellation|string|min:5'
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $order = DistributorOrder::findOrFail($id);
        $action = $request->action;

        DB::beginTransaction();
        try {
            switch ($action) {
                case 'accept_manager':
                    if (!$user->hasRole('salesmanager') || $order->sales_manager_id !== $user->salesManager->id) {
                        return response()->json(['error' => 'Unauthorized'], 403);
                    }
                    if ($order->status !== DistributorOrder::STATUS_PENDING) {
                        return response()->json(['error' => 'Only pending orders can be accepted'], 400);
                    }
                    $order->update(['status' => DistributorOrder::STATUS_ACCEPTED_BY_SALES_MANAGER]);
                    break;

                case 'approve_admin':
                    if (!$user->hasAnyRole(['admin', 'superadmin'])) {
                        return response()->json(['error' => 'Only admins can approve orders'], 403);
                    }
                    $invoicePath = $order->invoice_path;
                    if ($request->hasFile('invoice')) {
                        if ($invoicePath) Storage::disk('public')->delete($invoicePath);
                        $invoicePath = $request->file('invoice')->store('invoices/distributors', 'public');
                    }
                    $order->update([
                        'status' => DistributorOrder::STATUS_APPROVED,
                        'payment_status' => $request->payment_status,
                        'invoice_path' => $invoicePath
                    ]);
                    break;

                case 'confirm_receipt':
                    if (!$user->hasRole('distributor') || $order->distributor_id !== $user->distributor->id) {
                        return response()->json(['error' => 'Unauthorized'], 403);
                    }
                    if ($order->status !== DistributorOrder::STATUS_APPROVED) {
                        return response()->json(['error' => 'Only approved orders can be confirmed'], 400);
                    }
                    $order->update(['status' => DistributorOrder::STATUS_DELIVERED]);
                    $this->addOrderItemsToInventory($order);
                    break;

                case 'request_cancellation':
                    if (!$user->hasRole('distributor') || $order->distributor_id !== $user->distributor->id) {
                        return response()->json(['error' => 'Unauthorized'], 403);
                    }
                    if ($order->status !== DistributorOrder::STATUS_ACCEPTED_BY_SALES_MANAGER) {
                        return response()->json(['error' => 'Invalid status for cancellation request'], 400);
                    }
                    $order->update([
                        'status' => DistributorOrder::STATUS_CANCELLATION_REQUESTED,
                        'cancellation_reason' => $request->cancellation_reason
                    ]);
                    break;

                case 'approve_cancellation':
                    if (!$user->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
                        return response()->json(['error' => 'Unauthorized'], 403);
                    }
                    if ($order->status !== DistributorOrder::STATUS_CANCELLATION_REQUESTED) {
                        return response()->json(['error' => 'No cancellation request found'], 400);
                    }
                    $order->update(['status' => DistributorOrder::STATUS_CANCELLED]);
                    break;
            }

            DB::commit();
            return response()->json(['message' => 'Action performed successfully', 'order' => $this->formatOrder($order)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Action failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper to format order response.
     */
    private function formatOrder($order)
    {
        return [
            'id' => $order->id,
            'order_code' => $order->order_code,
            'distributor' => $order->distributor->user->name ?? 'N/A',
            'sales_manager' => $order->salesManager->user->name ?? 'N/A',
            'total_amount' => $order->total_amount,
            'total_items' => $order->total_items,
            'total_quantity' => $order->total_quantity,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'placed_at' => $order->placed_at,
            'invoice_url' => $order->invoice_path ? asset('storage/' . $order->invoice_path) : null,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->product_name ?? 'N/A',
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal
                ];
            })
        ];
    }

    private function addOrderItemsToInventory(DistributorOrder $order)
    {
        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product) continue;

            $qty = $item->quantity;
            $unit = strtolower($item->unit);
            $totalStrips = $qty;

            if ($unit === 'box') {
                $totalStrips = $qty * ($product->box_size ?? 1);
            } elseif ($unit === 'carton') {
                $boxSize = $product->box_size ?? 1;
                $cartonSize = $product->carton_size ?? 1;
                $totalStrips = $qty * $boxSize * $cartonSize;
            }

            $inventory = Inventory::firstOrNew([
                'distributor_id' => $order->distributor_id,
                'product_id' => $product->id,
            ]);

            if (!$inventory->exists) {
                $inventory->distributor_product_code = $product->product_code;
                $inventory->product_name = $product->product_name;
                $inventory->stock = 0;
            }
            $inventory->product_name = $product->product_name;
            $inventory->stock += $totalStrips;
            $inventory->save();
        }
    }
}
