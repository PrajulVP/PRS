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
                    $query->where('id', $distributor->id);
                }

                $totalData = $query->count();

                // Apply search filter
                if ($request->has('search') && !empty($request->input('search')['value'])) {
                    $searchValue = $request->input('search')['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('orders.id', 'like', "%{$searchValue}%")
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
                            $query->orderBy('orders.id', $sortDirection);
                            break;
                        case 'name':
                            $query->join('distributors', 'orders.id', '=', 'distributors.id')
                                 ->join('users', 'distributors.user_id', '=', 'users.id')
                                 ->orderBy('users.name', $sortDirection)
                                 ->select('orders.*');
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
                            $query->orderBy('orders.id', 'desc');
                            break;
                    }
                } else {
                    $query->orderBy('orders.id', 'desc'); // Default sort
                }

                // Apply pagination
                $start = $request->input('start');
                $length = $request->input('length');
                $orders = $query->offset($start)->limit($length)->get();

                $formattedOrders = $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'name' => $order->distributor->user->name ?? 'N/A',
                        'product_name' => $order->product_name,
                        'quantity' => $order->quantity,
                        'total_amount' => number_format($order->total_amount, 2),
                        'status' => ucfirst($order->status),
                        'placed_at' => $order->placed_at ? \Carbon\Carbon::parse($order->placed_at)->format('Y-m-d H:i:s') : '-',
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

        // No stock decrement for distributors initially, pending clarification.

        $data = $request->all();
        $data['product_name'] = $product->product_name; // Store product name from selected product
        $data['unit_price'] = 0; // Bulk orders are placed "without all charges"
        $data['total_amount'] = 0; // Bulk orders are placed "without all charges"
        $data['placed_at'] = now();
        $data['status'] = 'pending'; // Default status for bulk orders

        // If the authenticated user is a distributor, override the distributor_id
        if (Auth::user()->hasRole('distributor')) {
            $data['distributor_id'] = Auth::user()->distributor->id;
        }

        DistributorOrder::create($data);

        return redirect()->route('distributor-bulk-orders.index')->with('success', 'Bulk order placed successfully!');
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
            'status' => 'required|in:pending,accepted,dispatched,delivered,cancelled',
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
}
