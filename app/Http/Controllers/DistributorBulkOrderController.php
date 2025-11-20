<?php

namespace App\Http\Controllers;

use App\Models\DistributorOrder;
use App\Models\Distributor;
use App\Models\Product; // Added
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DistributorBulkOrderController extends Controller
{
    // Admin/Distributor: list all bulk orders
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = DistributorOrder::with(['distributor.user', 'items.product']);

                // Filter by distributor if authenticated user is a distributor
                if (Auth::user()->hasRole('distributor')) {
                    $distributor = Auth::user()->distributor;
                    $query->where('distributor_id', $distributor->id);
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
                        case 'order_code':
                            $query->orderBy('distributor_orders.order_code', $sortDirection);
                            break;
                        case 'name':
                            $query->join('distributors', 'distributor_orders.distributor_id', '=', 'distributors.id')
                                 ->join('users', 'distributors.user_id', '=', 'users.id')
                                 ->orderBy('users.name', $sortDirection)
                                 ->select('distributor_orders.*'); // Select back original columns
                            break;
                        case 'total_items':
                            $query->orderBy('distributor_orders.total_items', $sortDirection);
                            break;
                        case 'total_quantity':
                            $query->orderBy('distributor_orders.total_quantity', $sortDirection);
                            break;
                        case 'total_amount':
                            $query->orderBy('distributor_orders.total_amount', $sortDirection);
                            break;
                        case 'status':
                            $query->orderBy('distributor_orders.status', $sortDirection);
                            break;
                        case 'placed_at':
                            $query->orderBy('distributor_orders.placed_at', $sortDirection);
                            break;
                        default:
                            $query->orderBy('distributor_orders.id', 'desc');
                            break;
                    }
                } else {
                    $query->orderBy('distributor_orders.id', 'desc'); // Default sort
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
                        'name' => $order->distributor->user->name ?? 'N/A',
                        'total_items' => $order->total_items,
                        'total_quantity' => $order->total_quantity,
                        'total_amount' => number_format($order->total_amount, 2),
                        'product_summary' => $productSummary, // New field for summary
                        'status' => ucfirst($order->status),
                        'placed_at' => $order->placed_at ? \Carbon\Carbon::parse($order->placed_at)->format('Y-m-d H:i:s') : '-',
                        'actions' => null, // Placeholder for actions
                    ];
                });

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalData,
                    'recordsFiltered' => $totalFiltered,
                    'data' => $formattedOrders,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error in DistributorBulkOrderController@index: ' . $e->getMessage());
                return response()->json([
                    'error' => 'An error occurred while processing your request.'
                ], 500);
            }
        }

        return view('admin.orders.distributor_index');
    }

    // Admin/Distributor: show create form
    public function create()
    {
        $user = Auth::user();
        $products = \App\Models\Product::all(); // Fetch all products

        $distributors = collect(); // Initialize as empty collection
        $authenticatedDistributorId = null;

        if ($user->hasRole('distributor')) {
            $authenticatedDistributorId = $user->distributor->id ?? null;
            // If a distributor is creating an order, they are creating it for themselves
            // No need to select a distributor from a list
        } else {
            // If an admin/superadmin is creating, they need to select a distributor
            $distributors = Distributor::with('user')->get();
        }

        return view('admin.orders.create', compact('distributors', 'products', 'authenticatedDistributorId'))->with('orderType', 'distributor');
    }

    // Admin/Distributor: store bulk order
    public function store(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $distributorId = null;
        if (Auth::user()->hasRole('distributor')) {
            $distributorId = Auth::user()->distributor->id;
        } else {
            $request->validate(['distributor_id' => 'required|exists:distributors,id']);
            $distributorId = $request->distributor_id;
        }

        $totalAmount = 0;
        $totalItems = 0;
        $totalQuantity = 0;

        // Create the Distributor Order header
        $order = DistributorOrder::create([
            'distributor_id' => $distributorId,
            'status' => 'pending',
            'placed_at' => now(),
            'notes' => $request->notes,
            'total_amount' => 0, // Initialize to 0, will be updated
            'total_items' => 0,  // Initialize to 0, will be updated
            'total_quantity' => 0, // Initialize to 0, will be updated
        ]);

        try {
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);

                if (!$product) {
                    throw new \Exception('One or more selected products not found.');
                }

                if ($product->stock < $itemData['quantity']) {
                    throw new \Exception('Insufficient stock for ' . $product->product_name . '. Available: ' . $product->stock);
                }

                // Decrement stock
                $product->stock -= $itemData['quantity'];
                $product->save();

                // Create Order Item
                $unitPrice = $product->mrp;
                $itemTotalAmount = $itemData['quantity'] * $unitPrice;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'total_amount' => $itemTotalAmount,
                ]);

                $totalAmount += $itemTotalAmount;
                $totalItems++;
                $totalQuantity += $itemData['quantity'];
            }

            // Update the Distributor Order header with calculated totals
            $order->total_amount = $totalAmount;
            $order->total_items = $totalItems;
            $order->total_quantity = $totalQuantity;
            $order->save();

        } catch (\Exception $e) {
            // If any error occurs, delete the order and associated items
            $order->items()->delete();
            $order->delete();
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route('distributor-bulk-orders.index')->with('success', 'Order placed successfully!');
    }

    // Admin/Distributor: show single bulk order
    public function show(DistributorOrder $distributorBulkOrder)
    {
        $distributorBulkOrder->load(['distributor.user', 'items.product']);
        return view('admin.orders.show', ['order' => $distributorBulkOrder]);
    }

    // Admin/Distributor: edit form
    public function edit(DistributorOrder $distributorBulkOrder)
    {
        $distributorBulkOrder->load(['items.product']);
        $distributors = Distributor::with('user')->get();
        $products = Product::all(); // All products for selection
        return view('admin.orders.edit', compact('distributorBulkOrder', 'distributors', 'products'));
    }

    // Admin/Distributor: update
    public function update(Request $request, DistributorOrder $distributorBulkOrder)
    {
        $request->validate([
            'distributor_id' => 'required|exists:distributors,id',
            'status' => 'required|in:pending,accepted,dispatched,delivered,cancelled',
            'notes' => 'nullable|string',
            'delivery_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.order_item_id' => 'nullable|exists:distributor_order_items,id',
        ]);

        // Update order header
        $distributorBulkOrder->update([
            'distributor_id' => $request->distributor_id,
            'status' => $request->status,
            'notes' => $request->notes,
            'delivery_notes' => $request->delivery_notes,
        ]);

        $totalAmount = 0;
        $totalItems = 0;
        $totalQuantity = 0;
        $requestItemIds = []; // To keep track of items in the current request

        try {
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new \Exception('One or more selected products not found.');
                }

                $currentOrderItem = null;
                $oldQuantity = 0;

                if (isset($itemData['order_item_id']) && $itemData['order_item_id']) {
                    $currentOrderItem = $distributorBulkOrder->items()->find($itemData['order_item_id']);
                    if ($currentOrderItem) {
                        $oldQuantity = $currentOrderItem->quantity;
                    }
                }

                $newQuantity = $itemData['quantity'];
                $stockChange = $newQuantity - $oldQuantity;

                // Check for sufficient stock for the *change*
                // If stockChange is positive (new quantity > old quantity), check if enough stock available
                // If stockChange is negative (new quantity < old quantity), stock will increase, no check needed
                if ($stockChange > 0 && $product->stock < $stockChange) {
                    throw new \Exception('Insufficient stock for ' . $product->product_name . '. Available: ' . $product->stock);
                }

                // Adjust product stock based on the difference
                $product->stock -= $stockChange;
                $product->save();

                $unitPrice = $product->mrp;
                $itemTotalAmount = $newQuantity * $unitPrice;

                if ($currentOrderItem) {
                    // Update existing item
                    $currentOrderItem->update([
                        'product_id' => $product->id,
                        'quantity' => $newQuantity,
                        'unit_price' => $unitPrice,
                        'total_amount' => $itemTotalAmount,
                    ]);
                    $requestItemIds[] = $currentOrderItem->id;
                } else {
                    // Create new item
                    $newItem = $distributorBulkOrder->items()->create([
                        'product_id' => $product->id,
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
            $distributorBulkOrder->items()->whereNotIn('id', $requestItemIds)->get()->each(function ($item) {
                $product = $item->product;
                if ($product) {
                    $product->stock += $item->quantity; // Restore stock for deleted item
                    $product->save();
                }
                $item->delete();
            });

            // Update the Distributor Order header with calculated totals
            $distributorBulkOrder->total_amount = $totalAmount;
            $distributorBulkOrder->total_items = $totalItems;
            $distributorBulkOrder->total_quantity = $totalQuantity;
            $distributorBulkOrder->save();

        } catch (\Exception $e) {
            // Log the error and rollback any stock changes if needed (complex)
            // For simplicity, for now, just return with error. A more robust solution might involve DB transactions.
            \Log::error("Error updating order {$distributorBulkOrder->id}: " . $e->getMessage());
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route('distributor-bulk-orders.index')->with('success', 'Bulk order updated.');
    }

    // Admin/Distributor: delete
    public function destroy(DistributorOrder $distributorBulkOrder)
    {
        try {
            // Restore stock for each item in the order
            foreach ($distributorBulkOrder->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->stock += $item->quantity;
                    $product->save();
                }
            }
            $distributorBulkOrder->delete();
            return redirect()->route('distributor-bulk-orders.index')->with('success','Bulk order deleted successfully and stock restored.');
        } catch (\Exception $e) {
            \Log::error("Error deleting order {$distributorBulkOrder->id}: " . $e->getMessage());
            return back()->with('error', 'An error occurred while deleting the order and restoring stock.');
        }
    }

    // Admin/Manager: Confirm delivery of a distributor order
    public function confirmDelivery(DistributorOrder $distributorBulkOrder)
    {
        \Illuminate\Support\Facades\Log::info('Confirm Delivery: Order ID ' . $distributorBulkOrder->id . ' - Current Status: ' . $distributorBulkOrder->status);

        // Check if the authenticated user has permission to edit distributor orders
        if (!Auth::user()->hasPermissionToCategory('distributor_orders', 'edit')) {
            return response()->json(['error' => 'You do not have permission to confirm delivery of orders.'], 403);
        }

        // Check if the order is in an 'accepted' state
        if ($distributorBulkOrder->status !== 'accepted') {
            \Illuminate\Support\Facades\Log::warning('Confirm Delivery: Order ID ' . $distributorBulkOrder->id . ' - Status is not accepted. Actual: ' . $distributorBulkOrder->status);
            return response()->json(['error' => 'Only accepted orders can be confirmed as delivered.'], 400);
        }

        $distributorBulkOrder->status = 'delivered';
        $distributorBulkOrder->save();

                return response()->json(['success' => 'Order confirmed as delivered!']);

            }

        

            // Manager: Accept a pending distributor order

                public function acceptOrder(DistributorOrder $distributorBulkOrder)

                {

                    \Illuminate\Support\Facades\Log::info('Accept Order: Order ID ' . $distributorBulkOrder->id . ' - Current Status: ' . $distributorBulkOrder->status);

            

                    // Check if the authenticated user has permission to edit distributor orders

                    if (!Auth::user()->hasPermissionToCategory('distributor_orders', 'edit')) {

                        return response()->json(['error' => 'You do not have permission to accept orders.'], 403);

                    }

            

                    // Check if the order is in a pending state

                    if ($distributorBulkOrder->status !== 'pending') {

                        \Illuminate\Support\Facades\Log::warning('Accept Order: Order ID ' . $distributorBulkOrder->id . ' - Status is not pending. Actual: ' . $distributorBulkOrder->status);

                        return response()->json(['error' => 'Only pending orders can be accepted.'], 400);

                    }

            

                    $distributorBulkOrder->status = 'accepted';

                    $distributorBulkOrder->save();

            

                    $distributor = $distributorBulkOrder->distributor;

            

                    if ($distributor) {

                        foreach ($distributorBulkOrder->items as $item) {

                            $product = $item->product;

                            if ($product) {

                                $pivot = $distributor->products()->where('product_id', $product->id)->first();

                                if ($pivot) {

                                    // If pivot record exists, increment stock

                                    $newStock = $pivot->pivot->stock + $item->quantity;

                                    $distributor->products()->updateExistingPivot($product->id, ['stock' => $newStock]);

                                } else {

                                    // If no pivot record, create one

                                    $distributor->products()->attach($product->id, ['stock' => $item->quantity]);

                                }

                            }

                        }

                    }

            

                    return response()->json(['success' => 'Order accepted successfully!']);

                }

    // Distributor: Cancel a pending order
    public function cancelOrder(DistributorOrder $distributorBulkOrder)
    {
        // Ensure the user is a distributor
        if (!Auth::user()->hasRole('distributor')) {
            return response()->json(['error' => 'You do not have permission to cancel this order.'], 403);
        }

        // Ensure the order belongs to this distributor
        if ($distributorBulkOrder->distributor_id !== Auth::user()->distributor->id) {
            return response()->json(['error' => 'You can only cancel your own orders.'], 403);
        }

        // Check if the order is in a 'pending' state
        if ($distributorBulkOrder->status !== 'pending') {
            return response()->json(['error' => 'Only pending orders can be cancelled.'], 400);
        }

        // Restore product stock for each item in the order
        foreach ($distributorBulkOrder->items as $item) {
            $product = $item->product;
            if ($product) {
                $product->stock += $item->quantity;
                $product->save();
            }
        }

        $distributorBulkOrder->status = 'cancelled';
        $distributorBulkOrder->save();

        return response()->json(['success' => 'Order cancelled successfully!']);
    }

        }

