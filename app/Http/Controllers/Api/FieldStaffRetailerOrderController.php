<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RetailerOrder;
use App\Traits\HandlesNotifications;

class FieldStaffRetailerOrderController extends Controller
{
    use HandlesNotifications;

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
                        'product_name' => $item->product->product_name ?? 'N/A',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal
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
     *             @OA\Property(property="status", type="string", enum={"processing", "rejected", "cancelled"}),
     *             @OA\Property(property="cancellation_reason", type="string", description="Required if status is cancelled")
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
            'status' => 'required|in:processing,rejected,cancelled',
            'cancellation_reason' => 'required_if:status,cancelled|string|nullable'
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

        // Only the field staff who placed it can cancel it
        if ($request->status === RetailerOrder::STATUS_CANCELLED && $order->fieldstaff_id !== $fieldStaffId) {
            return response()->json(['error' => 'You can only cancel orders you placed.'], 403);
        }

        $order->status = $request->status;
        if ($request->status === RetailerOrder::STATUS_CANCELLED || $request->status === RetailerOrder::STATUS_REJECTED) {
            $order->cancellation_reason = $request->cancellation_reason;
        }
        $order->save();

        if ($this->clearOrderNotifications($order->id, 'retailer_order'));

        if ($order->status === RetailerOrder::STATUS_PROCESSING) {
            // Notify Sales Manager (or Admin if no SM)
            $salesManager = $user->fieldStaff->salesManager ?? null;
            if ($salesManager && $salesManager->user) {
                $this->notifyUnique($salesManager->user, new \App\Notifications\OrderActionRequired($order, "Retailer Order #{$order->order_code} assigned to your team is pending your processing.", url('/approvals/retailers'), 'retailer_order'));
            } else {
                $admins = \App\Models\User::role(['admin', 'superadmin'])->get();
                foreach ($admins as $admin) {
                    $this->notifyUnique($admin, new \App\Notifications\OrderActionRequired($order, "Retailer Order #{$order->order_code} is pending your processing.", url('/approvals/retailers'), 'retailer_order'));
                }
            }
        }

        return response()->json(['message' => 'Order status updated to ' . $order->status . '.']);
    }

    private function clearOrderNotifications($orderId, $type)
    {
        if (method_exists($this, 'deleteOrderNotifications')) {
            $this->deleteOrderNotifications($orderId, $type);
        }
    }
}
