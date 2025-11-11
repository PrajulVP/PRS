<?php

namespace App\Http\Controllers;

use App\Models\RetailerOrder;
use App\Models\Retailer;
use App\Http\Requests\StoreOrderRequest; // Assuming this request is still relevant or will be adapted
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
}
