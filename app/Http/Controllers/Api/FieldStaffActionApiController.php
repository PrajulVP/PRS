<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\LocationLog;
use App\Models\FieldVisit;
use App\Models\VisitPurpose;
use App\Models\RetailerOrder;
use App\Models\DistributorOrder;
use App\Models\VisitLog;
use App\Models\Retailer;
use App\Models\Distributor;
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
     *             @OA\Property(property="message", type="string", example="The user has been punched out."),
     *             @OA\Property(property="admin_approved", type="boolean", description="Whether the admin has granted permission for an additional punch-in today"),
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
        $message = 'The user has been punched out.';
        if ($lastPunch && $lastPunch->type === 'punch_in') {
            $status = 'punched_in';
            $message = 'The user is currently punched in.';
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
            'admin_approved' => (bool) $user->clock_in_permission,
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
     * @OA\Get(
     *     path="/api/field-visits/parties",
     *     summary="Get list of parties based on party_type",
     *     tags={"Field Staff"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="party_type",
     *         in="query",
     *         required=true,
     *         description="Type of party (retailer or distributor)",
     *         @OA\Schema(type="string", enum={"retailer", "distributor"})
     *     ),
     *     @OA\Response(response=200, description="List of parties")
     * )
     */
    public function parties(Request $request)
    {
        $type = $request->query('party_type');
        
        if ($type === 'retailer') {
            $parties = \App\Models\Retailer::select('id', 'shop_name as name', 'owner_name')->get();
        } elseif ($type === 'distributor') {
            $parties = \App\Models\Distributor::select('id', 'name')->get();
        } else {
            $parties = [];
        }
        
        return response()->json(['parties' => $parties]);
    }

/**
     * @OA\Get(
     *     path="/api/field-visits/purposes",
     *     summary="Get all active visit purposes",
     *     tags={"Field Staff"},
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
     *     tags={"Field Staff"},
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
                    $distanceInMeters = round($distance * 1000, 2);
                    return response()->json([
                        'error' => "You are {$distanceInMeters} meters away from the location. Please be inside {$radiusMeters} m.",
                        'current_distance' => "{$distanceInMeters} meters"
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
     *     tags={"Field Staff"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"purpose_id", "remarks"},
     *             @OA\Property(property="purpose_id", type="integer"),
     *             @OA\Property(property="remarks", type="string"),
     *             @OA\Property(property="location_lat", type="number", format="float"),
     *             @OA\Property(property="location_lng", type="number", format="float")
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

        // Auto-detect the active visit
        $visit = FieldVisit::where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->latest()
            ->first();

        if (!$visit) {
            return response()->json(['message' => 'No active visit found to stop or unauthorized'], 404);
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

    /**
     * @OA\Get(
     *     path="/api/field-visits/status",
     *     summary="Get current field visit status",
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
     *         description="Current visit status retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="has_active_visit", type="boolean"),
     *             @OA\Property(property="active_visit", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function status(Request $request)
    {
        $user = auth('api')->user();
        
        // Device Binding Security
        $deviceId = $request->header('X-Device-ID');
        if ($user->device_uuid && $user->device_uuid !== $deviceId) {
            return response()->json(['error' => 'Device mismatch.'], 403);
        }

        $activeVisit = FieldVisit::where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->latest()
            ->first();

        return response()->json([
            'has_active_visit' => $activeVisit ? true : false,
            'active_visit' => $activeVisit
        ]);
    }

/**
     * @OA\Get(
     *     path="/api/field-visits/history",
     *     summary="Get visit history for a specific party",
     *     tags={"Field Staff"},
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



