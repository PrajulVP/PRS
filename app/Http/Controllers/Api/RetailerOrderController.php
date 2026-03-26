<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RetailerOrder;
use Illuminate\Http\Request;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\HandlesNotifications;
use App\Traits\OneSignalNotifications;
use App\Traits\CalculatesPrices;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="RetailerOrder",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_code", type="string", example="RO-X9Y8Z7"),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="payment_status", type="string", example="pending"),
 *     @OA\Property(property="total_amount", type="string", example="1500.00"),
 *     @OA\Property(property="total_items", type="integer", example=3),
 *     @OA\Property(property="total_quantity", type="integer", example=12),
 *     @OA\Property(property="notes", type="string", example="Urgent delivery"),
 *     @OA\Property(property="loyalty_points_earned", type="integer", example=25),
 *     @OA\Property(property="placed_at", type="string", format="date-time", example="2023-10-25 10:00:00"),
 *     @OA\Property(property="delivered_at", type="string", format="date-time", nullable=true, example="2023-10-26 14:00:00")
 * )
 */
class RetailerOrderController extends Controller
{
    use HandlesNotifications, OneSignalNotifications, CalculatesPrices;

    /**
     * @OA\Get(
     *     path="/api/retailer-orders",
     *     summary="Get all orders for the authenticated retailer",
     *     tags={"Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of retailer orders",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/RetailerOrder")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - User is not a retailer"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();

        if (!$user->retailer) {
            return response()->json(['message' => 'User is not a retailer'], 403);
        }

        $orders = RetailerOrder::with(['items.product', 'distributor.user'])
            ->where('retailer_id', $user->retailer->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id'             => $order->id,
                    'order_code'     => $order->order_code,
                    'distributor_id' => $order->distributor_id,
                    'distributor_name' => $order->distributor?->user?->name ?? 'N/A',
                    'status'         => $order->status,
                    'payment_status' => $order->payment_status,
                    'total_amount'   => number_format($order->total_amount, 2),
                    'total_items'    => $order->total_items,
                    'total_quantity' => $order->total_quantity,
                    'notes'          => $order->notes,
                    'items'          => $order->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product_name ?? $item->product->product_name ?? 'N/A',
                            'quantity'   => $item->quantity,
                            'free_quantity' => $item->free_quantity,
                            'unit'       => $item->unit ?? 'Nos',
                        ];
                    }),
                    'invoice_url'    => $order->invoice_path ? asset('storage/' . $order->invoice_path) : null,
                    'placed_at'      => $order->placed_at?->format('Y-m-d H:i:s'),
                    'delivered_at'   => $order->delivered_at?->format('Y-m-d H:i:s'),
                    'loyalty_points_earned' => $order->status === RetailerOrder::STATUS_DELIVERED ? (int)$order->loyalty_points_earned : 0,
                ];
            });

        return response()->json($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/retailer-orders/calculate-price",
     *     summary="Calculate price for a product before placing an order",
     *     tags={"Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="product_id", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Parameter(name="quantity", in="query", required=true, @OA\Schema(type="number"), example=10),
     *     @OA\Parameter(name="unit", in="query", required=true, @OA\Schema(type="string", enum={"Nos", "Strips", "Box", "Carton"}), example="Box"),
     *     @OA\Parameter(name="variant", in="query", required=false, @OA\Schema(type="string"), example="M"),
     *     @OA\Response(
     *         response=200,
     *         description="Detailed price calculation",
     *         @OA\JsonContent(
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="product_name", type="string"),
     *             @OA\Property(property="has_variants", type="boolean"),
     *             @OA\Property(property="available_variants", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="selected_variant", type="string", nullable=true),
     *             @OA\Property(property="input_quantity", type="number"),
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
        $result = $this->computePriceResponse($product, $request->quantity, $request->unit, 'ptr', $request->variant);

        return response()->json($result);
    }



    /**
     * @OA\Post(
     *     path="/api/retailer-orders",
     *     summary="Place new retailer order(s). Items will be grouped by distributor_id into separate orders.",
     *     tags={"Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="quantity", type="integer", example=10),
     *                 @OA\Property(property="unit", type="string", enum={"Nos", "Strips", "Box", "Carton"}, example="Box"),
     *                 @OA\Property(property="variant", type="string", nullable=true, example="M"),
     *                 @OA\Property(property="distributor_id", type="integer", nullable=true, example=2)
     *             )),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Urgent order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201, 
     *         description="Orders placed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="orders", type="array", @OA\Items(ref="#/components/schemas/RetailerOrder"))
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=500, description="Error creating orders (e.g. insufficient stock)")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string',
            'items.*.variant' => 'nullable|string',
            'items.*.distributor_id' => 'nullable|exists:distributors,id',
            'notes' => 'nullable|string',
        ]);

        $user = auth('api')->user();
        if (!$user->retailer) {
            return response()->json(['message' => 'User is not a retailer'], 403);
        }

        $retailer = $user->retailer;
        $createdOrders = [];

        DB::beginTransaction();
        try {
            // Group items by distributor_id
            $itemsByDistributor = collect($request->items)->groupBy('distributor_id');

            foreach ($itemsByDistributor as $distributorId => $items) {
                $distributor = $distributorId ? \App\Models\Distributor::find($distributorId) : null;

                $order = RetailerOrder::create([
                    'retailer_id' => $retailer->id,
                    'distributor_id' => $distributor ? $distributor->id : null,
                    'fieldstaff_id' => $retailer->field_staff_id,
                    'order_code' => 'ORD-' . strtoupper(uniqid()),
                    'status' => RetailerOrder::STATUS_PENDING,
                    'notes' => $request->notes,
                    'total_amount' => 0,
                    'total_items' => 0,
                    'total_quantity' => 0,
                    'placed_at' => now(),
                ]);

                $totalAmount = 0;
                $totalItemsCount = 0;
                $totalQuantityNos = 0;

                foreach ($items as $itemData) {
                    $product = Product::findOrFail($itemData['product_id']);
                    $unit = $itemData['unit'] ?? 'Nos';
                    $qty = (int)$itemData['quantity'];

                    // Conversion logic using numeric fields
                    $multiplier = 1;
                    $normalizedUnit = strtolower($unit);
                    if ($normalizedUnit === 'box') {
                        $multiplier = (int)($product->strips_per_box ?? 1);
                    } elseif ($normalizedUnit === 'carton') {
                        $multiplier = (int)($product->boxes_per_carton ?? 1) * (int)($product->strips_per_box ?? 1);
                    } elseif ($normalizedUnit === 'nos' || $normalizedUnit === 'no' || $normalizedUnit === 'unit') {
                        $multiplier = 1 / (max(1, (int)($product->units_per_strip ?? 1)));
                    }

                    $totalQtyNos = $qty * $multiplier;
                    // If multiplier resulted in float, we handle it as needed (ceil for strips if we only sell full strips)
                    // But in pharma, usually they sell by strips. If they select Nos, it might be 10 Nos = 1 Strip.
                    // For now, let's keep it as is, or use ceil if we want to round up to nearest strip.
                    $totalQtyNos = ceil($totalQtyNos); 

                    // Stock Check
                    if ($distributor) {
                        $totalStock = DB::table('inventories')
                            ->where('distributor_id', $distributor->id)
                            ->where('product_id', $product->id)
                            ->when(!empty($itemData['variant']), function ($q) use ($itemData) {
                                return $q->where('variant', $itemData['variant']);
                            })
                            ->sum('stock');

                        if ($totalStock < $totalQtyNos) {
                            $variantMsg = !empty($itemData['variant']) ? " (variant: {$itemData['variant']})" : "";
                            throw new \Exception("Insufficient stock for product '{$product->product_name}'{$variantMsg} at selected distributor.");
                        }
                    }

                    $price = (float)$product->ptr; // Retailers buy at PTR
                    $gstRate = (float)($product->gst ?? 0);
                    $taxableSubtotal = $totalQtyNos * $price;
                    $subtotalWithGst = $taxableSubtotal * (1 + ($gstRate / 100));

                    // Append variant to product name if provided
                    $finalProductName = $product->product_name;
                    if (!empty($itemData['variant'])) {
                        $finalProductName .= ' [' . $itemData['variant'] . ']';
                    }

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $finalProductName,
                        'quantity' => $qty,
                        'free_quantity' => $itemData['free_quantity'] ?? 0,
                        'unit' => $unit,
                        'unit_price' => $price,
                        'total_amount' => $subtotalWithGst,
                        'variant' => $itemData['variant'] ?? null,
                    ]);

                    $totalAmount += $subtotalWithGst;
                    $totalItemsCount++;
                    $totalQuantityNos += $totalQtyNos;
                }

                $order->update([
                    'total_amount' => $totalAmount,
                    'total_items' => $totalItemsCount,
                    'total_quantity' => $totalQuantityNos
                ]);

                // Notify Field Staff
                if ($order->fieldStaff && $order->fieldStaff->user) {
                    $this->notifyUnique(
                        $order->fieldStaff->user,
                        new \App\Notifications\OrderActionRequired(
                            $order,
                            "New order #{$order->order_code} from {$retailer->shop_name} assigned to you.",
                            url('/approvals/retailers'),
                            'retailer_order'
                        )
                    );

                    // OneSignal Push
                    $this->sendOneSignalPush(
                        [$order->fieldStaff->user->id],
                        "New order #{$order->order_code} from {$retailer->shop_name} assigned to you.",
                        ['order_id' => $order->id, 'type' => 'retailer_order'],
                        'New Retailer Order'
                    );
                }

                $createdOrders[] = $order->load('items.product');
            }

            DB::commit();
            return response()->json([
                'message' => 'Orders placed successfully.',
                'orders' => $createdOrders
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Retailer Order creation failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/retailer-orders/{id}/update-status",
     *     summary="Update order status (e.g. confirm delivery or cancel)",
     *     tags={"Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"delivered", "cancelled"}),
     *             @OA\Property(property="cancellation_reason", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example="Order delivered."),
     *             @OA\Property(property="order", ref="#/components/schemas/RetailerOrder"),
     *             @OA\Property(property="new_points", type="integer", nullable=true, example=25)
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid status or order state"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:delivered,cancelled',
            'cancellation_reason' => 'required_if:status,cancelled|string|min:3'
        ]);

        $user = auth('api')->user();
        if (!$user->retailer) {
            return response()->json(['message' => 'User is not a retailer'], 403);
        }

        $order = RetailerOrder::where('id', $id)
            ->where('retailer_id', $user->retailer->id)
            ->firstOrFail();

        $status = $request->status;

        DB::beginTransaction();
        try {
            if ($status === RetailerOrder::STATUS_DELIVERED) {
                if ($order->status !== 'approved') {
                    return response()->json(['error' => 'Only accepted orders can be marked as delivered.'], 400);
                }
                $order->update([
                    'status' => $status,
                    'delivered_at' => now()
                ]);

                // Clear notifications
                if (method_exists($this, 'deleteOrderNotifications')) {
                    $this->deleteOrderNotifications($order->id, 'retailer_order');
                }

                // Notify Field Staff / Distributor that order is closed (Optional)
                if ($order->fieldStaff && $order->fieldStaff->user) {
                    $this->notifyUnique($order->fieldStaff->user, new \App\Notifications\OrderActionRequired($order, "Order #{$order->order_code} has been successfully delivered and confirmed by the retailer.", url('/field-staff/orders'), 'retailer_order'));
                }

                $msg = 'Order delivered.';
                $newPoints = $order->retailer->loyalty_points ?? 0;
            } elseif ($status === RetailerOrder::STATUS_CANCELLED) {
                if ($order->status !== 'pending') {
                    return response()->json(['error' => 'Orders can only be cancelled while in pending status.'], 400);
                }
                $order->update([
                    'status' => $status,
                    'cancellation_reason' => $request->cancellation_reason
                ]);

                if (method_exists($this, 'deleteOrderNotifications')) {
                    $this->deleteOrderNotifications($order->id, 'retailer_order');
                }
                $msg = 'Order cancelled.';
                $newPoints = null;
            }

            DB::commit();

            return response()->json([
                'success' => $msg,
                'order' => $order->refresh(),
                'new_points' => $newPoints
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Retailer Order update status failed: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to update status.'], 500);
        }
    }
}
