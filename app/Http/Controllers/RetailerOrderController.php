<?php

namespace App\Http\Controllers;

use App\Models\RetailerOrder;
use App\Models\Retailer;
use App\Http\Requests\StoreDistributorOrderRequest; // Assuming this request is still relevant or will be adapted
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RetailerOrderController extends Controller
{
    // Retailer: list orders
    public function retailerIndex(Request $request)
    {
        if ($request->ajax()) {
            $retailer = Auth::guard('web')->user()->load('retailer')->retailer;

            if (!$retailer) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }

            $query = RetailerOrder::where('retailer_id', $retailer->id);

            // Apply search filter
            if ($request->has('search') && !empty($request->input('search')['value'])) {
                $searchValue = $request->input('search')['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('id', 'like', "%{$searchValue}%")
                        ->orWhere('product_name', 'like', "%{$searchValue}%")
                        ->orWhere('status', 'like', "%{$searchValue}%");
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
                    'product_name' => $order->product_name,
                    'quantity' => $order->quantity,
                    'unit_price' => number_format($order->unit_price, 2),
                    'total_amount' => number_format($order->total_amount, 2),
                    'status' => ucfirst($order->status),
                    'placed_at' => $order->placed_at ? \Carbon\Carbon::parse($order->placed_at)->format('Y-m-d') : '-',
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

        return view('admin.orders.retailer_index');
    }

    public function show(RetailerOrder $retailerOrder)
    {
        $retailerOrder->load('retailer');
        \Illuminate\Support\Facades\Log::info('RetailerOrderController@show: $retailerOrder object', $retailerOrder->toArray());
        return view('admin.orders.show', compact('retailerOrder'));
    }

    
    // Retailer: show create form
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

    // Retailer: store order
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'prescription_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'notes' => 'nullable|string',
        ]);

        $retailer = Auth::user()->retailer;

        if (!$retailer || !$retailer->distributor) {
            return back()->withErrors(['retailer' => 'Retailer not assigned to a distributor.'])->withInput();
        }

        $distributor = $retailer->distributor;
        $product = $distributor->products()->where('product_id', $request->product_id)->first();

        if (!$product) {
            return back()->withErrors(['product_id' => 'Product not available from your assigned distributor.'])->withInput();
        }

        if ($product->pivot->stock < $request->quantity) {
            return back()->withErrors(['quantity' => 'Ordered quantity exceeds available stock from distributor. Available stock: ' . $product->pivot->stock])->withInput();
        }

        // Decrement stock from distributor_product pivot table
        $distributor->products()->updateExistingPivot($product->id, ['stock' => $product->pivot->stock - $request->quantity]);

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

        return redirect()->route('retailer.orders.index')->with('success', 'Medicine requirement sent successfully!');
    }

    public function confirmDelivery(RetailerOrder $retailerOrder)
    {
        // Check if the authenticated user is the retailer for this order
        if ($retailerOrder->retailer_id !== Auth::user()->retailer->id) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        // Check if the order is out for delivery
        if ($retailerOrder->status !== 'out_for_delivery') {
            return response()->json(['error' => 'Only orders that are out for delivery can be confirmed.'], 400);
        }

        $retailerOrder->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        return response()->json(['success' => 'Order delivery confirmed successfully!']);
    }
}
