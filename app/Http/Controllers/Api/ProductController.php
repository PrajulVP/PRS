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
     *             @OA\Property(property="brand", type="string"),
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
            'brand' => $product->brand,
            'generic_name' => $product->generic_name,
            'pack' => $product->pack,
            'has_variants' => $hasVariants,
            'available_variants' => $availableVariants,
            'available_units' => $availableUnits,
            'ptr' => (float)$product->ptr,
            'pts' => (float)$product->pts,
            'gst' => (float)$product->gst,
            'mrp' => (float)$product->mrp,
            'is_free' => (bool)$product->is_free_eligible,
            'free_item_threshold' => $product->free_qty_buy,
            'free_item_quantity' => $product->free_qty_get,
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
     *                 @OA\Property(property="brand", type="string", example="BrandName"),
     *                 @OA\Property(property="net_amount", type="string", example="18.00"),
     *                 @OA\Property(property="is_free", type="boolean", example=true),
     *                 @OA\Property(property="free_item_threshold", type="integer", nullable=true, example=10),
     *                 @OA\Property(property="free_item_quantity", type="integer", nullable=true, example=2)
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
        $products = Product::all()->map(function ($product) {
            $data = $product->toArray();
            
            // Map the free item attributes to match API requirements
            $data['is_free'] = (bool)$product->is_free_eligible;
            $data['free_item_threshold'] = $product->free_qty_buy;
            $data['free_item_quantity'] = $product->free_qty_get;
            
            // Add robust available units using CalculatesPrices trait
            $data['available_units'] = $this->getAvailableUnits($product);
            
            // Remove legacy internal field names from response
            unset($data['is_free_eligible']);
            unset($data['free_qty_buy']);
            unset($data['free_qty_get']);
            
            return $data;
        });
        
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
        $variant = $request->query('variant');
        $minQuantity = (int)$request->query('quantity', 0);

        // Fallback: If side/size are missing but variant is present, try to split or match it
        if ((empty($side) && empty($size)) && !empty($variant)) {
            // Check if variant matches a side (Left/Right/Universal)
            $upperVariant = strtoupper(trim($variant));
            if (in_array($upperVariant, ['LEFT', 'RIGHT', 'UNIVERSAL'])) {
                $side = $variant;
            } else {
                // Assume it's a size
                $size = $variant;
            }
        }

        // Get current stock levels for this product
        $stockMap = DB::table('inventories')
            ->where('product_id', $product->id)
            ->when(!empty($side), function($q) use ($side) {
                return $q->where(function($sq) use ($side) {
                    $sq->where('side', $side)
                       ->when(strtoupper($side) === 'UNIVERSAL', function($ssq) {
                           return $ssq->orWhereNull('side');
                       });
                });
            })
            ->when(!empty($size), function($q) use ($size) {
                return $q->where(function($sq) use ($size) {
                    $sq->where('size', $size)
                       ->when(strtoupper($size) === 'UNIVERSAL', function($ssq) {
                           return $ssq->orWhereNull('size');
                       });
                });
            })
            ->selectRaw('distributor_id, SUM(stock) as total_stock')
            ->groupBy('distributor_id')
            ->having('total_stock', '>', 0)
            ->when($minQuantity > 0, function($q) use ($minQuantity) {
                return $q->having('total_stock', '>=', $minQuantity);
            })
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


        // Final cleanup: Remove unwanted fields and relations (like 'user')
        $formatted = $distributors->map(function ($d) {
            $stock = $d->pivot->stock ?? 0;
            // Format stock: remove trailing zeros if decimal is 0, and keep as string
            $formattedStock = (string)($stock == (int)$stock ? (int)$stock : (float)$stock);
            
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
                'created_at' => $d->created_at ? $d->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $d->updated_at ? $d->updated_at->format('Y-m-d H:i:s') : null,
                'stock' => $formattedStock,
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
