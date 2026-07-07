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
 *     @OA\Property(property="total_amount", type="number", format="float", example=1500.00),
 *     @OA\Property(property="total_items", type="integer", example=3),
 *     @OA\Property(property="total_quantity", type="integer", example=12),
 *     @OA\Property(property="items", type="array", @OA\Items(
 *         @OA\Property(property="product_id", type="integer"),
 *         @OA\Property(property="product_name", type="string"),
 *         @OA\Property(property="quantity", type="integer"),
 *         @OA\Property(property="unit_price", type="number", format="float"),
 *         @OA\Property(property="subtotal", type="number", format="float"),
 *         @OA\Property(property="free_side", type="string", nullable=true),
 *         @OA\Property(property="free_size", type="string", nullable=true),
 *         @OA\Property(property="is_free", type="boolean"),
 *         @OA\Property(property="free_item_quantity", type="integer"),
 *         @OA\Property(property="free_item_threshold", type="integer")
 *     )),
 *     @OA\Property(property="delivery_notes", type="string", example="Urgent delivery"),
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

        $orders = RetailerOrder::with(['items.product', 'distributor.user', 'distributor.area', 'distributor.district', 'retailer.user', 'retailer.fieldStaff.user', 'retailer.fieldStaff.salesManager.user'])
            ->where('retailer_id', $user->retailer->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $distributor = $order->distributor;
                return [
                    'id'             => $order->id,
                    'order_code'     => $order->order_code,
                    'distributor_id' => $order->distributor_id,
                    'retailer_name'  => $order->retailer->user->name ?? 'N/A',
                    'retailer_shop'  => $order->retailer->shop_name ?? 'N/A',
                    'field_staff_name' => $order->retailer->fieldStaff->user->name ?? 'N/A',
                    'sales_manager'  => $order->retailer->fieldStaff->salesManager->user->name ?? 'N/A',
                    'distributor_name' => $distributor->user->name ?? 'N/A',
                    'distributor_contact' => $distributor->contact_no ?? $distributor->user->contact_no ?? null,
                    'distributor_address' => $distributor->user->address ?? null,
                    'status'         => $order->status,
                    'payment_status' => $order->payment_status,
                    'cancellation_reason' => $order->cancellation_reason,
                    'total_amount'   => (float)$order->total_amount,
                    'total_items'    => $order->total_items,
                    'total_quantity' => $order->total_quantity,
                    'delivery_notes' => $order->delivery_notes,
                    'items'          => $order->items->groupBy(function($item) {
                        $side = $item->side ? trim(strtolower($item->side)) : '';
                        $size = $item->size ? trim(strtolower($item->size)) : '';
                        $isFree = $item->unit_price == 0 ? 'free' : 'paid';
                        return $item->product_id . '-' . $side . '-' . $size . '-' . $isFree;
                    })->map(function ($group) {
                        $item = $group->first();
                        $isFreeItem = $item->unit_price == 0;
                        $baseName = $item->product ? $item->product->product_name : $item->product_name;
                        
                        return [
                            'id'         => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $isFreeItem ? $baseName . ' (Free)' : $baseName,
                            'quantity'   => $isFreeItem ? $group->sum('free_quantity') : $group->sum('quantity'),
                            'free_quantity' => $group->sum('free_quantity'),
                            'unit'       => $item->unit ?? 'Nos',
                            'unit_price' => (float)$item->unit_price,
                            'subtotal'   => (float)$group->sum('total_amount'),
                            'total_amount' => (float)$group->sum('total_amount'),
                            'side'       => $item->side,
                            'size'       => $item->size,
                            'free_side'  => $group->pluck('free_side')->filter()->first() ? preg_replace('/(\d+)x/', '$1 ', $group->pluck('free_side')->filter()->first()) : null,
                            'free_size'  => $group->pluck('free_size')->filter()->first() ? preg_replace('/(\d+)x/', '$1 ', $group->pluck('free_size')->filter()->first()) : null,
                            'is_free'    => $isFreeItem,
                            'price'      => (float)$item->unit_price,
                            'free_item_quantity' => $item->product ? (int)$item->product->free_qty_get : 0,
                            'free_item_threshold' => $item->product ? (int)$item->product->free_qty_buy : 0,
                        ];
                    })->values(),
                    'invoice_url'    => $order->invoice_path ? asset('storage/' . $order->invoice_path) : null,
                    'placed_at'      => $order->placed_at?->format('Y-m-d H:i:s'),
                    'delivered_at'   => $order->delivered_at?->format('Y-m-d H:i:s'),
                    'loyalty_points_earned' => in_array($order->status, [RetailerOrder::STATUS_APPROVED, RetailerOrder::STATUS_DELIVERED]) ? (int)$order->loyalty_points_earned : 0,
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
     *     @OA\Parameter(name="side", in="query", required=false, @OA\Schema(type="string"), example="Left"),
     *     @OA\Parameter(name="size", in="query", required=false, @OA\Schema(type="string"), example="XL"),
     *     @OA\Response(
     *         response=200,
     *         description="Detailed price calculation",
     *         @OA\JsonContent(
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="product_name", type="string"),
     *             @OA\Property(property="has_variants", type="boolean"),
     *             @OA\Property(property="available_variants", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="selected_side", type="string", nullable=true),
             @OA\Property(property="selected_size", type="string", nullable=true),
     *             @OA\Property(property="available_units", type="array", @OA\Items(type="string")),
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
        ]);

        $product = Product::findOrFail($request->product_id);
        $result = $this->computePriceResponse($product, $request->quantity, $request->unit, 'ptr', $request->side, $request->size);

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
     *                 @OA\Property(property="side", type="string", nullable=true, example="Left"),
     *                 @OA\Property(property="size", type="string", nullable=true, example="XL"),
     *                 @OA\Property(property="free_side", type="string", nullable=true, example="Left"),
     *                 @OA\Property(property="free_size", type="string", nullable=true, example="1 S, 2 XL"),
     *                 @OA\Property(property="is_free", type="boolean", nullable=true, example=false),
     *                 @OA\Property(property="free_item_quantity", type="integer", nullable=true, example=2),
     *                 @OA\Property(property="free_item_threshold", type="integer", nullable=true, example=10),
     *                 @OA\Property(property="distributor_id", type="integer", nullable=true, example=2)
     *             )),
     *             @OA\Property(property="delivery_notes", type="string", nullable=true, example="Urgent order")
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
            'items.*.side' => 'nullable|string',
            'items.*.size' => 'nullable|string',
            'items.*.distributor_id' => 'nullable|exists:distributors,id',
            'items.*.is_free' => 'nullable|boolean',
            'items.*.free_item_quantity' => 'nullable|integer|min:0',
            'items.*.free_item_threshold' => 'nullable|integer|min:0',
            'delivery_notes' => 'nullable|string',
        ]);

        $user = auth('api')->user();
        if (!$user->retailer) {
            return response()->json(['message' => 'User is not a retailer'], 403);
        }

        $retailer = $user->retailer;
        $createdOrders = [];

        DB::beginTransaction();
        try {
            // Log payload for debugging
            \Log::info('Retailer Order Payload:', $request->all());

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
                    'delivery_notes' => $request->delivery_notes,
                    'total_amount' => 0,
                    'total_items' => 0,
                    'total_quantity' => 0,
                    'placed_at' => now(),
                ]);

                $totalAmount = 0;
                $totalItemsCount = 0;
                $totalQuantityNos = 0;

                // Intelligent Merging Logic: Attach free items to paid items of the same product
                $rawItems = collect($items);
                $paidItems = $rawItems->filter(fn($i) => empty($i['is_free']) || !filter_var($i['is_free'], FILTER_VALIDATE_BOOLEAN));
                $freeItems = $rawItems->filter(fn($i) => !empty($i['is_free']) && filter_var($i['is_free'], FILTER_VALIDATE_BOOLEAN));

                // Group paid items by exact variant
                $mergedPaidItems = $paidItems->groupBy(function($i) {
                    $side = isset($i['side']) ? trim(strtolower($i['side'])) : '';
                    $size = isset($i['size']) ? trim(strtolower($i['size'])) : '';
                    return $i['product_id'] . '-' . $side . '-' . $size;
                })->map(function($group) {
                    $first = $group->first();
                    $first['quantity'] = $group->sum('quantity');
                    $first['free_quantity'] = $group->sum('free_quantity');
                    $first['is_free'] = false;
                    $first['side'] = isset($first['side']) ? trim($first['side']) : null;
                    $first['size'] = isset($first['size']) ? trim($first['size']) : null;
                    return $first;
                });

                // Process free items and attach them to paid items
                $freeItems->each(function($freeItem) use ($mergedPaidItems) {
                    $productId = $freeItem['product_id'];
                    $freeSide = isset($freeItem['side']) ? trim($freeItem['side']) : null;
                    $freeSize = isset($freeItem['size']) ? trim($freeItem['size']) : null;
                    $qty = (int)($freeItem['quantity'] ?? 0);
                    
                    // Find matching paid item (exact variant first)
                    $exactKey = $productId . '-' . strtolower($freeSide ?? '') . '-' . strtolower($freeSize ?? '');
                    $targetItem = $mergedPaidItems->get($exactKey);

                    // Fallback: any paid item with the same product ID
                    if (!$targetItem) {
                        $targetItem = $mergedPaidItems->first(function($item) use ($productId) {
                            return $item['product_id'] == $productId;
                        });
                    }

                    if ($targetItem) {
                        $targetKey = $targetItem['product_id'] . '-' . strtolower($targetItem['side'] ?? '') . '-' . strtolower($targetItem['size'] ?? '');
                        $updatedItem = $mergedPaidItems->get($targetKey);
                        $updatedItem['free_quantity'] = ($updatedItem['free_quantity'] ?? 0) + $qty;
                        if (!empty($freeSide)) {
                            $counts = $updatedItem['_free_side_counts'] ?? [];
                            $counts[$freeSide] = ($counts[$freeSide] ?? 0) + $qty;
                            $updatedItem['_free_side_counts'] = $counts;
                        }
                        if (!empty($freeSize)) {
                            $counts = $updatedItem['_free_size_counts'] ?? [];
                            $counts[$freeSize] = ($counts[$freeSize] ?? 0) + $qty;
                            $updatedItem['_free_size_counts'] = $counts;
                        }
                        $mergedPaidItems->put($targetKey, $updatedItem);
                    } else {
                        $standaloneKey = 'free-' . uniqid();
                        $freeItem['quantity'] = 0;
                        $freeItem['free_quantity'] = $qty;
                        $freeItem['is_free'] = true;
                        $freeItem['free_side'] = $freeSide;
                        $freeItem['free_size'] = $freeSize;
                        $freeItem['side'] = $freeSide;
                        $freeItem['size'] = $freeSize;
                        if (!empty($freeSide)) $freeItem['_free_side_counts'] = [$freeSide => $qty];
                        if (!empty($freeSize)) $freeItem['_free_size_counts'] = [$freeSize => $qty];
                        $mergedPaidItems->put($standaloneKey, $freeItem);
                    }
                });

                $mergedItems = $mergedPaidItems->map(function($item) {
                    if (!empty($item['_free_side_counts'])) {
                        $item['free_side'] = collect($item['_free_side_counts'])->map(fn($q, $s) => $q . ' ' . $s)->implode(', ');
                    }
                    if (!empty($item['_free_size_counts'])) {
                        $item['free_size'] = collect($item['_free_size_counts'])->map(fn($q, $s) => $q . ' ' . $s)->implode(', ');
                    }
                    return $item;
                })->values();

                foreach ($mergedItems as $itemData) {
                    $product = Product::findOrFail($itemData['product_id']);
                    $unit = $itemData['unit'] ?? 'Nos';
                    $qty = (int)$itemData['quantity'];
                    $iSide = $itemData['side'] ?? null;
                    $iSize = $itemData['size'] ?? null;
                    $variant = $itemData['variant'] ?? null;

                    // Fallback: If side/size are missing but variant is present, try to split it
                    if ((!$iSide || !$iSize) && $variant) {
                        if (str_contains($variant, ' - ')) {
                            $parts = explode(' - ', $variant);
                            if (count($parts) >= 2) {
                                if (in_array(strtoupper(trim($parts[0])), ['LEFT', 'RIGHT'])) {
                                    $iSide = $iSide ?: trim($parts[0]);
                                    $iSize = $iSize ?: trim($parts[1]);
                                } else {
                                    $iSize = $iSize ?: trim($parts[0]);
                                    $iSide = $iSide ?: trim($parts[1]);
                                }
                            }
                        } elseif (in_array(strtoupper(trim($variant)), ['LEFT', 'RIGHT'])) {
                            $iSide = $iSide ?: trim($variant);
                        } else {
                            $iSize = $iSize ?: trim($variant);
                        }
                    }
                    $vLabel = array_filter([$iSide, $iSize]);

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

                    // Stock Check
                    if ($distributor) {
                        $totalStock = DB::table('inventories')
                            ->where('distributor_id', $distributor->id)
                            ->where('product_id', $product->id)
                            ->when(!empty($iSide), function($q) use ($iSide) {
                                return $q->where(function($sq) use ($iSide) {
                                    $sq->where('side', $iSide)
                                       ->orWhereNull('side')
                                       ->orWhere('side', '');
                                });
                            })
                            ->when(!empty($iSize), function($q) use ($iSize) {
                                return $q->where(function($sq) use ($iSize) {
                                    $sq->where('size', $iSize)
                                       ->orWhereNull('size')
                                       ->orWhere('size', '');
                                });
                            })
                            ->sum('stock');


                        if ($totalStock < $totalQtyNos) {
                            $variantMsg = !empty($vLabel) ? " [" . implode('/', $vLabel) . "]" : "";
                            throw new \Exception("Insufficient stock for product '{$product->product_name}'{$variantMsg} at selected distributor.");
                        }
                    }

                    $isFree = $itemData['is_free'] ?? false;

                    if ($isFree) {
                        $price = 0;
                        $subtotalWithGst = 0;
                        $freeQty = $qty;
                        $qty = 0; // The actual billed quantity is 0
                        $freeSide = $iSide;
                        $freeSize = $iSize;
                    } else {
                        $price = (float)$product->ptr; // Retailers buy at PTR
                        $gstRate = (float)($product->gst ?? 0);
                        $taxableSubtotal = $totalQtyNos * $price;
                        $subtotalWithGst = $taxableSubtotal * (1 + ($gstRate / 100));

                        // --- FREE PRODUCT SCHEME LOGIC (Fallback for old app) ---
                        $freeQty = $itemData['free_quantity'] ?? 0;
                        $freeProductId = null;
                        $freeSide = $itemData['free_side'] ?? null;
                        $freeSize = $itemData['free_size'] ?? null;
        
                        if ($freeQty == 0) {
                            if ($product->free_qty_buy > 0 && $product->free_qty_get > 0) {
                                $eligibleFree = floor($qty / $product->free_qty_buy) * $product->free_qty_get;
                                if ($eligibleFree > 0) {
                                    $freeQty = $eligibleFree;
                                    if (isset($itemData['free_product_id'])) {
                                        $selectedFreeProduct = Product::find($itemData['free_product_id']);
                                        if ($selectedFreeProduct && $selectedFreeProduct->is_free_eligible) {
                                            $freeProductId = $selectedFreeProduct->id;
                                            $freeSide = $itemData['free_side'] ?? null;
                                            $freeSize = $itemData['free_size'] ?? null;
                                        }
                                    }
                                }
                            } elseif (strcasecmp($product->brand, 'Atomeds') === 0 || strcasecmp($product->brand, 'Atomets') === 0) {
                                $calculatedFree = floor($qty / 10) * 2;
                                if ($retailer->can_configure_free_strips) {
                                    $freeQty = isset($itemData['free_quantity']) ? (int)$itemData['free_quantity'] : $calculatedFree;
                                } else {
                                    $freeQty = $calculatedFree;
                                }
                            }
                        }
                    }

                    // Append variant to product name if provided
                    $finalProductName = $product->product_name;
                    if (!empty($vLabel)) {
                        $finalProductName .= ' [' . implode('/', $vLabel) . ']';
                    }

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $finalProductName,
                        'quantity' => $qty,
                        'free_quantity' => $freeQty,
                        'free_product_id' => $freeProductId ?? null,
                        'free_side' => $freeSide ?? null,
                        'free_size' => $freeSize ?? null,
                        'unit' => $unit,
                        'unit_price' => $price,
                        'total_amount' => $subtotalWithGst,
                        'side' => $iSide,
                        'size' => $iSize,
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
