<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\ManagesInventory;
use App\Models\Product;

class InventoryController extends Controller
{
    use ManagesInventory;

    private function getStockDisplay($product, $stock)
    {
        if (!$product) return (string)$stock . ' Strips';
        
        $unitsPerStrip = (int)($product->units_per_strip ?? 1);
        $isNos = false;
        
        if ($unitsPerStrip === 1) {
            $pName = strtolower($product->product_name ?? '');
            $pPack = strtolower($product->pack ?? '');
            $boxSizeStr = strtolower((string)($product->box_size ?? ''));
            
            if (str_contains($pName, 'pair') || str_contains($pName, 'pc') || str_contains($pName, 'unit') ||
                str_contains($pPack, 'pair') || str_contains($pPack, 'pc') || str_contains($pPack, 'unit') ||
                str_contains($boxSizeStr, 'pair') || str_contains($boxSizeStr, 'pc') || str_contains($boxSizeStr, 'unit')) {
                $isNos = true;
            } else {
                $exactMatches = ['1', '1s', '1 pair', '1 pc', '1 unit'];
                if (in_array($pPack, $exactMatches) || in_array($boxSizeStr, $exactMatches)) {
                    $isNos = true;
                }
            }
        }
        
        $displayVal = $isNos ? round((float)$stock * $unitsPerStrip) : (float)$stock;
        $unitText = $isNos ? 'Nos' : 'Strips';
        
        return $displayVal . ' ' . $unitText;
    }

    private function formatVariant($item)
    {
        $side = $item->side && $item->side !== '-' && strcasecmp($item->side, 'N/A') !== 0 ? trim($item->side) : null;
        $size = $item->size && $item->size !== '-' && strcasecmp($item->size, 'N/A') !== 0 ? trim($item->size) : null;
        $parts = array_filter([$side, $size]);
        return empty($parts) ? null : implode(' / ', $parts);
    }

    /**
     * @OA\Get(
     *     path="/api/distributor/inventory",
     *     summary="Get distributor inventory (Unified Endpoint)",
     *     description="Returns inventory for the logged-in user. If a Sales Manager, can optionally filter by distributor_id.",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="distributor_id",
     *         in="query",
     *         description="Filter by specific distributor (Manager/Admin only)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by product name or code",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="List of inventory items")
     * )
     */
    public function index(Request $request)
    {
        $query = Inventory::with(['product']);
        $user = Auth::user();

        if ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            if ($distributor) {
                $query->where('distributor_id', $distributor->id);
            } else {
                return response()->json([]);
            }
        } elseif ($request->filled('distributor_id')) {
            $dId = $request->distributor_id;

            if ($user->hasRole('salesmanager')) {
                // Check if distributor belongs to manager
                $query->where('distributor_id', $dId)
                    ->whereHas('distributor', function ($q) use ($user) {
                        $q->where('sales_manager_id', $user->salesManager->id);
                    });
            } else if ($user->hasAnyRole(['admin', 'superadmin'])) {
                $query->where('distributor_id', $dId);
            }
        } elseif ($user->hasRole('salesmanager')) {
            // Manager viewing all their distributors by default
            $query->whereHas('distributor', function ($q) use ($user) {
                $q->where('sales_manager_id', $user->salesManager->id);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%");
                })->orWhere('distributor_product_code', 'like', "%{$search}%");
            });
        }

        $inventory = $query->orderBy('updated_at', 'desc')->get();

        $grouped = $inventory->groupBy(function ($item) {
            return $item->product_id . '_' . $item->distributor_id;
        })->map(function ($items) {
            $first = $items->first();
            $product = $first->product;
            
            $totalStock = $items->sum('stock');
            if ($product && $product->has_variants) {
                $hasRealVariant = false;
                $sumRealVariants = 0;
                foreach ($items as $item) {
                    $v = $this->formatVariant($item);
                    if (!empty($v)) {
                        $hasRealVariant = true;
                        $sumRealVariants += $item->stock;
                    }
                }
                if ($hasRealVariant) {
                    $totalStock = $sumRealVariants;
                }
            }
            
            $batches = $items->map(function ($item) use ($product) {
                return [
                    'id' => $item->id,
                    'batch_no' => $item->batch_no,
                    'expiry_date' => $item->expiry_date,
                    'stock' => $item->stock,
                    'stock_display' => $this->getStockDisplay($product, $item->stock),
                    'variant' => $this->formatVariant($item),
                    'updated_at' => $item->updated_at,
                ];
            })->values()->toArray();

            return [
                'id' => $first->id,
                'product_id' => $first->product_id,
                'distributor_id' => $first->distributor_id,
                'stock' => $totalStock,
                'stock_display' => $this->getStockDisplay($product, $totalStock),
                'distributor_product_code' => $first->distributor_product_code,
                'product' => $product,
                'batches' => $batches,
            ];
        })->values();

        return response()->json($grouped);
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/{id}",
     *     summary="Get specific inventory item",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Inventory ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inventory item details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="distributor_id", type="integer", example=1),
     *             @OA\Property(property="stock", type="integer", example=100),
     *             @OA\Property(property="stock_display", type="string", example="100 Strips"),
     *             @OA\Property(property="distributor_product_code", type="string", example="P001"),
     *             @OA\Property(property="product", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_name", type="string", example="Product Name"),
     *                 @OA\Property(property="product_code", type="string", example="P001"),
     *                 @OA\Property(property="generic_name", type="string", example="Generic Name"),
     *                 @OA\Property(property="mrp", type="string", example="100.00"),
     *                 @OA\Property(property="ptr", type="string", example="80.00"),
     *                 @OA\Property(property="pts", type="string", example="70.00"),
     *                 @OA\Property(property="gst", type="string", example="12")
     *             ),
     *             @OA\Property(property="batches", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=12),
     *                     @OA\Property(property="batch_no", type="string", example="XXXX"),
     *                     @OA\Property(property="expiry_date", type="string", format="date", example="2024-12-01"),
     *                     @OA\Property(property="stock", type="integer", example=10),
     *                     @OA\Property(property="stock_display", type="string", example="10 Strips"),
     *                     @OA\Property(property="variant", type="string", example="M", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inventory not found"
     *     )
     * )
     */
    public function show($id)
    {
        $inventory = Inventory::with(['product'])->find($id);

        if (!$inventory) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }

        if (Auth::user()->hasRole('distributor')) {
            $distributor = Auth::user()->distributor;
            if (!$distributor || $inventory->distributor_id !== $distributor->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $allItems = Inventory::where('product_id', $inventory->product_id)
            ->where('distributor_id', $inventory->distributor_id)
            ->orderBy('updated_at', 'desc')
            ->get();

        $totalStock = $allItems->sum('stock');
        $product = $inventory->product;
        
        if ($product && $product->has_variants) {
            $hasRealVariant = false;
            $sumRealVariants = 0;
            foreach ($allItems as $item) {
                $v = $this->formatVariant($item);
                if (!empty($v)) {
                    $hasRealVariant = true;
                    $sumRealVariants += $item->stock;
                }
            }
            if ($hasRealVariant) {
                $totalStock = $sumRealVariants;
            }
        }
        
        $batches = $allItems->map(function ($item) use ($product) {
            return [
                'id' => $item->id,
                'batch_no' => $item->batch_no,
                'expiry_date' => $item->expiry_date,
                'stock' => $item->stock,
                'stock_display' => $this->getStockDisplay($product, $item->stock),
                'variant' => $this->formatVariant($item),
                'updated_at' => $item->updated_at,
            ];
        })->values()->toArray();

        $response = [
            'id' => $inventory->id,
            'product_id' => $inventory->product_id,
            'distributor_id' => $inventory->distributor_id,
            'stock' => $totalStock,
            'stock_display' => $this->getStockDisplay($product, $totalStock),
            'distributor_product_code' => $inventory->distributor_product_code,
            'product' => $product,
            'batches' => $batches,
        ];

        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/api/inventory",
     *     summary="Adjust inventory stock (Add/Subtract)",
     *     description="Finds or creates an inventory record by batch/expiry/variant and adjusts the stock. Mirroring web logic.",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="distributor_id", type="integer", nullable=true, description="Required for non-distributor users"),
     *             @OA\Property(property="stock", type="number", description="Quantity to adjust"),
     *             @OA\Property(property="unit", type="string", enum={"Nos", "Strips", "Box", "Carton"}),
     *             @OA\Property(property="variant", type="string", nullable=true),
     *             @OA\Property(property="batch_no", type="string"),
     *             @OA\Property(property="expiry_date", type="string", format="date"),
     *             @OA\Property(property="operation", type="string", enum={"add", "subtract"}, default="add")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Inventory updated successfully"),
     *     @OA\Response(response=400, description="Invalid adjustment or insufficient stock")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'distributor_id' => 'nullable|exists:distributors,id',
            'stock' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string',
            'variant' => 'nullable|string',
            'batch_no' => 'required|string|max:255',
            'expiry_date' => 'required|date',
            'operation' => 'nullable|in:add,subtract'
        ]);

        $product = Product::findOrFail($request->product_id);
        
        if ($product->has_variants && empty($request->variant)) {
            return response()->json(['error' => 'This product has variants. You must specify a variant.'], 422);
        }
        $user = Auth::user();
        $distributorId = null;

        if ($user->hasRole('distributor')) {
            $distributorId = $user->distributor->id;
        } else {
            if (!$request->has('distributor_id')) {
                return response()->json(['error' => 'distributor_id is required'], 422);
            }
            $distributorId = $request->distributor_id;
        }

        $result = $this->adjustInventoryStock(
            $distributorId,
            $product,
            [
                'quantity' => $request->stock,
                'unit' => $request->unit ?? 'Strips',
                'batch_no' => $request->batch_no,
                'expiry_date' => $request->expiry_date,
                'variant' => $request->variant,
                'operation' => $request->operation ?? 'add'
            ]
        );

        if (!$result['success']) {
            return response()->json(['error' => $result['message']], 400);
        }

        return response()->json([
            'message' => $result['message'],
            'inventory' => $result['inventory']
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/inventory/{id}",
     *     summary="Full edit of an inventory record",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="stock", type="number", nullable=true),
     *             @OA\Property(property="unit", type="string", nullable=true),
     *             @OA\Property(property="batch_no", type="string", nullable=true),
     *             @OA\Property(property="expiry_date", type="string", format="date", nullable=true),
     *             @OA\Property(property="variant", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Record updated")
     * )
     */
    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);
        $user = Auth::user();

        if ($user->hasRole('distributor')) {
            if ($inventory->distributor_id !== $user->distributor->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        $request->validate([
            'stock' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string',
            'batch_no' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'variant' => 'nullable|string',
        ]);

        $previousStock = $inventory->stock;
        $updateData = $request->only(['batch_no', 'expiry_date', 'variant']);

        if ($inventory->product && $inventory->product->has_variants) {
            $checkVariant = $request->has('variant') ? $request->variant : $this->formatVariant($inventory);
            if ($request->has('variant') && empty($request->variant)) {
                if (empty($checkVariant)) {
                    return response()->json(['error' => 'This product has variants. You must specify a variant.'], 422);
                }
            }
        }

        if ($request->has('stock')) {
            $newStock = $this->convertQuantityToStrips($inventory->product, $request->stock, $request->unit ?? 'Strips');
            $updateData['stock'] = $newStock;

            if ($newStock !== $previousStock) {
                \App\Models\StockHistory::create([
                    'inventory_id' => $inventory->id,
                    'user_id' => $user->id,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'quantity_change' => $newStock - $previousStock,
                    'change_type' => 'manual_edit_api',
                    'remarks' => 'Updated via API Edit'
                ]);
            }
        }

        $inventory->update(array_filter($updateData));

        return response()->json([
            'message' => 'Inventory updated successfully',
            'inventory' => $inventory
        ]);
    }
}
