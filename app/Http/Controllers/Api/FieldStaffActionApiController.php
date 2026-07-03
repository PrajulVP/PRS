<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\LocationLog;
use App\Models\VisitLog;
use App\Models\Expense;
use App\Models\LeaveRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\User;

class FieldStaffActionApiController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/field-staff/punch",
     *     summary="Punch-in or Punch-out with GPS validation",
     *     tags={"Field Staff"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", enum={"punch_in", "punch_out"}),
     *             @OA\Property(property="latitude", type="number"),
     *             @OA\Property(property="longitude", type="number"),
     *             @OA\Property(property="is_mock", type="boolean", description="Detected fake GPS usage")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="X-Device-ID",
     *         in="header",
     *         required=true,
     *         description="Unique Device identifier for binding",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Punch logged successfully")
     * )
     */
    public function punch(Request $request)
    {
        $request->validate([
            'type' => 'required|in:punch_in,punch_out',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'is_mock' => 'boolean',
        ]);

        $user = auth('api')->user();

        // Extra security: Verify Device ID if bound
        $deviceId = $request->header('X-Device-ID');
        if ($user->device_uuid && $user->device_uuid !== $deviceId) {
            return response()->json(['error' => 'Device mismatch. Use registered device.'], 403);
        }

        // Prevent duplicate punch per day
        $lastLog = AttendanceLog::where('user_id', $user->id)
            ->where('type', $request->type)
            ->whereDate('timestamp', Carbon::today())
            ->first();

        if ($lastLog) {
            return response()->json([
                'message' => 'You have already ' . str_replace('_', ' ', $request->type) . 'for today.',
                'already_punched' => true,
                'log' => $lastLog
            ], 200);
        }

        $log = AttendanceLog::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'device_id' => $deviceId,
            'is_mock_location' => $request->is_mock ?? false,
            'timestamp' => now(),
        ]);

        // Broadcast real-time update for Managers
        try {
            broadcast(new \App\Events\AttendanceLogged([
                'user_id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar_url,
                'type' => $request->type,
                'status' => $request->type === 'punch_in' ? 'online' : 'offline',
                'timestamp' => $log->timestamp->toDateTimeString(),
                'latitude' => $log->latitude,
                'longitude' => $log->longitude,
            ]))->toOthers();
        } catch (\Exception $e) {
            // Silently fail if broadcasting is not configured or fails
            \Log::error('Broadcasting failed: ' . $e->getMessage());
        }

        $message = ucfirst(str_replace('_', ' ', $request->type)) . ' successful.';
        if ($request->is_mock) {
            $message .= ' (Flagged for potential mock location)';
        }

        return response()->json([
            'message' => $message,
            'log' => $log
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/field-staff/punch",
     *     summary="Get current punch status of the field staff",
     *     tags={"Field Staff"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="X-Device-ID",
     *         in="header",
     *         required=true,
     *         description="Unique Device identifier for binding",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Last punch status retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"punched_in", "punched_out"}),
     *             @OA\Property(property="last_log", type="object")
     *         )
     *     )
     * )
     */
    public function getPunchStatus(Request $request)
    {
        $user = auth('api')->user();
        
        // Device Binding Security
        $deviceId = $request->header('X-Device-ID');
        if ($user->device_uuid && $user->device_uuid !== $deviceId) {
            return response()->json(['error' => 'Device mismatch.'], 403);
        }

        $lastPunch = AttendanceLog::where('user_id', $user->id)
            ->orderBy('timestamp', 'desc')
            ->first();

        return response()->json([
            'status' => $lastPunch ? ($lastPunch->type === 'punch_in' ? 'punched_in' : 'punched_out') : 'punched_out',
            'last_log' => $lastPunch
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/field-staff/ping",
     *     summary="Log continuous GPS location ping",
     *     tags={"Field Staff"},
     *     description="Receives field staff location pings. This endpoint also triggers a WebSocket broadcast on the `tracking` channel with the `location.updated` event.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="latitude", type="number"),
     *                 @OA\Property(property="longitude", type="number"),
     *                 @OA\Property(property="is_mock", type="boolean"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", description="Optional. Original time of ping.")
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="X-Device-ID",
     *         in="header",
     *         required=true,
     *         description="Unique Device identifier for binding",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Location pings saved and broadcasted")
     * )
     */
    public function pingLocation(Request $request)
    {
        $data = $request->all();
        // Support both single object (legacy) and array of objects
        if (is_array($data) && !isset($data[0])) {
            $data = [$data];
        }

        $validator = \Illuminate\Support\Facades\Validator::make($data, [
            '*' => 'required|array',
            '*.latitude' => 'required|numeric',
            '*.longitude' => 'required|numeric',
            '*.is_mock' => 'boolean',
            '*.timestamp' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $locations = $validator->validated();

        $user = auth('api')->user();

        // Extra security: Verify Device ID if bound
        $deviceId = $request->header('X-Device-ID');
        if ($user->device_uuid && $user->device_uuid !== $deviceId) {
            return response()->json(['error' => 'Device mismatch.'], 403);
        }

        $logs = [];
        foreach ($locations as $loc) {
            $timestampStr = $loc['timestamp'] ?? null;
            $timestamp = now();
            if ($timestampStr && $timestampStr !== '0' && (int)$timestampStr !== 0 && !str_starts_with($timestampStr, '1970')) {
                try {
                    $parsed = \Carbon\Carbon::parse($timestampStr);
                    if ($parsed->year > 2000) {
                        $timestamp = $parsed;
                    }
                } catch (\Exception $e) {
                    $timestamp = now();
                }
            }
            $log = LocationLog::create([
                'user_id' => $user->id,
                'latitude' => $loc['latitude'],
                'longitude' => $loc['longitude'],
                'is_mock_location' => $loc['is_mock'] ?? false,
                'timestamp' => $timestamp,
            ]);
            $logs[] = $log;
        }

        // Broadcast the update for real-time tracking (broadcast the latest one)
        if (!empty($logs)) {
            $latest = end($logs);
            broadcast(new \App\Events\LocationUpdated(
                $user->id, 
                (float)$latest->latitude, 
                (float)$latest->longitude, 
                $latest->timestamp->toDateTimeString()
            ));
        }

        return response()->json([
            'message' => count($logs) . ' pings received.', 
            'logs' => $logs
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/field-staff/log-visit",
     *     summary="Log a visit to a customer",
     *     tags={"Field Staff"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="customer_category", type="string", enum={"Doctor", "Hospital", "Retailer", "Distributor/Wholesaler"}),
     *             @OA\Property(property="customer_name", type="string"),
     *             @OA\Property(property="latitude", type="number"),
     *             @OA\Property(property="longitude", type="number"),
     *             @OA\Property(property="notes", type="string"),
     *             @OA\Property(property="next_follow_up", type="string", format="date")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="X-Device-ID",
     *         in="header",
     *         required=true,
     *         description="Unique Device identifier for binding",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Visit logged")
     * )
     */
    public function logVisit(Request $request)
    {
        $request->validate([
            'customer_category' => 'required|in:Doctor,Hospital,Retailer,Distributor/Wholesaler',
            'customer_name' => 'required|string',
            'customer_id' => 'nullable|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'notes' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
            'photo' => 'nullable|image|max:5120', // 5MB
        ]);

        $user = auth('api')->user();

        // Extra security: Verify Device ID if bound
        $deviceId = $request->header('X-Device-ID');
        if ($user->device_uuid && $user->device_uuid !== $deviceId) {
            return response()->json(['error' => 'Device mismatch. Use registered device.'], 403);
        }
        
        // Geofencing Check
        $isFlagged = false;
        if ($request->customer_id && $request->customer_category === 'Retailer') {
            $retailer = \App\Models\Retailer::find($request->customer_id);
            if ($retailer && $retailer->latitude && $retailer->longitude) {
                $distance = $this->calculateDistance(
                    $request->latitude, $request->longitude,
                    $retailer->latitude, $retailer->longitude
                );
                
                // radius: 10 meters (0.01 KM)
                if ($distance > 0.01) { 
                    return response()->json([
                        'error' => 'Geofence violation. You must be within 10 meters of the customer location.',
                        'current_distance' => round($distance * 1000, 2) . ' meters'
                    ], 403);
                }
            }
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('visits/' . $user->id, 'public');
        }

        $visit = VisitLog::create([
            'user_id' => $user->id,
            'customer_category' => $request->customer_category,
            'customer_name' => $request->customer_name,
            'customer_id' => $request->customer_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'check_in_at' => now(), 
            'notes' => $request->notes,
            'next_follow_up_date' => $request->next_follow_up,
            'photo_path' => $photoPath,
            'is_flagged' => $isFlagged, // Flagged if outside geofence
        ]);

        $msg = 'Visit logged successfully.';
        if ($isFlagged) {
            $msg .= ' (Warning: Location is far from registered customer address)';
        }

        return response()->json([
            'message' => $msg,
            'visit' => $visit
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return ($miles * 1.609344); // Distance in KM
    }

    /**
     * @OA\Post(
     *     path="/api/field-staff/expenses",
     *     summary="Submit an expense claim (with Auto TA/DA calculation)",
     *     description="Submit an expense. If type is 'TA' or 'DA', the system automatically calculates the amount based on GPS logs for the expense_date and the configured HQ radius (15km).",
     *     tags={"Field Staff"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="X-Device-ID",
     *         in="header",
     *         required=true,
     *         description="Unique Device identifier for binding",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"type", "expense_date"},
     *                 @OA\Property(property="type", type="string", description="Expense type: TA, DA, Travel, food, misc, etc."),
     *                 @OA\Property(property="expense_date", type="string", format="date", example="2023-10-27"),
     *                 @OA\Property(property="amount", type="number", description="Optional if TA/DA (auto-calculated)"),
     *                 @OA\Property(property="distance_km", type="number", description="Optional (auto-calculated from GPS)"),
     *                 @OA\Property(property="is_outstation", type="boolean", description="Optional (auto-determined)"),
     *                 @OA\Property(property="bill", type="string", format="binary", description="Invoice/Bill image/pdf")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201, 
     *         description="Expense submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="expense", type="object"),
     *             @OA\Property(property="calculation_details", type="object",
     *                 @OA\Property(property="gps_distance", type="number"),
     *                 @OA\Property(property="is_outstation", type="boolean"),
     *                 @OA\Property(property="applied_rate", type="number")
     *             )
     *         )
     *     )
     * )
     */
    public function submitExpense(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
            'distance_km' => 'nullable|numeric|min:0',
            'is_outstation' => 'boolean',
            'expense_date' => 'required|date',
            'bill' => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
        ]);

        $user = auth('api')->user();

        // Extra security: Verify Device ID if bound
        $deviceId = $request->header('X-Device-ID');
        if ($user->device_uuid && $user->device_uuid !== $deviceId) {
            return response()->json(['error' => 'Device mismatch. Use registered device.'], 403);
        }
        $type = strtoupper($request->type);
        $amount = $request->amount ?? 0;
        $distance = $request->distance_km ?? 0;
        $isOutstation = $request->is_outstation ?? false;

        // Auto-calculate for TA (Travel Allowance) or DA (Daily Allowance)
        if ($type === 'TA' || $type === 'DA' || $type === 'TRAVEL' || $type === 'DAILY') {
            $gpsDistance = LocationLog::calculateDailyDistance($user->id, $request->expense_date);
            $distance = $gpsDistance;
            
            $hqRadius = (float)Setting::getValue('hq_radius_km', 15);
            $isOutstation = ($gpsDistance > $hqRadius);
            
            if ($type === 'TA' || $type === 'TRAVEL') {
                $taRate = (float)Setting::getValue('ta_rate_per_km', 2.6);
                $amount = $isOutstation ? ($gpsDistance * $taRate) : 0;
            } else {
                $daRate = $isOutstation 
                    ? (float)Setting::getValue('da_outstation_rate', 500) 
                    : (float)Setting::getValue('da_hq_rate', 250);
                $amount = $daRate;
            }
        }

        $billPath = null;
        if ($request->hasFile('bill')) {
            $billPath = $request->file('bill')->store('expenses/' . $user->id, 'public');
        }

        $expense = Expense::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'amount' => round($amount, 2),
            'distance_km' => round($distance, 2),
            'bill_path' => $billPath,
            'is_outstation' => $isOutstation,
            'status' => 'pending',
            'expense_date' => $request->expense_date,
        ]);

        $msg = 'Expense submitted for approval.';
        if ($type === 'TA' || $type === 'DA') {
            $msg = ucfirst($type) . ' auto-calculated based on GPS logs (' . round($distance, 2) . ' km).';
        }

        return response()->json([
            'message' => $msg,
            'expense' => $expense,
            'calculation_details' => [
                'gps_distance' => round($distance, 2),
                'is_outstation' => $isOutstation,
                'applied_rate' => $amount
            ]
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/field-staff/leaves",
     *     summary="Request a leave or permission",
     *     tags={"Field Staff"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="X-Device-ID",
     *         in="header",
     *         required=true,
     *         description="Unique Device identifier for binding",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=201, description="Leave requested")
     * )
     */
    public function requestLeave(Request $request)
    {
        $request->validate([
            'type' => 'required|in:Sick Leave,Casual Leave,Permission',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'required|string',
        ]);

        $user = auth('api')->user();

        // Extra security: Verify Device ID if bound
        $deviceId = $request->header('X-Device-ID');
        if ($user->device_uuid && $user->device_uuid !== $deviceId) {
            return response()->json(['error' => 'Device mismatch. Use registered device.'], 403);
        }

        $leave = LeaveRequest::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Leave request submitted.',
            'leave' => $leave
        ], 201);
    }
}
