<?php

namespace App\Http\Controllers;

use App\Models\RetailerOrder;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                    'product_summary' => $order->items->map(function($i) {
                         $pName = $i->product_name ?? $i->product->product_name ?? 'Product';
                         return $pName . ' (' . $i->quantity . ')';
                         return $pName . ' (' . $i->quantity . ')';
                    })->implode(', '),
                    'total_amount' => number_format($order->total_amount, 2),
                    'status' => ucfirst($order->status),
                    'placed_at' => $order->placed_at ? $order->placed_at->format('Y-m-d') : '-',
                    'items' => $order->items->map(function($i) {
                        $pName = $i->product_name ?? $i->product->product_name ?? 'Product';
                        return [
                            'name' => $pName,
                            'qty' => $i->quantity,
                            'price' => $i->unit_price,
                            'total' => $i->total_amount,
                            'side' => $i->side,
                            'size' => $i->size
                        ];
                    }),
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
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string',
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
                'delivery_notes' => $request->delivery_notes,
                'total_amount' => 0,
                'total_items' => 0,
                'total_quantity' => 0,
                'order_code' => 'ORD-' . strtoupper(uniqid())
            ]);

            $totalAmt = 0;
            $totalQty = 0;

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) throw new \Exception('Product not found');

                $unit = $item['unit'] ?? 'Strips';
                $qty = (float)$item['quantity'];

                // Conversion logic using numeric fields
                $multiplier = 1;
                $normalizedUnit = strtolower($unit);
                if ($normalizedUnit === 'box') {
                    $multiplier = (int)($product->strips_per_box ?? 1);
                } elseif ($normalizedUnit === 'carton') {
                    $multiplier = (int)($product->boxes_per_carton ?? 1) * (int)($product->strips_per_box ?? 1);
                } elseif ($normalizedUnit === 'nos' || $normalizedUnit === 'no' || $normalizedUnit === 'unit') {
                    $multiplier = 1 / (max(1, (int)($product->units_per_strip ?? 1)));
                }

                $neededStrips = ceil($qty * $multiplier);

                // Availability check: ensure distributor has enough total stock across all active batches
                $available = Inventory::where('distributor_id', $distributor->id)
                    ->where('product_id', $product->id)
                    ->where('stock', '>', 0)
                    ->sum('stock');

                if ($available < $neededStrips) {
                    throw new \Exception("Insufficient total stock for product: {$product->product_name} (Requested: {$neededStrips}, Available: {$available})");
                }

                // Price Logic: Retailer buys at PTR (Price to Retailer)
                $price = (float)$product->ptr;
                $gstRate = (float)($product->gst ?? 0);
                $taxableSubtotal = $neededStrips * $price;
                $gstAmount = $taxableSubtotal * ($gstRate / 100);
                $subtotalWithGst = $taxableSubtotal + $gstAmount;
                
                // Append variant to product name if provided
                $finalProductName = $product->product_name;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $finalProductName,
                    'quantity' => $qty,
                    'unit' => $unit,
                    'price' => $price,
                    'subtotal' => $subtotalWithGst,
                    'side' => $item['side'] ?? null,
                    'size' => $item['size'] ?? null,
                ]);

                $totalAmt += $subtotalWithGst;
                $totalQty += $neededStrips;
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
