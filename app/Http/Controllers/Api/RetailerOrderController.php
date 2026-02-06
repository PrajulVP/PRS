<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RetailerOrder;
use Illuminate\Http\Request;

class RetailerOrderController extends Controller
{
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

        $orders = RetailerOrder::where('retailer_id', $user->retailer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/retailer-orders/{id}/products",
     *     summary="Get products in a retailer order",
     *     tags={"Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Retailer Order ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="order_code", type="string", example="RO-X9Y8Z7"),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=10),
     *                     @OA\Property(property="product_name", type="string", example="Paracetamol 500mg"),
     *                     @OA\Property(property="quantity", type="integer", example=5),
     *                     @OA\Property(property="unit_price", type="string", example="10.00"),
     *                     @OA\Property(property="total_amount", type="string", example="50.00"),
     *                     @OA\Property(property="product_details", type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function getOrderItems($id)
    {
        $order = RetailerOrder::with('items.product')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $products = $order->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->product_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_amount' => $item->total_amount,
                'product_details' => $item->product
            ];
        });

        return response()->json([
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'products' => $products
        ]);
    }
}
