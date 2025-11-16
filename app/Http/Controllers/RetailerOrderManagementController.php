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

                $query = RetailerOrder::with('retailer.user');

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
                        case 'retailer_name':
                            $query->join('retailers', 'retailer_orders.retailer_id', '=', 'retailers.id')
                                 ->join('users', 'retailers.user_id', '=', 'users.id')
                                 ->orderBy('users.name', $sortDirection)
                                 ->select('retailer_orders.*'); // Select retailer_orders.* to avoid ambiguity
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
                        'retailer_name' => $order->retailer->user->name ?? 'N/A',
                        'product_name' => $order->product_name,
                        'quantity' => $order->quantity,
                        'unit_price' => number_format($order->unit_price, 2),
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

        return view('admin.orders.index');
    }

    // Admin: show create form
    public function create()
    {
        $user = Auth::user();
        $retailer = $user->retailer;

        if (!$retailer || !$retailer->distributor) {
            // Handle case where retailer is not found or not assigned to a distributor
            return redirect()->back()->with('error', 'You are not assigned to a distributor or your retailer profile is incomplete.');
        }

        $distributorProducts = $retailer->distributor->products; // Get products associated with the retailer's distributor

        return view('admin.orders.create', ['products' => $distributorProducts])->with('orderType', 'retailer');
    }

    // Admin: store order
    public function store(Request $request) // Changed request type from StoreDistributorOrderRequest to Request
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'prescription_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'notes' => 'nullable|string',
        ]);

        $product = Product::find($request->product_id);

        if (!$product) {
            return back()->withErrors(['product_id' => 'Selected product not found.'])->withInput();
        }

        $orderedUnits = $request->quantity * $product->pack_quantity;
        if ($product->stock < $orderedUnits) {
            $availablePacks = floor($product->stock / $product->pack_quantity);
            return back()->withErrors(['quantity' => 'Ordered quantity exceeds available stock. Available packs: ' . $availablePacks])->withInput();
        }

        $retailer = Auth::user()->retailer;

        // Decrement product stock
        $product->stock -= $orderedUnits;
        $product->save();

        $data = $request->all();
        $data['retailer_id'] = $retailer->id;
        $data['product_name'] = $product->product_name; // Store product name from selected product
        $data['unit_price'] = $product->mrp; // Store unit price from selected product
        $data['total_amount'] = $request->quantity * $product->mrp;
        $data['placed_at'] = now();
        $data['status'] = 'pending';
        $data['distributor_id'] = $retailer->distributor_id; // Add distributor_id from the retailer

        if ($request->hasFile('prescription_photo')) {
            $data['prescription_photo'] = $request->file('prescription_photo')->store('prescriptions', 'public');
        }

        RetailerOrder::create($data);

        return redirect()->route('dashboard')->with('success', 'Medicine requirement sent successfully!');
    }

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
    public function assignDistributor(Request $request, RetailerOrder $order)
    {
        $request->validate([
            'distributor_id' => 'required|exists:distributors,id',
        ]);

        $order->update([
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
                ->whereIn('status', ['assigned_to_distributor', 'assigned_to_fieldstaff', 'out_for_delivery']);

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

            $totalFiltered = $query->count(); // Count after applying search filter

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

        return view('admin.orders.distributor_index');
    }

    // Distributor: assign order to field staff
    public function assignFieldStaff(Request $request, RetailerOrder $order)
    {
        $request->validate([
            'fieldstaff_id' => 'required|exists:fieldstaffs,id',
        ]);

        $order->update([
            'fieldstaff_id' => $request->fieldstaff_id,
            'status' => 'assigned_to_fieldstaff',
        ]);

        if ($request->ajax()) {
            $fieldstaffName = FieldStaff::find($request->fieldstaff_id)->user->name;
            return response()->json([
                'success' => 'Order assigned to field staff successfully!',
                'fieldstaff_name' => $fieldstaffName,
            ]);
        }

        return back()->with('success', 'Order assigned to field staff successfully!');
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
                ->whereIn('status', ['assigned_to_fieldstaff', 'out_for_delivery']);

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
    public function updateDeliveryStatus(Request $request, RetailerOrder $order)
    {
        $request->validate([
            'status' => 'required|in:out_for_delivery,delivered,cancelled',
            'delivery_notes' => 'nullable|string',
        ]);

        $order->update([
            'status' => $request->status,
            'delivery_notes' => $request->delivery_notes,
            'delivered_at' => ($request->status === 'delivered') ? now() : null,
        ]);

        return back()->with('success', 'Delivery status updated successfully!');
    }

    // Admin: show single order
    public function show(RetailerOrder $order)
    {
        $order->load('retailer');
        return view('admin.orders.show', compact('order'));
    }

    // Admin: edit form
    public function edit(RetailerOrder $order)
    {
        $retailers = Retailer::with('user')->get()->sortBy('user.name');
        return view('admin.orders.edit', compact('order','retailers'));
    }

    // Admin: update
    public function update(Request $request, RetailerOrder $order)
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

        $order->update($data);

        return redirect()->route('admin.orders.index')->with('success','Order updated.');
    }

    // Admin: delete
    public function destroy(RetailerOrder $order)
    {
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success','Order deleted.');
    }


}



// namespace App\Http\Controllers;

// use App\Models\Order;
// use App\Models\OrderItem;
// use App\Models\Distributor;
// use App\Models\FieldStaff;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Auth;

// class OrderController extends Controller
// {
//     // Retailer: show create form
//     public function create()
//     {
//         // If you have a product list you can pass it here.
//         // $products = Product::all();
//         return view('admin.retailers.create_order'); // create view for retailer in your views
//     }

//     // Retailer: store order
//     public function store(Request $request)
//     {
//         // Expected payload:
//         // items = [{product_name, sku, quantity, unit_price}, ...]
//         $data = $request->validate([
//             'items' => 'required|array|min:1',
//             'items.*.product_name' => 'required|string',
//             'items.*.quantity' => 'required|integer|min:1',
//             'items.*.unit_price' => 'required|numeric|min:0',
//             'notes' => 'nullable|string'
//         ]);

//         DB::transaction(function() use ($request, $data, &$order) {
//             // obtain retailer id from auth or request
//             $retailerId = $request->user()->id ?? $request->input('retailer_id');

//             $order = Order::create([
//                 'retailer_id' => $retailerId,
//                 'total_value' => 0,
//                 'status' => 'pending',
//                 'placed_at' => now(),
//                 'notes' => $data['notes'] ?? null,
//             ]);

//             $total = 0;
//             foreach ($data['items'] as $it) {
//                 $totalPrice = $it['quantity'] * $it['unit_price'];
//                 $order->items()->create([
//                     'product_name' => $it['product_name'],
//                     'sku' => $it['sku'] ?? null,
//                     'quantity' => $it['quantity'],
//                     'unit_price' => $it['unit_price'],
//                     'total_price' => $totalPrice
//                 ]);
//                 $total += $totalPrice;
//             }

//             $order->update(['total_value' => $total]);
//         });

//         return redirect()->route('retailers.orders.index')->with('success', 'Order placed successfully.');
//     }

//     // Admin/Manager: list all orders
//     public function index()
//     {
//         // admin view: show all orders with relations
//         $orders = Order::with('retailer','distributor','fieldstaff','items')->latest()->paginate(25);
//         return view('admin.orders.index', compact('orders'));
//     }

//     // Admin/Manager: show order details
//     public function show(Order $order)
//     {
//         $order->load('items','retailer','distributor','fieldstaff');
//         return view('admin.orders.show', compact('order'));
//     }

//     // Admin/Manager: approve & assign distributor
//     public function approveAndAssignDistributor(Request $request, Order $order)
//     {
//         $data = $request->validate([
//             'distributor_id' => 'required|exists:distributors,id'
//         ]);
//         $order->update([
//             'distributor_id' => $data['distributor_id'],
//             'status' => 'approved',
//             'approved_at' => now(),
//         ]);
//         return back()->with('success','Order approved and assigned to distributor.');
//     }

//     // Distributor: list orders assigned to distributor
//     public function distributorIndex()
//     {
//         $distributorId = Auth::user()->id ?? request()->user()->id;
//         $orders = Order::with('items','retailer','fieldstaff')
//             ->where('distributor_id', $distributorId)
//             ->latest()
//             ->paginate(25);
//         return view('admin.distributors.orders.index', compact('orders'));
//     }

//     // Distributor: assign field staff to order
//     public function assignFieldStaff(Request $request, Order $order)
//     {
//         $data = $request->validate([
//             'fieldstaff_id' => 'required|exists:fieldstaffs,id'
//         ]);
//         $order->update([
//             'fieldstaff_id' => $data['fieldstaff_id'],
//             'status' => 'assigned',
//         ]);
//         return back()->with('success','Field staff assigned.');
//     }

//     // Fieldstaff: mark delivered (with optional proof)
//     public function markDelivered(Request $request, Order $order)
//     {
//         $data = $request->validate([
//             'proof' => 'nullable|file|mimes:jpg,png,pdf|max:5120'
//         ]);

//         if ($request->hasFile('proof')) {
//             $path = $request->file('proof')->store('order_proofs','public');
//             // optionally save the path somewhere (order_proofs table or order->notes)
//             $order->notes = ($order->notes ? $order->notes . "\n" : '') . "Proof: $path";
//         }

//         $order->update([
//             'status' => 'delivered',
//             'delivered_at' => now(),
//         ]);

//         return back()->with('success','Order marked as delivered.');
//     }
// }
