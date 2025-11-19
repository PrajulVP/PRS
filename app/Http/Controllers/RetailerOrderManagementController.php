<?php


namespace App\Http\Controllers;

use App\Models\FieldStaff;
use App\Models\RetailerOrder; // Use the new RetailerOrder model
use App\Models\Retailer;
use App\Models\Product; // Added
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

                $query = RetailerOrder::with('retailer.user', 'fieldStaff.user');

                // Apply search filter
                if ($request->has('search') && !empty($request->input('search')['value'])) {
                    $searchValue = $request->input('search')['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('retailer_orders.id', 'like', "%{$searchValue}%")
                          ->orWhere('product_name', 'like', "%{$searchValue}%")
                          ->orWhere('status', 'like', "%{$searchValue}%")
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

                    // Map DataTables column names to database column names
                    switch ($columnName) {
                        case 'id':
                            $query->orderBy('retailer_orders.id', $sortDirection);
                            break;
                        case 'product_name':
                            $query->orderBy('product_name', $sortDirection);
                            break;
                        case 'quantity':
                            $query->orderBy('quantity', $sortDirection);
                            break;
                        case 'unit_price':
                            $query->orderBy('unit_price', $sortDirection);
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
                        'retailer_name' => $order->retailer?->user?->name ?? 'N/A',
                        'product_name' => $order->product_name,
                        'quantity' => $order->quantity,
                        'unit_price' => number_format($order->unit_price, 2),
                        'total_amount' => number_format($order->total_amount, 2),
                        'status' => ucfirst($order->status),
                        'placed_at' => $order->placed_at ? \Carbon\Carbon::parse($order->placed_at)->format('Y-m-d H:i:s') : '-',
                        'fieldstaff_name' => $order->fieldStaff?->user?->name ?? 'Not Assigned',
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
        } // Closing brace for if ($request->ajax())

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
                ->whereIn('status', ['pending', 'dispatched', 'delivered', 'cancelled']);

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
                    'fieldstaff_name' => $order->fieldStaff->user->name ?? 'Not Assigned',
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
        $fieldstaffs = FieldStaff::where('distributor_id', $distributor->id)->with('user')->get()->map(function($fieldstaff) {
            return ['id' => $fieldstaff->id, 'name' => $fieldstaff->user->name];
        });

        return view('admin.orders.distributor_retailer_orders_index', compact('fieldstaffs'));
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
                ->whereIn('status', ['accepted', 'dispatched']);

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
        $retailerOrder->load('retailer', 'fieldStaff.user'); // Load fieldStaff and its user
        return view('admin.orders.show', compact('retailerOrder'));
    }

    public function acceptAndAssignFieldStaff(Request $request, RetailerOrder $retailerOrder)
    {
        // Validate permissions
        if (!Auth::user()->hasRole(['distributor', 'admin', 'superadmin'])) {
            return response()->json(['error' => 'You do not have permission to accept and assign retailer orders.'], 403);
        }

        // If the user is a distributor, check if the order belongs to them
        if (Auth::user()->hasRole('distributor') && $retailerOrder->distributor_id !== Auth::user()->distributor->id) {
            return response()->json(['error' => 'You can only accept and assign orders assigned to your distributorship.'], 403);
        }

        // Validate request
        $request->validate([
            'fieldstaff_id' => 'required|exists:fieldstaffs,id',
        ]);

        // Check if the order is in a pending state
        if ($retailerOrder->status !== 'pending') {
            return response()->json(['error' => 'Only pending retailer orders can be accepted and assigned.'], 400);
        }

        // Update order status and assign field staff
        $retailerOrder->update([
            'fieldstaff_id' => $request->fieldstaff_id,
            'status' => 'dispatched', // Directly set to dispatched as per user's request
        ]);

        return response()->json(['success' => 'Retailer order accepted and assigned to field staff successfully!']);
    }

    // Admin: edit form
    public function edit(RetailerOrder $retailerOrder)
    {
        $retailers = Retailer::with('user')->get()->sortBy('user.name');
        return view('admin.orders.edit', compact('retailerOrder','retailers'));
    }

    // Admin: update
    public function update(Request $request, RetailerOrder $retailerOrder)
    {
        $data = $request->validate([
            'retailer_id' => 'required|exists:retailers,id',
            'product_name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'status' => 'required|in:pending,accepted,dispatched,delivered,cancelled',
            'notes' => 'nullable|string',
        ]);

        $data['total_amount'] = $data['quantity'] * $data['unit_price'];

        $retailerOrder->update($data);

        return redirect()->route('admin.orders.index')->with('success','Order updated.');
    }

    // Admin: delete
    public function destroy(RetailerOrder $retailerOrder)
    {
        $retailerOrder->delete();
        return redirect()->route('retailer-orders-management.index')->with('success','Order deleted.');
    }

    public function getProductsByDistributor(\App\Models\Distributor $distributor)
    {
        return response()->json($distributor->products);
    }
}