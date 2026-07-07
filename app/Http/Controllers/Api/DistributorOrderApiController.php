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
use App\Traits\OneSignalNotifications;
use App\Traits\HandlesNotifications;
use App\Traits\CalculatesPrices;

class DistributorOrderApiController extends Controller
{
    use HandlesNotifications, OneSignalNotifications, CalculatesPrices;
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
     *     @OA\Response(response=200, description="Order details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="order_code", type="string"),
     *             @OA\Property(property="distributor", type="string"),
     *             @OA\Property(property="total_amount", type="string"),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="product_name", type="string"),
     *                 @OA\Property(property="quantity", type="integer"),
     *                 @OA\Property(property="free_quantity", type="integer"),
     *                 @OA\Property(property="side", type="string", nullable=true),
     *                 @OA\Property(property="size", type="string", nullable=true),
     *                 @OA\Property(property="free_side", type="string", nullable=true),
     *                 @OA\Property(property="free_size", type="string", nullable=true),
     *                 @OA\Property(property="is_free", type="boolean"),
     *                 @OA\Property(property="free_item_quantity", type="integer"),
     *                 @OA\Property(property="free_item_threshold", type="integer"),
     *                 @OA\Property(property="batches", type="array", @OA\Items(
     *                     @OA\Property(property="batch_no", type="string"),
     *                     @OA\Property(property="expiry_date", type="string")
     *                 ))
     *             ))
     *         )
     *     ),
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
     * @OA\Get(
     *     path="/api/distributor-orders/calculate-price",
     *     summary="Calculate price for a product before placing a distributor order",
     *     tags={"Distributor Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="product_id", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Parameter(name="quantity", in="query", required=true, @OA\Schema(type="number"), example=10),
     *     @OA\Parameter(name="unit", in="query", required=true, @OA\Schema(type="string", enum={"Nos", "Strips", "Box", "Carton"}), example="Box"),
     *     @OA\Parameter(name="variant", in="query", required=false, @OA\Schema(type="string"), example="M"),
     *     @OA\Response(
     *         response=200,
     *         description="Detailed price calculation (PTS based)",
     *         @OA\JsonContent(
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="product_name", type="string"),
     *             @OA\Property(property="has_variants", type="boolean"),
             @OA\Property(property="available_variants", type="array", @OA\Items(type="string")),
             @OA\Property(property="selected_variant", type="string", nullable=true),
             @OA\Property(property="available_units", type="array", @OA\Items(type="string")),
             @OA\Property(property="input_quantity", type="number"),
     *             @OA\Property(property="input_unit", type="string"),
     *             @OA\Property(property="total_quantity_strips", type="integer"),
     *             @OA\Property(property="unit_price", type="number"),
     *             @OA\Property(property="taxable_amount", type="number"),
     *             @OA\Property(property="gst_rate", type="number"),
     *             @OA\Property(property="gst_amount", type="number"),
     *             @OA\Property(property="total_amount", type="number"),
     *             @OA\Property(property="currency", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function calculatePrice(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string',
            'variant' => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);
        $result = $this->computePriceResponse($product, $request->quantity, $request->unit, 'pts', $request->variant);

        return response()->json($result);
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
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="quantity", type="integer", example=10),
     *                 @OA\Property(property="unit", type="string", enum={"Nos", "Strips", "Box", "Carton"}, example="Box"),
     *                 @OA\Property(property="side", type="string", nullable=true, example="Left"),
     *                 @OA\Property(property="size", type="string", nullable=true, example="XL"),
     *                 @OA\Property(property="free_side", type="string", nullable=true, example="Left"),
     *                 @OA\Property(property="free_size", type="string", nullable=true, example="1 S, 2 XL"),
     *                 @OA\Property(property="is_free", type="boolean", nullable=true, example=false),
     *                 @OA\Property(property="free_item_quantity", type="integer", nullable=true, example=2),
     *                 @OA\Property(property="free_item_threshold", type="integer", nullable=true, example=10)
     *             )),
     *             @OA\Property(property="delivery_notes", type="string", nullable=true, example="Urgent distributor order")
     *         )
     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order placed successfully (Status: pending)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order placed successfully"),
     *             @OA\Property(property="order", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="order_code", type="string"),
     *                 @OA\Property(property="distributor", type="string"),
     *                 @OA\Property(property="total_amount", type="string", description="Total Value (PTS) - GST calculated on invoice"),
     *                 @OA\Property(property="total_items", type="integer"),
     *                 @OA\Property(property="total_quantity", type="integer"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="placed_at", type="string", format="date-time"),
     *                 @OA\Property(property="items", type="array", @OA\Items(
     *                     @OA\Property(property="product_name", type="string"),
     *                     @OA\Property(property="quantity", type="integer"),
     *                     @OA\Property(property="free_quantity", type="integer"),
     *                     @OA\Property(property="side", type="string", nullable=true),
     *                     @OA\Property(property="size", type="string", nullable=true),
     *                     @OA\Property(property="free_side", type="string", nullable=true),
     *                     @OA\Property(property="free_size", type="string", nullable=true),
     *                     @OA\Property(property="price", type="string"),
     *                     @OA\Property(property="subtotal", type="string"),
     *                     @OA\Property(property="is_free", type="boolean"),
     *                     @OA\Property(property="free_item_quantity", type="integer"),
     *                     @OA\Property(property="free_item_threshold", type="integer")
     *                 ))
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.free_quantity' => 'nullable|integer|min:0',
            'items.*.unit' => 'nullable|string',
            'items.*.side' => 'nullable|string',
            'items.*.size' => 'nullable|string',
            'items.*.is_free' => 'nullable|boolean',
            'items.*.free_item_quantity' => 'nullable|integer|min:0',
            'items.*.free_item_threshold' => 'nullable|integer|min:0',
            'delivery_notes' => 'nullable|string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $distributorId = null;
        $salesManagerId = null;

        if ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            $distributorId = $distributor->id;
            $salesManagerId = $distributor->sales_manager_id;
        } else {
            return response()->json(['error' => 'Only distributors can place orders via API'], 403);
        }

        DB::beginTransaction();
        try {
            // Log payload for debugging
            \Log::info('Distributor Order Payload:', $request->all());

            $order = DistributorOrder::create([
                'distributor_id' => $distributorId,
                'sales_manager_id' => $salesManagerId,
                'status' => DistributorOrder::STATUS_PENDING,
                'placed_at' => now(),
                'delivery_notes' => $request->delivery_notes,
                'total_amount' => 0,
                'total_items' => 0,
                'total_quantity' => 0,
            ]);

            $totalAmount = 0;
            $totalItems = 0;
            $totalQuantity = 0;

            // Merge identical items before processing
            $mergedItems = collect($request->items)->groupBy(function($i) {
                $side = isset($i['side']) ? trim(strtolower($i['side'])) : '';
                $size = isset($i['size']) ? trim(strtolower($i['size'])) : '';
                $isFree = isset($i['is_free']) && filter_var($i['is_free'], FILTER_VALIDATE_BOOLEAN) ? 'free' : 'paid';
                return $i['product_id'] . '-' . $side . '-' . $size . '-' . $isFree;
            })->map(function($group) {
                $first = $group->first();
                $first['quantity'] = $group->sum('quantity');
                $first['free_quantity'] = (int)$group->sum('free_quantity');
                
                // Keep normalized values
                $first['side'] = isset($first['side']) ? trim($first['side']) : null;
                $first['size'] = isset($first['size']) ? trim($first['size']) : null;
                $first['free_side'] = isset($first['free_side']) ? trim($first['free_side']) : null;
                $first['free_size'] = isset($first['free_size']) ? trim($first['free_size']) : null;
                $first['is_free'] = isset($first['is_free']) ? filter_var($first['is_free'], FILTER_VALIDATE_BOOLEAN) : false;
                
                return $first;
            });

            foreach ($mergedItems as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $unitPrice = $product->pts;
                $unit = $itemData['unit'] ?? 'Box';
                $qty = (float)$itemData['quantity'];
                $iSide = $itemData['side'] ?? null;
                $iSize = $itemData['size'] ?? null;

                // STRICT VALIDATION: Ensure variants are provided if product has them
                if ($product->has_variants && !empty($product->variant_options)) {
                    $requiredOptions = array_map('strtolower', array_keys((array)$product->variant_options));
                    
                    $isFreeStr = isset($itemData['is_free']) && $itemData['is_free'] ? 'free' : 'paid';
                    
                    if (in_array('side', $requiredOptions) && empty($iSide)) {
                        throw new \Exception("The product '{$product->product_name}' requires a valid side selection for {$isFreeStr} items.");
                    }
                    if (in_array('size', $requiredOptions) && empty($iSize)) {
                        throw new \Exception("The product '{$product->product_name}' requires a valid size selection for {$isFreeStr} items.");
                    }
                }

                // Conversion logic
                $multiplier = 1;
                $normalizedUnit = strtolower($unit);
                if ($normalizedUnit === 'box') {
                    $multiplier = (int)($product->strips_per_box ?? 1);
                } elseif ($normalizedUnit === 'carton') {
                    $multiplier = (int)($product->boxes_per_carton ?? 1) * (int)($product->strips_per_box ?? 1);
                } elseif ($normalizedUnit === 'nos' || $normalizedUnit === 'no') {
                    $multiplier = 1 / (max(1, (int)($product->units_per_strip ?? 1)));
                }

                $totalQtyStrips = $qty * $multiplier;

                $isFree = $itemData['is_free'] ?? false;

                $freeSide = $itemData['free_side'] ?? null;
                $freeSize = $itemData['free_size'] ?? null;

                if ($isFree) {
                    $unitPrice = 0;
                    $subtotalWithGst = 0;
                    $freeQty = $qty;
                    $qty = 0; // Billed quantity is 0
                    $freeSide = $iSide;
                    $freeSize = $iSize;
                } else {
                    $unitPrice = $product->pts;
                    $gstRate = (float)($product->gst ?? 0);
                    $taxableSubtotal = $totalQtyStrips * $unitPrice;
                    $subtotalWithGst = $taxableSubtotal * (1 + ($gstRate / 100));
                    $freeQty = $itemData['free_quantity'] ?? 0;
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'quantity' => $qty,
                    'free_quantity' => $freeQty,
                    'unit' => $unit,
                    'price' => (float)$unitPrice,
                    'subtotal' => $subtotalWithGst,
                    'side' => $itemData['side'] ?? null,
                    'size' => $itemData['size'] ?? null,
                    'free_side' => $freeSide,
                    'free_size' => $freeSize,
                ]);

                $totalAmount += $subtotalWithGst;
                $totalItems++;
                $totalQuantity += $totalQtyStrips;
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

                    // OneSignal Push
                    $this->sendOneSignalPush(
                        [$order->salesManager->user->id],
                        "New Distributor Order #{$order->order_code} is ready for your approval.",
                        ['order_id' => $order->id, 'type' => 'distributor_order'],
                        'Distributor Order Pending'
                    );
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
     *     summary="Update order status (Unified Status Endpoint)",
     *     description="Directly update order status. Handles logic for processing, accepted (admin), delivered (receipt), rejected, and cancelled.",
     *     tags={"Distributor Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"processing", "accepted", "delivered", "cancelled", "rejected"}),
     *             @OA\Property(property="cancellation_reason", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Status updated successfully")
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:processing,accepted,delivered,cancelled,rejected',
            'cancellation_reason' => 'nullable|string|min:5',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $order = DistributorOrder::findOrFail($id);
        $status = $request->status;

        DB::beginTransaction();
        try {
            switch ($status) {
                case DistributorOrder::STATUS_PROCESSING:
                    if (!$user->hasRole('salesmanager') && !$user->hasPermissionToCategory('distributor_approvals', 'edit')) {
                        return response()->json(['error' => 'Only sales managers can process orders'], 403);
                    }
                    if ($order->status !== DistributorOrder::STATUS_PENDING) {
                        return response()->json(['error' => 'Only pending orders can be processed'], 400);
                    }
                    $order->update([
                        'status' => $status,
                        'sales_manager_id' => $user->salesManager->id ?? $order->sales_manager_id
                    ]);
                    $this->clearOrderNotifications($order->id, 'distributor_order');
                    $admins = \App\Models\User::role(['admin', 'superadmin'])->get();
                    $adminIds = $admins->pluck('id')->toArray();
                    foreach ($admins as $admin) {
                        $this->notifyUnique($admin, new \App\Notifications\OrderActionRequired($order, "Distributor Order #{$order->order_code} has been processed and is ready for your approval.", url('/approvals/distributors'), 'distributor_order'));
                    }
                    
                    // OneSignal Push to Admins
                    if (!empty($adminIds)) {
                        $this->sendOneSignalPush(
                            $adminIds,
                            "Distributor Order #{$order->order_code} has been processed and is ready for your approval.",
                            ['order_id' => $order->id, 'type' => 'distributor_order'],
                            'Order Processing Required'
                        );
                    }
                    break;

                case DistributorOrder::STATUS_APPROVED:
                    if (!$user->hasAnyRole(['admin', 'superadmin']) && !$user->hasPermissionToCategory('distributor_approvals', 'edit')) {
                        return response()->json(['error' => 'Only admins can approve orders'], 403);
                    }
                    $order->update([
                        'status' => $status,
                    ]);
                    $this->clearOrderNotifications($order->id, 'distributor_order');
                    if ($order->distributor && $order->distributor->user) {
                        $this->notifyUnique($order->distributor->user, new \App\Notifications\OrderActionRequired($order, "Your order #{$order->order_code} has been accepted. Please confirm receipt upon delivery.", url('/distributor/orders'), 'distributor_order'));
                        
                        // OneSignal Push
                        $this->sendOneSignalPush(
                            [$order->distributor->user->id],
                            "Your order #{$order->order_code} has been accepted. Please confirm receipt upon delivery.",
                            ['order_id' => $order->id, 'type' => 'distributor_order'],
                            'Order Approved'
                        );
                    }
                    break;

                case DistributorOrder::STATUS_DELIVERED:
                    if (!$user->hasRole('distributor') || $order->distributor_id !== $user->distributor->id) {
                        return response()->json(['error' => 'Only the ordering distributor can confirm receipt'], 403);
                    }
                    if ($order->status !== DistributorOrder::STATUS_APPROVED) {
                        return response()->json(['error' => 'Only accepted orders can be confirmed'], 400);
                    }
                    $order->update([
                        'status' => $status,
                        'delivered_at' => now(),
                    ]);
                    $this->addOrderItemsToInventory($order);
                    $this->clearOrderNotifications($order->id, 'distributor_order');
                    break;

                case DistributorOrder::STATUS_CANCELLED:
                    if ($order->status !== DistributorOrder::STATUS_PENDING) {
                        return response()->json(['error' => 'Orders can only be cancelled while in pending status.'], 400);
                    }
                    if (!$user->hasRole('distributor') || $order->distributor_id !== $user->distributor->id) {
                        return response()->json(['error' => 'Unauthorized deletion'], 403);
                    }
                    $order->update([
                        'status' => $status,
                        'cancellation_reason' => $request->cancellation_reason ?? 'Cancelled via API'
                    ]);
                    $this->deleteOrderNotifications($order->id, 'distributor_order');
                    break;

                case DistributorOrder::STATUS_REJECTED:
                    if (!$user->hasAnyRole(['admin', 'superadmin', 'salesmanager']) && !$user->hasPermissionToCategory('distributor_approvals', 'edit')) {
                        return response()->json(['error' => 'Unauthorized rejection'], 403);
                    }
                    if (!in_array($order->status, [DistributorOrder::STATUS_PENDING, DistributorOrder::STATUS_PROCESSING])) {
                        return response()->json(['error' => 'Only pending or processing orders can be rejected.'], 400);
                    }
                    $order->update([
                        'status' => $status,
                        'cancellation_reason' => $request->cancellation_reason ?? 'Rejected via API'
                    ]);
                    $this->deleteOrderNotifications($order->id, 'distributor_order');
                    if ($order->distributor && $order->distributor->user) {
                        $this->notifyUnique($order->distributor->user, new \App\Notifications\OrderActionRequired($order, "Your order #{$order->order_code} has been rejected.", url('/distributor/orders'), 'distributor_order'));
                        
                        // OneSignal Push
                        $this->sendOneSignalPush(
                            [$order->distributor->user->id],
                            "Your order #{$order->order_code} has been rejected.",
                            ['order_id' => $order->id, 'type' => 'distributor_order'],
                            'Order Rejected'
                        );
                    }
                    break;
            }

            DB::commit();
            return response()->json([
                'message' => 'Order status updated successfully',
                'order' => $this->formatOrder($order->refresh()->load('items.product'))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/distributor-orders/{id}/approve",
     *     summary="Approve a distributor order",
     *     description="Sales Managers: Changes status to 'processing'. Admins: Changes status to 'accepted' (requires invoice and batches).",
     *     tags={"Distributor Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="payment_status", type="string", enum={"pending", "paid", "failed"}),
     *             @OA\Property(property="cancellation_reason", type="string"),
     *             @OA\Property(property="batches", type="object", description="Required for Admin 'accepted' status")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order approved successfully")
     * )
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->hasRole('salesmanager')) {
            $request->merge(['status' => DistributorOrder::STATUS_PROCESSING]);
        } elseif ($user->hasAnyRole(['admin', 'superadmin'])) {
            $request->merge(['status' => DistributorOrder::STATUS_APPROVED]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $this->updateStatus($request, $id);
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
            'cancellation_reason' => $order->cancellation_reason,
            'placed_at' => $order->placed_at ? $order->placed_at->format('Y-m-d H:i:s') : null,
            'invoice_url' => $order->invoice_path ? asset('storage/' . $order->invoice_path) : null,
            'items' => $order->items->groupBy(function($item) {
                $side = $item->side ? trim(strtolower($item->side)) : '';
                $size = $item->size ? trim(strtolower($item->size)) : '';
                $isFree = $item->price == 0 ? 'free' : 'paid';
                return $item->product_id . '-' . $side . '-' . $size . '-' . $isFree;
            })->map(function ($group) {
                $item = $group->first();
                $isFreeItem = $item->price == 0;
                $baseName = $item->product->product_name ?? $item->product_name ?? 'N/A';
                
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $isFreeItem ? $baseName . ' (Free)' : $baseName,
                    'quantity' => $isFreeItem ? $group->sum('free_quantity') : $group->sum('quantity'),
                    'free_quantity' => $group->sum('free_quantity'),
                    'unit' => $item->unit,
                    'side' => $item->side,
                    'size' => $item->size,
                    'free_side' => $group->pluck('free_side')->filter()->first() ? preg_replace('/(\d+)x/', '$1 ', $group->pluck('free_side')->filter()->first()) : null,
                    'free_size' => $group->pluck('free_size')->filter()->first() ? preg_replace('/(\d+)x/', '$1 ', $group->pluck('free_size')->filter()->first()) : null,
                    'price' => (float)$item->price,
                    'unit_price' => (float)$item->price,
                    'subtotal' => (float)$group->sum('subtotal'),
                    'total_amount' => (float)$group->sum('subtotal'),
                    'is_free' => $isFreeItem,
                    'free_item_quantity' => $item->product ? (int)$item->product->free_qty_get : 0,
                    'free_item_threshold' => $item->product ? (int)$item->product->free_qty_buy : 0,
                    'batches' => $group->flatMap->batches->map(function ($b) {
                        return [
                            'batch_no' => $b->batch_no,
                            'expiry_date' => $b->expiry_date,
                            'quantity' => $b->quantity,
                            'mrp' => $b->mrp,
                            'ptr' => $b->ptr,
                            'pts' => $b->pts,
                            'net_amount' => $b->net_amount
                        ];
                    })
                ];
            })->values()
        ];
    }

    private function addOrderItemsToInventory(DistributorOrder $order)
    {
        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product) continue;

            $qty = $item->quantity;
            $unit = strtolower($item->unit);
            $multiplier = 1;

            if ($unit === 'box') {
                $multiplier = (int)($product->strips_per_box ?? 1);
            } elseif ($unit === 'carton') {
                $multiplier = (int)($product->boxes_per_carton ?? 1) * (int)($product->strips_per_box ?? 1);
            } elseif ($unit === 'nos' || $unit === 'no' || $unit === 'unit') {
                $multiplier = 1 / (max(1, (int)($product->units_per_strip ?? 1)));
            }

            $multiplier = (float)$multiplier;
            $totalStrips = $qty * $multiplier;

            $inventory = Inventory::firstOrNew([
                'distributor_id' => $order->distributor_id,
                'product_id' => $product->id,
                'side' => $item->side,
                'size' => $item->size,
            ]);

            if (!$inventory->exists) {
                $inventory->distributor_product_code = $product->product_code ?? '---';
                $inventory->product_name = $product->product_name;
                $inventory->stock = 0;
            }
            $inventory->product_name = $product->product_name;
            $inventory->stock += $totalStrips;
            $inventory->save();
        }
    }
}
