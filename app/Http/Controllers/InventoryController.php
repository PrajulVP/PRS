<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\StockHistory;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function adjustStock(Request $request, Inventory $inventory)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'operation' => 'required|in:add,subtract'
        ]);

        if ($request->operation === 'add') {
            $change = $request->quantity;
        } else {
            if ($inventory->stock < $request->quantity) {
                if ($request->ajax()) return response()->json(['error' => 'Insufficient stock'], 400);
                return back()->with('error', 'Insufficient stock');
            }
            $change = -$request->quantity;
        }

        $previousStock = $inventory->stock;
        $inventory->stock += $change;
        $inventory->save();

        // Record History
        StockHistory::create([
            'inventory_id' => $inventory->id,
            'user_id' => Auth::id(),
            'previous_stock' => $previousStock,
            'new_stock' => $inventory->stock,
            'quantity_change' => $change,
            'change_type' => 'manual_adjustment',
            'remarks' => $request->operation . ' ' . $request->quantity
        ]);

        if ($request->ajax()) return response()->json(['success' => 'Stock updated successfully']);
        return back()->with('success', 'Stock updated');
    }

    public function index(Request $request)
    {
        // Treat DataTables requests as AJAX or when 'draw' param is present
        if ($request->ajax() || $request->has('draw') || $request->expectsJson()) {
            try {
                $query = Inventory::with('product');

                if ($request->has('search') && !empty($request->input('search')['value'])) {
                    $searchValue = $request->input('search')['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('distributor_product_code', 'like', "%{$searchValue}%")
                            ->orWhere('product_name', 'like', "%{$searchValue}%");
                    });
                }

                $totalData = Inventory::count(); // total before filtering
                $totalFiltered = $query->count();

                // ordering
                if ($request->has('order') && !empty($request->input('order'))) {
                    $columnIndex = $request->input('order')[0]['column'];
                    $columnName = $request->input('columns')[$columnIndex]['data'];
                    $sortDirection = $request->input('order')[0]['dir'];
                    $query->orderBy($columnName, $sortDirection);
                } else {
                    $query->orderBy('id', 'desc');
                }

                $start = intval($request->input('start', 0));
                $length = intval($request->input('length', 10));
                $items = $query->offset($start)->limit($length)->get();

                $formatted = $items->map(function ($i) {
                    return [
                        'id' => $i->id,
                        'distributor_product_code' => $i->distributor_product_code,
                        'product_name' => $i->product_name,
                        'stock' => (int) $i->stock,
                        'image' => $i->product && $i->product->image ? asset('storage/' . $i->product->image) : asset('admin/assets/images/dashboard/product-1.png'), // Placeholder
                        'product_details' => $i->product ? [
                            'generic_name' => $i->product->generic_name,
                            'pack' => $i->product->pack,
                            'mrp' => $i->product->mrp,
                            'ptr' => $i->product->ptr,
                            'gst' => $i->product->gst,
                            'hsn_code' => $i->product->hsn_code,
                            'box_size' => $i->product->box_size,
                            'description' => $i->product->description // Assuming description exists or null
                        ] : null
                    ];
                });

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalData,
                    'recordsFiltered' => $totalFiltered,
                    'data' => $formatted,
                ]);
            } catch (\Exception $e) {
                \Log::error('Inventory index error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                return response()->json(['error' => 'Server error while fetching inventories.'], 500);
            }
        }

        // Non-AJAX view: pass products to populate the create form
        $products = Product::select('id', 'product_name', 'product_code')->orderBy('product_name')->get();

        return view('admin.inventories.index', compact('products'));
    }

    public function create()
    {
        return view('admin.inventories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::find($request->product_id);

        // If user wants simplified flow where they just select product, we can use product_code as dist_prod_code
        // OR we can make dist_prod_code nullable in table (if not strict). But assuming table schema has it set.
        // User said: "so when adding the inventory we just select the product, no distributor code is entered"
        // This likely means we should auto-fill it with product_code or remove it if not needed.
        // Let's use product_code for now to satisfy unique constraint if that's the intention.

        $inventory = Inventory::create([
            'distributor_product_code' => $product->product_code, // Use actual product code
            'product_id' => $product->id,
            'product_name' => $product->product_name,
            'distributor_id' => $request->distributor_id,
            'stock' => $request->stock,
        ]);

        if ($request->stock > 0) {
            StockHistory::create([
                'inventory_id' => $inventory->id,
                'user_id' => Auth::id(),
                'previous_stock' => 0,
                'new_stock' => $request->stock,
                'quantity_change' => $request->stock,
                'change_type' => 'initial_stock',
                'remarks' => 'Initial stock on creation'
            ]);
        }

        return redirect()->route('inventories.index')->with('success', 'Inventory item created successfully.');
    }

    public function show(Inventory $inventory)
    {
        return view('admin.inventories.show', compact('inventory'));
    }

    public function edit(Inventory $inventory)
    {
        return view('admin.inventories.edit', compact('inventory'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $previousStock = $inventory->stock;
        $newStock = $request->stock;
        $quantityChange = $newStock - $previousStock;

        $inventory->update($request->only(['distributor_product_code', 'product_name', 'product_id', 'distributor_id', 'stock']));

        if ($quantityChange !== 0) {
            StockHistory::create([
                'inventory_id' => $inventory->id,
                'user_id' => Auth::id(),
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'quantity_change' => $quantityChange,
                'change_type' => 'manual_edit',
                'remarks' => 'Updated via edit form'
            ]);
        }

        return redirect()->route('inventories.index')->with('success', 'Inventory updated successfully.');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventories.index')->with('success', 'Inventory deleted successfully.');
    }
}
