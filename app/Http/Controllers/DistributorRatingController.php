<?php

namespace App\Http\Controllers;

use App\Models\FieldStaff;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DistributorRatingController extends Controller
{
    /**
     * Show the list of field staff assigned to the distributor's network.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $distributor = $user->distributor;

        if (!$distributor) {
            abort(403, 'Distributor profile not found.');
        }

        // Get unique field staff who have handled orders for this distributor
        $fieldStaff = FieldStaff::whereHas('retailerOrders', function ($query) use ($distributor) {
            $query->where('distributor_id', $distributor->id);
        })
        ->orWhereHas('retailers', function ($query) use ($distributor) {
            $query->where('distributor_id', $distributor->id);
        })
        ->with(['user', 'ratings' => function ($query) use ($distributor) {
            $query->where('distributor_id', $distributor->id);
        }])
        ->get();

        return view('distributor.ratings.index', compact('fieldStaff'));
    }

    /**
     * Store a new rating for a field staff member.
     */
    public function store(Request $request)
    {
        $request->validate([
            'field_staff_id' => 'required|exists:field_staffs,id',
            'rating' => 'required|integer|min:1|max:5',
            'category' => 'required|string', // e.g., 'service', 'communication', 'response_time'
            'comments' => 'nullable|string|max:500',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $distributor = $user->distributor;

        if (!$distributor) {
            return response()->json(['error' => 'Distributor profile not found.'], 403);
        }

        // Check if distributor has a relationship with this field staff
        $hasRelationship = DB::table('retailer_orders')
            ->where('distributor_id', $distributor->id)
            ->where('fieldstaff_id', $request->field_staff_id)
            ->exists();

        if (!$hasRelationship) {
             // Also check if assigned via retailer
             $hasRelationship = \App\Models\Retailer::where('distributor_id', $distributor->id)
                ->where('field_staff_id', $request->field_staff_id)
                ->exists();
        }

        if (!$hasRelationship) {
            return response()->json(['error' => 'You can only rate field staff assigned to your network.'], 403);
        }

        $rating = Rating::updateOrCreate(
            [
                'distributor_id' => $distributor->id,
                'field_staff_id' => $request->field_staff_id,
                'category' => $request->category,
            ],
            [
                'rating' => $request->rating,
                'comments' => $request->comments,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Rating submitted successfully!',
            'rating' => $rating
        ]);
    }
}
