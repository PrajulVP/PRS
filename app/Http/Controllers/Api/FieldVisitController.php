<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FieldVisit;
use App\Models\VisitPurpose;
use App\Models\RetailerOrder;
use App\Models\DistributorOrder;
use Carbon\Carbon;

class FieldVisitController extends Controller
{
    /**
     * Get all active visit purposes.
     */
    public function purposes()
    {
        $purposes = VisitPurpose::all();
        return response()->json(['purposes' => $purposes]);
    }

    /**
     * Start a field visit.
     */
    public function start(Request $request)
    {
        $request->validate([
            'party_type' => 'required|in:retailer,distributor,other',
            'party_id' => 'nullable|integer',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
        ]);

        $visit = FieldVisit::create([
            'user_id' => $request->user()->id,
            'party_type' => $request->party_type,
            'party_id' => $request->party_id,
            'start_at' => Carbon::now(),
            'location_lat' => $request->location_lat,
            'location_lng' => $request->location_lng,
            'status' => 'ongoing',
        ]);

        return response()->json([
            'message' => 'Visit started successfully',
            'visit' => $visit
        ]);
    }

    /**
     * Stop a field visit.
     */
    public function stop(Request $request)
    {
        $request->validate([
            'visit_id' => 'required|exists:field_visits,id',
            'purpose_id' => 'required|exists:visit_purposes,id',
            'remarks' => 'required|string',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
        ]);

        $visit = FieldVisit::where('id', $request->visit_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$visit) {
            return response()->json(['message' => 'Visit not found or unauthorized'], 403);
        }

        if ($visit->status === 'completed') {
            return response()->json(['message' => 'Visit is already completed'], 400);
        }

        $visit->update([
            'end_at' => Carbon::now(),
            'purpose_id' => $request->purpose_id,
            'remarks' => $request->remarks,
            'status' => 'completed',
        ]);

        // We can update location if stopped at a slightly different location
        if ($request->has('location_lat') && $request->has('location_lng')) {
             $visit->location_lat = $request->location_lat;
             $visit->location_lng = $request->location_lng;
             $visit->save();
        }

        return response()->json([
            'message' => 'Visit stopped successfully',
            'visit' => $visit
        ]);
    }

    /**
     * Get visit history for a specific party.
     */
    public function history(Request $request)
    {
        $request->validate([
            'party_type' => 'required|in:retailer,distributor,other',
            'party_id' => 'nullable|integer',
        ]);

        $partyType = $request->party_type;
        $partyId = $request->party_id;

        // Fetch previous visits
        $visitsQuery = FieldVisit::with('purpose')
            ->where('party_type', $partyType)
            ->where('status', 'completed')
            ->orderBy('end_at', 'desc');

        if ($partyType !== 'other' && $partyId) {
             $visitsQuery->where('party_id', $partyId);
        }

        $visits = $visitsQuery->take(10)->get();

        $orders = [];
        // Fetch order history if party is retailer or distributor
        if ($partyType === 'retailer' && $partyId) {
            $orders = RetailerOrder::where('retailer_id', $partyId)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        } elseif ($partyType === 'distributor' && $partyId) {
             $orders = DistributorOrder::where('distributor_id', $partyId)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }

        return response()->json([
            'visits' => $visits,
            'orders' => $orders,
            // Pending follow-ups can be added here in the future
            'pending_follow_ups' => [] 
        ]);
    }
}
