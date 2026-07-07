<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\RetailerOrder;
use App\Models\DistributorOrder;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\Distributor;
use App\Traits\HandlesNotifications;
use App\Traits\OneSignalNotifications;

class OrderApiController extends Controller
{
    use HandlesNotifications, OneSignalNotifications;

    /**
     * Unified Order Creation Endpoint
     * 
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Place new order (Retailer or Distributor based on payload)",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     description="Step 1 of the 2-step ordering process. Places the base order, creates the order record, calculates the total bill, and triggers notifications. Returns the generated order_id and a list of eligible free items to be presented in the UI (e.g. Bottom Sheet).",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="retailer_id", type="integer", nullable=true, description="Omit if Distributor order", example=12),
     *             @OA\Property(property="distributor_id", type="integer", nullable=true, description="Required for Distributor order, optional for Retailer order", example=27),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="product_id", type="integer", example=15),
     *                 @OA\Property(property="quantity", type="integer", example=5),
     *                 @OA\Property(property="unit", type="string", example="Nos"),
     *                 @OA\Property(property="side", type="string", example="Left"),
     *                 @OA\Property(property="size", type="string", example="M")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order created successfully with eligible free items returned for Step 2 selection.")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'retailer_id' => 'nullable|exists:retailers,id',
            'distributor_id' => 'nullable|exists:distributors,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string',
            'items.*.side' => 'nullable|string',
            'items.*.size' => 'nullable|string',
        ]);

        $user = auth('api')->user();

        // If retailer_id is present in the request payload, treat as a Retailer Order.
        if ($request->filled('retailer_id')) {
            return $this->createRetailerOrder($request, $user);
        } else {
            return $this->createDistributorOrder($request, $user);
        }
    }

    private function createRetailerOrder(Request $request, $user)
    {
        $retailer = Retailer::find($request->retailer_id);
        if (!$retailer) {
            return response()->json(['error' => 'Retailer not found'], 404);
        }

        $distributor = $request->filled('distributor_id') ? Distributor::find($request->distributor_id) : null;
        $items = $request->items;

        DB::beginTransaction();
        try {
            $order = RetailerOrder::create([
                'retailer_id' => $retailer->id,
                'distributor_id' => $distributor ? $distributor->id : null,
                'fieldstaff_id' => $retailer->field_staff_id,
                'order_code' => 'ORD-' . strtoupper(uniqid()),
                'status' => RetailerOrder::STATUS_PENDING,
                'total_amount' => 0,
                'total_items' => 0,
                'total_quantity' => 0,
                'placed_at' => now(),
            ]);

            $totalAmount = 0;
            $totalItemsCount = 0;
            $totalQuantityNos = 0;
            $eligibleFreeItems = [];

            // Merge identical items
            $mergedItems = collect($items)->groupBy(function($i) {
                $side = isset($i['side']) ? trim(strtolower($i['side'])) : '';
                $size = isset($i['size']) ? trim(strtolower($i['size'])) : '';
                $isFree = isset($i['is_free']) && $i['is_free'] ? '1' : '0';
                return $i['product_id'] . '-' . $side . '-' . $size . '-' . $isFree;
            })->map(function($group) {
                $first = $group->first();
                $first['quantity'] = $group->sum('quantity');
                $first['side'] = isset($first['side']) ? trim($first['side']) : null;
                $first['size'] = isset($first['size']) ? trim($first['size']) : null;
                $first['is_free'] = isset($first['is_free']) ? (bool)$first['is_free'] : false;
                return $first;
            });

            // Keep track of total qty ordered for each base product to calculate scheme eligibility globally per product
            $productTotals = [];

            foreach ($mergedItems as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                
                $unit = ucfirst(strtolower($itemData['unit'] ?? 'Nos'));
                $availableUnits = $this->getAvailableUnits($product);
                if (!in_array($unit, $availableUnits)) {
                    $unit = $availableUnits[0]; // Force to the primary valid unit (e.g., Strips)
                }

                $qty = (int)$itemData['quantity'];
                $iSide = $itemData['side'] ?? null;
                $iSize = $itemData['size'] ?? null;

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
                
                if (!isset($productTotals[$product->id])) {
                    $productTotals[$product->id] = ['total_qty' => 0, 'product' => $product];
                }
                $productTotals[$product->id]['total_qty'] += $totalQtyNos; // For schemes based on ordered strips

                $isFree = isset($itemData['is_free']) ? (bool)$itemData['is_free'] : false;
                $price = $isFree ? 0 : (float)$product->ptr;
                $gstRate = (float)($product->gst ?? 0);
                $taxableSubtotal = $totalQtyNos * $price;
                $subtotalWithGst = $taxableSubtotal * (1 + ($gstRate / 100));

                $vLabel = array_filter([$iSide, $iSize]);
                $finalProductName = $product->product_name;
                if (!empty($vLabel)) {
                    $finalProductName .= ' [' . implode('/', $vLabel) . ']';
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $finalProductName,
                    'quantity' => $isFree ? 0 : $qty,
                    'free_quantity' => $isFree ? $qty : 0,
                    'unit' => $unit,
                    'unit_price' => $price,
                    'total_amount' => $subtotalWithGst,
                    'side' => $iSide,
                    'size' => $iSize,
                ]);

                if (!$isFree) {
                    $totalAmount += $subtotalWithGst;
                    $totalQuantityNos += $totalQtyNos;
                }
                $totalItemsCount++;
            }

            $order->update([
                'total_amount' => $totalAmount,
                'total_items' => $totalItemsCount,
                'total_quantity' => $totalQuantityNos
            ]);

            // Calculate eligible free items
            foreach ($productTotals as $productId => $data) {
                $product = $data['product'];
                $totalQty = $data['total_qty'];
                
                $freeQty = 0;
                if ($product->free_qty_buy > 0 && $product->free_qty_get > 0) {
                    $freeQty = floor($totalQty / $product->free_qty_buy) * $product->free_qty_get;
                } elseif (strcasecmp($product->brand, 'Atomeds') === 0 || strcasecmp($product->brand, 'Atomets') === 0) {
                    $freeQty = floor($totalQty / 10) * 2;
                }

                if ($freeQty > 0) {
                    $variants = [];
                    if ($product->has_variants && !empty($product->variant_options)) {
                        $variants = $product->variant_options;
                        
                        // Filter variants based on distributor's available stock
                        $availableInventory = \App\Models\Inventory::where('distributor_id', $distributor->id)
                            ->where('product_id', $product->id)
                            ->where('stock', '>', 0)
                            ->get();

                        if ($availableInventory->count() > 0) {
                            $filteredVariants = [];
                            foreach ($variants as $type => $options) {
                                $validOptions = [];
                                foreach ($options as $option) {
                                    $key = strtolower($type);
                                    if ($key === 'size') {
                                        if ($availableInventory->contains('size', $option)) $validOptions[] = $option;
                                    } elseif ($key === 'side') {
                                        if ($availableInventory->contains('side', $option)) $validOptions[] = $option;
                                    } else {
                                        $validOptions[] = $option;
                                    }
                                }
                                if (!empty($validOptions)) {
                                    $filteredVariants[$type] = array_values($validOptions);
                                }
                            }
                            $variants = !empty($filteredVariants) ? $filteredVariants : $variants;
                        }
                    }
                    
                    $eligibleFreeItems[] = [
                        'promotion_id' => $product->id, // Usually there is a promo ID, falling back to product ID
                        'product_id' => $product->id,
                        'product_name' => $product->product_name,
                        'pack' => $product->pack,
                        'unit' => $this->getAvailableUnits($product)[0] ?? 'Nos',
                        'quantity_allowed' => $freeQty,
                        'requires_variants' => $product->has_variants ? true : false,
                        'allowed_variants' => $variants
                    ];
                }
            }

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

                $this->sendOneSignalPush(
                    [$order->fieldStaff->user->id],
                    "New order #{$order->order_code} from {$retailer->shop_name} assigned to you.",
                    ['order_id' => $order->id, 'type' => 'retailer_order'],
                    'New Retailer Order'
                );
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'total_amount' => round($totalAmount, 2),
                    'eligible_free_items' => $eligibleFreeItems
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Unified Retailer Order creation failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function createDistributorOrder(Request $request, $user)
    {
        $distributor = $request->filled('distributor_id') ? Distributor::find($request->distributor_id) : ($user->hasRole('distributor') ? $user->distributor : null);
        
        if (!$distributor) {
            return response()->json(['error' => 'Distributor not found or user is not a distributor'], 404);
        }

        $salesManagerId = $distributor->sales_manager_id;
        $items = $request->items;

        DB::beginTransaction();
        try {
            $order = DistributorOrder::create([
                'distributor_id' => $distributor->id,
                'sales_manager_id' => $salesManagerId,
                'status' => DistributorOrder::STATUS_PENDING,
                'placed_at' => now(),
                'total_amount' => 0,
                'total_items' => 0,
                'total_quantity' => 0,
            ]);

            $totalAmount = 0;
            $totalItemsCount = 0;
            $totalQuantityStrips = 0;
            $eligibleFreeItems = [];

            // Merge identical items
            $mergedItems = collect($items)->groupBy(function($i) {
                $side = isset($i['side']) ? trim(strtolower($i['side'])) : '';
                $size = isset($i['size']) ? trim(strtolower($i['size'])) : '';
                $isFree = isset($i['is_free']) && $i['is_free'] ? '1' : '0';
                return $i['product_id'] . '-' . $side . '-' . $size . '-' . $isFree;
            })->map(function($group) {
                $first = $group->first();
                $first['quantity'] = $group->sum('quantity');
                $first['side'] = isset($first['side']) ? trim($first['side']) : null;
                $first['size'] = isset($first['size']) ? trim($first['size']) : null;
                $first['is_free'] = isset($first['is_free']) ? (bool)$first['is_free'] : false;
                return $first;
            });

            $productTotals = [];

            foreach ($mergedItems as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                
                $unit = ucfirst(strtolower($itemData['unit'] ?? 'Box'));
                $availableUnits = $this->getAvailableUnits($product);
                if (!in_array($unit, $availableUnits)) {
                    $unit = $availableUnits[0]; // Force to the primary valid unit (e.g., Strips)
                }

                $isFree = isset($itemData['is_free']) ? (bool)$itemData['is_free'] : false;
                $unitPrice = $isFree ? 0 : $product->pts;
                $qty = (float)$itemData['quantity'];
                $iSide = $itemData['side'] ?? null;
                $iSize = $itemData['size'] ?? null;

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
                
                if (!$isFree) {
                    if (!isset($productTotals[$product->id])) {
                        $productTotals[$product->id] = ['total_qty' => 0, 'product' => $product];
                    }
                    $productTotals[$product->id]['total_qty'] += $totalQtyStrips;
                }

                $gstRate = (float)($product->gst ?? 0);
                $taxableSubtotal = $totalQtyStrips * $unitPrice;
                $subtotalWithGst = $taxableSubtotal * (1 + ($gstRate / 100));

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'quantity' => $isFree ? 0 : $qty,
                    'free_quantity' => $isFree ? $qty : 0,
                    'unit' => $unit,
                    'price' => (float)$unitPrice,
                    'subtotal' => $subtotalWithGst,
                    'side' => $iSide,
                    'size' => $iSize,
                    'free_side' => null,
                    'free_size' => null,
                ]);

                if (!$isFree) {
                    $totalAmount += $subtotalWithGst;
                    $totalQuantityStrips += $totalQtyStrips;
                }
                $totalItemsCount++;
            }

            $order->update([
                'total_amount' => $totalAmount,
                'total_items' => $totalItemsCount,
                'total_quantity' => $totalQuantityStrips,
            ]);

            // Calculate eligible free items
            foreach ($productTotals as $productId => $data) {
                $product = $data['product'];
                $totalQty = $data['total_qty'];
                
                $freeQty = 0;
                if ($product->free_qty_buy > 0 && $product->free_qty_get > 0) {
                    $freeQty = floor($totalQty / $product->free_qty_buy) * $product->free_qty_get;
                }

                if ($freeQty > 0) {
                    $variants = [];
                    if ($product->has_variants && !empty($product->variant_options)) {
                        $variants = $product->variant_options;
                    }
                    
                    $eligibleFreeItems[] = [
                        'promotion_id' => $product->id,
                        'product_id' => $product->id,
                        'product_name' => $product->product_name,
                        'pack' => $product->pack,
                        'unit' => $this->getAvailableUnits($product)[0] ?? 'Nos',
                        'quantity_allowed' => $freeQty,
                        'requires_variants' => $product->has_variants ? true : false,
                        'allowed_variants' => $variants
                    ];
                }
            }

            if ($order->salesManager && $order->salesManager->user) {
                $this->notifyUnique($order->salesManager->user, new \App\Notifications\OrderActionRequired(
                    $order,
                    "New Distributor Order #{$order->order_code} is ready for your approval.",
                    url('/approvals/distributors'),
                    'distributor_order'
                ));

                $this->sendOneSignalPush(
                    [$order->salesManager->user->id],
                    "New Distributor Order #{$order->order_code} is ready for your approval.",
                    ['order_id' => $order->id, 'type' => 'distributor_order'],
                    'Distributor Order Pending'
                );
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'total_amount' => round($totalAmount, 2),
                    'eligible_free_items' => $eligibleFreeItems
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Unified Distributor Order creation failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{id}/add-free-items",
     *     summary="Attach free items to an order (Retailer or Distributor)",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     description="Step 2 of the 2-step ordering process. Attaches user-selected free variants from the UI Bottom Sheet to the base order created in Step 1. Ensures the main paid order is secured first.",
     *     @OA\Parameter(name="id", in="path", required=true, description="The order_id generated from the Step 1 POST /api/orders call", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="order_type", type="string", enum={"retailer", "distributor"}),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="product_id", type="integer"),
     *                 @OA\Property(property="quantity", type="integer"),
     *                 @OA\Property(property="side", type="string"),
     *                 @OA\Property(property="size", type="string")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Free items attached successfully to the already placed base order.")
     * )
     */
    public function addFreeItems(Request $request, $id)
    {
        $request->validate([
            'order_type' => 'required|in:retailer,distributor',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.side' => 'nullable|string',
            'items.*.size' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            if ($request->order_type === 'retailer') {
                $order = RetailerOrder::findOrFail($id);
            } else {
                $order = DistributorOrder::findOrFail($id);
            }

            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                
                $vLabel = array_filter([$itemData['side'] ?? null, $itemData['size'] ?? null]);
                $finalProductName = $product->product_name;
                if (!empty($vLabel)) {
                    $finalProductName .= ' [' . implode('/', $vLabel) . ']';
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $finalProductName,
                    'quantity' => 0, // Paid quantity is 0
                    'free_quantity' => (int)$itemData['quantity'],
                    'free_product_id' => $product->id,
                    'free_side' => $itemData['side'] ?? null,
                    'free_size' => $itemData['size'] ?? null,
                    'unit' => 'Nos',
                    'unit_price' => 0,
                    'price' => 0,
                    'total_amount' => 0,
                    'subtotal' => 0,
                    'side' => $itemData['side'] ?? null,
                    'size' => $itemData['size'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Free items added successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Add Free Items failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function getAvailableUnits($product)
    {
        $units = [];
        if ($product->units_per_strip > 1) {
            $units[] = 'Strips';
        } else {
            $units[] = 'Nos';
        }
        
        if ($product->strips_per_box > 0) {
            $units[] = 'Box';
        }
        
        if ($product->boxes_per_carton > 0) {
            $units[] = 'Carton';
        }
        
        return empty($units) ? ['Nos'] : $units;
    }
}
