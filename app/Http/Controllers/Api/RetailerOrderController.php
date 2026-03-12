<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RetailerOrder;
use Illuminate\Http\Request;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\HandlesNotifications;
use Illuminate\Support\Facades\Storage;

class RetailerOrderController extends Controller
{
    use HandlesNotifications;

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
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="order_code", type="string", example="RO-X9Y8Z7"),
     *                 @OA\Property(property="total_amount", type="string", example="1500.00"),
     *                 @OA\Property(property="total_items", type="integer", example=3),
     *                 @OA\Property(property="total_quantity", type="integer", example=12),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="notes", type="string", example="Urgent delivery"),
     *                 @OA\Property(property="placed_at", type="string", format="date-time", example="2023-10-25 10:00:00")
     *             )
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

        $orders = RetailerOrder::with(['items.product'])
            ->where('retailer_id', $user->retailer->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id'             => $order->id,
                    'order_code'     => $order->order_code,
                    'status'         => $order->status,
                    'payment_status' => $order->payment_status,
                    'total_amount'   => number_format($order->total_amount, 2),
                    'total_items'    => $order->total_items,
                    'total_quantity' => $order->total_quantity,
                    'notes'          => $order->notes,
                    'items'          => $order->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->product_name ?? 'N/A',
                            'quantity'   => $item->quantity,
                        ];
                    }),
                    'invoice_url'    => $order->invoice_path
                        ? Storage::disk('public')->url($order->invoice_path)
                        : null,
                    'placed_at'      => $order->placed_at?->format('Y-m-d H:i:s'),
                    'delivered_at'   => $order->delivered_at?->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json($orders);
    }



    /**
     * @OA\Post(
     *     path="/api/retailer-orders",
     *     summary="Place a new retailer order",
     *     tags={"Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="product_id", type="integer"),
     *                 @OA\Property(property="quantity", type="integer")
     *             )),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Order placed successfully"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $user = auth('api')->user();
        if (!$user->retailer) {
            return response()->json(['message' => 'User is not a retailer'], 403);
        }

        $retailer = $user->retailer;

        DB::beginTransaction();
        try {
            $order = RetailerOrder::create([
                'retailer_id' => $retailer->id,
                'distributor_id' => null, // Needs assignment by admin/system later
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
            $totalItems = 0;
            $totalQuantity = 0;

            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $price = $product->ptr; // Retailers buy at PTR
                $qty = $itemData['quantity'];
                $subtotal = $qty * $price;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_amount' => $subtotal,
                ]);

                $totalAmount += $subtotal;
                $totalItems++;
                $totalQuantity += $qty;
            }

            $order->update([
                'total_amount' => $totalAmount,
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity
            ]);

            // Notify Field Staff
            if ($order->fieldStaff && $order->fieldStaff->user) {
                $this->notifyUnique(
                    $order->fieldStaff->user,
                    new \App\Notifications\OrderActionRequired(
                        $order,
                        "New order #{$order->order_code} from {$retailer->user->name} assigned to you.",
                        url('/approvals/retailers'),
                        'retailer_order'
                    )
                );
            }

            DB::commit();
            return response()->json([
                'message' => 'Order placed.',
                'order' => $order->load('items.product')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Retailer Order creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to place order.'], 500);
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
     *     @OA\Response(response=200, description="Status updated successfully"),
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
