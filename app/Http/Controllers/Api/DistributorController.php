<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DistributorController extends Controller
{


    /**
     * @OA\Get(
     *     path="/api/distributors/{distributorId}/products/{productId}/availability",
     *     summary="Check product availability in a distributor",
     *     tags={"Distributors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="distributorId",
     *         in="path",
     *         description="Distributor ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="side",
     *         in="query",
     *         description="Filter stock by specific side variant (e.g. Left/Right)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="size",
     *         in="query",
     *         description="Filter stock by specific size variant (e.g. M/L/XL)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product availability status",
     *         @OA\JsonContent(
     *             @OA\Property(property="distributor_id", type="integer", example=1),
     *             @OA\Property(property="product_id", type="integer", example=10),
     *             @OA\Property(property="available", type="boolean", example=true),
     *             @OA\Property(property="stock", type="integer", example=50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Distributor or Product not found"
     *     )
     * )
     */
    public function checkProductAvailability(Request $request, $distributorId, $productId)
    {
        $distributor = Distributor::find($distributorId);
        if (!$distributor) {
            return response()->json(['message' => 'Distributor not found'], 404);
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $side = $request->query('side');
        $size = $request->query('size');

        // Check inventory
        $query = DB::table('inventories')
            ->where('distributor_id', $distributorId)
            ->where('product_id', $productId);

        if (!empty($side)) {
            $query->where(function($q) use ($side) {
                $q->where('side', $side)->orWhereNull('side')->orWhere('side', '');
            });
        }
        
        if (!empty($size)) {
            $query->where(function($q) use ($size) {
                $q->where('size', $size)->orWhereNull('size')->orWhere('size', '');
            });
        }

        $totalStock = $query->sum('stock');

        if ($totalStock > 0) {
            return response()->json([
                'distributor_id' => (int)$distributorId,
                'product_id' => (int)$productId,
                'available' => true,
                'stock' => $totalStock
            ]);
        }

        return response()->json([
            'distributor_id' => (int)$distributorId,
            'product_id' => (int)$productId,
            'available' => false,
            'stock' => null
        ]);
    }
}
