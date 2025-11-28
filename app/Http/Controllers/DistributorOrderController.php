<?php

namespace App\Http\Controllers;

use App\Models\distributorOrder;
use App\Models\Distributor;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class distributorOrderController extends Controller
{
    // Admin/Distributor: list all orders
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = distributorOrder::with(['distributor.user', 'items.product', 'salesManager.user']);

                // Filter by distributor if authenticated user is a distributor
                if (Auth::user()->hasRole('distributor')) {
                    $distributor = Auth::user()->distributor;
                    $query->where('distributor_id', $distributor->id);
                }
                 // Filter by sales manager if authenticated user is a salesmanager
                if (Auth::user()->hasRole('salesmanager')) {
                    $salesManager = Auth::user()->salesManager;
                    // Sales managers see orders that are either pending (for them to accept)
                    // or orders they have already accepted, or orders that are awaiting their cancellation approval
                    $query->where(function ($q) use ($salesManager) {
                        $q->where('sales_manager_id', $salesManager->id)
                            ->orWhere('status', distributorOrder::STATUS_PENDING)
                            ->orWhere('status', distributorOrder::STATUS_CANCELLATION_REQUESTED);
                    });
                }

                // Filter for Admin/Superadmin: only show orders after Sales Manager approval
                if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('superadmin')) {
                    $query->whereIn('status', [
                        distributorOrder::STATUS_ACCEPTED_BY_SALES_MANAGER,
                        distributorOrder::STATUS_DELIVERED,
                    ]);
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
                        'sales_manager_name' => $order->salesManager?->user?->name ?? 'N/A',
                        'total_items' => $order->total_items,
                        'total_quantity' => $order->total_quantity,
                        'total_amount' => number_format($order->total_amount, 2),
                        'product_summary' => $productSummary, // New field for summary
                        'status' => ucfirst(str_replace('_', ' ', $order->status)), // Format for display
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
                Log::error('Error in distributorOrderController@index: ' . $e->getMessage());
                return response()->json([
                    'error' => 'An error occurred while processing your request.'
                ], 500);
            }
        }

        return view('admin.orders.distributors.index');
    }

    // Admin/Distributor: show create form
    public function create()
    {
        $user = Auth::user();
        $products = Product::all(); // Fetch all products

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

        return view('admin.orders.distributors.create', compact('distributors', 'products', 'authenticatedDistributorId'))->with('orderType', 'distributor');
    }

    // Admin/Distributor: store order
    public function store(Request $request)
    {
        $request->validate([
            'delivery_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
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

        // Create the Distributor Order header
        $order = distributorOrder::create([
            'distributor_id' => $distributorId,
            'sales_manager_id' => $distributorSalesManagerId, // Assign Sales Manager from Distributor
            'status' => distributorOrder::STATUS_PENDING, // Use constant
            'placed_at' => now(),
            'delivery_notes' => $request->delivery_notes,
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

        return redirect()->route('distributor-orders.index')->with('success', 'Order placed successfully!');
    }

    // Admin/Distributor: show single order
    public function show(distributorOrder $distributorOrder)
    {
        $distributorOrder->load(['distributor.user', 'items.product', 'salesManager.user']);
        return view('admin.orders.distributors.show', ['order' => $distributorOrder]);
    }

    // Admin/Distributor: edit form
    public function edit(distributorOrder $distributorOrder)
    {
        $distributorOrder->load(['items.product']);
        $distributors = Distributor::with('user')->get();
        $products = Product::all(); // All products for selection
        return view('admin.orders.distributors.edit', compact('distributorOrder', 'distributors', 'products'));
    }

    // Admin/Distributor: update
    public function update(Request $request, distributorOrder $distributorOrder)
    {
        $request->validate([
            'distributor_id' => 'required|exists:distributors,id',
            // Update validation to use new status constants
            'status' => 'required|in:' . implode(',', [
                distributorOrder::STATUS_PENDING,
                distributorOrder::STATUS_ACCEPTED_BY_SALES_MANAGER,
                distributorOrder::STATUS_DELIVERED,
                distributorOrder::STATUS_CANCELLED,
                distributorOrder::STATUS_CANCELLATION_REQUESTED,
            ]),
            'delivery_notes' => 'nullable|string',
            'cancellation_reason' => 'nullable|string', // Allow updating reason
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.order_item_id' => 'nullable|exists:distributor_order_items,id',
        ]);

        // Stock restoration for items removed or quantity reduced will be handled here
        // Get current items before update
        $oldItems = $distributorOrder->items->keyBy('product_id');


        // Update order header
        $distributorOrder->update([
            'distributor_id' => $request->distributor_id,
            'status' => $request->status,
            'delivery_notes' => $request->delivery_notes,
            'cancellation_reason' => $request->cancellation_reason,
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
                    $currentOrderItem = $distributorOrder->items()->find($itemData['order_item_id']);
                    if ($currentOrderItem) {
                        $oldQuantity = $currentOrderItem->quantity;
                    }
                }

                $newQuantity = $itemData['quantity'];
                $stockChange = $newQuantity - $oldQuantity;

                // Only adjust stock if the order is still pending or relevant for stock management
                // If it's already accepted, stock management might be external
                // For now, assuming stock is adjusted on creation and restoration on deletion/cancellation
                // Update: stock is decremented on order creation and restored on deletion/cancellation
                // So, no stock adjustment needed during update for stock that was already decremented at creation.
                // This logic might need refinement based on exact stock management rules.

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
                    $newItem = $distributorOrder->items()->create([
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

            // Restore stock for items that were in the old order but not in the new request
            $distributorOrder->items()->whereNotIn('id', $requestItemIds)->get()->each(function ($item) {
                $product = $item->product;
                if ($product) {
                    $product->stock += $item->quantity; // Restore stock for deleted item
                    $product->save();
                }
                $item->delete();
            });

            // Update the Distributor Order header with calculated totals
            $distributorOrder->total_amount = $totalAmount;
            $distributorOrder->total_items = $totalItems;
            $distributorOrder->total_quantity = $totalQuantity;
            $distributorOrder->save();

        } catch (\Exception $e) {
            Log::error("Error updating order {$distributorOrder->id}: " . $e->getMessage());
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route('distributor-orders.index')->with('success', 'Order updated.');
    }

    // Distributor: delete (only if pending)
    public function destroy(distributorOrder $distributorOrder)
    {
        // Allow deletion only if the order is pending
        if ($distributorOrder->status !== distributorOrder::STATUS_PENDING) {
            return back()->with('error', 'Only pending orders can be deleted.');
        }

        try {
            // Restore stock for each item in the order
            foreach ($distributorOrder->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->stock += $item->quantity;
                    $product->save();
                }
            }
            $distributorOrder->delete();
            return redirect()->route('distributor-orders.index')->with('success','Order deleted successfully and stock restored.');
        } catch (\Exception $e) {
            Log::error("Error deleting order {$distributorOrder->id}: " . $e->getMessage());
            return back()->with('error', 'An error occurred while deleting the order and restoring stock.');
        }
    }

    // Sales Manager: Accept a pending distributor order
    public function acceptBySalesManager(distributorOrder $distributorOrder)
    {
        Log::info('Accept By Sales Manager: Order ID ' . $distributorOrder->id . ' - Current Status: ' . $distributorOrder->status);

        // Check if the authenticated user has the 'salesmanager' role
        if (!Auth::user()->hasRole('salesmanager')) {
            return response()->json(['error' => 'You do not have permission to accept orders.'], 403);
        }

        // Check if the order is in a pending state
        if ($distributorOrder->status !== distributorOrder::STATUS_PENDING) {
            Log::warning('Accept By Sales Manager: Order ID ' . $distributorOrder->id . ' - Status is not pending. Actual: ' . $distributorOrder->status);
            return response()->json(['error' => 'Only pending orders can be accepted by a Sales Manager.'], 400);
        }

        $distributorOrder->status = distributorOrder::STATUS_ACCEPTED_BY_SALES_MANAGER;
        $distributorOrder->sales_manager_id = Auth::user()->salesManager->id; // Assign Sales Manager
        $distributorOrder->save();

        // No stock transfer at this stage as per new flow requirements

        return response()->json(['success' => 'Order accepted successfully!']);
    }

    // Admin: Accept a distributor order (after Sales Manager acceptance and external billing)
    public function acceptByAdmin(distributorOrder $distributorOrder)
    {
        Log::info('Accept By Admin: Order ID ' . $distributorOrder->id . ' - Current Status: ' . $distributorOrder->status);

        // Check if the authenticated user has the 'admin' role
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'You do not have permission to accept orders as Admin.'], 403);
        }

        // Check if the order is in the 'accepted_by_sales_manager' state
        if ($distributorOrder->status !== distributorOrder::STATUS_ACCEPTED_BY_SALES_MANAGER) {
            Log::warning('Accept By Admin: Order ID ' . $distributorOrder->id . ' - Status is not accepted by Sales Manager. Actual: ' . $distributorOrder->status);
            return response()->json(['error' => 'Only orders accepted by a Sales Manager can be accepted by Admin.'], 400);
        }

        $distributorOrder->status = distributorOrder::STATUS_DELIVERED; // Renamed from delivered to admin accepted as per the user's request.
        $distributorOrder->save();

        // Stock transfer logic (if any) would go here, but user mentioned billing is external and no further tracking needed after admin accepts.
        // Assuming no in-system stock transfer related to admin acceptance.
        $distributor = $distributorOrder->distributor;

        if ($distributor) {
            foreach ($distributorOrder->items as $item) {
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

        return response()->json(['success' => 'Order accepted by Admin successfully!']);
    }

    // Distributor: Request cancellation for an accepted order
    public function requestCancellation(Request $request, distributorOrder $distributorOrder)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|min:5',
        ]);

        // Ensure the user is a distributor
        if (!Auth::user()->hasRole('distributor')) {
            return response()->json(['error' => 'You do not have permission to request cancellation for this order.'], 403);
        }

        // Ensure the order belongs to this distributor
        if ($distributorOrder->distributor_id !== Auth::user()->distributor->id) {
            return response()->json(['error' => 'You can only request cancellation for your own orders.'], 403);
        }

        // Only allow cancellation request if accepted by Sales Manager
        if ($distributorOrder->status !== distributorOrder::STATUS_ACCEPTED_BY_SALES_MANAGER) {
            return response()->json(['error' => 'Cancellation can only be requested for orders accepted by a Sales Manager.'], 400);
        }

        $distributorOrder->status = distributorOrder::STATUS_CANCELLATION_REQUESTED;
        $distributorOrder->cancellation_reason = $request->cancellation_reason;
        $distributorOrder->save();

        return response()->json(['success' => 'Cancellation request submitted successfully!']);
    }

    // Sales Manager: Approve a cancellation request
    public function approveCancellation(distributorOrder $distributorOrder)
    {
        Log::info('Approve Cancellation: Order ID ' . $distributorOrder->id . ' - Current Status: ' . $distributorOrder->status);

        // Check if the authenticated user has the 'salesmanager' role
        if (!Auth::user()->hasRole('salesmanager')) {
            return response()->json(['error' => 'You do not have permission to approve cancellations.'], 403);
        }

        // Check if the order is in 'cancellation_requested' state
        if ($distributorOrder->status !== distributorOrder::STATUS_CANCELLATION_REQUESTED) {
            Log::warning('Approve Cancellation: Order ID ' . $distributorOrder->id . ' - Status is not cancellation requested. Actual: ' . $distributorOrder->status);
            return response()->json(['error' => 'Only orders with a cancellation request can be approved for cancellation.'], 400);
        }

        $distributorOrder->status = distributorOrder::STATUS_CANCELLED;
        $distributorOrder->save();

        // Restore product stock for each item in the order
        foreach ($distributorOrder->items as $item) {
            $product = $item->product;
            if ($product) {
                $product->stock += $item->quantity;
                $product->save();
            }
        }

        return response()->json(['success' => 'Order cancellation approved successfully! Stock restored.']);
    }

    // Distributor: Cancel a pending order (repurposed from original cancelOrder)
    // This method will now essentially handle deletion for pending orders only.
    // Requests for cancellation on accepted orders will go through requestCancellation.
    public function cancelOrder(distributorOrder $distributorOrder)
    {
        // Ensure the user is a distributor
        if (!Auth::user()->hasRole('distributor')) {
            return response()->json(['error' => 'You do not have permission to cancel this order.'], 403);
        }

        // Ensure the order belongs to this distributor
        if ($distributorOrder->distributor_id !== Auth::user()->distributor->id) {
            return response()->json(['error' => 'You can only cancel your own orders.'], 403);
        }

        // If the order is pending, treat it as a direct deletion/cancellation
        if ($distributorOrder->status === distributorOrder::STATUS_PENDING) {
            // Restore product stock for each item in the order (this was already here)
            foreach ($distributorOrder->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->stock += $item->quantity;
                    $product->save();
                }
            }
            $distributorOrder->delete(); // Directly delete
            return response()->json(['success' => 'Pending order cancelled successfully!']);
        }

        // If it's not pending, then it must be an accepted order for which a cancellation request should be made.
        // This method should ideally not be called if status is not PENDING for a full cancellation.
        // If distributor tries to cancel an accepted order, they should use requestCancellation instead.
        return response()->json(['error' => 'Only pending orders can be directly cancelled. For other orders, please request cancellation.'], 400);
    }
}

