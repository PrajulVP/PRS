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
                $query = DistributorOrder::with('distributor.user');

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
                        $q->where('distributor_orders.id', 'like', "%{$searchValue}%")
                          ->orWhere('product_name', 'like', "%{$searchValue}%")
                          ->orWhere('status', 'like', "%{$searchValue}%")
                          ->orWhereHas('distributor.user', function ($subQuery) use ($searchValue) {
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
                        case 'id':
                            $query->orderBy('distributor_orders.id', $sortDirection);
                            break;
                        case 'name':
                            $query->join('distributors', 'distributor_orders.distributor_id', '=', 'distributors.id')
                                 ->join('users', 'distributors.user_id', '=', 'users.id')
                                 ->orderBy('users.name', $sortDirection)
                                 ->select('distributor_orders.*');
                            break;
                        case 'product_name':
                            $query->orderBy('product_name', $sortDirection);
                            break;
                        case 'quantity':
                            $query->orderBy('quantity', $sortDirection);
                            break;
                        case 'total_amount':
                            $query->orderBy('total_amount', $sortDirection);
                            break;
                        case 'status':
                            $query->orderBy('status', $sortDirection);
                            break;
                        case 'placed_at':
                            $query->orderBy('placed_at', $sortDirection);
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
                    \Illuminate\Support\Facades\Log::info('DistributorBulkOrderController@index: Order ID ' . $order->id . ' - Status: ' . $order->status);
                    return [
                        'id' => $order->id,
                        'name' => $order->distributor->user->name ?? 'N/A',
                        'product_name' => $order->product_name,
                        'sku' => $order->sku,
                        'quantity' => $order->quantity,
                        'unit_price' => number_format($order->unit_price, 2),
                        'total_amount' => number_format($order->total_amount, 2),
                        'status' => ucfirst($order->status),
                        'placed_at' => $order->placed_at ? \Carbon\Carbon::parse($order->placed_at)->format('Y-m-d H:i:s') : '-',
                        'notes' => $order->notes,
                        'prescription_photo' => $order->prescription_photo,
                        'delivery_notes' => $order->delivery_notes,
                        'distributor_id' => $order->distributor_id,
                        'fieldstaff_id' => $order->fieldstaff_id,
                        'created_at' => $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('Y-m-d H:i:s') : '-',
                        'updated_at' => $order->updated_at ? \Carbon\Carbon::parse($order->updated_at)->format('Y-m-d H:i:s') : '-',
                        'actions' => null,
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
            'distributor_id' => 'required|exists:distributors,id', // Changed from 'id' to 'distributor_id'
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $product = Product::find($request->product_id);

        if (!$product) {
            return back()->withErrors(['product_id' => 'Selected product not found.'])->withInput();
        }

        // Check for sufficient stock
        if ($product->stock < $request->quantity) {
            return back()->withErrors(['quantity' => 'Insufficient stock for this product. Available: ' . $product->stock])->withInput();
        }

        // Decrement stock
        $product->stock -= $request->quantity;
        $product->save();

        $data = $request->all();
        $data['product_name'] = $product->product_name; // Store product name from selected product
        $data['unit_price'] = $product->mrp; // Populate unit price from product's MRP
        $data['sku'] = $product->product_code; // Populate SKU from product's code
        $data['total_amount'] = $request->quantity * $product->mrp; // Recalculate total amount
        $data['placed_at'] = now();
        $data['status'] = 'pending'; // Default status for bulk orders

        // If the authenticated user is a distributor, override the distributor_id
        if (Auth::user()->hasRole('distributor')) {
            $data['distributor_id'] = Auth::user()->distributor->id;
        }

        DistributorOrder::create($data);

        return redirect()->route('distributor-bulk-orders.index')->with('success', 'Order placed successfully!');
    }

    // Admin/Distributor: show single bulk order
    public function show(DistributorOrder $distributorBulkOrder)
    {
        $distributorBulkOrder->load('distributor.user');
        return view('admin.orders.show', compact('distributorBulkOrder'));
    }

    // Admin/Distributor: edit form
    public function edit(DistributorOrder $distributorBulkOrder)
    {
        $distributors = Distributor::with('user')->get();
        return view('admin.orders.edit', compact('distributorBulkOrder', 'distributors'));
    }

    // Admin/Distributor: update
    public function update(Request $request, DistributorOrder $distributorBulkOrder)
    {
        $data = $request->validate([
            'id' => 'required|exists:distributors,id',
            'product_name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'status' => 'required|in:pending,accepted,delivered,cancelled',
            'notes' => 'nullable|string',
        ]);

        $data['total_amount'] = $data['quantity'] * $data['unit_price'];

        $distributorBulkOrder->update($data);

        return redirect()->route('distributor-bulk-orders.index')->with('success','Bulk order updated.');
    }

    // Admin/Distributor: delete
    public function destroy(DistributorOrder $distributorBulkOrder)
    {
        $distributorBulkOrder->delete();
        return redirect()->route('distributor-bulk-orders.index')->with('success','Bulk order deleted.');
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
        
                // Add product to distributor's stock
                $product = Product::where('product_code', $distributorBulkOrder->sku)->first();
                $distributor = $distributorBulkOrder->distributor;
        
                if ($product && $distributor) {
                    $pivot = $distributor->products()->where('product_id', $product->id)->first();
                    if ($pivot) {
                        // If pivot record exists, increment stock
                        $newStock = $pivot->pivot->stock + $distributorBulkOrder->quantity;
                        $distributor->products()->updateExistingPivot($product->id, ['stock' => $newStock]);
                    } else {
                        // If no pivot record, create one
                        $distributor->products()->attach($product->id, ['stock' => $distributorBulkOrder->quantity]);
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

        // Restore product stock
        $product = Product::where('product_code', $distributorBulkOrder->sku)->first();
        if ($product) {
            $product->stock += $distributorBulkOrder->quantity;
            $product->save();
        }

        $distributorBulkOrder->status = 'cancelled';
        $distributorBulkOrder->save();

        return response()->json(['success' => 'Order cancelled successfully!']);
    }

        }

