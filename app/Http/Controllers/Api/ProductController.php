<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Traits\CalculatesPrices;

class ProductController extends Controller
{
    use CalculatesPrices;

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get detailed product info",
     *     description="Returns comprehensive details for a single product, including valid units and available variants.",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detailed product info",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="product_name", type="string"),
     *             @OA\Property(property="has_variants", type="boolean"),
     *             @OA\Property(property="available_variants", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="available_units", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="ptr", type="number"),
     *             @OA\Property(property="pts", type="number"),
     *             @OA\Property(property="gst", type="number"),
     *             @OA\Property(property="pack", type="string"),
     *             @OA\Property(property="generic_name", type="string")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);

        $availableVariants = $this->getAvailableVariants($product);
        $hasVariants = (bool)$product->has_variants || !empty($availableVariants);

        $availableUnits = $this->getAvailableUnits($product);

        return response()->json([
            'id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => $product->product_name,
            'generic_name' => $product->generic_name,
            'pack' => $product->pack,
            'has_variants' => $hasVariants,
            'available_variants' => $availableVariants,
            'available_units' => $availableUnits,
            'ptr' => (float)$product->ptr,
            'pts' => (float)$product->pts,
            'gst' => (float)$product->gst,
            'mrp' => (float)$product->mrp,
            'image' => $product->image ? asset('storage/' . $product->image) : null,
            'debug_fix_applied' => true,
        ]);
    }
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
     *     summary="Get distributors for a product based on availability and variant",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="side",
     *         in="query",
     *         required=false,
     *         description="Filter stock by side (e.g. Left, Right, Universal)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="size",
     *         in="query",
     *         required=false,
     *         description="Filter stock by size (e.g. S, M, L, XL, Universal)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="quantity",
     *         in="query",
     *         required=false,
     *         description="Filter distributors with stock greater than or equal to this quantity",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of distributors")
     * )
     */
    public function getDistributors(Request $request, Product $product)
    {
        $user = auth('api')->user();
        $retailer = $user ? $user->retailer : null;

        // Base query for distributors
        $query = Distributor::with('user');

        // Filter by retailer's district if available
        if ($retailer && $retailer->district_id) {
            $query->where('district_id', $retailer->district_id);
        }

        $allDistributors = $query->get();

        $side = $request->query('side');
        $size = $request->query('size');
        $minQuantity = (int)$request->query('quantity', 0);

        // Get current stock levels for this product
        $stockMap = DB::table('inventories')
            ->where('product_id', $product->id)
            ->when(!empty($side), function($q) use ($side) {
                return $q->where('side', $side);
            })
            ->when(!empty($size), function($q) use ($size) {
                return $q->where('size', $size);
            })
            ->selectRaw('distributor_id, SUM(stock) as total_stock')
            ->groupBy('distributor_id')
            ->having('total_stock', '>=', $minQuantity)
            ->pluck('total_stock', 'distributor_id');

        // Map and filter (Only those with enough stock)
        $distributors = $allDistributors->filter(function ($distributor) use ($stockMap) {
            return $stockMap->has($distributor->id);
        })->map(function ($distributor) use ($stockMap) {
            $distributor->pivot = (object)[
                'stock' => $stockMap[$distributor->id]
            ];
            return $distributor;
        });

        // Sort by stock (descending) since distance is removed
        $distributors = $distributors->sortByDesc(function ($d) {
            return $d->pivot->stock ?? 0;
        })->values();

        // Determine the unit (if it has strip_size, it's likely a medicine)
        $unit = (!empty($product->strip_size) || (!empty($product->units_per_strip) && $product->units_per_strip > 1)) ? ' Strips' : ' Nos';

        // Final cleanup: Remove unwanted fields and relations (like 'user')
        $formatted = $distributors->map(function ($d) use ($unit) {
            return [
                'id' => $d->id,
                'user_id' => $d->user_id,
                'sales_manager_id' => $d->sales_manager_id,
                'name' => $d->user->name ?? $d->name,
                'gst' => $d->gst,
                'drug_license_no' => $d->drug_license_no,
                'contact_no' => $d->contact_no,
                'address' => $d->address,
                'pincode' => $d->pincode,
                'latitude' => $d->latitude,
                'longitude' => $d->longitude,
                'district_id' => $d->district_id,
                'area_id' => $d->area_id,
                'created_at' => $d->created_at,
                'updated_at' => $d->updated_at,
                'stock' => ($d->pivot->stock ?? 0) . $unit,
            ];
        });

        return response()->json($formatted);
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
