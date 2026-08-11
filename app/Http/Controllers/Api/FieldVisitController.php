<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FieldVisit;
use App\Models\VisitPurpose;
use App\Models\RetailerOrder;
use App\Models\DistributorOrder;
use App\Models\VisitLog;
use App\Models\Retailer;
use App\Models\Distributor;
use Carbon\Carbon;

class FieldVisitController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/field-visits/purposes",
     *     summary="Get all active visit purposes",
     *     tags={"Field Visits"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of purposes")
     * )
     */
    public function purposes()
    {
        $purposes = VisitPurpose::all();
        return response()->json(['purposes' => $purposes]);
    }

    /**
     * @OA\Post(
     *     path="/api/field-visits/start",
     *     summary="Start a field visit",
     *     tags={"Field Visits"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"party_type"},
     *             @OA\Property(property="party_type", type="string", enum={"retailer", "distributor", "other"}),
     *             @OA\Property(property="party_id", type="integer", description="ID of the party (nullable for 'other')"),
     *             @OA\Property(property="location_lat", type="number"),
     *             @OA\Property(property="location_lng", type="number")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="X-Device-ID",
     *         in="header",
     *         required=true,
     *         description="Unique Device identifier for binding",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Visit started successfully")
     * )
     */
    public function start(Request $request)
    {
        $request->validate([
            'party_type' => 'required|in:retailer,distributor,other',
            'party_id' => 'nullable|integer',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
        ]);

        $user = $request->user();

        // Extra security: Verify Device ID if bound
        $deviceId = $request->header('X-Device-ID');
        if ($user->device_uuid && $user->device_uuid !== $deviceId) {
            return response()->json(['error' => 'Device mismatch. Use registered device.'], 403);
        }

        // Fetch Party (for Geofencing and VisitLog)
        $party = null;
        $customerName = 'Unknown';
        if ($request->party_type === 'retailer') {
            $party = Retailer::find($request->party_id);
            $customerName = $party ? $party->shop_name : 'Unknown Retailer';
        } elseif ($request->party_type === 'distributor') {
            $party = Distributor::find($request->party_id);
            $customerName = $party ? $party->user->name : 'Unknown Distributor';
        }

        // Geofencing Check
        $isFlagged = false;
        $radiusMeters = (float) \App\Models\Setting::getValue('geofence_radius', 20);
        $radiusKm = $radiusMeters / 1000;

        if ($party && $request->location_lat && $request->location_lng) {
            if ($party->latitude && $party->longitude) {
                $distance = $this->calculateDistance(
                    $request->location_lat, $request->location_lng,
                    $party->latitude, $party->longitude
                );
                
                if ($distance > $radiusKm) { 
                    return response()->json([
                        'error' => "Geofence violation. You must be within {$radiusMeters} meters of the customer location.",
                        'current_distance' => round($distance * 1000, 2) . ' meters'
                    ], 403);
                }
            } else {
                // Auto-learn location since it's missing
                $party->latitude = $request->location_lat;
                $party->longitude = $request->location_lng;
                $party->save();
            }
        }

        $visit = FieldVisit::create([
            'user_id' => $user->id,
            'party_type' => $request->party_type,
            'party_id' => $request->party_id,
            'start_at' => Carbon::now(),
            'location_lat' => $request->location_lat,
            'location_lng' => $request->location_lng,
            'status' => 'ongoing',
        ]);

        // Create VisitLog for Web Dashboard Sync
        $visitLogCategory = $request->party_type === 'other' ? 'Other' : ucfirst($request->party_type);
        $visitLog = VisitLog::create([
            'user_id' => $user->id,
            'customer_category' => $visitLogCategory,
            'customer_name' => $customerName,
            'customer_id' => $request->party_id,
            'latitude' => $request->location_lat,
            'longitude' => $request->location_lng,
            'check_in_at' => now(), 
            'is_flagged' => $isFlagged, 
        ]);

        return response()->json([
            'message' => 'Visit started successfully',
            'visit' => $visit,
            'visit_log_id' => $visitLog->id
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/field-visits/stop",
     *     summary="Stop a field visit",
     *     tags={"Field Visits"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"visit_id", "purpose_id", "remarks"},
     *             @OA\Property(property="visit_id", type="integer"),
     *             @OA\Property(property="purpose_id", type="integer"),
     *             @OA\Property(property="remarks", type="string"),
     *             @OA\Property(property="location_lat", type="number"),
     *             @OA\Property(property="location_lng", type="number")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="X-Device-ID",
     *         in="header",
     *         required=true,
     *         description="Unique Device identifier for binding",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Visit stopped successfully")
     * )
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

        $user = $request->user();

        // Extra security: Verify Device ID if bound
        $deviceId = $request->header('X-Device-ID');
        if ($user->device_uuid && $user->device_uuid !== $deviceId) {
            return response()->json(['error' => 'Device mismatch. Use registered device.'], 403);
        }

        $visit = FieldVisit::where('id', $request->visit_id)
            ->where('user_id', $user->id)
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

        // Sync with VisitLog
        // We find the VisitLog created around the same time for this user and party
        $visitLogCategory = $visit->party_type === 'other' ? 'Other' : ucfirst($visit->party_type);
        $visitLog = VisitLog::where('user_id', $user->id)
            ->where('customer_category', $visitLogCategory)
            ->where('customer_id', $visit->party_id)
            ->whereNull('check_out_at')
            ->orderBy('id', 'desc')
            ->first();

        if ($visitLog) {
            $visitLog->check_out_at = now();
            $visitLog->notes = $request->remarks;
            $visitLog->save();
        }

        return response()->json([
            'message' => 'Visit stopped successfully',
            'visit' => $visit
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));
        return $earthRadius * $c;
    }

    /**
     * @OA\Get(
     *     path="/api/field-visits/history",
     *     summary="Get visit history for a specific party",
     *     tags={"Field Visits"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="party_type",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", enum={"retailer", "distributor", "other"})
     *     ),
     *     @OA\Parameter(
     *         name="party_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Visit history and orders")
     * )
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
