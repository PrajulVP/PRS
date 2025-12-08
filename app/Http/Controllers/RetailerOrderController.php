<?php

namespace App\Http\Controllers;

use App\Models\RetailerOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RetailerOrderController extends Controller
{
    // Retailer: list orders (Unified View with Create Modal)
    public function retailerIndex(Request $request)
    {
        // If AJAX, return DataTable JSON
        if ($request->ajax()) {
            $retailer = Auth::guard('web')->user()->load('retailer')->retailer;
            if (!$retailer) return response()->json(['error' => 'Unauthorized'], 403);

            $query = RetailerOrder::where('retailer_id', $retailer->id)->with('items.product');

            $orders = $query->orderBy('id', 'desc')->get();

            $formatted = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'product_summary' => $order->items->map(fn($i) => $i->product->product_name . ' (' . $i->quantity . ')')->implode(', '),
                    'total_amount' => number_format($order->total_amount, 2),
                    'status' => ucfirst($order->status),
                    'placed_at' => $order->placed_at ? $order->placed_at->format('Y-m-d') : '-',
                    'items' => $order->items->map(fn($i) => [
                        'name' => $i->product->product_name,
                        'qty' => $i->quantity,
                        'price' => $i->unit_price,
                        'total' => $i->total_amount
                    ]),
                    'notes' => $order->notes,
                    'delivery_notes' => $order->delivery_notes
                ];
            });

            return response()->json(['data' => $formatted]);
        }

        // Return the unified view. 
        // We pass distributorProducts for the Create Modal if the user is a Retailer.
        $user = Auth::user();
        $retailer = $user->retailer;
        $distributorProducts = collect();
        if ($retailer && $retailer->distributor) {
            $distributorProducts = $retailer->distributor->products;
        }

        $retailers = collect(); // For Admin view compatibility (though this method is for retailer role mainly)
        $fieldstaffs = collect();
        $distributors = collect();
        $products = Product::all(); // Fallback

        // We use the SAME 'index' view but data will drive what is shown.
        // Actually, the Admin Index expects $retailers, $fieldstaffs etc. 
        // We should pass empty collections or just enough to not break the view.
        // Or we pass a flag 'isRetailer' to view.

        return view('admin.orders.retailers.index', compact('distributorProducts', 'retailers', 'fieldstaffs', 'distributors', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        $retailer = Auth::user()->retailer;
        if (!$retailer || !$retailer->distributor) return back()->with('error', 'No distributor assigned');

        $distributor = $retailer->distributor;

        try {
            DB::beginTransaction();
            $order = RetailerOrder::create([
                'retailer_id' => $retailer->id,
                'distributor_id' => $distributor->id,
                'status' => 'pending',
                'placed_at' => now(),
                'notes' => $request->notes,
                'delivery_notes' => $request->delivery_notes,
                'total_amount' => 0,
                'total_items' => 0,
                'total_quantity' => 0,
                'order_code' => 'ORD-' . strtoupper(uniqid())
            ]);

            $totalAmt = 0;
            $totalQty = 0;

            foreach ($request->items as $item) {
                $prod = $distributor->products()->where('product_id', $item['product_id'])->first();
                if (!$prod || $prod->pivot->stock < $item['quantity']) {
                    throw new \Exception('Insufficient stock for product id ' . $item['product_id']);
                }

                // Decrement
                $distributor->products()->updateExistingPivot($prod->id, ['stock' => $prod->pivot->stock - $item['quantity']]);

                $sub = $item['quantity'] * $prod->mrp;
                $order->items()->create([
                    'product_id' => $prod->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $prod->mrp,
                    'total_amount' => $sub
                ]);
                $totalAmt += $sub;
                $totalQty += $item['quantity'];
            }

            $order->update(['total_amount' => $totalAmt, 'total_items' => count($request->items), 'total_quantity' => $totalQty]);
            DB::commit();
            return back()->with('success', 'Order placed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
