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

        $query = RetailerOrder::with(['retailer.user', 'items.product', 'distributor.user'])
            ->where(function ($q) use ($fieldStaffId) {
                $q->where('fieldstaff_id', $fieldStaffId)
                    ->orWhereHas('retailer', function ($qr) use ($fieldStaffId) {
                        $qr->where('field_staff_id', $fieldStaffId);
                    });
            });

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->get();

        return response()->json($orders->map(function ($order) {
            return [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'retailer_name' => $order->retailer->user->name ?? 'N/A',
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'placed_at' => $order->placed_at,
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product_name ?? $item->product->product_name ?? 'N/A',
                        'quantity' => $item->quantity,
                        'free_quantity' => $item->free_quantity,
                        'unit' => $item->unit ?? 'Nos',
                        'side' => $item->side,
                        'size' => $item->size,
                        'unit_price' => $item->unit_price,
                        'total_amount' => $item->total_amount
                    ];
                })
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

        $order = RetailerOrder::with(['retailer.user', 'items.product', 'distributor.user'])
            ->where(function ($q) use ($fieldStaffId) {
                $q->where('fieldstaff_id', $fieldStaffId)
                    ->orWhereHas('retailer', function ($qr) use ($fieldStaffId) {
                        $qr->where('field_staff_id', $fieldStaffId);
                    });
            })->findOrFail($id);

        return response()->json($order);
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
        $order = RetailerOrder::where(function ($q) use ($fieldStaffId) {
            $q->where('fieldstaff_id', $fieldStaffId)
                ->orWhereHas('retailer', function ($qr) use ($fieldStaffId) {
                    $qr->where('field_staff_id', $fieldStaffId);
                });
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
            'retailer_id' => 'required|exists:retailers,id',
            'distributor_id' => 'nullable|exists:distributors,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $metadata = $retailerOrder->metadata ?? [];
        $metadata['is_edited'] = true;
        $metadata['last_edited_by'] = $user->name . ' (Field Staff)';
        $metadata['last_edited_at'] = now()->toDateTimeString();
        
        $editLogs = $metadata['edit_history'] ?? [];
        $editLogs[] = [
            'edited_by' => $user->name,
            'role' => 'fieldstaff',
            'edited_at' => now()->toDateTimeString(),
            'original_total' => $retailerOrder->total_amount,
        ];
        $metadata['edit_history'] = $editLogs;

        $retailerOrder->update([
            'retailer_id' => $request->retailer_id,
            'distributor_id' => $request->distributor_id,
            'delivery_notes' => $request->delivery_notes,
            'metadata' => $metadata,
        ]);

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
                $price = (float)$product->ptr;
                $gstRate = (float)($product->gst ?? 0);
                $taxableSubtotal = $qty * $price;
                $subtotalWithGst = $taxableSubtotal * (1 + ($gstRate / 100));

                $currentOrderItem = null;
                if (isset($itemData['order_item_id'])) {
                    $currentOrderItem = $retailerOrder->items()->find($itemData['order_item_id']);
                }

                if ($currentOrderItem) {
                    $currentOrderItem->update([
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total_amount' => $subtotalWithGst
                    ]);
                    $requestItemIds[] = $currentOrderItem->id;
                } else {
                    $newItem = $retailerOrder->items()->create([
                        'product_id' => $itemData['product_id'],
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total_amount' => $subtotalWithGst
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
