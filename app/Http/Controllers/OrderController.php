<?php


namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Retailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // Admin: list all orders
    public function index()
    {
        $orders = Order::with('retailer')->latest()->paginate(25);
        return view('admin.orders.index', compact('orders'));
    }

    // Admin: show create form
    public function create()
    {
        $retailers = Retailer::with('user')->get()->sortBy('user.name');
        return view('admin.orders.create', compact('retailers'));
    }

    // Admin: store order
    public function store(Request $request)
    {
        $data = $request->validate([
            'retailer_id' => 'required|exists:retailers,id',
            'product_name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $data['total_amount'] = $data['quantity'] * $data['unit_price'];
        $data['placed_at'] = now();

        Order::create($data);

        return redirect()->route('admin.orders.index')->with('success', 'Order created successfully.');
    }

    // Admin: show single order
    public function show(Order $order)
    {
        $order->load('retailer');
        return view('admin.orders.show', compact('order'));
    }

    // Admin: edit form
    public function edit(Order $order)
    {
        $retailers = Retailer::with('user')->get()->sortBy('user.name');
        return view('admin.orders.edit', compact('order','retailers'));
    }

    // Admin: update
    public function update(Request $request, Order $order)
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
    public function destroy(Order $order)
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
