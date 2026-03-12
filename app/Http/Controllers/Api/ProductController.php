<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get all products",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_code", type="string", example="P001"),
     *                 @OA\Property(property="product_name", type="string", example="Paracetamol 500mg"),
     *                 @OA\Property(property="generic_name", type="string", example="Paracetamol"),
     *                 @OA\Property(property="strip_size", type="integer", example=10),
     *                 @OA\Property(property="box_size", type="integer", example=10),
     *                 @OA\Property(property="carton_size", type="integer", example=100),
     *                 @OA\Property(property="hsn_code", type="string", example="3004"),
     *                 @OA\Property(property="mrp", type="string", example="20.00"),
     *                 @OA\Property(property="ptr", type="string", example="15.00"),
     *                 @OA\Property(property="pts", type="string", example="12.00"),
     *                 @OA\Property(property="gst", type="string", example="12.00"),
     *                 @OA\Property(property="net_amount", type="string", example="18.00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{product}/distributors",
     *     summary="Get distributors for a product based on availability",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of distributors")
     * )
     */
    public function getDistributors(Request $request, Product $product)
    {
        $user = auth('api')->user();
        $retailer = $user ? $user->retailer : null;

        $allDistributors = Distributor::with('user')->get();

        $stockMap = DB::table('inventories')
            ->where('product_id', $product->id)
            ->selectRaw('distributor_id, SUM(stock) as total_stock')
            ->groupBy('distributor_id')
            ->pluck('total_stock', 'distributor_id');

        $distributors = $allDistributors->filter(function ($distributor) use ($stockMap) {
            return $stockMap->has($distributor->id);
        })->map(function ($distributor) use ($retailer, $stockMap) {
            $distributor->pivot = (object)[
                'stock' => $stockMap[$distributor->id]
            ];

            if ($retailer && $retailer->latitude && $retailer->longitude && $distributor->latitude && $distributor->longitude) {
                $distributor->distance = $this->calculateDistance(
                    (float)$retailer->latitude,
                    (float)$retailer->longitude,
                    (float)$distributor->latitude,
                    (float)$distributor->longitude
                );
            } else {
                $distributor->distance = null;
            }
            return $distributor;
        });

        $distributors = $distributors->sort(function ($a, $b) {
            $distA = $a->distance ?? 999999;
            $distB = $b->distance ?? 999999;
            if ($distA != $distB) {
                return $distA <=> $distB;
            }
            $stockA = $a->pivot->stock ?? 0;
            $stockB = $b->pivot->stock ?? 0;
            return $stockB <=> $stockA;
        })->values();

        return response()->json([
            'product' => $product,
            'distributors' => $distributors
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
