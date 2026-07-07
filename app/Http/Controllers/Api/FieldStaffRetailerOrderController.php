<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RetailerOrder;
use App\Traits\HandlesNotifications;
use App\Traits\OneSignalNotifications;

class FieldStaffRetailerOrderController extends Controller
{
    use HandlesNotifications, OneSignalNotifications;

    /**
     * @OA\Get(
     *     path="/api/field-staff/retailer-orders",
     *     summary="List retailer orders assigned to the logged-in field staff",
     *     tags={"Field Staff Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="List of assigned retailer orders")
     * )
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('fieldstaff')) return response()->json(['error' => 'Unauthorized'], 403);

        $fieldStaffId = $user->fieldStaff->id;

        $query = RetailerOrder::with(['retailer.user', 'items.product', 'distributor.user', 'distributor.area', 'distributor.district'])
            ->whereHas('retailer', function ($qr) use ($fieldStaffId) {
                $qr->where('field_staff_id', $fieldStaffId);
            });

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->get();

        return response()->json($orders->map(function ($order) {
            $distributor = $order->distributor;
            return [
                'id'             => $order->id,
                'order_code'     => $order->order_code,
                'retailer_id'    => $order->retailer_id,
                'distributor_id' => $order->distributor_id,
                'retailer_name'  => $order->retailer->user->name ?? 'N/A',
                'retailer_shop'  => $order->retailer->shop_name ?? 'N/A',
                'field_staff_name' => $order->retailer->fieldStaff->user->name ?? 'N/A',
                'sales_manager'  => $order->retailer->fieldStaff->salesManager->user->name ?? 'N/A',
                'distributor_name' => $distributor->user->name ?? 'N/A',
                'distributor_contact' => $distributor->contact_no ?? $distributor->user->contact_no ?? null,
                'distributor_address' => $distributor->user->address ?? null,
                'total_amount'   => $order->total_amount,
                'status'         => $order->status,
                'payment_status' => $order->payment_status ?? null,
                'placed_at'      => $order->placed_at ? $order->placed_at->format('Y-m-d H:i:s') : null,
                'items'          => $order->items->map(function ($item) {
                    $isFreeItem = $item->unit_price == 0;
                    $baseName = $item->product_name ?? $item->product->product_name ?? 'N/A';
                    
                    return [
                        'id'           => $item->id,
                        'product_id'   => $item->product_id,
                        'product_name' => $isFreeItem ? $baseName . ' (Free)' : $baseName,
                        'brand'        => $item->product->brand ?? null,
                        'quantity'     => $isFreeItem ? $item->free_quantity : $item->quantity,
                        'free_quantity' => $item->free_quantity,
                        'is_free'      => $isFreeItem,
                        'price'        => (float)$item->unit_price,
                        'unit'         => $item->unit ?? 'Nos',
                        'side'         => $item->side,
                        'size'         => $item->size,
                        'unit_price'   => $item->unit_price,
                        'total_amount' => $item->total_amount,
                    ];
                }),
            ];
        }));
    }

    /**
     * @OA\Get(
     *     path="/api/field-staff/retailer-orders/{id}",
     *     summary="Get a single retailer order detail",
     *     tags={"Field Staff Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Order Details")
     * )
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!$user->hasRole('fieldstaff')) return response()->json(['error' => 'Unauthorized'], 403);

        $fieldStaffId = $user->fieldStaff->id;

        $order = RetailerOrder::with(['retailer.user', 'retailer.area', 'retailer.district', 'items.product', 'distributor.user', 'distributor.area', 'distributor.district'])
            ->whereHas('retailer', function ($qr) use ($fieldStaffId) {
                $qr->where('field_staff_id', $fieldStaffId);
            })->findOrFail($id);

        $distributor = $order->distributor;

        return response()->json([
            'id'             => $order->id,
            'order_code'     => $order->order_code,
            'retailer_id'    => $order->retailer_id,
            'distributor_id' => $order->distributor_id,
            'retailer_name'  => $order->retailer->user->name ?? 'N/A',
            'retailer_shop'  => $order->retailer->shop_name ?? 'N/A',
            'field_staff_name' => $order->retailer->fieldStaff->user->name ?? 'N/A',
            'sales_manager'  => $order->retailer->fieldStaff->salesManager->user->name ?? 'N/A',
            'distributor_name' => $distributor->user->name ?? 'N/A',
            'distributor_contact' => $distributor->contact_no ?? $distributor->user->contact_no ?? null,
            'distributor_address' => $distributor->user->address ?? null,
            'retailer'       => [
                'id'         => $order->retailer->id,
                'name'       => $order->retailer->user->name ?? 'N/A',
                'shop_name'  => $order->retailer->shop_name ?? 'N/A',
                'contact'    => $order->retailer->contact_no ?? $order->retailer->user->contact_no ?? null,
                'area'       => $order->retailer->area->name ?? null,
                'district'   => $order->retailer->district->name ?? null,
            ],
            'total_amount'   => $order->total_amount,
            'status'         => $order->status,
            'payment_status' => $order->payment_status ?? null,
            'placed_at'      => $order->placed_at ? $order->placed_at->format('Y-m-d H:i:s') : null,
            'delivered_at'   => $order->delivered_at ? $order->delivered_at->format('Y-m-d H:i:s') : null,
            'delivery_notes' => $order->delivery_notes ?? null,
            'invoice_url'    => $order->invoice_url ?? null,
            'items'          => $order->items->map(function ($item) {
                $isFreeItem = $item->unit_price == 0;
                $baseName = $item->product_name ?? $item->product->product_name ?? 'N/A';

                return [
                    'id'            => $item->id,
                    'product_id'    => $item->product_id,
                    'product_name'  => $isFreeItem ? $baseName . ' (Free)' : $baseName,
                    'product_code'  => $item->product->product_code ?? null,
                    'brand'         => $item->product->brand ?? null,
                    'quantity'      => $isFreeItem ? $item->free_quantity : $item->quantity,
                    'free_quantity' => $item->free_quantity,
                    'is_free'       => $isFreeItem,
                    'price'         => (float)$item->unit_price,
                    'unit'          => $item->unit ?? 'Nos',
                    'side'          => $item->side,
                    'size'          => $item->size,
                    'unit_price'    => $item->unit_price,
                    'total_amount'  => $item->total_amount,
                    'mrp'           => $item->product->mrp ?? null,
                ];
            }),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/field-staff/retailer-orders/calculate-price",
     *     summary="Calculate price for a product before placing an order",
     *     tags={"Field Staff Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="product_id", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="quantity", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="distributor_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Price calculation")
     * )
     */
    public function calculatePrice(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('fieldstaff')) return response()->json(['error' => 'Unauthorized'], 403);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'distributor_id' => 'nullable|exists:distributors,id'
        ]);

        $productId = $request->product_id;
        $quantity = $request->quantity;
        $distributorId = $request->distributor_id;

        $product = \App\Models\Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }
        
        if ($distributorId) {
            $distributor = \App\Models\Distributor::find($distributorId);
            if ($distributor) {
                $distProduct = $distributor->products()->where('product_id', $productId)->first();
                if (!$distProduct) {
                    return response()->json(['error' => 'Product not available from selected distributor'], 422);
                }
            }
        }

        $price = (float)$product->ptr;
        $gstRate = (float)($product->gst ?? 0);
        $taxableSubtotal = $quantity * $price;
        $subtotalWithGst = $taxableSubtotal * (1 + ($gstRate / 100));

        return response()->json([
            'unit_price' => $price,
            'quantity' => $quantity,
            'gst_percentage' => $gstRate,
            'subtotal' => $taxableSubtotal,
            'total_amount' => $subtotalWithGst
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/field-staff/retailer-orders",
     *     summary="Place new retailer order(s)",
     *     description="Items will be grouped by distributor_id into separate orders.",
     *     tags={"Field Staff Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"retailer_id", "items"},
     *             @OA\Property(property="retailer_id", type="integer", example=1),
     *             @OA\Property(property="delivery_notes", type="string", example="Urgent order"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"product_id", "quantity"},
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=10),
     *                     @OA\Property(property="unit", type="string", example="Box"),
     *                     @OA\Property(property="side", type="string", example="Left"),
     *                     @OA\Property(property="size", type="string", example="XL"),
     *                     @OA\Property(property="distributor_id", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Orders placed successfully")
     * )
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('fieldstaff')) return response()->json(['error' => 'Unauthorized'], 403);

        $request->validate([
            'retailer_id' => 'required|exists:retailers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string',
            'items.*.side' => 'nullable|string',
            'items.*.size' => 'nullable|string',
            'items.*.distributor_id' => 'nullable|exists:distributors,id',
            'items.*.is_free' => 'nullable|boolean',
            'items.*.free_quantity' => 'nullable|integer|min:0',
            'items.*.free_side' => 'nullable|string',
            'items.*.free_size' => 'nullable|string',
            'delivery_notes' => 'nullable|string'
        ]);

        $fieldStaffId = $user->fieldStaff->id;
        
        $retailer = \App\Models\Retailer::where('id', $request->retailer_id)
            ->where('field_staff_id', $fieldStaffId)
            ->first();

        if (!$retailer) {
            return response()->json(['error' => 'Retailer not assigned to you or invalid.'], 403);
        }

        // Group items by distributor_id
        $groupedItems = collect($request->items)->groupBy('distributor_id');
        $createdOrders = [];

        \DB::beginTransaction();
        try {
            foreach ($groupedItems as $distributorId => $items) {
                // If distributorId is an empty string, make it null
                $distributorId = $distributorId === "" ? null : $distributorId;

                $totalAmount = 0;
                $totalQuantity = 0;
                $totalItems = count($items);

                // Create Order
                $orderCode = 'RO-' . strtoupper(uniqid());
                $order = RetailerOrder::create([
                    'order_code' => $orderCode,
                    'retailer_id' => $retailer->id,
                    'distributor_id' => $distributorId,
                    'fieldstaff_id' => $fieldStaffId,
                    'status' => RetailerOrder::STATUS_PROCESSING,
                    'payment_status' => 'pending',
                    'total_amount' => 0, // Will update below
                    'total_items' => $totalItems,
                    'total_quantity' => 0, // Will update below
                    'delivery_notes' => $request->delivery_notes,
                    'placed_at' => now(),
                    'metadata' => [
                        'placed_by' => 'fieldstaff',
                        'field_staff_id' => $fieldStaffId,
                        'field_staff_name' => $user->name
                    ]
                ]);

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
                            if (!empty($updatedItem['free_side'])) {
                                $sides = array_map('trim', explode(',', $updatedItem['free_side']));
                                if (!in_array($freeSide, $sides)) {
                                    $updatedItem['free_side'] .= ', ' . $freeSide;
                                }
                            } else {
                                $updatedItem['free_side'] = $freeSide;
                            }
                        }
                        if (!empty($freeSize)) {
                            if (!empty($updatedItem['free_size'])) {
                                $sizes = array_map('trim', explode(',', $updatedItem['free_size']));
                                if (!in_array($freeSize, $sizes)) {
                                    $updatedItem['free_size'] .= ', ' . $freeSize;
                                }
                            } else {
                                $updatedItem['free_size'] = $freeSize;
                            }
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
                        $mergedPaidItems->put($standaloneKey, $freeItem);
                    }
                });

                $mergedItems = $mergedPaidItems->values();

                foreach ($mergedItems as $itemData) {
                    $product = \App\Models\Product::find($itemData['product_id']);
                    if (!$product) continue;

                    $qty = $itemData['quantity'];
                    $isFree = $itemData['is_free'];
                    $iSide = $itemData['side'] ?? null;
                    $iSize = $itemData['size'] ?? null;

                    if ($isFree) {
                        $price = 0;
                        $subtotalWithGst = 0;
                        $freeQty = $qty;
                        $qty = 0; // The actual billed quantity is 0
                        $freeSide = $iSide;
                        $freeSize = $iSize;
                    } else {
                        $price = (float)$product->ptr;
                        $gstRate = (float)($product->gst ?? 0);
                        $taxableSubtotal = $qty * $price;
                        $subtotalWithGst = $taxableSubtotal * (1 + ($gstRate / 100));

                        // Free product auto-calculation logic
                        $freeQty = $itemData['free_quantity'] ?? 0;
                        $freeSide = $itemData['free_side'] ?? null;
                        $freeSize = $itemData['free_size'] ?? null;

                        if ($freeQty == 0) {
                            if ($product->free_qty_buy > 0 && $product->free_qty_get > 0) {
                                $eligibleFree = floor($qty / $product->free_qty_buy) * $product->free_qty_get;
                                if ($eligibleFree > 0) {
                                    $freeQty = $eligibleFree;
                                }
                            } elseif (strcasecmp($product->brand, 'Atomeds') === 0 || strcasecmp($product->brand, 'Atomets') === 0) {
                                $freeQty = floor($qty / 10) * 2;
                            }
                        }
                    }

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->product_name,
                        'quantity' => $qty,
                        'unit' => $itemData['unit'] ?? 'Nos',
                        'side' => $iSide,
                        'size' => $iSize,
                        'unit_price' => $price,
                        'total_amount' => $subtotalWithGst,
                        'free_quantity' => $freeQty,
                        'free_side' => $freeSide,
                        'free_size' => $freeSize,
                    ]);

                    $totalAmount += $subtotalWithGst;
                    $totalQuantity += $qty;
                }

                $order->update([
                    'total_amount' => $totalAmount,
                    'total_quantity' => $totalQuantity
                ]);

                // Notify Distributor if assigned
                if ($distributorId) {
                    $distributor = \App\Models\Distributor::find($distributorId);
                    if ($distributor && $distributor->user) {
                        $this->notifyUnique($distributor->user, new \App\Notifications\OrderActionRequired($order, "New Retailer Order #{$order->order_code} assigned to you.", url('/distributor/orders'), 'retailer_order'));
                        $this->sendOneSignalPush([$distributor->user->id], "New Retailer Order #{$order->order_code} assigned to you.", ['order_id' => $order->id, 'type' => 'retailer_order'], 'New Order Received');
                    }
                } else {
                    // Notify Sales Manager if no distributor
                    $salesManager = $user->fieldStaff->salesManager ?? null;
                    if ($salesManager && $salesManager->user) {
                        $this->notifyUnique($salesManager->user, new \App\Notifications\OrderActionRequired($order, "New Retailer Order #{$order->order_code} needs a distributor assignment.", url('/approvals/retailers'), 'retailer_order'));
                        $this->sendOneSignalPush([$salesManager->user->id], "New Retailer Order #{$order->order_code} needs a distributor assignment.", ['order_id' => $order->id, 'type' => 'retailer_order'], 'Distributor Assignment Required');
                    }
                }

                // Notify Field Staff
                $this->notifyUnique($user, new \App\Notifications\OrderActionRequired($order, "Your Retailer Order #{$order->order_code} has been successfully placed.", '/retailer-orders', 'retailer_order'));
                $this->sendOneSignalPush([$user->id], "Your Retailer Order #{$order->order_code} has been successfully placed.", ['order_id' => $order->id, 'type' => 'retailer_order'], 'Order Placed Successfully');

                $createdOrders[] = $order->load('items');
            }
            \DB::commit();

            return response()->json([
                'message' => 'Orders placed successfully',
                'orders' => $createdOrders
            ], 201);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => 'Error creating orders: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/field-staff/retailer-orders/{id}/update-status",
     *     summary="Accept, reject, or cancel a retailer order",
     *     description="Field Staff can accept (moves to processing), reject, or cancel (if they placed it).",
     *     tags={"Field Staff Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"processing", "rejected"}),
     *             @OA\Property(property="cancellation_reason", type="string", description="Required if status is rejected")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order status updated")
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasRole('fieldstaff')) return response()->json(['error' => 'Unauthorized'], 403);

        $request->validate([
            'status' => 'required|in:processing,rejected',
            'cancellation_reason' => 'required_if:status,rejected|string|nullable'
        ]);

        $fieldStaffId = $user->fieldStaff->id;
        $order = RetailerOrder::whereHas('retailer', function ($qr) use ($fieldStaffId) {
            $qr->where('field_staff_id', $fieldStaffId);
        })->findOrFail($id);

        if ($order->status !== RetailerOrder::STATUS_PENDING) {
            return response()->json(['error' => 'Only pending orders can be updated.'], 400);
        }

        $order->status = $request->status;
        if ($request->status === RetailerOrder::STATUS_REJECTED) {
            $order->cancellation_reason = $request->cancellation_reason;
        }
        $order->save();

        if ($this->clearOrderNotifications($order->id, 'retailer_order'));

        if ($order->status === RetailerOrder::STATUS_PROCESSING) {
            // Notify Sales Manager (or Admin if no SM)
            $salesManager = $user->fieldStaff->salesManager ?? null;
            if ($salesManager && $salesManager->user) {
                $this->notifyUnique($salesManager->user, new \App\Notifications\OrderActionRequired($order, "Retailer Order #{$order->order_code} assigned to your team is pending your processing.", url('/approvals/retailers'), 'retailer_order'));
                
                // OneSignal Push
                $this->sendOneSignalPush(
                    [$salesManager->user->id],
                    "Retailer Order #{$order->order_code} assigned to your team is pending your processing.",
                    ['order_id' => $order->id, 'type' => 'retailer_order'],
                    'Order Processing Required'
                );
            } else {
                $admins = \App\Models\User::role(['admin', 'superadmin'])->get();
                $adminIds = $admins->pluck('id')->toArray();
                foreach ($admins as $admin) {
                    $this->notifyUnique($admin, new \App\Notifications\OrderActionRequired($order, "Retailer Order #{$order->order_code} is pending your processing.", url('/approvals/retailers'), 'retailer_order'));
                }
                
                // OneSignal Push to Admins
                if (!empty($adminIds)) {
                    $this->sendOneSignalPush(
                        $adminIds,
                        "Retailer Order #{$order->order_code} is pending your processing.",
                        ['order_id' => $order->id, 'type' => 'retailer_order'],
                        'Order Processing Required'
                    );
                }
            }
        }

        return response()->json(['message' => 'Order status updated to ' . $order->status . '.']);
    }

    /**
     * @OA\Put(
     *     path="/api/field-staff/retailer-orders/{id}",
     *     summary="Edit a retailer order",
     *     description="Allows Field Staff to edit items and delivery notes of a retailer order assigned to them (status must be pending or processing).",
     *     tags={"Field Staff Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"retailer_id", "items"},
     *             @OA\Property(property="retailer_id", type="integer", example=1),
     *             @OA\Property(property="distributor_id", type="integer", example=2, nullable=true),
     *             @OA\Property(property="delivery_notes", type="string", example="Deliver during morning hours", nullable=true),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"product_id", "quantity"},
     *                     @OA\Property(property="product_id", type="integer", example=5),
     *                     @OA\Property(property="quantity", type="integer", example=10),
     *                     @OA\Property(property="size", type="string", example="XL", nullable=true),
     *                     @OA\Property(property="free_quantity", type="integer", example=2, nullable=true),
     *                     @OA\Property(property="free_side", type="string", example="Left", nullable=true),
     *                     @OA\Property(property="free_size", type="string", example="1 S", nullable=true),
     *                     @OA\Property(property="order_item_id", type="integer", example=12, description="Optional. Pass this to update an existing item instead of recreating it.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order updated successfully"),
     *     @OA\Response(response=400, description="Invalid status or input data"),
     *     @OA\Response(response=403, description="Unauthorized to edit this order"),
     *     @OA\Response(response=442, description="Validation failed or invalid records")
     * )
     */
    public function update(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('fieldstaff')) {
            return response()->json(['error' => 'Unauthorized role.'], 403);
        }

        $fieldStaff = $user->fieldStaff;
        if (!$fieldStaff) {
            return response()->json(['error' => 'Field staff record not found.'], 422);
        }

        $retailerOrder = RetailerOrder::findOrFail($id);

        $isOwner = ($retailerOrder->fieldstaff_id === $fieldStaff->id) || 
                   ($retailerOrder->retailer && $retailerOrder->retailer->field_staff_id === $fieldStaff->id);
        if (!$isOwner) {
            return response()->json(['error' => 'You are not authorized to edit this order.'], 403);
        }

        if (!in_array($retailerOrder->status, [RetailerOrder::STATUS_PENDING, RetailerOrder::STATUS_PROCESSING])) {
            return response()->json(['error' => 'You can only edit pending or processing orders.'], 422);
        }

        $request->validate([
            'retailer_id' => 'nullable|exists:retailers,id',
            'distributor_id' => 'nullable|exists:distributors,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string',
            'items.*.side' => 'nullable|string',
            'items.*.size' => 'nullable|string',
            'items.*.is_free' => 'nullable|boolean',
            'items.*.free_quantity' => 'nullable|integer|min:0',
            'items.*.free_side' => 'nullable|string',
            'items.*.free_size' => 'nullable|string',
        ]);

        $metadata = $retailerOrder->metadata ?? [];
        $metadata['is_edited'] = true;
        $metadata['last_edited_by'] = $user->name . ' (Field Staff)';
        $metadata['last_edited_at'] = now()->toDateTimeString();
        
        $snapshot = $retailerOrder->items->map(function($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name ?? ($item->product ? $item->product->product_name : 'Unknown Product'),
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'price' => $item->unit_price,
                'subtotal' => $item->total_amount,
                'side' => $item->side,
                'size' => $item->size,
                'free_quantity' => $item->free_quantity,
                'free_side' => $item->free_side,
                'free_size' => $item->free_size,
            ];
        })->toArray();

        $editLogs = $metadata['edit_history'] ?? [];
        $editLogs[] = [
            'edited_by' => $user->name,
            'role' => 'fieldstaff',
            'edited_at' => now()->toDateTimeString(),
            'original_total' => $retailerOrder->total_amount,
            'snapshot' => $snapshot,
        ];
        $metadata['edit_history'] = $editLogs;

        $updateData = [
            'delivery_notes' => $request->delivery_notes,
            'metadata' => $metadata,
        ];

        if ($request->has('retailer_id') && $request->retailer_id) {
            $updateData['retailer_id'] = $request->retailer_id;
        }

        if ($request->has('distributor_id') && $request->distributor_id) {
            $updateData['distributor_id'] = $request->distributor_id;
        }

        $retailerOrder->update($updateData);

        $distributor = $retailerOrder->distributor;

        $totalAmount = 0;
        $totalItems = 0;
        $totalQuantity = 0;
        $requestItemIds = [];

        try {
            foreach ($request->items as $itemData) {
                $product = null;
                if ($distributor) {
                    $product = $distributor->products()->where('product_id', $itemData['product_id'])->first();
                    if (!$product) {
                        return response()->json(['error' => 'Product ' . $itemData['product_id'] . ' is not available from the assigned distributor.'], 422);
                    }
                } else {
                    $product = \App\Models\Product::find($itemData['product_id']);
                }

                if (!$product) {
                    return response()->json(['error' => 'Product ' . $itemData['product_id'] . ' not found.'], 404);
                }

                $qty = $itemData['quantity'];
                $unit = $itemData['unit'] ?? 'Nos';
                $iSide = $itemData['side'] ?? null;
                $iSize = $itemData['size'] ?? null;
                $isFree = isset($itemData['is_free']) ? filter_var($itemData['is_free'], FILTER_VALIDATE_BOOLEAN) : false;
                
                if ($isFree) {
                    $price = 0;
                    $subtotalWithGst = 0;
                    $freeQty = $qty;
                    $qty = 0;
                    $freeSide = $iSide;
                    $freeSize = $iSize;
                } else {
                    $price = (float)$product->ptr;
                    $gstRate = (float)($product->gst ?? 0);
                    $taxableSubtotal = $qty * $price;
                    $subtotalWithGst = $taxableSubtotal * (1 + ($gstRate / 100));

                    $freeQty = $itemData['free_quantity'] ?? 0;
                    $freeSide = $itemData['free_side'] ?? null;
                    $freeSize = $itemData['free_size'] ?? null;

                    if ($freeQty == 0) {
                        if ($product->free_qty_buy > 0 && $product->free_qty_get > 0) {
                            $eligibleFree = floor($qty / $product->free_qty_buy) * $product->free_qty_get;
                            if ($eligibleFree > 0) {
                                $freeQty = $eligibleFree;
                            }
                        } elseif (strcasecmp($product->brand, 'Atomeds') === 0 || strcasecmp($product->brand, 'Atomets') === 0) {
                            $freeQty = floor($qty / 10) * 2;
                        }
                    }
                }

                $currentOrderItem = null;
                if (isset($itemData['order_item_id'])) {
                    $currentOrderItem = $retailerOrder->items()->find($itemData['order_item_id']);
                }

                if ($currentOrderItem) {
                    $currentOrderItem->update([
                        'product_id' => $itemData['product_id'],
                        'quantity' => $qty,
                        'unit' => $unit,
                        'side' => $iSide,
                        'size' => $iSize,
                        'unit_price' => $price,
                        'total_amount' => $subtotalWithGst,
                        'free_quantity' => $freeQty,
                        'free_side' => $freeSide,
                        'free_size' => $freeSize,
                    ]);
                    $requestItemIds[] = $currentOrderItem->id;
                } else {
                    $newItem = $retailerOrder->items()->create([
                        'product_id' => $itemData['product_id'],
                        'product_name' => $product->product_name,
                        'quantity' => $qty,
                        'unit' => $unit,
                        'side' => $iSide,
                        'size' => $iSize,
                        'unit_price' => $price,
                        'total_amount' => $subtotalWithGst,
                        'free_quantity' => $freeQty,
                        'free_side' => $freeSide,
                        'free_size' => $freeSize,
                    ]);
                    $requestItemIds[] = $newItem->id;
                }
                $totalAmount += $subtotalWithGst;
                $totalItems++;
                $totalQuantity += $qty;
            }

            // Delete removed items
            $retailerOrder->items()->whereNotIn('id', $requestItemIds)->delete();

            $retailerOrder->update([
                'total_amount' => $totalAmount,
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully.',
            'order' => $retailerOrder->load('items')
        ]);
    }

    private function clearOrderNotifications($orderId, $type)
    {
        if (method_exists($this, 'deleteOrderNotifications')) {
            $this->deleteOrderNotifications($orderId, $type);
        }
    }
}
