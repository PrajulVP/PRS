<?php


namespace App\Http\Controllers;

use App\Models\FieldStaff;
use App\Models\RetailerOrder; // Use the new RetailerOrder model
use App\Models\Distributor;
use App\Models\Retailer; // Added
use App\Models\Product; // Added
use App\Models\SalesManager; // Added
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RetailerOrderManagementController extends Controller
{
    // Admin: list all orders
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $totalData = RetailerOrder::count();

                $query = RetailerOrder::with(['retailer.user', 'fieldStaff.user', 'items.product']);

                // Apply search filter
                if ($request->has('search') && !empty($request->input('search')['value'])) {
                    $searchValue = $request->input('search')['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('retailer_orders.order_code', 'like', "%{$searchValue}%")
                          ->orWhere('retailer_orders.status', 'like', "%{$searchValue}%")
                          ->orWhereHas('retailer.user', function ($subQuery) use ($searchValue) {
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

                    // Map DataTables column names to database column names
                    switch ($columnName) {
                        case 'id':
                            $query->orderBy('retailer_orders.id', $sortDirection);
                            break;
                        case 'order_code':
                            $query->orderBy('retailer_orders.order_code', $sortDirection);
                            break;
                        case 'retailer_name':
                            $query->join('retailers', 'retailer_orders.retailer_id', '=', 'retailers.id')
                                ->join('users', 'retailers.user_id', '=', 'users.id')
                                ->orderBy('users.name', $sortDirection)
                                ->select('retailer_orders.*'); // Select back original columns
                            break;
                        case 'total_items':
                            $query->orderBy('retailer_orders.total_items', $sortDirection);
                            break;
                        case 'total_quantity':
                            $query->orderBy('retailer_orders.total_quantity', $sortDirection);
                            break;
                        case 'total_amount':
                            $query->orderBy('retailer_orders.total_amount', $sortDirection);
                            break;
                        case 'status':
                            $query->orderBy('retailer_orders.status', $sortDirection);
                            break;
                        case 'placed_at':
                            $query->orderBy('retailer_orders.placed_at', $sortDirection);
                            break;
                        case 'fieldstaff_name':
                            $query->leftJoin('field_staff', 'retailer_orders.fieldstaff_id', '=', 'field_staff.id')
                                ->leftJoin('users as fs_users', 'field_staff.user_id', '=', 'fs_users.id')
                                ->orderBy('fs_users.name', $sortDirection)
                                ->select('retailer_orders.*'); // Select back original columns
                            break;
                        default:
                            $query->orderBy('retailer_orders.id', 'desc');
                            break;
                    }
                } else {
                    $query->orderBy('retailer_orders.id', 'desc'); // Default sort
                }

                // Apply pagination
                $start = $request->input('start');
                $length = $request->input('length');
                $orders = $query->offset($start)->limit($length)->get();

                $formattedOrders = $orders->map(function ($order) {
                    $productSummary = $order->items->map(function ($item) {
                        return $item->product->product_name . ' (' . $item->quantity . ')';
                    })->implode(', ');
                    
                    return [
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'retailer_name' => $order->retailer?->user?->name ?? 'N/A',
                        'product_summary' => $productSummary, // New field for summary
                        'total_items' => $order->total_items,
                        'total_quantity' => $order->total_quantity,
                        'total_amount' => number_format($order->total_amount, 2),
                        'status' => ucfirst($order->status),
                        'placed_at' => $order->placed_at ? \Carbon\Carbon::parse($order->placed_at)->format('Y-m-d H:i:s') : '-',
                        'actions' => null, // Actions column will be rendered by DataTables
                    ];
                });

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalData,
                    'recordsFiltered' => $totalFiltered,
                    'data' => $formattedOrders,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error in RetailerOrderManagementController@index: ' . $e->getMessage());
                return response()->json([
                    'error' => 'An error occurred while processing your request.'
                ], 500);
            }
        }
        $fieldstaffs = FieldStaff::with('user')->get()->map(function($fieldstaff) {
            return ['id' => $fieldstaff->id, 'name' => $fieldstaff->user->name];
        });

        return view('admin.orders.index', compact('fieldstaffs'));
    }

    // Admin: show create form
    // public function create()
    // {
    //     $user = Auth::user();
    //     $retailer = $user->retailer;

    //     if (!$retailer || !$retailer->distributor) {
    //         // Handle case where retailer is not found or not assigned to a distributor
    //         return redirect()->back()->with('error', 'You are not assigned to a distributor or your retailer profile is incomplete.');
    //     }

    //     $distributorProducts = $retailer->distributor->products; // Get products associated with the retailer's distributor

    //     return view('admin.orders.create', ['products' => $distributorProducts])->with('orderType', 'retailer');
    // }

    // Admin: store order
    // public function store(Request $request) // Changed request type from StoreDistributorOrderRequest to Request
    // {
    //     $request->validate([
    //         'product_id' => 'required|exists:products,id',
    //         'quantity' => 'required|integer|min:1',
    //         'prescription_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //         'notes' => 'nullable|string',
    //     ]);

    //     $retailer = Auth::user()->retailer;

    //     if (!$retailer || !$retailer->distributor) {
    //         return back()->withErrors(['retailer' => 'Retailer not assigned to a distributor.'])->withInput();
    //     }

    //     $distributor = $retailer->distributor;
    //     $product = $distributor->products()->where('product_id', $request->product_id)->first();

    //     if (!$product) {
    //         return back()->withErrors(['product_id' => 'Product not available from your assigned distributor.'])->withInput();
    //     }

    //     if ($product->pivot->stock < $request->quantity) {
    //         return back()->withErrors(['quantity' => 'Ordered quantity exceeds available stock from distributor. Available stock: ' . $product->pivot->stock])->withInput();
    //     }

    //     // Decrement stock from distributor_product pivot table
    //     $distributor->products()->updateExistingPivot($product->id, ['stock' => $product->pivot->stock - $request->quantity]);

    //     $data = $request->all();
    //     $data['retailer_id'] = $retailer->id;
    //     $data['product_name'] = $product->product_name; // Store product name from selected product
    //     $data['unit_price'] = $product->mrp; // Store unit price from selected product
    //     $data['total_amount'] = $request->quantity * $product->mrp;
    //     $data['placed_at'] = now();
    //     $data['status'] = 'pending';
    //     $data['distributor_id'] = $retailer->distributor_id; // Add distributor_id from the retailer

    //     if ($request->hasFile('prescription_photo')) {
    //         $data['prescription_photo'] = $request->file('prescription_photo')->store('prescriptions', 'public');
    //     }

    //     RetailerOrder::create($data);

    //     return redirect()->route('retailer.orders.index')->with('success', 'Medicine requirement sent successfully!');
    // }

    // Manager: list all pending orders
    public function managerIndex(Request $request)
    {
        if ($request->ajax()) {
            $totalData = RetailerOrder::where('status', 'pending')->count();

            $query = RetailerOrder::with('retailer.user')->where('status', 'pending');

            // Apply search filter
            if ($request->has('search') && !empty($request->input('search')['value'])) {
                $searchValue = $request->input('search')['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('id', 'like', "%{$searchValue}%")
                      ->orWhere('product_name', 'like', "%{$searchValue}%")
                      ->orWhereHas('retailer.user', function ($subQuery) use ($searchValue) {
                          $subQuery->where('name', 'like', "%{$searchValue}%");
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
                    case 'retailer_name':
                        $query->join('retailers', 'retailer_orders.retailer_id', '=', 'retailers.id')
                              ->join('users', 'retailers.user_id', '=', 'users.id')
                              ->orderBy('users.name', $sortDirection)
                              ->select('retailer_orders.*'); // Select retailer_orders.* to avoid ambiguity
                        break;
                    case 'fieldstaff_name':
                        $query->leftJoin('field_staff', 'retailer_orders.fieldstaff_id', '=', 'field_staff.id')
                              ->leftJoin('users as fs_users', 'field_staff.user_id', '=', 'fs_users.id')
                              ->orderBy('fs_users.name', $sortDirection)
                              ->select('retailer_orders.*'); // Select retailer_orders.* to avoid ambiguity
                        break;
                    case 'id':
                    case 'product_name':
                    case 'quantity':
                    case 'total_amount':
                    case 'status':
                        $query->orderBy($columnName, $sortDirection);
                        break;
                    default:
                        $query->orderBy('retailer_orders.id', 'desc');
                        break;
                }
            } else {
                $query->orderBy('retailer_orders.id', 'desc'); // Default sort
            }

            // Apply pagination
            $start = $request->input('start');
            $length = $request->input('length');
            $orders = $query->offset($start)->limit($length)->get();

            $formattedOrders = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'retailer_name' => $order->retailer->user->name ?? 'N/A',
                    'product_name' => $order->product_name,
                    'quantity' => $order->quantity,
                    'total_amount' => number_format($order->total_amount, 2),
                    'status' => ucfirst(str_replace('_', ' ', $order->status)),
                    'placed_at' => $order->placed_at->format('Y-m-d H:i'),
                    'actions' => null, // Actions column will be rendered by DataTables
                ];
            });

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalData,
                'recordsFiltered' => $totalFiltered,
                'data' => $formattedOrders,
            ]);
        }

        return view('admin.orders.manager_index');
    }

    // Manager: assign order to distributor
    public function assignDistributor(Request $request, RetailerOrder $retailerOrder)
    {
        $request->validate([
            'distributor_id' => 'required|exists:distributors,id',
        ]);

        $retailerOrder->update([
            'distributor_id' => $request->distributor_id,
            'status' => 'assigned_to_distributor',
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => 'Order assigned to distributor successfully!']);
        }

        return back()->with('success', 'Order assigned to distributor successfully!');
    }

    // Distributor: list orders assigned to them
    public function distributorIndex(Request $request)
    {
        if ($request->ajax()) {
            $distributor = Auth::guard('web')->user()->load('distributor')->distributor;

            if (!$distributor) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }

            $initialQuery = RetailerOrder::with('retailer.user', 'fieldStaff.user')
                ->where('distributor_id', $distributor->id)
                ->whereIn('status', ['pending', 'accepted_by_distributor', 'assigned_to_fieldstaff', 'dispatched', 'delivered', 'cancelled']);

            $totalData = $initialQuery->count(); // Count before applying search filter

            $query = $initialQuery->newQuery(); // Create a new query instance for filtering and pagination

            // Apply search filter
            if ($request->has('search') && !empty($request->input('search')['value'])) {
                $searchValue = $request->input('search')['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('id', 'like', "%{$searchValue}%")
                        ->orWhere('product_name', 'like', "%{$searchValue}%")
                        ->orWhereHas('retailer.user', function ($subQuery) use ($searchValue) {
                            $subQuery->where('name', 'like', "%{$searchValue}%");
                        });
                });
            }

            // Apply order (sorting)
            if ($request->has('order') && !empty($request->input('order'))) {
                $columnIndex = $request->input('order')[0]['column'];
                $columnName = $request->input('columns')[$columnIndex]['data'];
                $sortDirection = $request->input('order')[0]['dir'];

                switch ($columnName) {
                    case 'id':
                    case 'product_name':
                    case 'quantity':
                    case 'total_amount':
                    case 'status':
                        $query->orderBy($columnName, $sortDirection);
                        break;
                    default:
                        $query->orderBy('retailer_orders.id', 'desc');
                        break;
                }
            } else {
                $query->orderBy('retailer_orders.id', 'desc'); // Default sort
            }

            // Apply pagination
            $start = $request->input('start');
            $length = $request->input('length');
            $orders = $query->offset($start)->limit($length)->get();

            $formattedOrders = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'retailer_name' => $order->retailer->user->name ?? 'N/A',
                    'product_name' => $order->product_name,
                    'quantity' => $order->quantity,
                    'total_amount' => number_format($order->total_amount, 2),
                    'status' => ucfirst(str_replace('_', ' ', $order->status)),
                    'actions' => null, // Actions column will be rendered by DataTables
                ];
            });

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalData,
                'recordsFiltered' => $totalFiltered,
                'data' => $formattedOrders,
            ]);
        }

        $distributor = Auth::guard('web')->user()->load('distributor')->distributor;
        // Removed $fieldstaffs query as per user's clarification: "no conncetion with distribnutor in fieldfstaffs table"

        return view('admin.orders.distributor_retailer_orders_index');
    }

    // Field Staff: list orders assigned to them
    public function fieldStaffIndex(Request $request)
    {
        if ($request->ajax()) {
            $fieldStaff = Auth::guard('web')->user()->load('fieldStaff')->fieldStaff;

            if (!$fieldStaff) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }

            $query = RetailerOrder::with('retailer.user')
                ->where('fieldstaff_id', $fieldStaff->id)
                ->whereIn('status', ['assigned_to_fieldstaff', 'dispatched', 'delivered', 'cancelled']);

            // Apply search filter
            if ($request->has('search') && !empty($request->input('search')['value'])) {
                $searchValue = $request->input('search')['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('id', 'like', "%{$searchValue}%")
                        ->orWhere('product_name', 'like', "%{$searchValue}%")
                        ->orWhereHas('retailer.user', function ($subQuery) use ($searchValue) {
                            $subQuery->where('name', 'like', "%{$searchValue}%");
                        });
                });
            }

            $totalFiltered = $query->count();

            // Apply order (sorting)
            if ($request->has('order') && !empty($request->input('order'))) {
                $columnIndex = $request->input('order')[0]['column'];
                $columnName = $request->input('columns')[$columnIndex]['data'];
                $sortDirection = $request->input('order')[0]['dir'];

                $query->orderBy($columnName, $sortDirection);
            } else {
                $query->orderBy('id', 'desc'); // Default sort
            }

            $totalData = $query->count();

            // Apply pagination
            $start = $request->input('start');
            $length = $request->input('length');
            $orders = $query->offset($start)->limit($length)->get();

            $formattedOrders = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'retailer_name' => $order->retailer->user->name ?? 'N/A',
                    'product_name' => $order->product_name,
                    'quantity' => $order->quantity,
                    'total_amount' => number_format($order->total_amount, 2),
                    'status' => ucfirst(str_replace('_', ' ', $order->status)),
                    'actions' => null, // Actions column will be rendered by DataTables
                ];
            });

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalData,
                'recordsFiltered' => $totalFiltered,
                'data' => $formattedOrders,
            ]);
        }

        return view('admin.orders.fieldstaff_index');
    }

    // Field Staff: update delivery status
    public function updateDeliveryStatus(Request $request, RetailerOrder $retailerOrder)
    {
        $request->validate([
            'status' => 'required|in:dispatched,delivered,cancelled',
            'delivery_notes' => 'nullable|string',
        ]);

        $retailerOrder->update([
            'status' => $request->status,
            'delivery_notes' => $request->delivery_notes,
            'delivered_at' => ($request->status === 'delivered') ? now() : null,
        ]);

        return back()->with('success', 'Delivery status updated successfully!');
    }

    // Admin: show single order
    public function show(RetailerOrder $retailerOrder)
    {
        $retailerOrder->load(['retailer.user', 'fieldStaff.user', 'items.product']);
        return view('admin.orders.show', compact('retailerOrder'));
    }

    public function acceptOrder(RetailerOrder $retailerOrder)
    {
        // Validate permissions (Distributor, Admin, Superadmin can accept)
        if (!Auth::user()->hasRole(['distributor', 'admin', 'superadmin'])) {
            return response()->json(['error' => 'You do not have permission to accept retailer orders.'], 403);
        }

        // If the user is a distributor, check if the order belongs to them
        if (Auth::user()->hasRole('distributor') && $retailerOrder->distributor_id !== Auth::user()->distributor->id) {
            return response()->json(['error' => 'You can only accept orders assigned to your distributorship.'], 403);
        }

        // Check if the order is in a pending state
        if ($retailerOrder->status !== 'pending') {
            return response()->json(['error' => 'Only pending retailer orders can be accepted.'], 400);
        }

        $retailerOrder->update([
            'status' => 'accepted_by_distributor',
        ]);

        return response()->json(['success' => 'Retailer order accepted by distributor successfully!']);
    }

    // Admin: edit form
    public function edit(RetailerOrder $retailerOrder)
    {
        $retailerOrder->load(['items.product']);
        $retailers = Retailer::with('user')->get()->sortBy('user.name');
        $products = Product::all(); // All products for selection
        return view('admin.orders.edit', compact('retailerOrder', 'retailers', 'products'));
    }

    // Admin: update
    public function update(Request $request, RetailerOrder $retailerOrder)
    {
        $request->validate([
            'retailer_id' => 'required|exists:retailers,id',
            'distributor_id' => 'nullable|exists:distributors,id', // Can be assigned later
            'status' => 'required|in:pending,accepted_by_distributor,assigned_to_fieldstaff,out_for_delivery,delivered,rejected',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.order_item_id' => 'nullable|exists:retailer_order_items,id', // For existing items
        ]);

        // Update order header
        $retailerOrder->update([
            'retailer_id' => $request->retailer_id,
            'distributor_id' => $request->distributor_id,
            'status' => $request->status,
            'notes' => $request->notes,
            'delivered_at' => ($request->status === 'delivered') ? now() : null, // Set delivered_at if status is delivered
        ]);

        $totalAmount = 0;
        $totalItems = 0;
        $totalQuantity = 0;
        $requestItemIds = []; // To keep track of items in the current request

        // Load the distributor for stock management
        $distributor = $retailerOrder->distributor;
        if (!$distributor) {
            // This should not happen if order has a distributor_id, but good to check
            // For now, if no distributor, we cannot adjust stock.
            // A more robust solution might require assigning a distributor first or disallowing updates.
            \Log::warning("Retailer order {$retailerOrder->id} has no distributor assigned during update. Stock not adjusted.");
            // Decide how to handle this: throw error, skip stock adjustment, etc.
            // For now, we'll allow it to proceed but stock won't be adjusted.
        }

        try {
            foreach ($request->items as $itemData) {
                // If distributor exists, check and adjust its stock
                if ($distributor) {
                    $product = $distributor->products()->where('product_id', $itemData['product_id'])->first();

                    if (!$product) {
                        throw new \Exception('Product ' . $itemData['product_id'] . ' not available from the assigned distributor.');
                    }

                    $currentOrderItem = null;
                    $oldQuantity = 0;

                    if (isset($itemData['order_item_id']) && $itemData['order_item_id']) {
                        $currentOrderItem = $retailerOrder->items()->find($itemData['order_item_id']);
                        if ($currentOrderItem) {
                            $oldQuantity = $currentOrderItem->quantity;
                        }
                    }

                    $newQuantity = $itemData['quantity'];
                    $stockChange = $newQuantity - $oldQuantity; // Positive for increase, negative for decrease

                    // Check for sufficient stock for the *change* from distributor's stock
                    if ($stockChange > 0 && $product->pivot->stock < $stockChange) {
                        throw new \Exception('Insufficient stock for ' . $product->product_name . '. Available: ' . $product->pivot->stock);
                    }

                    // Adjust distributor's product stock in the pivot table
                    $distributor->products()->updateExistingPivot($product->id, ['stock' => $product->pivot->stock - $stockChange]);
                    $unitPrice = $product->mrp; // Assuming product MRP as unit price
                } else {
                    // If no distributor, we can't get stock or mrp from distributor_product pivot
                    // Fallback to Product model's MRP, but stock adjustment is skipped
                    $product = Product::find($itemData['product_id']);
                    if (!$product) {
                        throw new \Exception('Product ' . $itemData['product_id'] . ' not found.');
                    }
                    $unitPrice = $product->mrp;
                }

                $itemTotalAmount = $newQuantity * $unitPrice;

                if ($currentOrderItem) {
                    // Update existing item
                    $currentOrderItem->update([
                        'product_id' => $itemData['product_id'], // Use itemData product_id, not $product->id in case distributor was null
                        'quantity' => $newQuantity,
                        'unit_price' => $unitPrice,
                        'total_amount' => $itemTotalAmount,
                    ]);
                    $requestItemIds[] = $currentOrderItem->id;
                } else {
                    // Create new item
                    $newItem = $retailerOrder->items()->create([
                        'product_id' => $itemData['product_id'],
                        'quantity' => $newQuantity,
                        'unit_price' => $unitPrice,
                        'total_amount' => $itemTotalAmount,
                    ]);
                    $requestItemIds[] = $newItem->id;
                }

                $totalAmount += $itemTotalAmount;
                $totalItems++;
                $totalQuantity += $newQuantity;
            }

            // Delete items not in the current request
            $retailerOrder->items()->whereNotIn('id', $requestItemIds)->get()->each(function ($item) use ($distributor) {
                if ($distributor) {
                    $product = $distributor->products()->where('product_id', $item->product_id)->first();
                    if ($product) {
                        // Restore stock for deleted item
                        $distributor->products()->updateExistingPivot($product->id, ['stock' => $product->pivot->stock + $item->quantity]);
                    }
                }
                $item->delete();
            });

            // Update the Retailer Order header with calculated totals
            $retailerOrder->total_amount = $totalAmount;
            $retailerOrder->total_items = $totalItems;
            $retailerOrder->total_quantity = $totalQuantity;
            $retailerOrder->save();

        } catch (\Exception $e) {
            \Log::error("Error updating retailer order {$retailerOrder->id}: " . $e->getMessage());
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route('retailer-orders-management.index')->with('success', 'Retailer order updated successfully!');
    }

    // Admin: delete
    public function destroy(RetailerOrder $retailerOrder)
    {
        try {
            $distributor = $retailerOrder->distributor;
            if ($distributor) {
                // Restore stock for each item in the order
                foreach ($retailerOrder->items as $item) {
                    $product = $distributor->products()->where('product_id', $item->product_id)->first();
                    if ($product) {
                        $distributor->products()->updateExistingPivot($product->id, ['stock' => $product->pivot->stock + $item->quantity]);
                    }
                }
            }
            $retailerOrder->delete();
            return redirect()->route('retailer-orders-management.index')->with('success','Order deleted successfully and stock restored.');
        } catch (\Exception $e) {
            \Log::error("Error deleting retailer order {$retailerOrder->id}: " . $e->getMessage());
            return back()->with('error', 'An error occurred while deleting the order and restoring stock.');
        }
    }

    public function getProductsByDistributor(\App\Models\Distributor $distributor)
    {
        return response()->json($distributor->products);
    }
}