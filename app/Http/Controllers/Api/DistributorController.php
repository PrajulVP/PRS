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
    public function checkProductAvailability($distributorId, $productId)
    {
        $distributor = Distributor::find($distributorId);
        if (!$distributor) {
            return response()->json(['message' => 'Distributor not found'], 404);
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Check inventory
        $inventory = DB::table('inventories')
            ->where('distributor_id', $distributorId)
            ->where('product_id', $productId)
            ->first();

        if ($inventory) {
            return response()->json([
                'distributor_id' => (int)$distributorId,
                'product_id' => (int)$productId,
                'available' => $inventory->stock > 0,
                'stock' => null
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
