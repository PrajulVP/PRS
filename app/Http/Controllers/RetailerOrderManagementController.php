<?php

namespace App\Http\Controllers;

use App\Models\FieldStaff;
use App\Models\RetailerOrder;
use App\Models\Distributor;
use App\Models\Retailer;
use App\Models\Product;
use App\Models\SalesManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Notifications\OrderActionRequired;
use App\Traits\HandlesNotifications;
use App\Traits\OneSignalNotifications;
use Illuminate\Support\Facades\Storage;

class RetailerOrderManagementController extends Controller
{
    use HandlesNotifications, OneSignalNotifications, \App\Traits\CalculatesPrices;
    // Create Order Page
    public function create()
    {
        $retailers = Retailer::with('user')->get();
        // Distributors are now fetched via AJAX per product, or generally available?
        // Actually, we need distributors list if we were to show them all, but we show per product.
        // We can just pass active retailers.

        $products = Product::with('distributors.user')->get(); // We can optimize this later if needed, or remove eager load if AJAX is used for everything.
        // For now, let's keep products list but remove eager loading from index to speed up, 
        // AND add the AJAX endpoint.
        // User asked for AJAX to fetch info.

        // Revised: We will keep the products list for the dropdown, but minimal data.
        $products = Product::select('id', 'product_name', 'mrp', 'ptr', 'pack', 'brand')->get();
        $brands = Product::select('brand')->distinct()->whereNotNull('brand')->where('brand', '!=', '')->orderBy('brand')->pluck('brand');
        $eligibleFreeProducts = Product::where('is_free_eligible', true)->get();

        return view('admin.orders.retailers.create', compact('retailers', 'products', 'brands', 'eligibleFreeProducts'));
    }

    public function getProducts(Request $request)
    {
        $brand = $request->get('brand');
        $query = Product::select('id', 'product_name', 'mrp', 'ptr', 'pack', 'brand');
        if ($brand) {
            $query->where('brand', $brand);
        }
        $products = $query->get();
        return response()->json($products);
    }

    public function getProductDetails(Request $request, Product $product)
    {
        $retailerId = $request->get('retailer_id');
        $retailer = Retailer::find($retailerId);

        $side = $request->get('side');
        $size = $request->get('size');
        $minQuantity = (int)$request->get('quantity', 0);
        $unit = $request->get('unit', 'Strips');

        // Filter distributors by the retailer's district
        $query = Distributor::with('user');
        if ($retailer && $retailer->district_id) {
            $query->where('district_id', $retailer->district_id);
        }
        $allDistributors = $query->get();

        // Convert quantity to base unit (Strips/Nos) for correct DB query filtering
        $multiplier = 1.0;
        if ($unit === 'Box') {
            $multiplier = (double)($product->strips_per_box ?: 1);
        } elseif ($unit === 'Carton') {
            $multiplier = (double)($product->strips_per_box ?: 1) * (double)($product->boxes_per_carton ?: 1);
        } elseif ($unit === 'Nos') {
            $multiplier = 1.0 / max(1, (int)($product->units_per_strip ?: 1));
        }
        $minQuantityBase = (double)$minQuantity * $multiplier;

        // Get current stock levels for this product (and specific variant if provided)
        $stockMap = DB::table('inventories')
            ->where('product_id', $product->id)
            ->when(!empty($side), function ($q) use ($side) {
                return $q->where(function ($sq) use ($side) {
                    $sq->where('side', $side)
                       ->orWhereNull('side')
                       ->orWhere('side', '');
                });
            })
            ->when(!empty($size), function ($q) use ($size) {
                return $q->where(function ($sq) use ($size) {
                    $sq->where('size', $size)
                       ->orWhereNull('size')
                       ->orWhere('size', '');
                });
            })
            ->selectRaw('distributor_id, SUM(stock) as total_stock')
            ->groupBy('distributor_id')
            ->having('total_stock', '>', 0)
            ->when($minQuantityBase > 0, function ($q) use ($minQuantityBase) {
                return $q->having('total_stock', '>=', $minQuantityBase);
            })
            ->pluck('total_stock', 'distributor_id');

        $distributors = $allDistributors->filter(function ($distributor) use ($stockMap) {
            return $stockMap->has($distributor->id);
        })->map(function ($distributor) use ($stockMap) {
            $distributor->pivot = (object)[
                'stock' => $stockMap[$distributor->id]
            ];
            return $distributor;
        });

        // Sort by stock (descending) as distance is removed
        $distributors = $distributors->sortByDesc(function ($d) {
            return $d->pivot->stock ?? 0;
        })->values();

        return response()->json([
            'product' => $product->makeVisible(['box_size']),
            'distributors' => $distributors
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

    // Store (Admin Create)
    public function store(Request $request)
    {
        $request->validate([
            'retailer_id' => 'required|exists:retailers,id',
            'status' => 'required',
            'items' => 'required|array|min:1',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            DB::beginTransaction();

            $retailer = Retailer::findOrFail($request->retailer_id);

            // Group items by distributor
            $itemsByDistributor = collect($request->items)->groupBy('distributor_id');

            foreach ($itemsByDistributor as $distributorId => $items) {
                // Ensure distributor exists if ID is present
                $distributor = $distributorId ? Distributor::find($distributorId) : null;

                // If no distributor selected/found, fallback or skip (based on logic, here we create order even if null if allowed)

                // Create Order
                $order = RetailerOrder::create([
                    'retailer_id' => $retailer->id,
                    'distributor_id' => $distributor ? $distributor->id : null,
                    'fieldstaff_id' => ($user->hasRole('fieldstaff') && $user->fieldStaff) ? $user->fieldStaff->id : $retailer->field_staff_id,
                    'order_code' => 'ORD-' . strtoupper(uniqid()),
                    'status' => $request->status,
                    'delivery_notes' => $request->delivery_notes,
                    'total_amount' => 0,
                    'total_items' => 0,
                    'total_quantity' => 0,
                    'placed_at' => now(),
                ]);

                $totalAmount = 0;
                $totalItems = 0;
                $totalQuantity = 0;

                // Merge identical items before processing
                $mergedItems = collect($items)->groupBy(function ($i) {
                    $side = isset($i['side']) ? trim(strtolower($i['side'])) : '';
                    $size = isset($i['size']) ? trim(strtolower($i['size'])) : '';
                    return $i['product_id'] . '-' . $side . '-' . $size;
                })->map(function ($group) {
                    $first = $group->first();
                    $first['quantity'] = $group->sum('quantity');
                    $first['free_quantity'] = $group->sum('free_quantity');

                    // Keep normalized values for later creation
                    $first['side'] = isset($first['side']) ? trim($first['side']) : null;
                    $first['size'] = isset($first['size']) ? trim($first['size']) : null;

                    return $first;
                });

                foreach ($mergedItems as $itemData) {
                    $product = Product::find($itemData['product_id']);
                    if (!$product) continue;

                    $unit = $itemData['unit'] ?? 'Nos';
                    $qty = (int)$itemData['quantity'];

                    // Conversion logic using numeric fields
                    $multiplier = 1;
                    $normalizedUnit = strtolower($unit);
                    if ($normalizedUnit === 'box') {
                        $multiplier = (int)($product->strips_per_box ?? 1);
                    } elseif ($normalizedUnit === 'carton') {
                        $multiplier = (int)($product->boxes_per_carton ?? 1) * (int)($product->strips_per_box ?? 1);
                    } elseif ($normalizedUnit === 'nos' || $normalizedUnit === 'no' || $normalizedUnit === 'unit') {
                        $multiplier = 1 / (max(1, (int)($product->units_per_strip ?? 1)));
                    }
                    $totalQtyNos = $qty * $multiplier;

                    $iSide = $itemData['side'] ?? null;
                    $iSize = $itemData['size'] ?? null;
                    $variant = $itemData['variant'] ?? null;

                    // Fallback: If side/size are missing but variant is present, try to split it
                    if ((!$iSide || !$iSize) && $variant) {
                        if (str_contains($variant, ' - ')) {
                            $parts = explode(' - ', $variant);
                            if (count($parts) >= 2) {
                                if (in_array(strtoupper(trim($parts[0])), ['LEFT', 'RIGHT'])) {
                                    $iSide = $iSide ?: trim($parts[0]);
                                    $iSize = $iSize ?: trim($parts[1]);
                                } else {
                                    $iSize = $iSize ?: trim($parts[0]);
                                    $iSide = $iSide ?: trim($parts[1]);
                                }
                            }
                        } elseif (in_array(strtoupper(trim($variant)), ['LEFT', 'RIGHT'])) {
                            $iSide = $iSide ?: trim($variant);
                        } else {
                            $iSize = $iSize ?: trim($variant);
                        }
                    }

                    // Availability check (In base units)
                    if ($distributor) {
                        $totalStock = DB::table('inventories')
                            ->where('distributor_id', $distributor->id)
                            ->where('product_id', $product->id)
                            ->when(!empty($iSide), function ($q) use ($iSide) {
                                return $q->where(function ($sq) use ($iSide) {
                                    $sq->where('side', $iSide)
                                       ->orWhereNull('side')
                                       ->orWhere('side', '');
                                });
                            })
                            ->when(!empty($iSize), function ($q) use ($iSize) {
                                return $q->where(function ($sq) use ($iSize) {
                                    $sq->where('size', $iSize)
                                       ->orWhereNull('size')
                                       ->orWhere('size', '');
                                });
                            })
                            ->sum('stock');

                        if ($totalStock < $totalQtyNos) {
                            $vInfo = array_filter([$iSide, $iSize]);
                            $vLabel = !empty($vInfo) ? " (" . implode('/', $vInfo) . ")" : "";
                            throw new \Exception("Insufficient stock for {$product->product_name}{$vLabel}. Please select another distributor.");
                        }
                    }

                    // Price Logic: Retailer buys at PTR (Price to Retailer)
                    $price = (float)$product->ptr;
                    $gstRate = (float)($product->gst ?? 0);
                    $taxableSubtotal = $totalQtyNos * $price;
                    $gstAmount = $taxableSubtotal * ($gstRate / 100);
                    $subtotalWithGst = $taxableSubtotal + $gstAmount;

                    // Append variant to product name if provided
                    $finalProductName = $product->product_name;
                    $vLabelParts = array_filter([$iSide, $iSize]);
                    if (!empty($vLabelParts)) {
                        $finalProductName .= ' [' . implode('/', $vLabelParts) . ']';
                    }

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $finalProductName,
                        'quantity' => $qty,
                        'free_quantity' => $itemData['free_quantity'] ?? 0,
                        'unit' => $unit,
                        'unit_price' => $price,
                        'total_amount' => $subtotalWithGst,
                        'side' => $iSide,
                        'size' => $iSize,
                        'free_product_id' => $itemData['free_product_id'] ?? null,
                        'free_side' => $itemData['free_side'] ?? null,
                        'free_size' => $itemData['free_size'] ?? null,
                    ]);

                    $totalAmount += $subtotalWithGst;
                    $totalItems++;
                    $totalQuantity += $totalQtyNos;
                }

                $order->update([
                    'total_amount' => $totalAmount,
                    'total_items' => $totalItems,
                    'total_quantity' => $totalQuantity
                ]);

                // Notify Field Staff
                if ($order->fieldStaff && $order->fieldStaff->user) {
                    $order->fieldStaff->user->notify(new OrderActionRequired($order, "New order #{$order->order_code} assigned to you. Action required: Process or Cancel.", route('admin.approvals.retailer')));

                    // OneSignal Push
                    $this->sendOneSignalPush(
                        [$order->fieldStaff->user->id],
                        "New order #{$order->order_code} assigned to you. Action required: Process or Cancel.",
                        ['order_id' => $order->id, 'type' => 'retailer_order'],
                        'New Order Assigned'
                    );
                }
            }

            DB::commit();

            return response()->json(['success' => 'Order created.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(RetailerOrder $retailerOrder)
    {
        return $this->invoice($retailerOrder);
    }

    // Admin/Manager: List all orders
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Permission check
        if (!$user->hasAnyRole(['admin', 'superadmin', 'salesmanager', 'retailer', 'distributor', 'fieldstaff']) && !$user->hasPermissionToCategory('retailer_orders', 'view')) {
            abort(403, 'Unauthorized action. You do not have permission to view retailer orders.');
        }

        if ($request->ajax()) {
            try {
                // Determine query based on role
                $query = RetailerOrder::with(['retailer.user', 'retailer.area', 'retailer.district', 'retailer.salesManager.user', 'retailer.fieldStaff.user', 'fieldStaff.user', 'items.product', 'distributor.user', 'returnRequests']);
                Log::info("RetailerOrderManagementController@index: User ID " . $user->id . " Role " . ($user->hasRole('retailer') ? 'retailer' : 'other'));

                if ($user->hasRole('distributor')) {
                    $distributor = Auth::user()->distributor;
                    if ($distributor) {
                        $query->where('distributor_id', $distributor->id);
                    } else {
                        // Return empty if no distributor profile found for this user
                        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
                    }
                } elseif ($user->hasRole('fieldstaff')) {
                    $fieldStaff = $user->fieldStaff;
                    if ($fieldStaff) {
                        $query->where(function ($q) use ($fieldStaff) {
                            $q->where('fieldstaff_id', $fieldStaff->id)
                                ->orWhereHas('retailer', function ($subQ) use ($fieldStaff) {
                                    $subQ->where('field_staff_id', $fieldStaff->id);
                                });
                        });
                    } else {
                        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
                    }
                } elseif ($user->hasRole('salesmanager')) {
                    $salesManager = $user->salesManager;
                    if ($salesManager) {
                        $query->where(function ($q) use ($salesManager) {
                            $q->whereHas('retailer', function ($subQ) use ($salesManager) {
                                $subQ->whereHas('fieldStaff', function ($fsQ) use ($salesManager) {
                                    $fsQ->where('sales_manager_id', $salesManager->id);
                                });
                            })->orWhereHas('fieldStaff', function ($fsQ) use ($salesManager) {
                                $fsQ->where('sales_manager_id', $salesManager->id);
                            });
                        });
                    }
                } elseif ($user->hasRole('retailer')) {
                    $retailer = $user->retailer;
                    if ($retailer) {
                        $query->where('retailer_id', $retailer->id);
                    } else {
                        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
                    }
                }

                // Base count for this role (without custom filters)
                $totalData = $query->count();

                // Apply Custom Filters from request
                if ($request->has('retailer_id') && !empty($request->retailer_id)) {
                    $query->where('retailer_orders.retailer_id', $request->retailer_id);
                }

                if ($request->has('distributor_id') && !empty($request->distributor_id)) {
                    $query->where('retailer_orders.distributor_id', $request->distributor_id);
                }

                if ($request->has('fieldstaff_id') && !empty($request->fieldstaff_id)) {
                    $fsId = $request->fieldstaff_id;
                    $query->where(function ($q) use ($fsId) {
                        $q->where('retailer_orders.fieldstaff_id', $fsId)
                            ->orWhereHas('retailer', function ($subQ) use ($fsId) {
                                $subQ->where('field_staff_id', $fsId);
                            });
                    });
                }

                if ($request->has('sales_manager_id') && !empty($request->sales_manager_id)) {
                    $smId = $request->sales_manager_id;
                    $query->where(function ($q) use ($smId) {
                        $q->whereHas('fieldStaff', function ($sub) use ($smId) {
                            $sub->where('sales_manager_id', $smId);
                        })->orWhereHas('retailer.fieldStaff', function ($sub) use ($smId) {
                            $sub->where('sales_manager_id', $smId);
                        });
                    });
                }

                // Apply status filter if exists
                if ($request->has('status') && !empty($request->input('status'))) {
                    $query->where('retailer_orders.status', $request->input('status'));
                }

                // Apply date range filters if exist
                if ($request->has('start_date') && !empty($request->input('start_date'))) {
                    $query->whereDate('retailer_orders.placed_at', '>=', $request->input('start_date'));
                }
                if ($request->has('end_date') && !empty($request->input('end_date'))) {
                    $query->whereDate('retailer_orders.placed_at', '<=', $request->input('end_date'));
                }

                // Apply payment_status filter if exists
                if ($request->has('payment_status') && !empty($request->input('payment_status'))) {
                    $status = $request->input('payment_status');
                    if ($status === 'pending') {
                        $query->where(function ($q) {
                            $q->where('retailer_orders.payment_status', 'pending')
                                ->orWhereNull('retailer_orders.payment_status');
                        });
                    } else {
                        $query->where('retailer_orders.payment_status', $status);
                    }
                }

                // Global Search
                if ($request->has('search') && !empty($request->input('search')['value'])) {
                    $searchValue = $request->input('search')['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('retailer_orders.order_code', 'like', "%{$searchValue}%")
                            ->orWhere('retailer_orders.status', 'like', "%{$searchValue}%")
                            ->orWhereHas('retailer.user', function ($subQuery) use ($searchValue) {
                                $subQuery->where('name', 'like', "%{$searchValue}%");
                            });
                    });
                }

                $totalFiltered = $query->count();

                // Validation for order/sort inputs
                $orderColumnIndex = $request->input('order.0.column', 0); // Default to 0
                $orderDir = $request->input('order.0.dir', 'desc');
                $columns = $request->input('columns', []);
                $columnName = $columns[$orderColumnIndex]['data'] ?? 'id';

                switch ($columnName) {
                    case 'id':
                        $query->orderBy('retailer_orders.id', $orderDir);
                        break;
                    case 'order_code':
                        $query->orderBy('retailer_orders.order_code', $orderDir);
                        break;
                    case 'retailer_name':
                        $query->join('retailers', 'retailer_orders.retailer_id', '=', 'retailers.id')
                            ->join('users', 'retailers.user_id', '=', 'users.id')
                            ->orderBy('users.name', $orderDir)
                            ->select('retailer_orders.*');
                        break;
                    case 'distributor_name':
                        $query->leftJoin('distributors', 'retailer_orders.distributor_id', '=', 'distributors.id')
                            ->leftJoin('users as dist_users', 'distributors.user_id', '=', 'dist_users.id')
                            ->orderBy('dist_users.name', $orderDir)
                            ->select('retailer_orders.*');
                        break;
                    case 'total_amount':
                        $query->orderBy('retailer_orders.total_amount', $orderDir);
                        break;
                    case 'status':
                        $query->orderBy('retailer_orders.status', $orderDir);
                        break;
                    case 'placed_at':
                        $query->orderBy('retailer_orders.placed_at', $orderDir);
                        break;
                    default:
                        $query->orderBy('retailer_orders.id', 'desc');
                        break;
                }

                $start = $request->input('start', 0);
                $length = $request->input('length', 10);
                $orders = $query->offset($start)->limit($length)->get();
                $formattedOrders = $orders->map(function ($order) {
                    $groupedItems = $order->items->groupBy(function ($item) {
                        $side = $item->side ? trim(strtolower($item->side)) : '';
                        $size = $item->size ? trim(strtolower($item->size)) : '';
                        return $item->product_id . '-' . $side . '-' . $size;
                    })->map(function ($group) {
                        $first = $group->first();
                        return (object)[
                            'product_name' => $first->product_name ?? $first->product->product_name ?? $first->name ?? 'Product',
                            'quantity' => $group->sum('quantity'),
                            'free_quantity' => $group->sum('free_quantity'),
                            'unit' => $first->unit,
                            'side' => $first->side,
                            'size' => $first->size,
                            'product' => $first->product
                        ];
                    });

                    $productSummary = $groupedItems->map(function ($item) {
                        $pName = $item->product ? $item->product->product_name : $item->product_name;

                        // Clean up product name from any existing brackets to prevent duplication
                        if (str_contains($pName, '[')) {
                            $pName = trim(explode('[', $pName)[0]);
                        }

                        $vLabel = array_filter([$item->side, $item->size]);
                        $pBrand = $item->product ? $item->product->brand : null;

                        $pPack = $item->product ? $item->product->pack : null;

                        $summary = '<div class="product-summary-item mb-2" style="line-height: 1.35; width: 100%; white-space: normal; word-break: break-word; overflow-wrap: break-word;">';
                        $summary .= '<div style="display: block; margin-bottom: 2px;">';
                        $summary .= '<span class="fw-bold" style="color: #334155; font-size: 0.85rem; word-break: break-word;">' . $pName . '</span>';
                        if (!empty(trim($pPack)) && strtoupper(trim($pPack)) !== 'N/A') {
                            $summary .= '<span class="small fw-semibold" style="color: #94a3b8; font-size: 0.75rem; white-space: nowrap; margin-left: 3px;">[' . $pPack . ']</span>';
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

                    $brandSummary = $order->items->map(function ($item) {
                        return $item->product ? ($item->product->brand ?? 'N/A') : 'N/A';
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

                    return [
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'brand_summary' => $brandSummary,
                        'free_summary' => $freeSummary,
                        'retailer_name' => $order->retailer?->user?->name ?? 'N/A',
                        'retailer_sm_name' => $order->retailer?->salesManager?->user?->name ?? 'N/A',
                        'retailer_fs_name' => $order->retailer?->fieldStaff?->user?->name ?? 'N/A',
                        'retailer_shop' => $order->retailer?->shop_name ?? '',
                        'retailer_phone' => $order->retailer?->contact_no ?? $order->retailer?->phone ?? '',
                        'retailer_address' => trim(($order->retailer?->address ?? '') . ' ' . ($order->retailer?->pincode ?? '')),
                        'retailer_area' => $order->retailer?->area?->name ?? 'N/A',
                        'retailer_district' => $order->retailer?->district?->name ?? 'N/A',
                        'retailer_gst' => $order->retailer?->gst ?? 'N/A',
                        'retailer_dl' => $order->retailer?->drug_license_no ?? 'N/A',
                        'retailer_id' => $order->retailer_id,
                        'distributor_id' => $order->distributor_id,
                        'distributor_name' => $order->distributor?->name ?? $order->distributor?->user?->name ?? 'N/A',
                        'distributor_phone' => $order->distributor?->contact_no ?? $order->distributor?->phone ?? '',
                        'fieldstaff_id' => $order->fieldstaff_id,
                        'retailer_fs_id' => $order->retailer?->field_staff_id,
                        'metadata' => $order->metadata,
                        'product_summary' => $productSummary,
                        'items' => $order->items->map(function ($item) use ($order) {
                            $pName = $item->product_name ?? $item->product->product_name ?? $item->name ?? 'Product';

                            // Find corresponding return request if any
                            $retReq = $order->returnRequests->where('product_id', $item->product_id)
                                ->where('side', $item->side)
                                ->where('size', $item->size)
                                ->first();

                            return [
                                'product_name' => $pName,
                                'side' => $item->side,
                                'size' => $item->size,
                                'free_quantity' => $item->free_quantity,
                                'free_side' => $item->free_side,
                                'free_size' => $item->free_size,
                                'quantity' => $item->quantity,
                                'unit' => $item->unit ?? 'Strips',
                                'unit_price' => $item->unit_price,
                                'total_amount' => $item->total_amount,
                                'order_item_id' => $item->id,
                                'product_id' => $item->product_id,
                                'is_returnable' => $item->product?->is_returnable ?? true,
                                'is_free' => $item->is_free ?? false,

                                'returned_qty' => (float)$order->returnRequests
                                    ->where('product_id', $item->product_id)
                                    ->where('side', $item->side)
                                    ->where('size', $item->size)
                                    ->where('status', 'completed')
                                    ->sum('quantity'),

                                'pending_return_qty' => (float)$order->returnRequests
                                    ->where('product_id', $item->product_id)
                                    ->where('side', $item->side)
                                    ->where('size', $item->size)
                                    ->whereIn('status', ['pending', 'approved_tier1'])
                                    ->sum('quantity'),

                                'return_status' => $retReq ? $retReq->status : null,
                                'return_code' => $retReq ? $retReq->return_code : null,
                                'pack' => $item->product?->pack,
                                'brand' => $item->product?->brand,
                                'generic_name' => $item->product?->generic_name,
                                'product_code' => $item->product?->product_code,
                                'strip_size' => $item->product?->strip_size,
                                'box_size' => $item->product?->box_size,
                                'carton_size' => $item->product?->carton_size,
                                'stock' => 9999,
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
                        'total_items' => $order->total_items,
                        'total_quantity' => $order->total_quantity,
                        'total_amount' => $order->total_amount,
                        'status' => ucfirst(str_replace('_', ' ', $order->status)),
                        'placed_at' => $order->placed_at ? \Carbon\Carbon::parse($order->placed_at)->format('Y-m-d H:i:s') : '-',
                        'delivered_at' => $order->delivered_at ? \Carbon\Carbon::parse($order->delivered_at)->format('Y-m-d H:i:s') : null,
                        'payment_status' => $order->payment_status ?? 'pending',
                        'invoice_url' => $order->invoice_path ? asset('storage/' . $order->invoice_path) : null,
                        'cancellation_reason' => $order->cancellation_reason,
                    ];
                });

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalData,
                    'recordsFiltered' => $totalFiltered,
                    'data' => $formattedOrders,
                ]);
            } catch (\Exception $e) {
                Log::error('Error in RetailerOrderManagementController@index: ' . $e->getMessage());
                return response()->json(['error' => 'Server Error'], 500);
            }
        }

        $fieldstaffs = collect();
        $retailers = collect();
        $salesManagers = collect();
        $distributors = Distributor::with('user')->get();
        $products = Product::all();

        if ($user->hasAnyRole(['admin', 'superadmin'])) {
            $salesManagers = SalesManager::with('user')->get();
            $fieldstaffs = FieldStaff::with('user')->get();
            $retailers = Retailer::with('user')->get();
        } elseif ($user->hasRole('salesmanager')) {
            $salesManagers = SalesManager::where('user_id', $user->id)->with('user')->get();
            $fieldstaffs = FieldStaff::where('sales_manager_id', $user->salesManager->id)->with('user')->get();
            $retailers = Retailer::whereIn('field_staff_id', $fieldstaffs->pluck('id'))->with('user')->get();
        } elseif ($user->hasRole('fieldstaff')) {
            $fieldstaffs = FieldStaff::where('user_id', $user->id)->with('user')->get();
            $retailers = Retailer::where('field_staff_id', $user->fieldStaff->id)->with('user')->get();
        }

        return view('admin.orders.retailers.index', compact('fieldstaffs', 'retailers', 'products', 'distributors', 'salesManagers'));
    }

    public function getFieldStaffsByManager(Request $request)
    {
        $managerId = $request->manager_id;
        $query = FieldStaff::with('user');

        if ($managerId) {
            $query->where('sales_manager_id', $managerId);
        }

        $fs = $query->get()->map(function ($f) {
            return ['id' => $f->id, 'name' => $f->user->name ?? 'N/A'];
        });
        return response()->json($fs);
    }

    public function getRetailersByFieldStaff(Request $request)
    {
        $fsId = $request->fieldstaff_id;
        $query = Retailer::with('user');

        if ($fsId) {
            $query->where('field_staff_id', $fsId);
        }

        $retailers = $query->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'name' => ($r->shop_name ?? 'N/A') . ' (' . ($r->user->name ?? 'N/A') . ')'
            ];
        });
        return response()->json($retailers);
    }

    // Manager/Admin/Superadmin/FieldStaff/Distributor: Accept Order
    public function rejectOrder(Request $request, RetailerOrder $retailerOrder)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5'
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if distributor or admin/manager
        if ($user->hasRole('distributor')) {
            if ($retailerOrder->distributor_id !== $user->distributor->id) {
                return response()->json(['error' => 'This order is not assigned to your distributorship.'], 403);
            }
        } elseif (!$user->hasAnyRole(['admin', 'superadmin', 'salesmanager', 'fieldstaff'])) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        if (!in_array($retailerOrder->status, [RetailerOrder::STATUS_PENDING, RetailerOrder::STATUS_PROCESSING])) {
            return response()->json(['error' => 'Only pending or processing orders can be rejected.'], 400);
        }

        $retailerOrder->update([
            'status' => RetailerOrder::STATUS_REJECTED,
            'cancellation_reason' => $request->rejection_reason
        ]);

        // Notify Retailer
        if ($retailerOrder->retailer && $retailerOrder->retailer->user) {
            $retailerOrder->retailer->user->notify(new OrderActionRequired(
                $retailerOrder,
                "Your order #{$retailerOrder->order_code} has been rejected. Reason: {$request->rejection_reason}",
                route('retailer.orders.index'),
                'retailer_order'
            ));

            // OneSignal Push to Retailer
            $this->sendOneSignalPush(
                [$retailerOrder->retailer->user->id],
                "Your order #{$retailerOrder->order_code} has been rejected by " . ($user->hasRole('distributor') ? 'the distributor' : 'admin') . ".",
                ['order_id' => $retailerOrder->id, 'type' => 'retailer_order'],
                'Order Rejected'
            );
        }

        // Notify Field Staff
        if ($retailerOrder->fieldStaff && $retailerOrder->fieldStaff->user) {
            $retailerOrder->fieldStaff->user->notify(new OrderActionRequired(
                $retailerOrder,
                "Order #{$retailerOrder->order_code} has been rejected.",
                route('admin.approvals.retailer'),
                'retailer_order'
            ));

            // OneSignal Push to FS
            $this->sendOneSignalPush(
                [$retailerOrder->fieldStaff->user->id],
                "Order #{$retailerOrder->order_code} has been rejected.",
                ['order_id' => $retailerOrder->id, 'type' => 'retailer_order'],
                'Order Rejected'
            );
        }

        return response()->json(['success' => 'Order rejected.']);
    }

    public function validateBatches(Request $request)
    {
        $itemsBatches = $request->items_batches;
        if (!$itemsBatches) return response()->json(['success' => true]);

        $orderId = $request->order_id;
        $order = \App\Models\RetailerOrder::find($orderId);
        if (!$order) return response()->json(['success' => true]);

        $distributorId = $order->distributor_id;
        $errors = [];

        foreach ($itemsBatches as $allocation) {
            $orderItem = $order->items()->find($allocation['order_item_id']);
            if (!$orderItem) continue;

            $product = $orderItem->product;
            foreach ($allocation['batches'] as $batchData) {
                if (empty($batchData['batch_no'])) continue;

                $invQuery = \App\Models\Inventory::where('distributor_id', $distributorId)
                    ->where('product_id', $product->id);

                if (empty($orderItem->side)) {
                    $invQuery->where(function($q) {
                        $q->whereNull('side')->orWhere('side', '');
                    });
                } else {
                    $invQuery->where('side', $orderItem->side);
                }

                if (empty($orderItem->size)) {
                    $invQuery->where(function($q) {
                        $q->whereNull('size')->orWhere('size', '');
                    });
                } else {
                    $invQuery->where('size', $orderItem->size);
                }

                $inventory = $invQuery->where('batch_no', $batchData['batch_no'])->first();
                if (!$inventory) {
                    $vLabel = array_filter([$orderItem->side, $orderItem->size]);
                    $pName = $product->product_name;
                    if (!empty($vLabel)) {
                        $pName .= ' [' . implode('/', $vLabel) . ']';
                    }
                    $errors[] = "Could not find batch '{$batchData['batch_no']}' in your inventory for {$pName}.";
                }
            }
        }

        if (count($errors) > 0) {
            return response()->json(['success' => false, 'errors' => $errors]);
        }

        return response()->json(['success' => true]);
    }
    public function acceptOrder(Request $request, RetailerOrder $retailerOrder)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Permission check: Need retailer_approvals.edit or Admin/Manager roles
        if (!$user->hasPermissionToCategory('retailer_approvals', 'edit') && !$user->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            // Also allow Field Staff and Distributor to accept if it's their order
            if (!$user->hasRole(['fieldstaff', 'distributor'])) {
                return response()->json(['error' => 'Permission denied'], 403);
            }
        }

        $status = $retailerOrder->status;

        // --- Role Specific Logic ---

        // 1. Field Staff accepting pending order
        if ($user->hasRole('fieldstaff')) {
            $fieldStaff = $user->fieldStaff;
            if (!$fieldStaff) {
                Log::error("Field Staff approval failed: User ID {$user->id} has fieldstaff role but no associated FieldStaff model.");
                return response()->json(['error' => 'Field Staff profile not found.'], 403);
            }

            // Allowed if assigned to the order OR assigned to the retailer
            $isAssignedToOrder = ($retailerOrder->fieldstaff_id == $fieldStaff->id);
            $isAssignedToRetailer = ($retailerOrder->retailer && $retailerOrder->retailer->field_staff_id == $fieldStaff->id);

            if (!$isAssignedToOrder && !$isAssignedToRetailer) {
                return response()->json(['error' => 'This order is not assigned to you, and the retailer is not in your list.'], 403);
            }

            if ($status !== 'pending') {
                return response()->json(['error' => 'Only pending orders can be accepted by Field Staff.'], 400);
            }

            // If it wasn't assigned to this specific FS order-wise but they are the retailer's FS, assign them now
            if (!$retailerOrder->fieldstaff_id) {
                $retailerOrder->fieldstaff_id = $fieldStaff->id;
            }

            $retailerOrder->status = 'processing';
            $retailerOrder->save();

            // Notify Distributor
            if ($retailerOrder->distributor && $retailerOrder->distributor->user) {
                $this->notifyUnique($retailerOrder->distributor->user, new OrderActionRequired($retailerOrder, "Order #{$retailerOrder->order_code} has been processed and is ready for your approval.", route('admin.approvals.retailer'), 'retailer_order'));

                // OneSignal Push
                $this->sendOneSignalPush(
                    [$retailerOrder->distributor->user->id],
                    "Order #{$retailerOrder->order_code} has been processed and is ready for your approval.",
                    ['order_id' => $retailerOrder->id, 'type' => 'retailer_order'],
                    'Order Processing Required'
                );
            }

            return response()->json(['success' => 'Order accepted.']);
        }

        // 2. Distributor accepting fieldstaff-accepted order
        if ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            if (!$distributor) {
                return response()->json(['error' => 'Distributor profile not found.'], 403);
            }

            if ($retailerOrder->distributor_id != $distributor->id) {
                return response()->json(['error' => 'This order is not for your distributorship.'], 403);
            }
            if ($status !== 'processing') {
                return response()->json(['error' => 'Order must be processed by Field Staff first.'], 400);
            }

            try {
                DB::beginTransaction();

                // 1. Batch Allocation Logic (Paid Items from Invoice)
                $allocatedOrderItemIds = [];
                
                if ($request->has('items_batches')) {
                    $itemsBatches = $request->items_batches; // Expected: [ {order_item_id: X, batches: [ {id: Y, quantity: Z} ]} ]
                    
                    // Validate allocations first
                    $qtyErrors = [];
                    foreach ($itemsBatches as $allocation) {
                        $orderItem = $retailerOrder->items()->findOrFail($allocation['order_item_id']);
                        $product = $orderItem->product;
                        $totalAllocated = 0;
                        foreach ($allocation['batches'] as $batchData) {
                            $totalAllocated += (float)$batchData['quantity'];
                        }
                        $pName = $product->product_name;
                        $vLabel = array_filter([$orderItem->side, $orderItem->size]);
                        if (!empty($vLabel)) {
                            $pName .= ' [' . implode('/', $vLabel) . ']';
                        }
                        
                        $expectedPaid = $orderItem->quantity;
                        
                        if ($totalAllocated < $expectedPaid) {
                            $qtyErrors[] = "Total allocated quantity ({$totalAllocated}) is less than the ordered paid quantity ({$expectedPaid}) for item: " . $pName;
                        }
                        // We NO LONGER overwrite free_quantity here. We trust the free_quantity already set on the order item.
                    }

                    if (!empty($qtyErrors)) {
                        throw new \Exception(implode("<br>", $qtyErrors));
                    }

                    foreach ($itemsBatches as $allocation) {
                        $orderItem = $retailerOrder->items()->findOrFail($allocation['order_item_id']);
                        $allocatedOrderItemIds[] = $orderItem->id;
                        $product = $orderItem->product;
                        // Conversion Factor using shared helper
                        $multiplier = $this->convertQuantityToStrips($product, 1, $orderItem->unit);
                        $totalAllocated = 0;
                        
                        $totalPaidQtyStrips = $orderItem->quantity * $multiplier;
                        $currentlyAllocatedStrips = 0;
                        
                        if ($orderItem->batches) {
                            $orderItem->batches()->delete(); // Clear existing batches
                        }
                        foreach ($allocation['batches'] as $batchData) {
                            if ($currentlyAllocatedStrips >= $totalPaidQtyStrips) {
                                break; // We have already allocated the required PAID quantity. Ignore excess from UI.
                            }
                            
                            $invId = isset($batchData['inventory_id']) ? str_replace(['"', "'"], '', $batchData['inventory_id']) : null;

                            $invQuery = \App\Models\Inventory::where('distributor_id', $distributor->id)
                                ->where('product_id', $product->id);

                            if (empty($orderItem->side)) {
                                $invQuery->where(function($q) {
                                    $q->whereNull('side')->orWhere('side', '');
                                });
                            } else {
                                $invQuery->where(function($q) use ($orderItem) {
                                    $q->where('side', $orderItem->side)
                                      ->orWhereNull('side')
                                      ->orWhere('side', '');
                                });
                            }

                            if (empty($orderItem->size)) {
                                $invQuery->where(function($q) {
                                    $q->whereNull('size')->orWhere('size', '');
                                });
                            } else {
                                $invQuery->where(function($q) use ($orderItem) {
                                    $q->where('size', $orderItem->size)
                                      ->orWhereNull('size')
                                      ->orWhere('size', '');
                                });
                            }

                            $baseInvQuery = clone $invQuery;

                            if ($invId) {
                                $inventory = $invQuery->findOrFail($invId);
                            } elseif (isset($batchData['batch_no'])) {
                                $inventory = $invQuery->where('batch_no', $batchData['batch_no'])->first();
                                if (!$inventory) {
                                    throw new \Exception("Could not find batch '{$batchData['batch_no']}' in your inventory for {$product->product_name}. Please ensure the batch number matches exactly.");
                                }
                            } else {
                                throw new \Exception("Inventory ID or Batch Number is required for allocation of {$product->product_name}");
                            }

                            $deductQtyBase = $batchData['quantity'] * $multiplier;
                            
                            // Cap the deduction for this batch so we don't exceed the total paid quantity
                            if ($currentlyAllocatedStrips + $deductQtyBase > $totalPaidQtyStrips) {
                                $deductQtyBase = $totalPaidQtyStrips - $currentlyAllocatedStrips;
                            }
                            
                            $remainingQtyToDeduct = $deductQtyBase;

                            // 1. First, attempt to deduct from the explicitly selected batch
                            $takeFromPrimary = min($inventory->stock, $remainingQtyToDeduct);
                            
                            if ($takeFromPrimary > 0) {
                                DB::table('inventories')->where('id', $inventory->id)->decrement('stock', $takeFromPrimary);
                                
                                \App\Models\RetailerOrderItemBatch::create([
                                    'retailer_order_item_id' => $orderItem->id,
                                    'batch_no' => $inventory->batch_no,
                                    'expiry_date' => $inventory->expiry_date,
                                    'quantity' => $takeFromPrimary / $multiplier,
                                ]);
                                
                                $remainingQtyToDeduct -= $takeFromPrimary;
                            }

                            // 2. If we still need more, automatically cascade (spillover) to other available batches (FIFO by expiry)
                            if ($remainingQtyToDeduct > 0) {
                                // Clone the base query to find other batches of the EXACT SAME variant
                                $otherBatches = (clone $baseInvQuery)
                                    ->where('id', '!=', $inventory->id)
                                    ->where('stock', '>', 0)
                                    ->orderBy('expiry_date', 'asc')
                                    ->get();

                                foreach ($otherBatches as $otherBatch) {
                                    if ($remainingQtyToDeduct <= 0) break;

                                    $takeFromOther = min($otherBatch->stock, $remainingQtyToDeduct);
                                    
                                    DB::table('inventories')->where('id', $otherBatch->id)->decrement('stock', $takeFromOther);
                                    
                                    \App\Models\RetailerOrderItemBatch::create([
                                        'retailer_order_item_id' => $orderItem->id,
                                        'batch_no' => $otherBatch->batch_no,
                                        'expiry_date' => $otherBatch->expiry_date,
                                        'quantity' => $takeFromOther / $multiplier,
                                    ]);
                                    
                                    $remainingQtyToDeduct -= $takeFromOther;
                                }

                                // 3. If after exhausting all other batches we still haven't met the requirement, throw an error
                                if ($remainingQtyToDeduct > 0) {
                                    throw new \Exception("Insufficient total stock across all available batches for product {$product->product_name}. You are short by " . ($remainingQtyToDeduct / $multiplier) . " items.");
                                }
                            }

                            $totalAllocated += ($deductQtyBase / $multiplier);
                            $currentlyAllocatedStrips += $deductQtyBase;
                        }
                    }
                }

                // 2. Auto-Deduct Free Quantities (and any unallocated items) via FEFO
                // This correctly deducts free items that don't have batches specified on the invoice.
                foreach ($retailerOrder->items as $orderItem) {
                    $product = $orderItem->product;
                    $multiplier = $this->convertQuantityToStrips($product, 1, $orderItem->unit);
                    
                    $isAllocated = in_array($orderItem->id, $allocatedOrderItemIds);
                    $neededForAutoDeduction = $isAllocated ? $orderItem->free_quantity : ($orderItem->quantity + $orderItem->free_quantity);
                    
                    if ($neededForAutoDeduction <= 0) continue;

                    $deductionTasks = [];

                    // Parse comma-separated variants with quantities like "2 M, 2 S"
                    if (!empty($orderItem->free_size) && preg_match('/^\d+\s+/', trim($orderItem->free_size))) {
                        $parts = array_map('trim', explode(',', $orderItem->free_size));
                        foreach ($parts as $part) {
                            if (preg_match('/^(\d+)\s+(.+)$/', $part, $matches)) {
                                $deductionTasks[] = [
                                    'qty' => (int)$matches[1],
                                    'side' => $orderItem->free_side ?: $orderItem->side, // Fallback to main side if free_side is empty
                                    'size' => trim($matches[2])
                                ];
                            } else {
                                // Fallback for unparseable part
                                $deductionTasks[] = [
                                    'qty' => $orderItem->free_quantity, 
                                    'side' => $orderItem->free_side ?: $orderItem->side,
                                    'size' => $part
                                ];
                            }
                        }
                        
                        // If unallocated, we must also deduct the paid quantity
                        if (!$isAllocated && $orderItem->quantity > 0) {
                            $deductionTasks[] = [
                                'qty' => $orderItem->quantity,
                                'side' => $orderItem->side,
                                'size' => $orderItem->size
                            ];
                        }
                    } else {
                        // Standard fallback
                        $deductionTasks[] = [
                            'qty' => $neededForAutoDeduction,
                            'side' => $isAllocated ? $orderItem->free_side : ($orderItem->side ?: $orderItem->free_side),
                            'size' => $isAllocated ? $orderItem->free_size : ($orderItem->size ?: $orderItem->free_size)
                        ];
                    }

                    foreach ($deductionTasks as $task) {
                        $neededStrips = $task['qty'] * $multiplier;
                        if ($neededStrips <= 0) continue;

                        $querySide = $task['side'];
                        $querySize = $task['size'];

                        $invQuery = \App\Models\Inventory::where('distributor_id', $distributor->id)
                            ->where('product_id', $product->id);

                        if (empty($querySide)) {
                            $invQuery->where(function($q) {
                                $q->whereNull('side')->orWhere('side', '');
                            });
                        } else {
                            $invQuery->where(function($q) use ($querySide) {
                                $q->where('side', $querySide)
                                  ->orWhereNull('side')
                                  ->orWhere('side', '');
                            });
                        }

                        if (empty($querySize)) {
                            $invQuery->where(function($q) {
                                $q->whereNull('size')->orWhere('size', '');
                            });
                        } else {
                            $invQuery->where(function($q) use ($querySize) {
                                $q->where('size', $querySize)
                                  ->orWhereNull('size')
                                  ->orWhere('size', '');
                            });
                        }

                        $inventories = $invQuery->where('stock', '>', 0)
                            ->orderBy('expiry_date', 'asc')
                            ->get();

                        if ($inventories->sum('stock') < $neededStrips) {
                            $variantInfo = array_filter([$querySide, $querySize]);
                            $vText = !empty($variantInfo) ? ' [' . implode('/', $variantInfo) . ']' : '';
                            throw new \Exception("Insufficient stock for free/unallocated item: {$product->product_name}{$vText}");
                        }

                        $remainingStrips = $neededStrips;
                        foreach ($inventories as $inv) {
                            if ($remainingStrips <= 0) break;
                            $takeStrips = min($inv->stock, $remainingStrips);
                            DB::table('inventories')
                                ->where('id', $inv->id)
                                ->decrement('stock', $takeStrips);

                            \App\Models\RetailerOrderItemBatch::create([
                                'retailer_order_item_id' => $orderItem->id,
                                'batch_no' => $inv->batch_no,
                                'expiry_date' => $inv->expiry_date,
                                'quantity' => $takeStrips / $multiplier,
                            ]);

                            $remainingStrips -= $takeStrips;
                        }
                    }
                }

                $updateData = ['status' => 'approved'];
                if ($request->filled('payment_status') && in_array($request->payment_status, ['pending', 'paid'])) {
                    $updateData['payment_status'] = $request->payment_status;
                }

                if ($request->hasFile('invoice')) {
                    // Invoice validation
                    if (!$request->filled('invoice_no')) {
                        throw new \Exception('Invoice Number is required for approval.');
                    }

                    $invoiceNo = $request->invoice_no;

                    $existsInDistOrders = \App\Models\DistributorOrder::where('distributor_id', $distributor->id)
                        ->where('invoice_no', $invoiceNo)
                        ->exists();

                    $existsInRetailOrders = RetailerOrder::where('distributor_id', $distributor->id)
                        ->where('invoice_no', $invoiceNo)
                        ->where('id', '!=', $retailerOrder->id)
                        ->exists();

                    if ($existsInDistOrders || $existsInRetailOrders) {
                        throw new \Exception("The invoice number '{$invoiceNo}' has already been used for another order by your dealership.");
                    }

                    $file = $request->file('invoice');
                    $filename = 'invoice_' . $retailerOrder->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('retailer_invoices', $filename, 'public');

                    $updateData['invoice_path'] = $path;
                    $updateData['invoice_no'] = $invoiceNo; // Save invoice number
                } else {
                    throw new \Exception('Invoice upload is strictly required for approval.');
                }

                $this->clearOrderNotifications($retailerOrder->id, 'retailer_order');

                $metadata = $retailerOrder->metadata ?? [];
                if (!isset($metadata['estimated_amount'])) {
                    $metadata['estimated_amount'] = (float)$retailerOrder->total_amount;
                }

                if ($request->filled('final_amount')) {
                    $updateData['total_amount'] = (float)$request->final_amount;
                    $metadata['invoice_net_amount'] = (float)$request->final_amount;
                }
                if ($request->filled('taxable_amount')) {
                    $metadata['invoice_taxable_amount'] = (float)$request->taxable_amount;
                }

                $updateData['metadata'] = $metadata;

                $retailerOrder->update($updateData);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()], 422);
            }

            // Notify Retailer
            if ($retailerOrder->retailer && $retailerOrder->retailer->user) {
                $this->notifyUnique($retailerOrder->retailer->user, new OrderActionRequired($retailerOrder, "Your order #{$retailerOrder->order_code} has been accepted. Please confirm your order.", url('/retailer/orders'), 'retailer_order'));

                // OneSignal Push
                $this->sendOneSignalPush(
                    [$retailerOrder->retailer->user->id],
                    "Your order #{$retailerOrder->order_code} has been accepted. Please confirm your order.",
                    ['order_id' => $retailerOrder->id, 'type' => 'retailer_order'],
                    'Order Approved'
                );
            }

            // Award Loyalty Points handled by RetailerOrderObserver
            return response()->json(['success' => 'Order accepted.']);
        }

        if ($user->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            if ($status === 'pending') {
                $retailerOrder->update(['status' => 'processing']);
                // Notify Distributor
                if ($retailerOrder->distributor && $retailerOrder->distributor->user) {
                    $this->notifyUnique($retailerOrder->distributor->user, new OrderActionRequired($retailerOrder, "Order #{$retailerOrder->order_code} is ready for your approval.", route('admin.approvals.retailer'), 'retailer_order'));
                }
                return response()->json(['success' => 'Order accepted.']);
            } elseif ($status === 'processing') {
                if ($request->hasFile('invoice')) {
                    $file = $request->file('invoice');
                    $filename = 'invoice_' . $retailerOrder->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('retailer_invoices', $filename, 'public');

                    $metadata = $retailerOrder->metadata ?? [];
                    if (!isset($metadata['estimated_amount'])) {
                        $metadata['estimated_amount'] = (float)$retailerOrder->total_amount;
                    }

                    $updateData = [
                        'status' => 'approved',
                        'invoice_path' => $path
                    ];

                    if ($request->filled('invoice_no')) {
                        $updateData['invoice_no'] = trim($request->invoice_no);
                    }

                    if ($request->filled('final_amount')) {
                        $updateData['total_amount'] = (float)$request->final_amount;
                        $metadata['invoice_net_amount'] = (float)$request->final_amount;
                    }
                    if ($request->filled('taxable_amount')) {
                        $metadata['invoice_taxable_amount'] = (float)$request->taxable_amount;
                    }

                    $updateData['metadata'] = $metadata;

                    // Finalize status only if invoice is present
                    $retailerOrder->update($updateData);
                } else {
                    return response()->json(['error' => 'Invoice upload is required for final approval.'], 422);
                }

                // Notify Retailer
                if ($retailerOrder->retailer && $retailerOrder->retailer->user) {
                    $this->notifyUnique($retailerOrder->retailer->user, new OrderActionRequired($retailerOrder, "Your order #{$retailerOrder->order_code} has been accepted. Please confirm your order.", url('/retailer/orders'), 'retailer_order'));
                }

                // Award Loyalty Points handled by RetailerOrderObserver
                return response()->json(['success' => 'Order accepted (Distributor stage)!', 'new_points' => $retailerOrder->retailer->loyalty_points ?? 0]);
            } else {
                return response()->json(['error' => 'Order is in a state that cannot be accepted/approved further.'], 400);
            }
        }

        return response()->json(['error' => 'Unauthorized or invalid state'], 403);
    }


    // Manager: Assign Field Staff
    public function assignFieldStaff(Request $request, RetailerOrder $retailerOrder)
    {
        $request->validate(['fieldstaff_id' => 'required|exists:field_staff,id']);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Permission check
        if (!$user->hasPermissionToCategory('retailer_approvals', 'edit') && !$user->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $retailerOrder->update([
            'fieldstaff_id' => $request->fieldstaff_id,
            'status' => 'pending' // Stay pending until FS accepts
        ]);

        return response()->json(['success' => 'Field staff assigned successfully!']);
    }

    // Update (Admin Edit / Fieldstaff Edit)
    public function update(Request $request, RetailerOrder $retailerOrder)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->hasRole('fieldstaff')) {
            $fieldStaff = $user->fieldStaff;
            if (!$fieldStaff) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'Field staff record not found.'], 422);
                }
                return back()->withErrors(['error' => 'Field staff record not found.']);
            }
            $isOwner = ($retailerOrder->fieldstaff_id === $fieldStaff->id) || 
                       ($retailerOrder->retailer && $retailerOrder->retailer->field_staff_id === $fieldStaff->id);
            if (!$isOwner) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'You are not authorized to edit this order.'], 403);
                }
                return back()->withErrors(['error' => 'You are not authorized to edit this order.']);
            }
            if (!in_array($retailerOrder->status, [RetailerOrder::STATUS_PENDING, RetailerOrder::STATUS_PROCESSING])) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'You can only edit pending or processing orders.'], 422);
                }
                return back()->withErrors(['error' => 'You can only edit pending or processing orders.']);
            }
            // Field staff cannot change status
            $request->merge(['status' => $retailerOrder->status]);
        } elseif ($user->hasRole('salesmanager')) {
            $salesManager = $user->salesManager;
            if (!$salesManager) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'Sales manager record not found.'], 422);
                }
                return back()->withErrors(['error' => 'Sales manager record not found.']);
            }
            
            $isOwner = false;
            if ($retailerOrder->retailer && $retailerOrder->retailer->fieldStaff && $retailerOrder->retailer->fieldStaff->sales_manager_id === $salesManager->id) {
                $isOwner = true;
            }
            if ($retailerOrder->distributor && $retailerOrder->distributor->sales_manager_id === $salesManager->id) {
                $isOwner = true;
            }
            
            if (!$isOwner) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'You are not authorized to edit this order.'], 403);
                }
                return back()->withErrors(['error' => 'You are not authorized to edit this order.']);
            }
            if (!in_array($retailerOrder->status, [RetailerOrder::STATUS_PENDING, RetailerOrder::STATUS_PROCESSING])) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'You can only edit pending or processing orders.'], 422);
                }
                return back()->withErrors(['error' => 'You can only edit pending or processing orders.']);
            }
            // Sales manager cannot change status
            $request->merge(['status' => $retailerOrder->status]);
        } elseif (!$user->hasAnyRole(['admin', 'superadmin'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }
            return back()->withErrors(['error' => 'Unauthorized action.']);
        }

        $request->validate([
            'retailer_id' => 'required|exists:retailers,id',
            'distributor_id' => 'nullable|exists:distributors,id',
            'status' => 'required',
            'items' => 'required|array|min:1',
        ]);

        $metadata = $retailerOrder->metadata ?? [];
        $roleName = 'Admin';
        if ($user->hasRole('fieldstaff')) $roleName = 'Field Staff';
        if ($user->hasRole('salesmanager')) $roleName = 'Sales Manager';
        if ($user->hasRole('superadmin')) $roleName = 'Super Admin';

        $hasChanges = false;
        $dbNotes = $retailerOrder->delivery_notes === null ? '' : (string)$retailerOrder->delivery_notes;
        $reqNotes = $request->delivery_notes === null ? '' : (string)$request->delivery_notes;

        if ((string)$retailerOrder->retailer_id !== (string)$request->retailer_id) { $hasChanges = true; \Log::info("Change: retailer_id"); }
        if ((string)$retailerOrder->distributor_id !== (string)$request->distributor_id) { $hasChanges = true; \Log::info("Change: distributor_id"); }
        if ((string)$retailerOrder->status !== (string)$request->status) { $hasChanges = true; \Log::info("Change: status"); }
        if ($dbNotes !== $reqNotes) { $hasChanges = true; \Log::info("Change: delivery_notes '{$dbNotes}' !== '{$reqNotes}'"); }

        $requestItemsGrouped = collect($request->items ?? [])->groupBy('order_item_id');
        $requestPaidCount = $requestItemsGrouped->count();

        if ($retailerOrder->items->count() !== $requestPaidCount) {
            $hasChanges = true;
        } else {
            foreach ($requestItemsGrouped as $orderItemId => $group) {
                if (empty($orderItemId)) {
                    $hasChanges = true;
                    break;
                }
                $existingItem = $retailerOrder->items->find($orderItemId);
                if (!$existingItem) {
                    $hasChanges = true;
                    break;
                }

                $paidItem = $group->first();

                if ($paidItem) {
                    if ((string)$existingItem->product_id !== (string)$paidItem['product_id']) { $hasChanges = true; \Log::info("Change: product_id {$existingItem->product_id} !== {$paidItem['product_id']}"); }
                    if (round((float)$existingItem->quantity, 2) !== round((float)$paidItem['quantity'], 2)) { $hasChanges = true; \Log::info("Change: quantity {$existingItem->quantity} !== {$paidItem['quantity']}"); }
                    
                    $dbUnit = strtolower(trim($existingItem->unit ?? ''));
                    if ($dbUnit === '') $dbUnit = 'box';
                    $reqUnit = isset($paidItem['unit']) ? strtolower(trim($paidItem['unit'])) : 'box';
                    if ($dbUnit !== $reqUnit && !($dbUnit === 'strips' && $reqUnit === 'box')) {
                        $hasChanges = true; \Log::info("Change: unit {$dbUnit} !== {$reqUnit}"); 
                    }
                    
                    $existingSide = $existingItem->side === null ? '' : strtolower(trim((string)$existingItem->side));
                    $newSide = empty($paidItem['side']) ? '' : strtolower(trim((string)$paidItem['side']));
                    if ($existingSide !== $newSide) { $hasChanges = true; \Log::info("Change: side {$existingSide} !== {$newSide}"); }

                    $existingSize = $existingItem->size === null ? '' : strtolower(trim((string)$existingItem->size));
                    $newSize = empty($paidItem['size']) ? '' : strtolower(trim((string)$paidItem['size']));
                    if ($existingSize !== $newSize) { $hasChanges = true; \Log::info("Change: size {$existingSize} !== {$newSize}"); }
                    
                    if (round((float)$existingItem->free_quantity, 2) !== round((float)($paidItem['free_quantity'] ?? 0), 2)) { $hasChanges = true; \Log::info("Change: free_quantity {$existingItem->free_quantity} !== " . ($paidItem['free_quantity'] ?? 0)); }
                    
                    $existingFreeSide = $existingItem->free_side === null ? '' : strtolower(trim((string)$existingItem->free_side));
                    $newFreeSide = empty($paidItem['free_side']) ? '' : strtolower(trim((string)$paidItem['free_side']));
                    if ($existingFreeSide !== $newFreeSide) { $hasChanges = true; \Log::info("Change: free side {$existingFreeSide} !== {$newFreeSide}"); }

                    $existingFreeSize = $existingItem->free_size === null ? '' : strtolower(trim((string)$existingItem->free_size));
                    $newFreeSize = empty($paidItem['free_size']) ? '' : strtolower(trim((string)$paidItem['free_size']));
                    if ($existingFreeSize !== $newFreeSize) { $hasChanges = true; \Log::info("Change: free size {$existingFreeSize} !== {$newFreeSize}"); }
                }

                if ($hasChanges) break;
            }
        }

        if ($hasChanges) {
            $metadata['is_edited'] = true;
            $metadata['last_edited_by'] = $user->name . " ($roleName)";
            $metadata['last_edited_at'] = now()->toDateTimeString();

            $snapshot = $retailerOrder->items->map(function($item) {
                return [
                    'product_name' => $item->product_name ?? ($item->product ? $item->product->product_name : 'Unknown Product'),
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'price' => $item->unit_price,
                    'subtotal' => $item->total_amount,
                    'side' => $item->side,
                    'size' => $item->size,
                ];
            })->toArray();

            $editLogs = $metadata['edit_history'] ?? [];
            $editLogs[] = [
                'edited_by' => $user->name,
                'role' => strtolower(str_replace(' ', '', $roleName)),
                'edited_at' => now()->toDateTimeString(),
                'original_total' => $retailerOrder->total_amount,
                'snapshot' => $snapshot,
            ];
            $metadata['edit_history'] = $editLogs;
        }

        $retailerOrder->update([
            'retailer_id' => $request->retailer_id,
            'distributor_id' => $request->distributor_id,
            'status' => $request->status,
            'delivery_notes' => $request->delivery_notes,
            'delivered_at' => ($request->status === 'delivered') ? now() : null,
            'metadata' => $metadata,
        ]);

        $distributor = $retailerOrder->distributor; // Reload in case ID changed

        $totalAmount = 0;
        $totalItems = 0;
        $totalQuantity = 0;
        $requestItemIds = [];

        try {
            foreach ($request->items as $itemData) {
                $product = null;
                if ($distributor) {
                    $product = $distributor->products()->where('product_id', $itemData['product_id'])->first();
                    if (!$product) throw new \Exception('Product not available from assigned distributor');
                } else {
                    $product = Product::find($itemData['product_id']);
                }

                $unitPrice = $product->ptr;
                if ($distributor) $unitPrice = $product->pivot->stock ? $product->ptr : 0; 

                $currentOrderItem = null;
                if (!empty($itemData['order_item_id'])) {
                    $currentOrderItem = $retailerOrder->items()->find($itemData['order_item_id']);
                }

                $qty = $itemData['quantity'];
                $price = (float)$product->ptr;
                $gstRate = (float)($product->gst ?? 0);
                $taxableSubtotal = $qty * $price;
                $itemTotalAmount = $taxableSubtotal * (1 + ($gstRate / 100));

                $unit = $itemData['unit'] ?? 'Strips';
                $side = $itemData['side'] ?? null;
                $size = $itemData['size'] ?? null;

                $currentOrderItem = null;
                if (!empty($itemData['order_item_id'])) {
                    $currentOrderItem = $retailerOrder->items()->find($itemData['order_item_id']);
                }

                if ($currentOrderItem) {
                    $currentOrderItem->update([
                        'quantity' => $qty,
                        'unit' => $unit,
                        'side' => $side,
                        'size' => $size,
                        'unit_price' => $price,
                        'total_amount' => $itemTotalAmount,
                        'free_quantity' => $itemData['free_quantity'] ?? 0,
                        'free_side' => $itemData['free_side'] ?? null,
                        'free_size' => $itemData['free_size'] ?? null,
                    ]);
                    $requestItemIds[] = $currentOrderItem->id;
                } else {
                    $newItem = $retailerOrder->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->product_name,
                        'quantity' => $qty,
                        'unit' => $unit,
                        'side' => $side,
                        'size' => $size,
                        'unit_price' => $price,
                        'total_amount' => $itemTotalAmount,
                        'free_quantity' => $itemData['free_quantity'] ?? 0,
                        'free_side' => $itemData['free_side'] ?? null,
                        'free_size' => $itemData['free_size'] ?? null,
                    ]);
                    $requestItemIds[] = $newItem->id;
                }
                $totalAmount += $itemTotalAmount;
                $totalItems++;
                $totalQuantity += $qty;
            }

            // Delete removed
            $retailerOrder->items()->whereNotIn('id', $requestItemIds)->delete();

            $retailerOrder->update([
                'total_amount' => $totalAmount,
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity
            ]);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->withErrors(['items' => $e->getMessage()]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Order updated successfully.']);
        }
        return redirect()->route('admin.retailer.index')->with('success', 'Order updated.');
    }

    public function requestCancellation(Request $request, RetailerOrder $retailerOrder)
    {
        $request->validate(['cancellation_reason' => 'required|string|min:3']);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('distributor')) return response()->json(['error' => 'No permission'], 403);
        if ($retailerOrder->distributor_id !== $user->distributor->id) return response()->json(['error' => 'Not your order'], 403);

        if (!in_array($retailerOrder->status, [RetailerOrder::STATUS_PENDING, RetailerOrder::STATUS_PROCESSING])) {
            return response()->json(['error' => 'Orders can only be cancelled while in pending or processing status.'], 400);
        }

        $retailerOrder->status = RetailerOrder::STATUS_CANCELLED;
        $retailerOrder->cancellation_reason = $request->cancellation_reason;
        $retailerOrder->save();

        $this->deleteOrderNotifications($retailerOrder->id, 'retailer_order');

        return response()->json(['success' => 'Order cancelled successfully!']);
    }

    public function approveCancellation(RetailerOrder $retailerOrder)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('salesmanager')) return response()->json(['error' => 'No permission'], 403);
        if ($retailerOrder->status !== RetailerOrder::STATUS_CANCELLED) return response()->json(['error' => 'Invalid status'], 400);

        $retailerOrder->status = RetailerOrder::STATUS_CANCELLED;
        $retailerOrder->save();

        return response()->json(['success' => 'Order cancellation approved successfully!']);
    }

    public function cancelOrder(Request $request, RetailerOrder $retailerOrder)
    {
        $request->validate(['cancellation_reason' => 'required|string|min:3']);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->hasRole('retailer') && $retailerOrder->retailer_id !== $user->retailer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($retailerOrder->status !== RetailerOrder::STATUS_PENDING) {
            return response()->json(['error' => 'Orders can only be cancelled while in pending status.'], 400);
        }

        $retailerOrder->update([
            'status' => RetailerOrder::STATUS_CANCELLED,
            'cancellation_reason' => $request->cancellation_reason
        ]);

        $this->deleteOrderNotifications($retailerOrder->id, 'retailer_order');

        return response()->json(['success' => 'Order cancelled successfully!']);
    }

    public function destroy(Request $request, RetailerOrder $retailerOrder)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isOwner = ($user->hasRole('retailer') && $retailerOrder->retailer_id === $user->retailer?->id);
        $isAdmin = $user->hasAnyRole(['admin', 'superadmin', 'salesmanager']);

        if (!$isAdmin && !$isOwner) {
            return response()->json(['error' => 'No permission to delete orders.'], 403);
        }

        if ($isOwner && $retailerOrder->status !== RetailerOrder::STATUS_PENDING) {
            return response()->json(['error' => 'You can only delete orders while they are pending.'], 400);
        }

        try {
            $distributor = $retailerOrder->distributor;
            if ($distributor) {
                foreach ($retailerOrder->items as $item) {
                    $pivot = $distributor->products()->where('product_id', $item->product_id)->first();
                    if ($pivot) {
                        $distributor->products()->updateExistingPivot($item->product_id, ['stock' => $pivot->pivot->stock + $item->quantity]);
                    }
                }
            }
            $retailerOrder->items()->delete();
            $this->deleteOrderNotifications($retailerOrder->id, 'retailer_order');
            $retailerOrder->delete();

            if ($request->ajax()) {
                return response()->json(['success' => 'Order deleted successfully!']);
            }

            return redirect()->route('admin.retailer.index')->with('success', 'Order deleted.');
        } catch (\Exception $e) {
            if ($request->ajax()) return response()->json(['error' => $e->getMessage()], 500);
            return back()->with('error', $e->getMessage());
        }
    }
    public function updateStatus(Request $request, RetailerOrder $retailerOrder)
    {
        $request->validate([
            'status' => 'required',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Permission check
        if (!$user->hasPermissionToCategory('retailer_approvals', 'edit') && !$user->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $oldStatus = $retailerOrder->status;
        $newStatus = $request->status;

        $retailerOrder->status = $newStatus;
        if ($newStatus == 'delivered') {
            $retailerOrder->delivered_at = \Illuminate\Support\Carbon::now();
        }
        $retailerOrder->save();

        // Handle stock logic if needed for cancellations/rejections similar to DistributorOrder
        // Minimal logic for now as per user request to enable functionality

        return response()->json(['success' => 'Status updated successfully to ' . ucfirst(str_replace('_', ' ', $newStatus))]);
    }

    public function updatePaymentStatus(Request $request, RetailerOrder $retailerOrder)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Allow admins, superadmins, salesmanagers
        if (!$user->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            // Allow distributor — only for their own orders
            if ($user->hasRole('distributor')) {
                if (!$user->distributor || $retailerOrder->distributor_id != $user->distributor->id) {
                    return response()->json(['error' => 'You are not authorized to update this order.'], 403);
                }
            } else {
                return response()->json(['error' => 'Permission denied.'], 403);
            }
        }

        $retailerOrder->payment_status = $request->payment_status;
        $retailerOrder->save();

        $label = $request->payment_status === 'paid' ? 'Paid' : 'Unpaid';
        return response()->json(['success' => 'Payment status updated to ' . $label]);
    }

    public function invoice(RetailerOrder $retailerOrder)
    {
        $retailerOrder->load(['retailer.user', 'items.product', 'distributor.user']);
        $cgst = \App\Models\Setting::getValue('cgst', 9);
        $sgst = \App\Models\Setting::getValue('sgst', 9);
        return view('admin.orders.retailers.invoice', compact('retailerOrder', 'cgst', 'sgst'));
    }



    public function uploadInvoice(Request $request, RetailerOrder $retailerOrder)
    {
        $request->validate([
            'invoice'    => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'invoice_no' => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('invoice')) {
            $file = $request->file('invoice');

            // --- Invoice Number Uniqueness Check ---
            if ($request->filled('invoice_no')) {
                $invoiceNo = trim($request->invoice_no);
                $distributor = $retailerOrder->distributor;

                if ($distributor) {
                    $existsInDistOrders = \App\Models\DistributorOrder::where('distributor_id', $distributor->id)
                        ->where('invoice_no', $invoiceNo)
                        ->exists();

                    $existsInRetailOrders = RetailerOrder::where('id', '!=', $retailerOrder->id)
                        ->where('distributor_id', $distributor->id)
                        ->where('invoice_no', $invoiceNo)
                        ->exists();

                    if ($existsInDistOrders || $existsInRetailOrders) {
                        return response()->json([
                            'error' => "Invoice number '{$invoiceNo}' has already been used for another order by this distributor. Please use a unique invoice number.",
                            'duplicate' => true
                        ], 422);
                    }
                }
            }

            // --- File Hash Duplication Check ---
            $fileHash = md5_file($file->getRealPath());

            $existingRetailer = RetailerOrder::whereNotNull('metadata')
                ->where('id', '!=', $retailerOrder->id)
                ->whereJsonContains('metadata->invoice_hash', $fileHash)
                ->first();

            $existingDistributor = \App\Models\DistributorOrder::whereNotNull('metadata')
                ->whereJsonContains('metadata->invoice_hash', $fileHash)
                ->first();

            if ($existingRetailer || $existingDistributor) {
                $code = $existingRetailer ? $existingRetailer->order_code : $existingDistributor->order_code;
                $role = $existingRetailer ? 'Retailer' : 'Distributor';
                return response()->json([
                    'error' => "This invoice has already been uploaded for $role Order #$code. Duplicate uploads are not allowed across roles.",
                    'duplicate' => true
                ], 400);
            }

            if ($retailerOrder->invoice_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($retailerOrder->invoice_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($retailerOrder->invoice_path);
            }

            $path = $file->store('invoices/retailers', 'public');

            // Store hash in metadata for future duplication checks
            $metadata = $retailerOrder->metadata ?? [];
            $metadata['invoice_hash'] = $fileHash;

            $retailerOrder->invoice_path = $path;
            $retailerOrder->metadata    = $metadata;

            // Save invoice number if provided
            if ($request->filled('invoice_no')) {
                $retailerOrder->invoice_no = trim($request->invoice_no);
            }

            $retailerOrder->save();

            return response()->json([
                'success'     => 'Invoice uploaded successfully!',
                'invoice_url' => asset('storage/' . $path)
            ]);
        }

        return response()->json(['error' => 'No file uploaded.'], 400);
    }
    public function removeInvoice(Request $request, RetailerOrder $retailerOrder)
    {
        if ($retailerOrder->invoice_path) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($retailerOrder->invoice_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($retailerOrder->invoice_path);
            }
            $retailerOrder->invoice_path = null;
            $retailerOrder->save();
            return response()->json(['success' => 'Invoice removed successfully']);
        }
        return response()->json(['error' => 'No invoice to remove'], 400);
    }

    public function distributorIndex(Request $request)
    {
        return $this->index($request);
    }

    public function fieldStaffIndex(Request $request)
    {
        return $this->index($request);
    }

    public function confirmReceipt(RetailerOrder $retailerOrder)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if user is the retailer for this order
        // Allow Admin/Manager too? Usually it's for retailer.
        if ((!$user->hasRole('retailer') || $retailerOrder->retailer_id !== $user->retailer->id) && !$user->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            return response()->json(['error' => 'Permission denied. Only the retailer can confirm order.'], 403);
        }

        if ($retailerOrder->status !== 'approved') {
            return response()->json(['error' => 'Order must be accepted by Distributor before confirmation.'], 400);
        }

        try {
            DB::beginTransaction();

            $retailerOrder->update([
                'status' => 'delivered',
                'delivered_at' => now()
            ]);

            // Loyalty points calculation moved to be awarded when Distributor Accepts order.

            // Optional: Notify Field Staff / Distributor that order is closed
            if ($retailerOrder->fieldStaff && $retailerOrder->fieldStaff->user) {
                $retailerOrder->fieldStaff->user->notify(new OrderActionRequired($retailerOrder, "Order #{$retailerOrder->order_code} has been successfully delivered and confirmed by the retailer.", route('fieldstaff.orders.index')));

                // OneSignal Push
                $this->sendOneSignalPush(
                    [$retailerOrder->fieldStaff->user->id],
                    "Order #{$retailerOrder->order_code} has been successfully delivered and confirmed by the retailer.",
                    ['order_id' => $retailerOrder->id, 'type' => 'retailer_order'],
                    'Order Delivered'
                );
            }

            DB::commit();

            $msg = 'Order delivery confirmed!';

            return response()->json(['success' => $msg, 'new_points' => $retailerOrder->retailer->loyalty_points ?? 0]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error confirming order: ' . $e->getMessage()], 500);
        }
    }
}
