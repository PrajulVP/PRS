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
use App\Traits\ManagesInventory;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    use ManagesInventory;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            /** @var \App\Models\User $user */
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user->hasAnyRole(['admin', 'superadmin', 'salesmanager', 'distributor'])) {
                return $next($request);
            }
            if (!$user->hasPermissionToCategory('inventories', 'view')) {
                abort(403, 'Unauthorized action. You do not have permission to view inventory.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function adjustStock(Request $request, Inventory $inventory)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string',
            'operation' => 'required|in:add,subtract'
        ]);

        $result = $this->adjustInventoryStock(
            $inventory->distributor_id,
            $inventory->product,
            [
                'quantity' => $request->quantity,
                'unit' => $request->unit ?? 'Strips',
                'batch_no' => $inventory->batch_no,
                'expiry_date' => $inventory->expiry_date,
                'variant' => $inventory->variant,
                'operation' => $request->operation
            ]
        );

        if (!$result['success']) {
            if ($request->ajax()) return response()->json(['error' => $result['message']], 400);
            return back()->with('error', $result['message']);
        }

        if ($request->ajax()) return response()->json(['success' => 'Stock updated.']);
        return back()->with('success', 'Stock updated');
    }

    public function index(Request $request)
    {
        // Treat DataTables requests as AJAX or when 'draw' param is present
        if ($request->ajax() || $request->has('draw') || $request->expectsJson()) {
            try {
                $query = Inventory::with(['product', 'distributor.user']);

                /** @var \App\Models\User $authUser */
                $authUser = Auth::user();
                if ($authUser->hasRole('distributor')) {
                    $distributor = $authUser->distributor;
                    if ($distributor) {
                        $query->where('distributor_id', $distributor->id);
                    } else {
                        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
                    }
                }

                if ($request->has('distributor_id') && !empty($request->input('distributor_id'))) {
                    $query->where('distributor_id', $request->input('distributor_id'));
                }

                if ($request->has('product_id') && !empty($request->input('product_id'))) {
                    $query->where('product_id', $request->input('product_id'));
                }


                if ($request->has('search') && !empty($request->input('search')['value'])) {
                    $searchValue = $request->input('search')['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('distributor_product_code', 'like', "%{$searchValue}%")
                            ->orWhere('product_name', 'like', "%{$searchValue}%");
                    });
                }

                // Add Distributor Filter
                if ($request->has('distributor_id') && !empty($request->input('distributor_id'))) {
                    $query->where('distributor_id', $request->input('distributor_id'));
                }

                // Correct totals for grouped data
                $totalData = Inventory::count();

                $totalFiltered = (clone $query)->get()->count();

                // ordering
                if ($request->has('order') && !empty($request->input('order'))) {
                    $columnIndex = $request->input('order')[0]['column'];
                    $columnName = $request->input('columns')[$columnIndex]['data'];
                    $sortDirection = $request->input('order')[0]['dir'];
                    // Use a raw order if it's the stock column to ensure SUM(stock) is ordered correctly
                    if ($columnName === 'stock') {
                        $query->orderByRaw("SUM(stock) $sortDirection");
                    } elseif (!empty($columnName)) {
                        $query->orderBy($columnName, $sortDirection);
                    } else {
                        $query->orderBy('updated_at', 'desc');
                    }
                } else {
                    $query->orderBy('updated_at', 'desc');
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
                        'product_id' => $i->product_id,
                        'distributor_id' => $i->distributor_id,
                        'distributor_name' => $i->distributor?->user?->name ?? 'N/A',
                        'stock' => (int) $i->stock,
                        'variant' => $i->variant,
                        'updated_at' => $i->updated_at?->toIso8601String(),
                        'image' => $i->product && $i->product->image ? \Illuminate\Support\Facades\Storage::disk('public')->url($i->product->image) : asset('admin/assets/images/dashboard/product-1.png'), // Placeholder
                        'batch_no' => $i->batch_no ?? '-',
                        'raw_expiry_date' => $i->expiry_date,
                        'expiry_date' => $i->expiry_date ? (function ($date) {
                            $parsed = \Carbon\Carbon::parse($date);
                            if ($parsed->copy()->endOfMonth()->isSameDay($parsed)) {
                                return $parsed->format('m-Y');
                            }
                            return $parsed->format('d-m-Y');
                        })($i->expiry_date) : '-',
                        'product_details' => $i->product ? [
                            'generic_name' => $i->product->generic_name,
                            'pack' => $i->product->pack,
                            'mrp' => $i->product->mrp,
                            'ptr' => $i->product->ptr,
                            'gst' => $i->product->gst,
                            'hsn_code' => $i->product->hsn_code,
                            'box_size' => $i->product->box_size,
                            'strips_per_box' => $i->product->strips_per_box,
                            'boxes_per_carton' => $i->product->boxes_per_carton,
                            'units_per_strip' => $i->product->units_per_strip,
                            'has_variants' => (bool)$i->product->has_variants,
                            'description' => $i->product->description
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
        $products = Product::select('id', 'product_name', 'product_code', 'box_size', 'carton_size', 'units_per_strip', 'strips_per_box', 'boxes_per_carton', 'has_variants')
            ->orderBy('product_name')
            ->get();
        $distributors = [];
        /** @var \App\Models\User $authUserFiltered */
        $authUserFiltered = Auth::user();
        if ($authUserFiltered->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
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
            'stock' => 'required|numeric|min:0',
            'unit' => 'nullable|string',
            'variant' => 'nullable|string',
            'batch_no' => 'required|string|max:255',
            'expiry_date' => 'required|date',
        ]);

        $product = Product::find($request->product_id);

        // If user wants simplified flow where they just select product, we can use product_code as dist_prod_code
        // OR we can make dist_prod_code nullable in table (if not strict). But assuming table schema has it set.
        // User said: "so when adding the inventory we just select the product, no distributor code is entered"
        // This likely means we should auto-fill it with product_code or remove it if not needed.
        // Let's use product_code for now to satisfy unique constraint if that's the intention.

        /** @var \App\Models\User $authUserStore */
        $authUserStore = Auth::user();
        $distributorId = null;
        if ($authUserStore->hasRole('distributor')) {
            $distributorId = $authUserStore->distributor->id;
        } else {
            $request->validate(['distributor_id' => 'required|exists:distributors,id']);
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
                'operation' => 'add'
            ]
        );

        if (!$result['success']) {
            if ($request->ajax()) return response()->json(['error' => $result['message']], 400);
            return back()->with('error', $result['message']);
        }

        if ($request->ajax()) {
            return response()->json(['success' => 'Inventory updated.']);
        }

        return redirect()->route('inventories.index')->with('success', 'Inventory updated.');
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
            'stock' => 'required|numeric|min:0',
            'unit' => 'nullable|string',
            'batch_no' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
        ]);

        $previousStock = $inventory->stock;
        
        // Convert the input stock to strips based on the selected unit
        $newStock = $this->convertQuantityToStrips($inventory->product, $request->stock, $request->unit ?? 'Strips');
        
        $quantityChange = $newStock - $previousStock;

        $updateData = $request->only(['distributor_product_code', 'product_name', 'product_id', 'distributor_id', 'batch_no', 'expiry_date']);
        $updateData['stock'] = $newStock;

        // Remove empty strings so we don't accidentally wipe foreign keys if they are structurally omitted from a form
        $updateData = array_filter($updateData, function ($value) {
            return $value !== null && $value !== '';
        });

        $inventory->update($updateData);

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
            return response()->json(['success' => 'Inventory updated.']);
        }

        return redirect()->route('inventories.index')->with('success', 'Inventory updated.');
    }

    public function destroy(Request $request, Inventory $inventory)
    {
        $inventory->delete();

        if ($request->ajax()) {
            return response()->json(['success' => 'Inventory deleted.']);
        }

        return redirect()->route('inventories.index')->with('success', 'Inventory deleted.');
    }
}
