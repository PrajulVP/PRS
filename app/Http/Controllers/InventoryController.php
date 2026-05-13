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
use Illuminate\Support\Facades\DB;

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
                'side' => $inventory->side,
                'size' => $inventory->size,
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
                            ->orWhere('product_name', 'like', "%{$searchValue}%")
                            ->orWhereHas('product', function ($qp) use ($searchValue) {
                                $qp->where('brand', 'like', "%{$searchValue}%");
                            });
                    });
                }

                // Correct totals for grouped data
                $totalData = DB::table('inventories')
                    ->select('product_id', 'side', 'size')
                    ->groupBy('product_id', 'side', 'size')
                    ->get()
                    ->count();

                // Mandatory distributor selection for non-distributors
                $user = Auth::user();
                $roles = ['admin', 'superadmin', 'salesmanager'];
                $isAdminRole = in_array($user->role, $roles);
                
                if ($isAdminRole && empty($request->input('distributor_id'))) {
                    return response()->json([
                        'draw' => intval($request->input('draw')),
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => [],
                        'message' => 'Please select a distributor to view inventory'
                    ]);
                }

                $totalFiltered = (clone $query)->select('product_id', 'side', 'size')
                    ->groupBy('product_id', 'side', 'size')
                    ->get()
                    ->count();

                // Select aggregated columns
                $query->select(
                    'product_id',
                    DB::raw('MAX(product_name) as product_name'),
                    DB::raw('SUM(stock) as stock'),
                    DB::raw('MAX(id) as id'),
                    DB::raw('MAX(distributor_id) as distributor_id'),
                    DB::raw('MAX(updated_at) as updated_at'),
                    DB::raw('MAX(batch_no) as batch_no'),
                    DB::raw('MAX(expiry_date) as expiry_date'),
                    'side',
                    'size'
                )
                ->groupBy('product_id', 'side', 'size');

                // ordering
                if ($request->has('order') && !empty($request->input('order'))) {
                    $columnIndex = $request->input('order')[0]['column'];
                    $columnName = $request->input('columns')[$columnIndex]['data'];
                    $sortDirection = $request->input('order')[0]['dir'];
                    
                    if ($columnName === 'stock') {
                        $query->orderBy('stock', $sortDirection);
                    } elseif (!empty($columnName) && in_array($columnName, ['product_name', 'updated_at', 'distributor_product_code', 'batch_no', 'expiry_date'])) {
                        $query->orderBy($columnName, $sortDirection);
                    } else {
                        $query->orderBy('updated_at', 'desc');
                    }
                } else {
                    $query->orderBy('updated_at', 'desc');
                }

                $start = intval($request->input('start', 0));
                $length = intval($request->input('length', 10));

                $items = ($length == -1) 
                    ? $query->with(['product', 'distributor.user'])->get() 
                    : $query->with(['product', 'distributor.user'])->offset($start)->limit($length)->get();

                $formatted = $items->map(function ($i) {
                    // Fetch all batches for this group to show in the breakdown
                    // We sum stock for batches with same batch_no and expiry
                    $rawBatches = Inventory::with(['distributor.user'])
                        ->where('product_id', $i->product_id)
                        ->where('distributor_id', $i->distributor_id)
                        ->where('side', $i->side)
                        ->where('size', $i->size)
                        ->orderBy('expiry_date', 'asc')
                        ->get();
                    
                    $batches = $rawBatches->map(function($b) {
                        return [
                            'id' => $b->id,
                            'batch_no' => $b->batch_no,
                            'stock' => $b->stock,
                            'side' => $b->side,
                            'size' => $b->size,
                            'expiry_date' => $b->expiry_date ? $b->expiry_date->format('d-m-Y') : 'N/A',
                            'raw_expiry_date' => $b->expiry_date ? $b->expiry_date->format('Y-m-d') : null,
                            'distributor_name' => $b->distributor?->user?->name ?? 'N/A',
                        ];
                    })->values();

                    return [
                        'id' => $i->id,
                        'distributor_product_code' => $i->distributor_product_code,
                        'product_name' => $i->product ? $i->product->product_name : $i->product_name,
                        'product_id' => $i->product_id,
                        'distributor_id' => $i->distributor_id,
                        'distributor_name' => $i->distributor?->user?->name ?? 'N/A',
                        'stock' => $i->stock,
                        'side' => $i->side,
                        'size' => $i->size,
                        'batches' => $batches,
                        'product_details' => $i->product ? [
                            'id' => $i->product->id,
                            'product_name' => $i->product->product_name,
                            'product_code' => $i->product->product_code,
                            'generic_name' => $i->product->generic_name,
                            'pack' => $i->product->pack,
                            'mrp' => $i->product->mrp,
                            'ptr' => $i->product->ptr,
                            'hsn_code' => $i->product->hsn_code,
                            'units_per_strip' => (function($p) {
                                $val = (string)($p->units_per_strip ?: ($p->strip_size ?: 1));
                                if (((int)$val <= 1 || $val === "1") && $p->strip_size) $val = $p->strip_size;
                                // For complex strings like 10x3x10, the last number is usually the units-per-strip
                                if (str_contains($val, 'x')) {
                                    $parts = explode('x', $val);
                                    return (int)preg_replace('/[^0-9]/', '', end($parts));
                                }
                                preg_match('/[0-9]+/', $val, $m);
                                return intval($m[0] ?? 1);
                            })($i->product),
                            'strips_per_box' => (function($p) {
                                $val = (string)($p->strips_per_box ?: ($p->box_size ?: 1));
                                if (((int)$val <= 1 || $val === "1") && $p->box_size) $val = $p->box_size;
                                // For complex strings like 10x3x10, strips-per-box is typically the product of leading numbers
                                if (str_contains($val, 'x')) {
                                    $parts = explode('x', $val);
                                    if (count($parts) > 1) {
                                        $multiplier = 1;
                                        // Multiply all but the last part
                                        for ($i=0; $i < count($parts)-1; $i++) {
                                            $multiplier *= (int)preg_replace('/[^0-9]/', '', $parts[$i]);
                                        }
                                        return $multiplier > 0 ? $multiplier : 1;
                                    }
                                }
                                preg_match('/[0-9]+/', $val, $m);
                                return intval($m[0] ?? 1);
                            })($i->product),
                            'boxes_per_carton' => (function($p) {
                                $val = (string)($p->boxes_per_carton ?: ($p->carton_size ?: 1));
                                if (((int)$val <= 1 || $val === "1") && $p->carton_size) $val = $p->carton_size;
                                preg_match('/[0-9]+/', $val, $m);
                                return intval($m[0] ?? 1);
                            })($i->product),
                            'strip_size' => $i->product->strip_size,
                            'box_size' => $i->product->box_size,
                            'carton_size' => $i->product->carton_size,
                            'gst' => $i->product->gst,
                            'brand' => $i->product->brand,
                            'description' => $i->product->description,
                            'has_variants' => (bool)$i->product->has_variants,
                        ] : null,
                        'updated_at' => \Carbon\Carbon::parse($i->updated_at)->toIso8601String(),
                        'image' => $i->product && $i->product->image ? asset('storage/' . $i->product->image) : asset('admin/assets/images/dashboard/product-1.png'), // Placeholder
                        'batch_no' => $i->batch_no ?? '-',
                        'raw_expiry_date' => $i->expiry_date,
                        'expiry_date' => $i->expiry_date ? (function ($date) {
                            $parsed = \Carbon\Carbon::parse($date);
                            if ($parsed->copy()->endOfMonth()->isSameDay($parsed)) {
                                return $parsed->format('m-Y');
                            }
                            return $parsed->format('d-m-Y');
                        })($i->expiry_date) : '-',
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
            'side' => 'nullable|string',
            'size' => 'nullable|string',
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
                'side' => $request->side,
                'size' => $request->size,
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
            'operation' => 'nullable|string|in:set,add,subtract',
            'batch_no' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
        ]);

        $operation = $request->input('operation', 'set');
        $product = $inventory->product;
        
        // Convert the input stock to strips based on the selected unit using shared helper
        $inputStrips = $this->convertQuantityToStrips($product, $request->stock, $request->unit ?? 'Strips');
        
        $previousStock = $inventory->stock;
        $newStock = $previousStock;

        if ($operation === 'add') {
            $newStock += $inputStrips;
        } elseif ($operation === 'subtract') {
            $newStock = max(0, $previousStock - $inputStrips);
        } else {
            $newStock = $inputStrips;
        }
        
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
