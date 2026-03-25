<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
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

        return response()->json($inventory);
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

        return response()->json($inventory);
    }

    /**
     * @OA\Post(
     *     path="/api/inventory",
     *     summary="Add product to inventory",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="distributor_id", type="integer", nullable=true),
     *             @OA\Property(property="stock", type="number"),
     *             @OA\Property(property="unit", type="string", enum={"Nos", "Strips", "Box", "Carton"}),
     *             @OA\Property(property="variant", type="string", nullable=true),
     *             @OA\Property(property="batch_no", type="string"),
     *             @OA\Property(property="expiry_date", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Inventory updated")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'distributor_id' => 'nullable|exists:distributors,id',
            'stock' => 'required|numeric|min:0',
            'unit' => 'nullable|string',
            'variant' => 'nullable|string',
            'batch_no' => 'required|string|max:255',
            'expiry_date' => 'required|date',
        ]);

        $product = \App\Models\Product::findOrFail($request->product_id);
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

        $unit = $request->unit ?? 'Strip';
        $qtyInput = (float)$request->stock;

        // Conversion Logic
        $multiplier = 1;
        $normalizedUnit = strtolower($unit);
        if ($normalizedUnit === 'box') {
            $multiplier = (int)($product->strips_per_box ?? 1);
        } elseif ($normalizedUnit === 'carton') {
            $multiplier = (int)($product->boxes_per_carton ?? 1) * (int)($product->strips_per_box ?? 1);
        } elseif ($normalizedUnit === 'nos' || $normalizedUnit === 'no' || $normalizedUnit === 'unit') {
            $multiplier = 1 / (max(1, (int)($product->units_per_strip ?? 1)));
        }

        $finalStockAdd = ceil($qtyInput * $multiplier);

        DB::beginTransaction();
        try {
            $inventory = Inventory::where('product_id', $product->id)
                ->where('distributor_id', $distributorId)
                ->where('batch_no', $request->batch_no)
                ->where('variant', $request->variant)
                ->where('expiry_date', $request->expiry_date)
                ->first();

            if ($inventory) {
                $previousStock = $inventory->stock;
                $inventory->stock += $finalStockAdd;
                $inventory->save();
            } else {
                $previousStock = 0;
                $inventory = Inventory::create([
                    'distributor_product_code' => $product->product_code ?? 'NA-' . $product->id,
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'distributor_id' => $distributorId,
                    'stock' => $finalStockAdd,
                    'batch_no' => $request->batch_no,
                    'variant' => $request->variant,
                    'expiry_date' => $request->expiry_date,
                ]);
            }

            // Record History
            \App\Models\StockHistory::create([
                'inventory_id' => $inventory->id,
                'user_id' => $user->id,
                'previous_stock' => $previousStock,
                'new_stock' => $inventory->stock,
                'quantity_change' => $finalStockAdd,
                'change_type' => 'manual_api',
                'remarks' => 'Updated via API. Unit: ' . $unit . ', Qty: ' . $qtyInput
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Inventory updated successfully',
                'inventory' => $inventory
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
