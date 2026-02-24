<?php

namespace App\Http\Controllers;

use App\Models\Retailer;
use App\Models\RetailerOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class LoyaltyPointsController extends Controller
{
    /**
     * Display a listing of loyalty points.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. If Retailer, show only their own points
        if ($user->hasRole('retailer')) {
            $retailer = $user->retailer;
            if (!$retailer) {
                return redirect()->back()->with('error', 'Retailer profile not found.');
            }

            // Get history of points earned (show all finalized orders)
            $orders = $retailer->retailerOrders()
                ->whereIn('status', ['accepted_by_distributor', 'delivered'])
                ->orderBy('updated_at', 'desc')
                ->get();

            return view('admin.loyalty_points.retailer_view', compact('retailer', 'orders'));
        }

        // 2. For other roles (Admin, Manager, Field Staff), show selector page
        if ($request->ajax()) {
            // Return logic for DataTables if we want to list ALL retailers with points summary
            // But the requirement is "select box".
            // So we might return JSON for select2 or handle search.

            // If it's a request for the summary TABLE of a selected retailer
            if ($request->has('retailer_id')) {
                $retailerId = $request->input('retailer_id');
                // Permission check: ensure user can view this retailer
                $retailer = Retailer::findOrFail($retailerId);

                // Add scoped permission checks here if needed (e.g. Field Staff can only see own retailers)
                if ($user->hasRole('fieldstaff') && $retailer->field_staff_id !== $user->fieldStaff->id) {
                    return response()->json(['error' => 'Unauthorized access to this retailer.'], 403);
                }

                $orders = $retailer->retailerOrders()
                    ->with('items.product')
                    ->whereIn('status', ['accepted_by_distributor', 'delivered'])
                    ->orderBy('updated_at', 'desc');

                return DataTables::of($orders)
                    ->addIndexColumn()
                    ->addColumn('product_summary', function ($row) {
                        return $row->items->map(function ($item) {
                            $prodName = $item->product ? $item->product->product_name : 'Unknown';
                            return $prodName . ' (' . $item->quantity . ' qty)';
                        })->implode('<br>');
                    })
                    ->editColumn('updated_at', function ($row) {
                        return $row->updated_at->format('d M Y, h:i A');
                    })
                    ->editColumn('loyalty_points_earned', function ($row) {
                        return number_format($row->loyalty_points_earned, 2);
                    })
                    ->rawColumns(['product_summary', 'loyalty_points_earned'])
                    ->make(true);
            }
        }

        // Fetch list of retailers available to this user for the dropdown
        $retailers = collect();

        if ($user->hasPermissionToCategory('loyalty_points', 'view')) {
            $retailers = Retailer::with('user')->get();
        } elseif ($user->hasRole('fieldstaff')) {
            if ($user->fieldStaff) {
                $retailers = Retailer::with('user')->where('field_staff_id', $user->fieldStaff->id)->get();
            }
        } elseif ($user->hasRole('distributor')) {
            // Distributors might want to see retailers assigned to them?
            if ($user->distributor) {
                $retailers = Retailer::with('user')->where('distributor_id', $user->distributor->id)->get();
            }
        }

        return view('admin.loyalty_points.index', compact('retailers'));
    }

    /**
     * Get summary stats for a selected retailer via AJAX
     */
    public function getSummary(Request $request, Retailer $retailer)
    {
        $user = Auth::user();

        // Permission check
        if ($user->hasRole('fieldstaff') && $retailer->field_staff_id !== $user->fieldStaff->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        // Add similar checks for distributor/manager if strict scoping is needed

        return response()->json([
            'total_points' => $retailer->loyalty_points,
            'redeemed_points' => 0, // Placeholder if redemption logic exists
            'shop_name' => $retailer->shop_name,
            'owner_name' => $retailer->user->name ?? 'N/A'
        ]);
    }
}
