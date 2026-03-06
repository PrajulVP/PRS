<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RetailerOrder;
use Illuminate\Http\Request;

class DistributorRetailerOrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/distributor/retailer-orders",
     *     summary="List retailer orders placed to the authenticated distributor",
     *     tags={"Distributor Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="retailer_id", in="query", required=false, description="Filter by retailer ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", required=false, description="Filter by order status (pending, processing, accepted, delivered, cancelled)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="payment_status", in="query", required=false, description="Filter by payment status: paid or pending (unpaid)", @OA\Schema(type="string", enum={"paid","pending"})),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Results per page (default 15)", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of retailer orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="order_code", type="string"),
     *                 @OA\Property(property="retailer_id", type="integer"),
     *                 @OA\Property(property="retailer_name", type="string"),
     *                 @OA\Property(property="retailer_shop", type="string"),
     *                 @OA\Property(property="total_amount", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="payment_status", type="string"),
     *                 @OA\Property(property="placed_at", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Not a distributor")
     * )
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();

        if (!$user || !$user->distributor) {
            return response()->json(['message' => 'Authenticated user is not a distributor.'], 403);
        }

        $distributorId = $user->distributor->id;

        $query = RetailerOrder::with([
            'retailer.user',
            'fieldStaff.user',
            'items.product',
        ])
            ->where('distributor_id', $distributorId)
            ->latest('placed_at');

        // Optional: filter by retailer
        if ($request->filled('retailer_id')) {
            $query->where('retailer_id', (int) $request->retailer_id);
        }

        // Optional: filter by order status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Optional: filter by payment status
        if ($request->filled('payment_status')) {
            $ps = $request->payment_status;
            if ($ps === 'pending') {
                $query->where(function ($q) {
                    $q->where('payment_status', 'pending')->orWhereNull('payment_status');
                });
            } else {
                $query->where('payment_status', $ps);
            }
        }

        $perPage = (int) $request->get('per_page', 15);
        $orders  = $query->paginate($perPage);

        $data = $orders->map(function ($order) {
            return [
                'id'              => $order->id,
                'order_code'      => $order->order_code,
                'retailer_id'     => $order->retailer_id,
                'retailer_name'   => $order->retailer?->user?->name ?? 'N/A',
                'retailer_shop'   => $order->retailer?->shop_name ?? 'N/A',
                'field_staff'     => $order->fieldStaff?->user?->name ?? 'N/A',
                'total_amount'    => number_format($order->total_amount, 2),
                'total_items'     => $order->total_items,
                'total_quantity'  => $order->total_quantity,
                'status'          => $order->status,
                'payment_status'  => $order->payment_status ?? 'pending',
                'invoice_url'     => $order->invoice_path ? asset('storage/' . $order->invoice_path) : null,
                'placed_at'       => $order->placed_at?->format('Y-m-d H:i:s'),
                'delivered_at'    => $order->delivered_at?->format('Y-m-d H:i:s'),
                'notes'           => $order->notes,
                'delivery_notes'  => $order->delivery_notes,
                'items'           => $order->items->map(function ($item) {
                    return [
                        'product_id'   => $item->product_id,
                        'product_name' => $item->product?->product_name ?? 'N/A',
                        'quantity'     => $item->quantity,
                        'unit_price'   => $item->unit_price,
                        'total_amount' => $item->total_amount,
                    ];
                }),
            ];
        });

        return response()->json([
            'data'         => $data,
            'current_page' => $orders->currentPage(),
            'per_page'     => $orders->perPage(),
            'total'        => $orders->total(),
            'last_page'    => $orders->lastPage(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/distributor/retailer-orders/{id}",
     *     summary="Get a single retailer order detail (must belong to authenticated distributor)",
     *     tags={"Distributor Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Retailer Order ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Order detail with items"),
     *     @OA\Response(response=403, description="Not a distributor"),
     *     @OA\Response(response=404, description="Order not found or not yours")
     * )
     */
    public function show($id)
    {
        $user = auth('api')->user();

        if (!$user || !$user->distributor) {
            return response()->json(['message' => 'Authenticated user is not a distributor.'], 403);
        }

        $order = RetailerOrder::with(['retailer.user', 'fieldStaff.user', 'items.product'])
            ->where('id', $id)
            ->where('distributor_id', $user->distributor->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found or does not belong to you.'], 404);
        }

        return response()->json([
            'id'             => $order->id,
            'order_code'     => $order->order_code,
            'retailer_id'    => $order->retailer_id,
            'retailer_name'  => $order->retailer?->user?->name ?? 'N/A',
            'retailer_shop'  => $order->retailer?->shop_name ?? 'N/A',
            'field_staff'    => $order->fieldStaff?->user?->name ?? 'N/A',
            'total_amount'   => number_format($order->total_amount, 2),
            'total_items'    => $order->total_items,
            'total_quantity' => $order->total_quantity,
            'status'         => $order->status,
            'payment_status' => $order->payment_status ?? 'pending',
            'invoice_url'    => $order->invoice_path ? asset('storage/' . $order->invoice_path) : null,
            'placed_at'      => $order->placed_at?->format('Y-m-d H:i:s'),
            'delivered_at'   => $order->delivered_at?->format('Y-m-d H:i:s'),
            'notes'          => $order->notes,
            'delivery_notes' => $order->delivery_notes,
            'items'          => $order->items->map(function ($item) {
                return [
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product?->product_name ?? 'N/A',
                    'quantity'     => $item->quantity,
                    'unit_price'   => $item->unit_price,
                    'total_amount' => $item->total_amount,
                ];
            }),
        ]);
    }
}
