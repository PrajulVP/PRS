<?php

namespace App\Http\Controllers;

use App\Models\DistributorOrder;
use App\Models\Inventory;
use App\Models\Distributor;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Notifications\OrderActionRequired;
use App\Traits\HandlesNotifications;

class DistributorOrderController extends Controller
{
    use HandlesNotifications, \App\Traits\CalculatesPrices;

    public function show(DistributorOrder $distributorOrder)
    {
        return $this->invoice($distributorOrder);
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Permission check
        if (!$user->hasAnyRole(['admin', 'superadmin', 'salesmanager', 'distributor']) && !$user->hasPermissionToCategory('distributor_orders', 'view')) {
            abort(403, 'Unauthorized action. You do not have permission to view distributor orders.');
        }

        if ($request->ajax()) {
            try {
                $query = DistributorOrder::with(['distributor.user', 'items.product', 'salesManager.user', 'returnRequests']);

                /** @var \App\Models\User $user */
                $user = Auth::user();

                // Filter by distributor if authenticated user is a distributor
                if ($user->hasRole('distributor')) {
                    $distributor = $user->distributor;
                    $query->where('distributor_id', $distributor->id);
                }
                // Filter by sales manager if authenticated user is a salesmanager
                if ($user->hasRole('salesmanager')) {
                    $salesManager = $user->salesManager;
                    $query->whereHas('distributor', function ($q) use ($salesManager) {
                        $q->where('sales_manager_id', $salesManager->id);
                    });
                }

                // Filter for Admin/Superadmin: Show all orders
                if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
                    // No additional filtering needed to show all orders
                }

                // Apply status filter if exists
                if ($request->has('status') && !empty($request->input('status'))) {
                    $query->where('status', $request->input('status'));
                }

                // Apply date range filters if exist
                if ($request->has('start_date') && !empty($request->input('start_date'))) {
                    $query->whereDate('distributor_orders.placed_at', '>=', $request->input('start_date'));
                }
                if ($request->has('end_date') && !empty($request->input('end_date'))) {
                    $query->whereDate('distributor_orders.placed_at', '<=', $request->input('end_date'));
                }

                // Apply payment_status filter if exists
                if ($request->has('payment_status') && !empty($request->input('payment_status'))) {
                    $status = $request->input('payment_status');
                    if ($status === 'pending') {
                        $query->where(function ($q) {
                            $q->where('payment_status', 'pending')
                                ->orWhereNull('payment_status');
                        });
                    } else {
                        $query->where('payment_status', $status);
                    }
                }

                $totalData = $query->count();

                // Apply search filter
                if ($request->has('search') && !empty($request->input('search')['value'])) {
                    $searchValue = $request->input('search')['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('distributor_orders.order_code', 'like', "%{$searchValue}%")
                            ->orWhere('distributor_orders.status', 'like', "%{$searchValue}%")
                            ->orWhereHas('distributor.user', function ($subQuery) use ($searchValue) {
                                $subQuery->where('name', 'like', "%{$searchValue}%");
                            })
                            ->orWhereHas('items.product', function ($subQuery) use ($searchValue) {
                                $subQuery->where('product_name', 'like', "%{$searchValue}%");
                            });
                    });
                }

                $totalFiltered = $query->count();

                // Apply order (sorting)
                if ($request->has('order') && !empty($request->input('order'))) {
                    $columnIndex = $request->input('order')[0]['column'];
                    $columnName = $request->input('columns')[$columnIndex]['data'];
                    $sortDirection = $request->input('order')[0]['dir'];

                    switch ($columnName) {
                        case 'id':
                            $query->orderBy('distributor_orders.id', $sortDirection);
                            break;
                        // Add other cases as needed
                        default:
                            $query->orderBy('distributor_orders.id', 'desc');
                            break;
                    }
                } else {
                    $query->orderBy('distributor_orders.id', 'desc');
                }

                $start = (int) $request->input('start');
                $length = (int) $request->input('length');

                if ($length > 0) {
                    $query->skip($start)->take($length);
                }
                $orders = $query->get();

                $formattedOrders = $orders->map(function ($order) {
                    $productSummary = $order->items->map(function ($item) {
                        $pName = $item->product_name ?? $item->product->product_name ?? $item->name ?? 'Product';
                        
                        // Clean up product name from any existing brackets to prevent duplication
                        if (str_contains($pName, '[')) {
                            $pName = trim(explode('[', $pName)[0]);
                        }
                        
                        $vLabel = array_filter([$item->side, $item->size]);
                        $pBrand = $item->product ? $item->product->brand : null;
                        
                        $pPack = $item->product ? $item->product->pack : null;
                        
                        $summary = '<div class="product-summary-item mb-2" style="line-height: 1.35; width: 100%; white-space: normal; word-break: break-word; overflow-wrap: break-word;">';
                        $summary .= '<div style="display: block; margin-bottom: 2px;">';
                        $summary .= '<span class="fw-bold" style="color: #334155; font-size: 0.85rem; word-break: break-word;">'.$pName.'</span>';
                        if (!empty(trim($pPack)) && strtoupper(trim($pPack)) !== 'N/A') {
                            $summary .= '<span class="small fw-semibold" style="color: #94a3b8; font-size: 0.75rem; white-space: nowrap; margin-left: 3px;">['.$pPack.']</span>';
                        }
                        if (!empty($vLabel)) {
                            $summary .= ' <span class="badge rounded-pill align-middle" style="background: #e0f2fe; color: #0369a1; font-size: 0.65rem; padding: 2px 6.5px; font-weight: 700; letter-spacing: 0.3px; white-space: nowrap; margin-left: 4px; display: inline-block;">' . strtoupper(implode(' / ', $vLabel)) . '</span>';
                        }
                        $summary .= '</div>';
                        
                        $meta = [];
                        $qtyText = $item->quantity . ' ' . ($item->unit ?? 'Nos');
                        $meta[] = '<span class="text-primary fw-bold" style="font-size: 0.75rem;">' . $qtyText . '</span>';
                        
                        if (!empty($meta)) {
                            $summary .= '<div class="d-flex flex-wrap align-items-center gap-1 mt-1" style="word-break: break-word;">' . implode(' <span class="text-muted" style="font-size: 0.75rem; margin: 0 2px;">•</span> ', $meta) . '</div>';
                        }
                        $summary .= '</div>';
                        return $summary;
                    })->implode('|||');

                    $freeItemsGrouped = [];
                    foreach ($order->items as $item) {
                        if ($item->free_quantity > 0) {
                            $pName = $item->product_name ?? $item->product->product_name ?? $item->name ?? 'Product';
                            if (str_contains($pName, '[')) {
                                $pName = trim(explode('[', $pName)[0]);
                            }
                            if (!isset($freeItemsGrouped[$pName])) {
                                $freeItemsGrouped[$pName] = [
                                    'qty' => 0,
                                    'labels' => []
                                ];
                            }
                            $freeItemsGrouped[$pName]['qty'] += $item->free_quantity;
                            
                            $freeLabel = array_filter([$item->free_side, $item->free_size]);
                            if (!empty($freeLabel)) {
                                $formattedFreeLabel = preg_replace('/(\d+)X([A-Z]+)/', '$1 $2', strtoupper(implode(' / ', $freeLabel)));
                                $freeItemsGrouped[$pName]['labels'][] = $formattedFreeLabel;
                            }
                        }
                    }

                    $freeSummary = '';
                    foreach ($freeItemsGrouped as $name => $data) {
                        $labelsStr = '';
                        if (!empty($data['labels'])) {
                             $uniqueLabels = array_unique($data['labels']);
                             // Recalculate true quantity from variant labels if they exist
                             $variantSum = 0;
                             foreach ($uniqueLabels as $l) {
                                 preg_match_all('/(\d+)\s+[A-Z]+/', $l, $matches);
                                 if (!empty($matches[1])) {
                                     $variantSum += array_sum($matches[1]);
                                 }
                             }
                             if ($variantSum > 0) {
                                 $data['qty'] = max($data['qty'], $variantSum);
                             }
                             $labelsStr = '<span style="font-size: 0.75rem; color: #0369a1; background: #e0f2fe; padding: 2px 8px; border-radius: 12px; font-weight: 700; letter-spacing: 0.2px; text-align: left; word-break: break-word; white-space: normal;">' . implode(', ', $uniqueLabels) . '</span>';
                        }
                        $freeSummary .= '<div class="mb-1" style="line-height: 1.2;"><span class="fw-bold" style="color: #334155; font-size: 0.8rem;">' . $name . '</span><br><div class="d-flex flex-column align-items-start mt-1 gap-1"><span class="text-success fw-bold d-inline-flex align-items-center" style="font-size: 0.95rem;"><i class="fa fa-gift me-1" style="font-size: 0.85rem;"></i> ' . $data['qty'] . '</span>' . $labelsStr . '</div></div>|||';
                    }
                    $freeSummary = rtrim($freeSummary, '|||');

                    $brandSummary = $order->items->map(function ($item) {
                        return $item->product ? ($item->product->brand ?? 'N/A') : 'N/A';
                    })->implode('|||');

                    return [
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'brand_summary' => $brandSummary,
                        'name' => $order->distributor?->name ?? $order->distributor?->user?->name ?? 'N/A',
                        'distributor_email' => $order->distributor?->email ?? $order->distributor?->user?->email ?? '',
                        'distributor_phone' => $order->distributor?->contact_no ?? $order->distributor?->phone ?? '',
                        'distributor_address' => trim(($order->distributor?->address ?? '') . ' ' . ($order->distributor?->pincode ?? '')),
                        'distributor_name' => $order->distributor?->name ?? $order->distributor?->user?->name ?? 'N/A',
                        'distributor_location' => trim(($order->distributor?->address ?? '') . ' ' . ($order->distributor?->pincode ?? '')),
                        'distributor_gst' => $order->distributor?->gst ?? '',
                        'distributor_dl' => $order->distributor?->drug_license_no ?? '',
                        'distributor_id' => $order->distributor_id,
                        'sales_manager_name' => $order->salesManager?->user?->name ?? 'N/A',
                        'sales_manager_id' => $order->sales_manager_id,
                        'distributor_sm_id' => $order->distributor?->sales_manager_id,
                        'total_items' => $order->total_items,
                        'total_quantity' => $order->total_quantity,
                        'total_amount' => $order->total_amount,
                        'metadata' => $order->metadata,
                        'product_summary' => $productSummary,
                        'free_summary' => $freeSummary,
                        'status' => ucfirst(str_replace('_', ' ', $order->status)),
                        'placed_at' => $order->placed_at ? \Carbon\Carbon::parse($order->placed_at)->format('Y-m-d H:i:s') : '-',
                        'items' => $order->items->map(function ($item) use ($order) {
                            $pName = $item->product_name ?? $item->product->product_name ?? $item->name ?? 'Product';
                            
                            // Aggregate return requests for this specific variant
                            $itemReturns = $order->returnRequests
                                ->where('product_id', $item->product_id)
                                ->where('side', $item->side)
                                ->where('size', $item->size);

                            $returnedQty = $itemReturns->where('status', 'completed')->sum('quantity');
                            $pendingQty = $itemReturns->where('status', 'pending')->sum('quantity');
                            $tier1Qty = $itemReturns->where('status', 'approved_tier1')->sum('quantity');
                            
                            // Combine pending and tier1 for "Active Request" visibility
                            $activeRequestQty = $pendingQty + $tier1Qty;
                            
                            // Get the most recent return code for display if applicable
                            $latestReturn = $itemReturns->sortByDesc('created_at')->first();

                            return [
                                'product_id' => $item->product_id,
                                'product_name' => $pName,
                                'product_code' => $item->product->product_code ?? 'N/A',
                                'generic_name' => $item->product->generic_name ?? null,
                                'pack' => $item->product->pack ?? null,
                                'brand' => $item->product->brand ?? null,
                                'side' => $item->side,
                                'size' => $item->size,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->price,
                                'total_amount' => $item->subtotal,
                                'stock_at_time' => null, // Stock check disabled
                                'unit' => $item->unit,
                                'order_item_id' => $item->id,
                                'is_returnable' => $item->product->is_returnable ?? true,
                                'is_free' => $item->is_free ?? false,
                                'free_quantity' => $item->free_quantity,
                                'free_qty_buy' => $item->product->free_qty_buy ?? 0,
                                'free_qty_get' => $item->product->free_qty_get ?? 1,
                                'free_side' => $item->free_side,
                                'free_size' => $item->free_size,
                                'returned_qty' => (float)$returnedQty,
                                'pending_return_qty' => (float)$activeRequestQty,
                                'return_status' => $latestReturn ? $latestReturn->status : null,
                                'return_code' => $latestReturn ? $latestReturn->return_code : null,
                                'batches' => $item->batches->map(function ($b) {
                                    return [
                                        'id' => $b->id,
                                        'batch_no' => $b->batch_no,
                                        'expiry_date' => $b->expiry_date ? (function ($date) {
                                            $parsed = \Carbon\Carbon::parse($date);
                                            if ($parsed->copy()->endOfMonth()->isSameDay($parsed)) {
                                                return $parsed->format('m/Y');
                                            }
                                            return $parsed->format('d/m/Y');
                                        })($b->expiry_date) : '-',
                                        'quantity' => $b->quantity
                                    ];
                                })
                            ];
                        }),
                        'delivery_notes' => $order->delivery_notes,
                        'invoice_url' => $order->invoice_path ? asset('storage/' . $order->invoice_path) : null,
                        'payment_status' => $order->payment_status, // Added for payment status display
                        'cancellation_reason' => $order->cancellation_reason,
                        'raw_status' => $order->status,
                        'delivered_at' => (isset($order->delivered_at) && $order->delivered_at) ? \Carbon\Carbon::parse($order->delivered_at)->format('Y-m-d H:i:s') : (($order->status === 'delivered' || $order->status === 'completed') ? \Carbon\Carbon::parse($order->updated_at)->format('Y-m-d H:i:s') : null)
                    ];
                });

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalData,
                    'recordsFiltered' => $totalFiltered,
                    'data' => $formattedOrders,
                ]);
            } catch (\Exception $e) {
                Log::error('Error in distributorOrderController@index: ' . $e->getMessage());
                return response()->json([
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        $products = Product::all();
        $distributors = collect();
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            $distributors = Distributor::with('user')->get();
        }

        return view('admin.orders.distributors.index', compact('products', 'distributors'));
    }

    // Create Order Page
    public function create()
    {
        $products = Product::select('id', 'product_name', 'mrp', 'pts', 'pack', 'brand')->get();
        $brands = Product::select('brand')->distinct()->whereNotNull('brand')->where('brand', '!=', '')->orderBy('brand')->pluck('brand');
        $distributors = collect();
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            $distributors = Distributor::with('user')->whereHas('user', function ($q) {
                $q->where('status', 'active');
            })->get();
        }
        $eligibleFreeProducts = Product::where('is_free_eligible', true)->get();

        return view('admin.orders.distributors.create', compact('products', 'distributors', 'brands', 'eligibleFreeProducts'));
    }

    public function getProducts(Request $request)
    {
        $brand = $request->get('brand');
        $query = Product::select('id', 'product_name', 'mrp', 'pts', 'pack', 'brand');
        if ($brand) {
            $query->where('brand', $brand);
        }
        $products = $query->get();
        return response()->json($products);
    }

    public function getProductDetails(Product $product)
    {
        return response()->json([
            'product' => $product,
            'strips_per_box' => $product->strips_per_box,
            'boxes_per_carton' => $product->boxes_per_carton,
        ]);
    }

    public function getDistributorProductVariants(Request $request, Product $product)
    {
        $distributorId = $request->get('distributor_id');

        if (!$distributorId) {
            return response()->json(['variants' => []]);
        }

        $stockData = DB::table('inventories')
            ->where('product_id', $product->id)
            ->where('distributor_id', $distributorId)
            ->where('stock', '>', 0)
            ->select('side', 'size', 'stock')
            ->get();

        return response()->json([
            'variants' => $stockData
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Distributor Order Store Request Data:', $request->all());

        $request->validate([
            'delivery_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string',
        ]);

        $distributorId = null;
        $distributorSalesManagerId = null;
        if (Auth::user()->hasRole('distributor')) {

            $distributor = Auth::user()->distributor;
            $distributorId = $distributor->id;
            $distributorSalesManagerId = $distributor->sales_manager_id;
        } else {
            $request->validate(['distributor_id' => 'required|exists:distributors,id']);
            $distributorId = $request->distributor_id;
            $distributor = Distributor::find($distributorId);
            $distributorSalesManagerId = $distributor->sales_manager_id;
        }

        $totalAmount = 0;
        $totalItems = 0;
        $totalQuantity = 0;



        $order = DistributorOrder::create([
            'distributor_id' => $distributorId,
            'sales_manager_id' => $distributorSalesManagerId,
            'status' => DistributorOrder::STATUS_PENDING,
            'placed_at' => now(),
            'delivery_notes' => $request->delivery_notes,
            'total_amount' => 0,
            'total_items' => 0,
            'total_quantity' => 0,
        ]);


        try {
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);

                if (!$product) {
                    throw new \Exception('One or more selected products not found.');
                }

                //$product->stock -= $itemData['quantity'];
                //$product->save();

                // Price Logic: Distributor buys at PTS (Price to Stockist)
                $unitPrice = (float)$product->pts; // Strictly PTS
                $unit = $itemData['unit'] ?? 'Strips';
                $qty = (float)$itemData['quantity'];

                // Conversion logic
                $multiplier = 1;
                $normalizedUnit = strtolower($unit);
                if ($normalizedUnit === 'box') {
                    $multiplier = (int)($product->strips_per_box ?? 1);
                } elseif ($normalizedUnit === 'carton') {
                    $multiplier = (int)($product->boxes_per_carton ?? 1) * (int)($product->strips_per_box ?? 1);
                } elseif ($normalizedUnit === 'nos' || $normalizedUnit === 'no' || $normalizedUnit === 'unit') {
                    $multiplier = 1 / (max(1, (int)($product->units_per_strip ?? 1)));
                }

                $totalQtyNos = ceil($qty * $multiplier);
                $gstRate = (float)($product->gst ?? 0);
                $taxableAmount = $totalQtyNos * $unitPrice;
                $itemTotalWithGst = $taxableAmount * (1 + ($gstRate / 100));

                // Append variant info to product name if provided
                $finalProductName = $product->product_name;
                $iSide = $itemData['side'] ?? null;
                $iSize = $itemData['size'] ?? null;
                $vInfo = array_filter([$iSide, $iSize]);
                if (!empty($vInfo)) {
                    $finalProductName .= ' [' . implode('/', $vInfo) . ']';
                }

                $freeQty = 0;
                $freeProductId = null;
                $freeSide = null;
                $freeSize = null;

                if ($product->free_qty_buy > 0 && $product->free_qty_get > 0) {
                    $eligibleFree = floor($qty / $product->free_qty_buy) * $product->free_qty_get;
                    if ($eligibleFree > 0) {
                        $freeQty = $eligibleFree;
                        if (isset($itemData['free_product_id'])) {
                            $selectedFreeProduct = Product::find($itemData['free_product_id']);
                            if ($selectedFreeProduct && $selectedFreeProduct->is_free_eligible) {
                                $freeProductId = $selectedFreeProduct->id;
                                $freeSide = $itemData['free_side'] ?? null;
                                $freeSize = $itemData['free_size'] ?? null;
                            }
                        }
                    }
                } elseif (strcasecmp($product->brand, 'Atomeds') === 0 || strcasecmp($product->brand, 'Atomets') === 0) {
                    $freeQty = floor($qty / 10) * 2;
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $finalProductName,
                    'quantity' => $qty,
                    'free_quantity' => $freeQty,
                    'free_product_id' => $freeProductId,
                    'free_side' => $freeSide,
                    'free_size' => $freeSize,
                    'unit' => $unit,
                    'price' => $unitPrice,
                    'subtotal' => $itemTotalWithGst,
                    'side' => $iSide,
                    'size' => $iSize,
                ]);

                $totalAmount += $itemTotalWithGst;
                $totalItems++;
                $totalQuantity += $totalQtyNos;
            }

            $order->total_amount = $totalAmount;
            $order->total_items = $totalItems;
            $order->total_quantity = $totalQuantity;
            $order->save();

            // Notify Sales Manager
            if ($order->salesManager && $order->salesManager->user) {
                $this->notifyUnique($order->salesManager->user, new \App\Notifications\OrderActionRequired(
                    $order,
                    "New Distributor Order #{$order->order_code} is ready for your approval.",
                    route('admin.approvals.distributor'),
                    'distributor_order'
                ));
            }
        } catch (\Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $order->items()->delete();
            $order->delete();
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => 'Order placed successfully.']);
    }

    public function update(Request $request, distributorOrder $distributorOrder)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->hasRole('salesmanager')) {
            $salesManager = $user->salesManager;
            if (!$salesManager) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'Sales manager record not found.'], 422);
                }
                return back()->withErrors(['error' => 'Sales manager record not found.']);
            }
            $isOwner = ($distributorOrder->sales_manager_id === $salesManager->id) || 
                       ($distributorOrder->distributor && $distributorOrder->distributor->sales_manager_id === $salesManager->id);
            if (!$isOwner) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'You are not authorized to edit this order.'], 403);
                }
                return back()->withErrors(['error' => 'You are not authorized to edit this order.']);
            }
            if (!in_array($distributorOrder->status, [DistributorOrder::STATUS_PENDING, DistributorOrder::STATUS_PROCESSING])) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'You can only edit pending or processing orders.'], 422);
                }
                return back()->withErrors(['error' => 'You can only edit pending or processing orders.']);
            }
            // Sales manager cannot change status
            $request->merge(['status' => $distributorOrder->status]);
        } elseif (!$user->hasAnyRole(['admin', 'superadmin'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }
            return back()->withErrors(['error' => 'Unauthorized action.']);
        }

        $request->validate([
            'distributor_id' => 'required|exists:distributors,id',
            'status' => 'required',
            'items' => 'required|array',
        ]);

        $metadata = $distributorOrder->metadata ?? [];
        $roleName = 'Admin';
        if ($user->hasRole('salesmanager')) $roleName = 'Sales Manager';
        if ($user->hasRole('superadmin')) $roleName = 'Super Admin';

        $hasChanges = false;
        
        $dbNotes = $distributorOrder->delivery_notes === null ? '' : (string)$distributorOrder->delivery_notes;
        $reqNotes = $request->delivery_notes === null ? '' : (string)$request->delivery_notes;

        if ((string)$distributorOrder->distributor_id !== (string)$request->distributor_id) { $hasChanges = true; \Log::info("Change: distributor_id"); }
        if ((string)$distributorOrder->status !== (string)$request->status) { $hasChanges = true; \Log::info("Change: status"); }
        if ($dbNotes !== $reqNotes) { $hasChanges = true; \Log::info("Change: delivery_notes '{$dbNotes}' !== '{$reqNotes}'"); }

        $requestItemsGrouped = collect($request->items ?? [])->groupBy('order_item_id');
        $requestPaidCount = $requestItemsGrouped->filter(function($group) {
            return $group->where('is_free', '!=', 1)->count() > 0;
        })->count();

        if ($distributorOrder->items->count() !== $requestPaidCount) {
            $hasChanges = true;
        } else {
            foreach ($requestItemsGrouped as $orderItemId => $group) {
                if (empty($orderItemId)) {
                    $hasChanges = true;
                    break;
                }
                $existingItem = $distributorOrder->items->find($orderItemId);
                if (!$existingItem) {
                    $hasChanges = true;
                    break;
                }

                $paidItem = $group->firstWhere('is_free', '!=', 1);
                $freeItem = $group->firstWhere('is_free', '==', 1);

                if ($paidItem) {
                    if ((string)$existingItem->product_id !== (string)$paidItem['product_id']) { $hasChanges = true; \Log::info("Change: product_id {$existingItem->product_id} !== {$paidItem['product_id']}"); }
                    if (round((float)$existingItem->quantity, 2) !== round((float)$paidItem['quantity'], 2)) { $hasChanges = true; \Log::info("Change: quantity {$existingItem->quantity} !== {$paidItem['quantity']}"); }
                    
                    $dbUnit = strtolower(trim($existingItem->unit ?? ''));
                    if ($dbUnit === '') $dbUnit = 'strips';
                    $reqUnit = isset($paidItem['unit']) ? strtolower(trim($paidItem['unit'])) : 'strips';
                    if ($dbUnit !== $reqUnit && !($dbUnit === 'box' && $reqUnit === 'strips')) { 
                        $hasChanges = true; \Log::info("Change: unit {$dbUnit} !== {$reqUnit}"); 
                    }
                    
                    $existingSide = $existingItem->side === null ? '' : strtolower(trim((string)$existingItem->side));
                    $newSide = empty($paidItem['side']) ? '' : strtolower(trim((string)$paidItem['side']));
                    if ($existingSide !== $newSide) { $hasChanges = true; \Log::info("Change: side {$existingSide} !== {$newSide}"); }

                    $existingSize = $existingItem->size === null ? '' : strtolower(trim((string)$existingItem->size));
                    $newSize = empty($paidItem['size']) ? '' : strtolower(trim((string)$paidItem['size']));
                    if ($existingSize !== $newSize) { $hasChanges = true; \Log::info("Change: size {$existingSize} !== {$newSize}"); }
                }

                if ($freeItem) {
                    $existingFreeSide = $existingItem->free_side === null ? '' : strtolower(trim((string)$existingItem->free_side));
                    $newFreeSide = empty($freeItem['side']) ? '' : strtolower(trim((string)$freeItem['side']));
                    if ($existingFreeSide !== $newFreeSide) { $hasChanges = true; \Log::info("Change: free side {$existingFreeSide} !== {$newFreeSide}"); }

                    $existingFreeSize = $existingItem->free_size === null ? '' : strtolower(trim((string)$existingItem->free_size));
                    $newFreeSize = empty($freeItem['size']) ? '' : strtolower(trim((string)$freeItem['size']));
                    if ($existingFreeSize !== $newFreeSize) { $hasChanges = true; \Log::info("Change: free size {$existingFreeSize} !== {$newFreeSize}"); }
                }

                if ($hasChanges) break;
            }
        }

        if ($hasChanges) {
            $metadata['is_edited'] = true;
            $metadata['last_edited_by'] = $user->name . " ($roleName)";
            $metadata['last_edited_at'] = now()->toDateTimeString();

            $snapshot = $distributorOrder->items->map(function($item) {
                return [
                    'product_name' => $item->product_name ?? ($item->product ? $item->product->product_name : 'Unknown Product'),
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                    'side' => $item->side,
                    'size' => $item->size,
                ];
            })->toArray();

            $editLogs = $metadata['edit_history'] ?? [];
            $editLogs[] = [
                'edited_by' => $user->name,
                'role' => strtolower(str_replace(' ', '', $roleName)),
                'edited_at' => now()->toDateTimeString(),
                'original_total' => $distributorOrder->total_amount,
                'snapshot' => $snapshot,
            ];
            $metadata['edit_history'] = $editLogs;
        }

        $distributorOrder->update([
            'distributor_id' => $request->distributor_id,
            'status' => $request->status,
            'delivery_notes' => $request->delivery_notes,
            'metadata' => $metadata,
        ]);

        $totalAmount = 0;
        $totalItems = 0;
        $totalQuantity = 0;
        $requestItemIds = [];

        try {
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                $currentOrderItem = null;
                if (!empty($itemData['order_item_id'])) {
                    $currentOrderItem = $distributorOrder->items()->find($itemData['order_item_id']);
                }

                $unit = $itemData['unit'] ?? 'Box';
                $newQuantity = $itemData['quantity'];

                $multiplier = 1;
                $normalizedUnit = strtolower($unit);
                if ($normalizedUnit === 'box') {
                    $multiplier = (int)($product->strips_per_box ?? 1);
                } elseif ($normalizedUnit === 'carton') {
                    $multiplier = (int)($product->boxes_per_carton ?? 1) * (int)($product->strips_per_box ?? 1);
                } elseif ($normalizedUnit === 'nos' || $normalizedUnit === 'no' || $normalizedUnit === 'unit') {
                    $multiplier = 1 / (max(1, (int)($product->units_per_strip ?? 1)));
                }

                $totalQtyNos = ceil($newQuantity * $multiplier);
                $unitPrice = (float)$product->pts;
                $itemTotalAmount = $totalQtyNos * $unitPrice;

                $iSide = $itemData['side'] ?? null;
                $iSize = $itemData['size'] ?? null;

                if ($currentOrderItem) {
                    $currentOrderItem->update([
                        'quantity' => $newQuantity,
                        'unit' => $unit,
                        'price' => $unitPrice,
                        'subtotal' => $itemTotalAmount,
                        'side' => $iSide,
                        'size' => $iSize,
                        'free_quantity' => $itemData['free_quantity'] ?? 0,
                        'free_side' => $itemData['free_side'] ?? null,
                        'free_size' => $itemData['free_size'] ?? null,
                    ]);
                    $requestItemIds[] = $currentOrderItem->id;
                } else {
                    $newItem = $distributorOrder->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->product_name,
                        'quantity' => $newQuantity,
                        'unit' => $unit,
                        'price' => $unitPrice,
                        'subtotal' => $itemTotalAmount,
                        'side' => $iSide,
                        'size' => $iSize,
                        'free_quantity' => $itemData['free_quantity'] ?? 0,
                        'free_side' => $itemData['free_side'] ?? null,
                        'free_size' => $itemData['free_size'] ?? null,
                    ]);
                    $requestItemIds[] = $newItem->id;
                }

                $totalAmount += $itemTotalAmount;
                $totalItems++;
                $totalQuantity += $totalQtyNos;
            }

            // Delete removed items
            $distributorOrder->items()->whereNotIn('id', $requestItemIds)->get()->each(function ($item) {
                // $item->product->increment('stock', $item->quantity); // Restore stock (Skipped)
                $item->delete();
            });

            $distributorOrder->total_amount = $totalAmount;
            $distributorOrder->total_items = $totalItems;
            $distributorOrder->total_quantity = $totalQuantity;
            $distributorOrder->save();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => 'Order updated.']);
    }

    public function acceptBySalesManager(distributorOrder $distributorOrder)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasPermissionToCategory('distributor_approvals', 'edit') && !$user->hasRole('salesmanager')) return response()->json(['error' => 'No permission'], 403);
        if ($distributorOrder->status !== DistributorOrder::STATUS_PENDING) return response()->json(['error' => 'Not pending'], 400);

        $distributorOrder->status = DistributorOrder::STATUS_PROCESSING;
        if ($user->salesManager) {
            $distributorOrder->sales_manager_id = $user->salesManager->id;
        }
        $distributorOrder->save();

        // Notify Admins
        $admins = \App\Models\User::role('admin')->get();
        foreach ($admins as $admin) {
            $this->notifyUnique($admin, new \App\Notifications\OrderActionRequired(
                $distributorOrder,
                "Distributor Order #{$distributorOrder->order_code} has been processed and is ready for your approval.",
                route('admin.approvals.distributor'),
                'distributor_order'
            ));
        }

        return response()->json(['success' => 'Order accepted.']);
    }

    public function acceptByAdmin(Request $request, DistributorOrder $distributorOrder)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasPermissionToCategory('distributor_approvals', 'edit') && !$user->hasAnyRole(['admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'payment_status' => 'sometimes|nullable|in:pending,paid,failed',
            'invoice' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'invoice_no' => 'required|string|max:100', // Capture invoice number
            'final_amount' => 'nullable|numeric|min:0',
            'taxable_amount' => 'nullable|numeric|min:0',
            'batches' => 'required|array',
            'batches.*' => 'required|array|min:1',
            'batches.*.*.batch_no' => 'required|string|max:255',
            'batches.*.*.expiry_date' => 'required|date',
            'batches.*.*.quantity' => 'required|integer|min:1',
            'batches.*.*.mrp' => 'nullable|numeric|min:0',
            'batches.*.*.ptr' => 'nullable|numeric|min:0',
            'batches.*.*.pts' => 'nullable|numeric|min:0',
            'batches.*.*.taxable_value' => 'nullable|numeric|min:0',
            'batches.*.*.cgst' => 'nullable|numeric|min:0',
            'batches.*.*.sgst' => 'nullable|numeric|min:0',
            'batches.*.*.igst' => 'nullable|numeric|min:0',
            'batches.*.*.net_amount' => 'nullable|numeric|min:0',
        ]);

        // Unique Invoice Number Check for the specific Distributor
        $distributorId = $distributorOrder->distributor_id;
        $invoiceNo = $request->invoice_no;

        $existsInDistOrders = DistributorOrder::where('distributor_id', $distributorId)
            ->where('invoice_no', $invoiceNo)
            ->where('id', '!=', $distributorOrder->id)
            ->exists();

        $existsInRetailOrders = \App\Models\RetailerOrder::where('distributor_id', $distributorId)
            ->where('invoice_no', $invoiceNo)
            ->exists();

        if ($existsInDistOrders || $existsInRetailOrders) {
            return response()->json([
                'error' => "The invoice number '{$invoiceNo}' has already been used for another order by this distributor."
            ], 422);
        }

        DB::beginTransaction();
        try {
            /* Stock check logic commented out - App\Models\Stock does not exist (disabled for now)
            foreach ($distributorOrder->items as $item) {
                $stock = \App\Models\Stock::where('product_id', $item->product_id)->first();
                if ($stock) {
                    if ($stock->quantity < $item->quantity) {
                        throw new \Exception("Not enough stock for product: " . $item->product->product_name);
                    }
                    $stock->decrement('quantity', $item->quantity);
                }
            }
            */

            // Handle Invoice Upload
            $invoicePath = $distributorOrder->invoice_path ? $distributorOrder->invoice_path : null; // Initialize properly
            if ($request->hasFile('invoice')) {
                // Delete old invoice if exists
                if ($invoicePath && Storage::disk('public')->exists($invoicePath)) {
                    Storage::disk('public')->delete($invoicePath);
                }

                $file = $request->file('invoice');
                $extension = $file->getClientOriginalExtension();
                // Create a readable filename: Invoice_ORD123_2024-02-13_103000.pdf
                $timestamp = now()->format('Y-m-d_His'); // Includes time for uniqueness
                $filename = "Invoice_{$distributorOrder->order_code}_{$timestamp}.{$extension}";

                // Store with the new custom filename
                $invoicePath = $file->storeAs('invoices/distributors', $filename, 'public');
            }

            $metadata = $distributorOrder->metadata ?? [];
            if (!isset($metadata['estimated_amount'])) {
                $metadata['estimated_amount'] = (float)$distributorOrder->total_amount;
            }
            
            $updateData = [
                'status' => DistributorOrder::STATUS_APPROVED,
                'invoice_path' => $invoicePath,
                'invoice_no' => $request->invoice_no // Save the invoice number
            ];

            if ($request->filled('final_amount')) {
                $updateData['total_amount'] = (float)$request->final_amount;
                $metadata['invoice_net_amount'] = (float)$request->final_amount;
            }
            if ($request->filled('taxable_amount')) {
                $metadata['invoice_taxable_amount'] = (float)$request->taxable_amount;
            }

            $updateData['metadata'] = $metadata;

            if ($request->filled('payment_status')) {
                $updateData['payment_status'] = $request->payment_status;
            }

            $distributorOrder->update($updateData);

            // Save Batch Details
            $qtyErrors = [];
            foreach ($request->batches as $itemId => $batches) {
                $orderItem = $distributorOrder->items()->find($itemId);
                if (!$orderItem) continue;

                // Cross-check: Invoiced quantity should not exceed ordered quantity
                $totalInvoicedQty = 0;
                foreach ($batches as $batchData) {
                    $totalInvoicedQty += (int)$batchData['quantity'];
                }

                if ($totalInvoicedQty > $orderItem->quantity) {
                    $qtyErrors[] = "You ordered {$orderItem->quantity} but the invoiced quantity is {$totalInvoicedQty} for item: {$orderItem->product_name}";
                }
            }

            if (!empty($qtyErrors)) {
                throw new \Exception(implode("<br>", $qtyErrors));
            }

            // If no errors, proceed to save
            foreach ($request->batches as $itemId => $batches) {
                $orderItem = $distributorOrder->items()->find($itemId);
                if (!$orderItem) continue;

                if ($request->has("free_quantity.{$itemId}")) {
                    $orderItem->free_quantity = $request->input("free_quantity.{$itemId}");
                    $orderItem->save();
                }

                // Delete existing batches if re-approving (though usually direct approved)
                $orderItem->batches()->delete();

                foreach ($batches as $batchData) {
                    $orderItem->batches()->create([
                        'batch_no' => $batchData['batch_no'],
                        'expiry_date' => $batchData['expiry_date'],
                        'quantity' => $batchData['quantity'],
                        'mrp' => $batchData['mrp'] ?? null,
                        'ptr' => $batchData['ptr'] ?? null,
                        'pts' => $batchData['pts'] ?? null,
                        'taxable_value' => $batchData['taxable_value'] ?? null,
                        'cgst' => $batchData['cgst'] ?? null,
                        'sgst' => $batchData['sgst'] ?? null,
                        'igst' => $batchData['igst'] ?? null,
                        'net_amount' => $batchData['net_amount'] ?? null,
                    ]);
                }
            }

            // This logic is now handled by confirmReceipt in this controller
            // $this->addOrderItemsToInventory($distributorOrder);

            DB::commit();

            // Clear existing notifications for this order
            $this->clearOrderNotifications($distributorOrder->id, 'distributor_order');

            // Notify Distributor
            if ($distributorOrder->distributor && $distributorOrder->distributor->user) {
                $this->notifyUnique($distributorOrder->distributor->user, new OrderActionRequired(
                    $distributorOrder,
                    "Your order #{$distributorOrder->order_code} has been accepted. Please confirm receipt upon delivery.",
                    route('admin.distributor-orders.index'),
                    'distributor_order'
                ));
            }

            return response()->json(['success' => 'Order accepted.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function requestCancellation(Request $request, distributorOrder $distributorOrder)
    {
        $request->validate(['cancellation_reason' => 'required|string|min:5']);
        if (!Auth::user()->hasRole('distributor')) return response()->json(['error' => 'No permission'], 403);
        if ($distributorOrder->distributor_id !== Auth::user()->distributor->id) return response()->json(['error' => 'Not your order'], 403);

        if ($distributorOrder->status !== DistributorOrder::STATUS_PENDING) {
            return response()->json(['error' => 'Orders can only be cancelled while in pending status.'], 400);
        }

        $distributorOrder->status = DistributorOrder::STATUS_CANCELLED;
        $distributorOrder->cancellation_reason = $request->cancellation_reason;
        $distributorOrder->save();

        $this->deleteOrderNotifications($distributorOrder->id, 'distributor_order');

        return response()->json(['success' => 'Order cancelled.']);
    }

    public function rejectOrder(Request $request, DistributorOrder $distributorOrder)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'superadmin', 'salesmanager']) && !$user->hasPermissionToCategory('distributor_approvals', 'edit')) {
            return response()->json(['error' => 'Unauthorized rejection'], 403);
        }

        if (!in_array($distributorOrder->status, [DistributorOrder::STATUS_PENDING, DistributorOrder::STATUS_PROCESSING])) {
            return response()->json(['error' => 'Only pending or processing orders can be rejected.'], 400);
        }

        $request->validate(['reason' => 'required|string|min:5']);

        $distributorOrder->update([
            'status' => DistributorOrder::STATUS_REJECTED,
            'cancellation_reason' => $request->reason
        ]);

        $this->deleteOrderNotifications($distributorOrder->id, 'distributor_order');

        if ($distributorOrder->distributor && $distributorOrder->distributor->user) {
            $this->notifyUnique($distributorOrder->distributor->user, new OrderActionRequired(
                $distributorOrder,
                "Your order #{$distributorOrder->order_code} has been rejected.",
                route('admin.distributor-orders.index'),
                'distributor_order'
            ));
        }

        return response()->json(['success' => 'Order rejected.']);
    }

    public function cancelOrder(Request $request, DistributorOrder $distributorOrder)
    {
        if (!Auth::user()->hasRole('distributor')) return response()->json(['error' => 'No permission'], 403);
        if ($distributorOrder->distributor_id !== Auth::user()->distributor->id) return response()->json(['error' => 'Not your order'], 403);

        if ($distributorOrder->status !== DistributorOrder::STATUS_PENDING) {
            return response()->json(['error' => 'Only pending orders can be directly cancelled.'], 400);
        }

        $request->validate([
            'cancellation_reason' => 'required|string|min:3',
        ]);

        $distributorOrder->update([
            'status' => DistributorOrder::STATUS_CANCELLED,
            'cancellation_reason' => $request->cancellation_reason
        ]);

        $this->deleteOrderNotifications($distributorOrder->id, 'distributor_order');

        return response()->json(['success' => 'Order cancelled successfully!']);
    }

    public function updateStatus(Request $request, distributorOrder $distributorOrder)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,accepted,cancelled,delivered',
        ]);

        // Permission check
        if (Auth::user()->hasRole('distributor') && $distributorOrder->distributor_id !== Auth::user()->distributor->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $oldStatus = $distributorOrder->status;
        $newStatus = $request->status;

        $distributorOrder->status = $newStatus;

        // Handle side effects
        if ($newStatus === 'processing') {
            if (Auth::user()->hasRole('salesmanager')) {
                $distributorOrder->sales_manager_id = Auth::user()->salesManager->id;
            }
        }

        $distributorOrder->save();

        // Removed: Stock addition now only happens via confirmReceipt as per user request
        /*
        if ($oldStatus !== 'delivered' && $newStatus === 'delivered') {
            $this->addOrderItemsToInventory($distributorOrder);
        }
        */

        return response()->json(['success' => 'Status updated.']);
    }

    public function updatePaymentStatus(Request $request, DistributorOrder $distributorOrder)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed',
        ]);

        $user = Auth::user();
        if (!$user->hasRole(['superadmin', 'admin', 'salesmanager'])) {
            return response()->json(['error' => 'You do not have permission to update payment status.'], 403);
        }

        if ($user->hasRole('salesmanager') && $distributorOrder->sales_manager_id !== $user->salesManager->id) {
            return response()->json(['error' => 'You are not authorized to update this order.'], 403);
        }

        $newStatus = $request->payment_status;
        $distributorOrder->payment_status = $newStatus;
        $distributorOrder->save();

        return response()->json(['success' => 'Payment status updated.']);
    }

    public function invoice(DistributorOrder $distributorOrder)
    {
        $distributorOrder->load(['distributor.user', 'items.product', 'salesManager.user']);
        $cgst = \App\Models\Setting::getValue('cgst', 9);
        $sgst = \App\Models\Setting::getValue('sgst', 9);
        return view('admin.orders.distributors.invoice', compact('distributorOrder', 'cgst', 'sgst'));
    }

    public function uploadInvoice(Request $request, distributorOrder $distributorOrder)
    {
        $request->validate([
            'invoice'    => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'invoice_no' => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('invoice')) {
            $file = $request->file('invoice');

            // --- Invoice Number Uniqueness Check ---
            if ($request->filled('invoice_no')) {
                $invoiceNo    = trim($request->invoice_no);
                $distributorId = $distributorOrder->distributor_id;

                $existsInDistOrders = DistributorOrder::where('distributor_id', $distributorId)
                    ->where('invoice_no', $invoiceNo)
                    ->where('id', '!=', $distributorOrder->id)
                    ->exists();

                $existsInRetailOrders = \App\Models\RetailerOrder::where('distributor_id', $distributorId)
                    ->where('invoice_no', $invoiceNo)
                    ->exists();

                if ($existsInDistOrders || $existsInRetailOrders) {
                    return response()->json([
                        'error'     => "Invoice number '{$invoiceNo}' has already been used for another order by this distributor. Please use a unique invoice number.",
                        'duplicate' => true
                    ], 422);
                }
            }

            // --- File Hash Duplication Check ---
            $fileHash = md5_file($file->getRealPath());

            // Check retail orders for the same hash
            $existingRetailer = \App\Models\RetailerOrder::whereNotNull('metadata')->whereJsonContains('metadata->invoice_hash', $fileHash)->first();
            // Check dist orders for the same hash
            $existingDistributor = DistributorOrder::whereNotNull('metadata')
                ->where('id', '!=', $distributorOrder->id)
                ->whereJsonContains('metadata->invoice_hash', $fileHash)
                ->first();

            if ($existingRetailer || $existingDistributor) {
                $code = $existingRetailer ? $existingRetailer->order_code : $existingDistributor->order_code;
                $role = $existingRetailer ? 'Retailer' : 'Distributor';
                return response()->json([
                    'error'     => "This invoice has already been uploaded for $role Order #$code. Duplicate uploads are not allowed across roles.",
                    'duplicate' => true
                ], 400);
            }

            if ($distributorOrder->invoice_path && Storage::disk('public')->exists($distributorOrder->invoice_path)) {
                Storage::disk('public')->delete($distributorOrder->invoice_path);
            }

            $path = $file->store('invoices/distributors', 'public');

            $metadata = $distributorOrder->metadata ?? [];
            $metadata['invoice_hash'] = $fileHash;

            $distributorOrder->invoice_path = $path;
            $distributorOrder->metadata     = $metadata;

            // Save invoice number if provided
            if ($request->filled('invoice_no')) {
                $distributorOrder->invoice_no = trim($request->invoice_no);
            }

            $distributorOrder->save();

            return response()->json([
                'success'     => 'Invoice uploaded successfully!',
                'invoice_url' => asset('storage/' . $path)
            ]);
        }

        return response()->json(['error' => 'No file uploaded.'], 400);
    }

    public function destroy(distributorOrder $distributorOrder)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isOwner = ($user->hasRole('distributor') && $distributorOrder->distributor_id === $user->distributor?->id);
        $isAdmin = $user->hasAnyRole(['admin', 'superadmin']);

        if (!$isAdmin && !$isOwner) {
            return response()->json(['error' => 'No permission to delete orders.'], 403);
        }

        if ($isOwner && $distributorOrder->status !== DistributorOrder::STATUS_PENDING) {
            return response()->json(['error' => 'You can only delete orders while they are pending.'], 400);
        }

        $distributorOrder->items()->delete(); // Delete items first
        $this->deleteOrderNotifications($distributorOrder->id, 'distributor_order');
        $distributorOrder->delete();

        return response()->json(['success' => 'Order deleted.']);
    }

    public function approveOrder(Request $request, DistributorOrder $distributorOrder)
    {
        // Invoice validation is now mandatory
        $request->validate([
            'invoice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if (!Auth::user()->hasPermissionToCategory('distributor_approvals', 'edit') && !Auth::user()->hasRole('salesmanager')) {
            return response()->json(['error' => 'Only Sales Managers can approve orders.'], 403);
        }

        // Upload Invoice (Mandatory)
        $path = null;
        if ($request->hasFile('invoice')) {
            $path = $request->file('invoice')->store('invoices/distributors', 'public');
        }

        $distributorOrder->update([
            'status' => DistributorOrder::STATUS_PROCESSING,
            'sales_manager_id' => Auth::user()->salesManager->id,
            'invoice_path' => $path,
        ]);

        // Clear existing notifications for this order
        $this->clearOrderNotifications($distributorOrder->id, 'distributor_order');

        // Notify Admins
        $admins = \App\Models\User::role('admin')->get();
        foreach ($admins as $admin) {
            $this->notifyUnique($admin, new OrderActionRequired(
                $distributorOrder,
                "Distributor Order #{$distributorOrder->order_code} has been processed and is ready for your approval.",
                route('admin.approvals.distributor'),
                'distributor_order'
            ));
        }

        return response()->json([
            'success' => 'Order approved.',
            'invoice_url' => $path ? asset('storage/' . $path) : null
        ]);
    }

    public function removeInvoice(Request $request, DistributorOrder $distributorOrder)
    {
        if ($distributorOrder->invoice_path) {
            if (Storage::disk('public')->exists($distributorOrder->invoice_path)) {
                Storage::disk('public')->delete($distributorOrder->invoice_path);
            }
            $distributorOrder->invoice_path = null;
            $distributorOrder->save();
            return response()->json(['success' => 'Invoice removed.']);
        }
        return response()->json(['error' => 'No invoice to remove'], 400);
    }
    private function addOrderItemsToInventory(DistributorOrder $order)
    {
        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product) continue;

            $unit = strtolower($item->unit);
            $freeStrips = $this->convertQuantityToStrips($product, $item->free_quantity ?? 0, $unit);
            $freeAdded = false;

            foreach ($item->batches as $batch) {
                $qty = $batch->quantity;
                
                // Use shared conversion helper for accuracy (handles box, carton, nos/tablets)
                $totalStrips = $this->convertQuantityToStrips($product, $qty, $unit);

                if (!$freeAdded && $freeStrips > 0) {
                    $totalStrips += $freeStrips;
                    $freeAdded = true;
                }

                $inventory = Inventory::firstOrNew([
                    'distributor_id' => $order->distributor_id,
                    'product_id' => $product->id,
                    'batch_no' => $batch->batch_no,
                    'expiry_date' => $batch->expiry_date,
                    'side' => empty($item->side) ? null : $item->side,
                    'size' => empty($item->size) ? null : $item->size
                ]);

                if (!$inventory->exists) {
                    $inventory->distributor_product_code = $product->product_code ?? '---';
                    $inventory->product_name = $item->product_name;
                    $inventory->stock = 0;
                }
                $inventory->product_name = $item->product_name;

                $inventory->stock += $totalStrips;

                // Copy financial records from the confirmed order batch to the distributor's inventory
                $inventory->mrp = $batch->mrp;
                $inventory->ptr = $batch->ptr;
                $inventory->pts = $batch->pts;
                $inventory->taxable_value = $batch->taxable_value;
                $inventory->cgst = $batch->cgst;
                $inventory->sgst = $batch->sgst;
                $inventory->igst = $batch->igst;
                $inventory->net_amount = $batch->net_amount;

                $inventory->save();
            }
        }
    }

    public function confirmReceipt(DistributorOrder $distributorOrder)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if user is the distributor for this order or has admin roles
        $isOrderDistributor = ($user->hasRole('distributor') && $distributorOrder->distributor_id === $user->distributor?->id);
        $isAdminLike = $user->hasAnyRole(['admin', 'superadmin', 'salesmanager']);

        if (!$isOrderDistributor && !$isAdminLike) {
            return response()->json(['error' => 'Unauthorized action. Only the assigned distributor or an admin can confirm receipt.'], 403);
        }

        if ($distributorOrder->status !== 'approved') {
            return response()->json(['error' => 'Order must be accepted by Admin before confirmation.'], 400);
        }

        try {
            DB::beginTransaction();

            $distributorOrder->update([
                'status' => 'delivered',
                'delivered_at' => now()
            ]);

            // Add items to distributor inventory ONLY when they confirm receipt
            $this->addOrderItemsToInventory($distributorOrder);

            DB::commit();

            return response()->json(['success' => 'Order delivery confirmed and items added to inventory!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirming distributor order receipt: ' . $e->getMessage());
            return response()->json(['error' => 'Error confirming order: ' . $e->getMessage()], 500);
        }
    }
}
