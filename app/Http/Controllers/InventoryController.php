<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Treat DataTables requests as AJAX or when 'draw' param is present
        if ($request->ajax() || $request->has('draw') || $request->expectsJson()) {
            try {
                $query = Inventory::query();

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
            'distributor_product_code' => 'required|string|unique:inventories,distributor_product_code|max:255',
            'product_id' => 'required|exists:products,id',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::find($request->product_id);

        Inventory::create([
            'distributor_product_code' => $request->distributor_product_code,
            'product_id' => $product->id,
            'product_name' => $product->product_name,
            'distributor_id' => $request->distributor_id,
            'stock' => $request->stock,
        ]);

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
            'distributor_product_code' => 'required|string|max:255|unique:inventories,distributor_product_code,' . $inventory->id,
            'product_name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
        ]);

        $inventory->update($request->only(['distributor_product_code', 'product_name', 'product_id', 'distributor_id', 'stock']));

        return redirect()->route('inventories.index')->with('success', 'Inventory updated successfully.');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventories.index')->with('success', 'Inventory deleted successfully.');
    }
}
