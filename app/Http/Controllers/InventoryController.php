<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\StockHistory;
use Illuminate\Support\Facades\Auth;
use App\Models\Distributor;

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
                $query = Inventory::with(['product', 'distributor.user'])
                    ->selectRaw('MAX(id) as id, product_id, distributor_id, SUM(stock) as stock, MAX(distributor_product_code) as distributor_product_code, MAX(product_name) as product_name')
                    ->groupBy('product_id', 'distributor_id');

                if (Auth::user()->hasRole('distributor')) {
                    $distributor = Auth::user()->distributor;
                    if ($distributor) {
                        $query->where('distributor_id', $distributor->id);
                    } else {
                        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
                    }
                }

                if ($request->has('search') && !empty($request->input('search')['value'])) {
                    $searchValue = $request->input('search')['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('distributor_product_code', 'like', "%{$searchValue}%")
                            ->orWhere('product_name', 'like', "%{$searchValue}%");
                    });
                }

                // Correct totals for grouped data
                $totalDataQuery = Inventory::query();
                if (Auth::user()->hasRole('distributor')) {
                    $totalDataQuery->where('distributor_id', Auth::user()->distributor->id);
                }
                $totalData = $totalDataQuery->select('product_id', 'distributor_id')->groupBy('product_id', 'distributor_id')->get()->count();

                $totalFiltered = (clone $query)->get()->count();

                // ordering
                if ($request->has('order') && !empty($request->input('order'))) {
                    $columnIndex = $request->input('order')[0]['column'];
                    $columnName = $request->input('columns')[$columnIndex]['data'];
                    $sortDirection = $request->input('order')[0]['dir'];
                    // Use a raw order if it's the stock column to ensure SUM(stock) is ordered correctly
                    if ($columnName === 'stock') {
                        $query->orderByRaw("SUM(stock) $sortDirection");
                    } else {
                        $query->orderBy($columnName, $sortDirection);
                    }
                } else {
                    $query->orderBy('id', 'desc');
                }

                $start = intval($request->input('start', 0));
                $length = intval($request->input('length', 10));

                // If length is -1, fetch all
                if ($length == -1) {
                    $items = $query->get();
                } else {
                    $items = $query->offset($start)->limit($length)->get();
                }

                $formatted = $items->map(function ($i) {
                    return [
                        'id' => $i->id,
                        'distributor_product_code' => $i->distributor_product_code,
                        'product_name' => $i->product_name,
                        'distributor_name' => $i->distributor?->user?->name ?? 'N/A',
                        'stock' => (int) $i->stock,
                        'image' => $i->product && $i->product->image ? asset('storage/' . $i->product->image) : asset('admin/assets/images/dashboard/product-1.png'), // Placeholder
                        'product_details' => $i->product ? [
                            'generic_name' => $i->product->generic_name,
                            'pack' => $i->product->pack,
                            'mrp' => $i->product->mrp,
                            'ptr' => $i->product->ptr,
                            'gst' => $i->product->gst,
                            'hsn_code' => $i->product->hsn_code,
                            'strip_size' => $i->product->strip_size,
                            'box_size' => $i->product->box_size,
                            'carton_size' => $i->product->carton_size,
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
                Log::error('Inventory index error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                return response()->json(['error' => 'Server error while fetching inventories.'], 500);
            }
        }

        // Non-AJAX view: pass products to populate the create form
        $products = Product::select('id', 'product_name', 'product_code', 'box_size', 'carton_size', 'strip_size')->orderBy('product_name')->get();
        $distributors = [];
        if (Auth::user()->hasRole(['admin', 'superadmin', 'manager'])) {
            $distributors = Distributor::with('user')->get();
        }

        return view('admin.inventories.index', compact('products', 'distributors'));
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

        $distributorId = null;
        if (Auth::user()->hasRole('distributor')) {
            $distributorId = Auth::user()->distributor->id;
        } else {
            $request->validate(['distributor_id' => 'required|exists:distributors,id']);
            $distributorId = $request->distributor_id;
        }

        $inventory = Inventory::where('product_id', $product->id)
            ->where('distributor_id', $distributorId)
            ->first();

        if ($inventory) {
            $previousStock = $inventory->stock;
            $inventory->stock += $request->stock;
            $inventory->save();

            $changeType = 'restock';
            $remarks = 'Restocked via inventory form';
        } else {
            $previousStock = 0;
            $inventory = Inventory::create([
                'distributor_product_code' => $product->product_code,
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'distributor_id' => $distributorId,
                'stock' => $request->stock,
            ]);
            $changeType = 'initial_stock';
            $remarks = 'Initial stock on creation';
        }

        if ($request->stock > 0) {
            StockHistory::create([
                'inventory_id' => $inventory->id,
                'user_id' => Auth::id(),
                'previous_stock' => $previousStock,
                'new_stock' => $inventory->stock,
                'quantity_change' => $request->stock,
                'change_type' => $changeType,
                'remarks' => $remarks
            ]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => 'Inventory updated successfully.']);
        }

        return redirect()->route('inventories.index')->with('success', 'Inventory updated successfully.');
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

        if ($request->ajax()) {
            return response()->json(['success' => 'Inventory updated successfully.']);
        }

        return redirect()->route('inventories.index')->with('success', 'Inventory updated successfully.');
    }

    public function destroy(Request $request, Inventory $inventory)
    {
        $inventory->delete();

        if ($request->ajax()) {
            return response()->json(['success' => 'Inventory deleted successfully.']);
        }

        return redirect()->route('inventories.index')->with('success', 'Inventory deleted successfully.');
    }
}
