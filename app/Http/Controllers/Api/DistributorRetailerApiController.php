<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Retailer;
use App\Models\RetailerOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DistributorRetailerApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/distributor/retailers",
     *     summary="List all retailers assigned to the logged-in distributor",
     *     tags={"Distributor Retailers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Results per page (default 15)", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of retailers",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="shop_name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="total_orders", type="integer"),
     *                 @OA\Property(property="total_revenue", type="string"),
     *                 @OA\Property(property="top_product", type="string")
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
        $perPage = (int) $request->get('per_page', 15);

        // Fetch paginated retailers who have placed orders with this distributor
        $retailers = Retailer::with('user')
            ->whereHas('retailerOrders', function ($orderQuery) use ($distributorId) {
                $orderQuery->where('distributor_id', $distributorId);
            })
            ->latest()
            ->paginate($perPage);

        // Map over retailers to calculate stats and top products dynamically
        $data = $retailers->map(function ($retailer) use ($distributorId) {
            // Stats
            $stats = RetailerOrder::select(
                DB::raw('COUNT(id) as total_orders'),
                DB::raw('SUM(total_amount) as total_revenue')
            )
                ->where('retailer_id', $retailer->id)
                ->where('distributor_id', $distributorId)
                ->first();

            // Find their most ordered product from this distributor
            $topProduct = DB::table('retailer_order_items')
                ->join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
                ->join('products', 'retailer_order_items.product_id', '=', 'products.id')
                ->where('retailer_orders.retailer_id', $retailer->id)
                ->where('retailer_orders.distributor_id', $distributorId)
                ->select('products.product_name', DB::raw('SUM(retailer_order_items.quantity) as total_qty'))
                ->groupBy('products.product_name')
                ->orderByDesc('total_qty')
                ->first();

            return [
                'id'            => $retailer->id,
                'name'          => $retailer->user?->name ?? 'N/A',
                'shop_name'     => $retailer->shop_name ?? 'N/A',
                'email'         => $retailer->user?->email ?? 'N/A',
                'phone'         => $retailer->user?->phone ?? 'N/A',
                'address'       => $retailer->address ?? 'N/A',
                // Additional stats optionally appended
                'total_orders'  => $stats->total_orders ?? 0,
                'total_revenue' => number_format($stats->total_revenue ?? 0, 2),
                'top_product'   => $topProduct ? $topProduct->product_name : 'No orders yet',
            ];
        });

        return response()->json([
            'data'         => $data,
            'current_page' => $retailers->currentPage(),
            'per_page'     => $retailers->perPage(),
            'total'        => $retailers->total(),
            'last_page'    => $retailers->lastPage(),
        ]);
    }
}
