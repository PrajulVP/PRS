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
use App\Models\OfflineLog;
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
     *     @OA\Response(
     *         response=200, 
     *         description="Punch logged successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="already_punched", type="boolean", nullable=true),
     *             @OA\Property(property="log", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Punch rejected (e.g., duplicate punch without permission, invalid device)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You have already punched today. Contact admin for permission to punch in again."),
     *             @OA\Property(property="already_punched", type="boolean", example=true),
     *             @OA\Property(property="log", type="object", nullable=true)
     *         )
     *     )
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

        // Attendance Geofence
        $fieldStaff = $user->fieldStaff;
        if ($fieldStaff && $fieldStaff->latitude && $fieldStaff->longitude) {
            $radiusMeters = (float) \App\Models\Setting::getValue('geofence_radius', 20);
            $radiusKm = $radiusMeters / 1000;
            
            $distance = $this->calculateDistance(
                $request->latitude, $request->longitude,
                $fieldStaff->latitude, $fieldStaff->longitude
            );

            if ($distance > $radiusKm) {
                return response()->json([
                    'error' => "Geofence violation. You must be within {$radiusMeters} meters of your registered base location.",
                    'current_distance' => round($distance * 1000, 2) . ' meters'
                ], 403);
            }
        }

        $lastPunch = AttendanceLog::where('user_id', $user->id)
            ->orderBy('timestamp', 'desc')
            ->first();

        if ($request->type === 'punch_in') {
            $hasPunchToday = AttendanceLog::where('user_id', $user->id)
                ->whereDate('timestamp', Carbon::today())
                ->exists();

            if ($hasPunchToday && !$user->clock_in_permission) {
                return response()->json([
                    'message' => 'You have already punched today. Contact admin for permission to punch in again.',
                    'already_punched' => true,
                    'log' => $lastPunch
                ], 403);
            }
            
            // If they are allowed to punch in today because of permission, check if their current status is punched_out
            $currentStatus = 'punched_out';
            if ($lastPunch && $lastPunch->type === 'punch_in' && Carbon::parse($lastPunch->timestamp)->isToday()) {
                $currentStatus = 'punched_in';
            }
            
            if ($currentStatus === 'punched_in') {
                return response()->json([
                    'message' => 'You are already punched in.',
                    'already_punched' => true,
                    'log' => $lastPunch
                ], 200);
            }
            
            // Consume the permission if used
            if ($hasPunchToday && $user->clock_in_permission) {
                $user->clock_in_permission = false;
                $user->save();
            }
            
        } elseif ($request->type === 'punch_out') {
            // Can punch out if last punch was punch_in (regardless of what day it was)
            if (!$lastPunch || $lastPunch->type === 'punch_out') {
                return response()->json([
                    'message' => 'You must be punched in to punch out.',
                    'already_punched' => true,
                    'log' => $lastPunch
                ], 403);
            }
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

        $status = 'punched_out';
        if ($lastPunch && $lastPunch->type === 'punch_in') {
            $status = 'punched_in';
        }

        return response()->json([
            'status' => $status,
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
        $radiusMeters = (float) \App\Models\Setting::getValue('geofence_radius', 20);
        $radiusKm = $radiusMeters / 1000;

        if ($request->customer_id) {
            $party = null;
            if ($request->customer_category === 'Retailer') {
                $party = \App\Models\Retailer::find($request->customer_id);
            } elseif ($request->customer_category === 'Distributor/Wholesaler') {
                $party = \App\Models\Distributor::find($request->customer_id);
            } // Expand to other models if they exist in the future

            if ($party) {
                if ($party->latitude && $party->longitude) {
                    $distance = $this->calculateDistance(
                        $request->latitude, $request->longitude,
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
                    $party->latitude = $request->latitude;
                    $party->longitude = $request->longitude;
                    $party->save();
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

    /**
     * @OA\Post(
     *     path="/api/field-staff/checkout-visit",
     *     summary="Check out of a logged visit",
     *     tags={"Field Staff"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="visit_id", type="integer")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="X-Device-ID",
     *         in="header",
     *         required=true,
     *         description="Unique Device identifier for binding",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Visit checked out successfully")
     * )
     */
    public function checkoutVisit(Request $request)
    {
        $request->validate([
            'visit_id' => 'required|integer|exists:visit_logs,id',
        ]);

        $user = auth('api')->user();

        // Extra security: Verify Device ID if bound
        $deviceId = $request->header('X-Device-ID');
        if ($user->device_uuid && $user->device_uuid !== $deviceId) {
            return response()->json(['error' => 'Device mismatch. Use registered device.'], 403);
        }

        $visit = VisitLog::where('id', $request->visit_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$visit) {
            return response()->json(['error' => 'Visit not found or unauthorized.'], 404);
        }

        if ($visit->check_out_at) {
            return response()->json(['error' => 'Visit already checked out.'], 400);
        }

        $visit->check_out_at = now();
        $visit->save();

        return response()->json([
            'message' => 'Checked out successfully.',
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
     *     path="/api/field-staff/sync-offline-logs",
     *     summary="Sync offline logs when internet is restored",
     *     tags={"Field Staff"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="logs", type="array", @OA\Items(
     *                 @OA\Property(property="from_time", type="string", format="date-time"),
     *                 @OA\Property(property="to_time", type="string", format="date-time"),
     *                 @OA\Property(property="latitude", type="number", nullable=true),
     *                 @OA\Property(property="longitude", type="number", nullable=true),
     *                 @OA\Property(property="reason", type="string", example="Mobile Data Off")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200, 
     *         description="Logs synced successfully"
     *     )
     * )
     */
    public function syncOfflineLogs(Request $request)
    {
        $request->validate([
            'logs' => 'required|array',
            'logs.*.from_time' => 'required|date',
            'logs.*.to_time' => 'required|date',
            'logs.*.latitude' => 'nullable|numeric',
            'logs.*.longitude' => 'nullable|numeric',
            'logs.*.reason' => 'nullable|string',
        ]);

        $user = auth('api')->user();
        $insertedCount = 0;

        foreach ($request->logs as $logData) {
            OfflineLog::create([
                'user_id' => $user->id,
                'from_time' => Carbon::parse($logData['from_time']),
                'to_time' => Carbon::parse($logData['to_time']),
                'latitude' => $logData['latitude'] ?? null,
                'longitude' => $logData['longitude'] ?? null,
                'reason' => $logData['reason'] ?? null,
            ]);
            $insertedCount++;
        }

        return response()->json([
            'message' => 'Offline logs synced successfully',
            'synced_count' => $insertedCount,
        ]);
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
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'duration_type' => 'nullable|in:full_day,first_half,second_half',
        ]);

        $user = auth('api')->user();

        // Extra security: Verify Device ID if bound
        $deviceId = $request->header('X-Device-ID');
        if ($user->device_uuid && $user->device_uuid !== $deviceId) {
            return response()->json(['error' => 'Device mismatch. Use registered device.'], 403);
        }
        
        $durationType = $request->duration_type ?? 'full_day';
        $start = \Carbon\Carbon::parse($request->start_date);
        $end = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : $start->copy();
        
        if ($durationType !== 'full_day' && !$start->isSameDay($end)) {
            return response()->json(['error' => 'Half day leaves must be on a single day.'], 400);
        }

        $type = $request->type;
        $leaveType = \App\Models\LeaveType::where('name', $type)->first();
        
        if ($leaveType) {
            $days = ($durationType === 'full_day') ? ($start->diffInDays($end) + 1) : 0.5;
            
            $userBalance = \App\Models\UserLeaveBalance::where('user_id', $user->id)
                ->where('leave_type_id', $leaveType->id)
                ->first();
                
            $currentBalance = $userBalance ? $userBalance->balance : 0;
            
            if ($currentBalance < $days) {
                return response()->json([
                    'error' => "Insufficient $type balance. Requested: $days days. Available: $currentBalance days.",
                ], 400);
            }
        } elseif ($type !== 'Permission') {
            return response()->json([
                'error' => "Invalid leave type.",
            ], 400);
        }

        $leave = LeaveRequest::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'duration_type' => $durationType,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Leave request submitted.',
            'leave' => $leave
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/field-staff/leaves",
     *     summary="Get leave history and balances",
     *     tags={"Field Staff"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Leave history retrieved")
     * )
     */
    public function getLeaves(Request $request)
    {
        $user = auth('api')->user();
        $leaves = LeaveRequest::where('user_id', $user->id)->latest()->get();
        
        $balances = \App\Models\UserLeaveBalance::with('leaveType')->where('user_id', $user->id)->get()->map(function($b) {
            return [
                'type' => $b->leaveType->name,
                'balance' => $b->balance
            ];
        });
        
        return response()->json([
            'balances' => $balances,
            'leaves' => $leaves
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/field-staff/leave-types",
     *     summary="Get available leave types",
     *     tags={"Field Staff"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Leave types retrieved")
     * )
     */
    public function getLeaveTypes(Request $request)
    {
        $types = \App\Models\LeaveType::all()->map(function($t) {
            return $t->name;
        });
        // Always include 'Permission' as a hardcoded un-tracked type if they want
        $types->push('Permission');
        return response()->json(['leave_types' => $types]);
    }

    /**
     * @OA\Post(
     *     path="/api/field-staff/visits/report-location",
     *     summary="Report location when explicitly turned off or logged by system",
     *     tags={"Field Staff"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="latitude", type="number"),
     *             @OA\Property(property="longitude", type="number"),
     *             @OA\Property(property="remarks", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Location reported")
     * )
     */
    public function reportLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'remarks' => 'nullable|string',
        ]);

        $user = auth('api')->user();

        $log = LocationLog::create([
            'user_id' => $user->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'remarks' => $request->remarks,
            'is_mock_location' => false,
            'timestamp' => now(),
        ]);

        return response()->json([
            'latitude' => (float)$log->latitude,
            'longitude' => (float)$log->longitude,
            'remarks' => $log->remarks,
        ]);
    }
}
