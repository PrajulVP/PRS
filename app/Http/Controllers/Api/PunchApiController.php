<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\LocationLog;
use Carbon\Carbon;

class PunchApiController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/punch",
     *     summary="Punch-in or Punch-out with GPS validation",
     *     tags={"Punch & Location Tracking"},
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
     *         required=false,
     *         description="Unique Device identifier for binding",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200, 
     *         description="Punch logged successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Punch rejected"
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

        // Device ID binding verification (if device_uuid is configured on user)
        $deviceId = $request->header('X-Device-ID');
        if ($user->device_uuid && $user->device_uuid !== $deviceId) {
            return response()->json(['error' => 'Device mismatch. Use registered device.'], 403);
        }

        // Attendance Geofence for Field Staff (if configured)
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
            
            if ($hasPunchToday && $user->clock_in_permission) {
                $user->clock_in_permission = false;
                $user->save();
            }
            
        } elseif ($request->type === 'punch_out') {
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

        // Broadcast real-time update
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
     *     path="/api/punch",
     *     summary="Get current punch status of the user",
     *     tags={"Punch & Location Tracking"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="X-Device-ID",
     *         in="header",
     *         required=false,
     *         description="Unique Device identifier for binding",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Last punch status retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"punched_in", "punched_out"}),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="admin_approved", type="boolean"),
     *             @OA\Property(property="last_log", type="object")
     *         )
     *     )
     * )
     */
    public function getPunchStatus(Request $request)
    {
        $user = auth('api')->user();
        
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
     *     path="/api/ping",
     *     summary="Log continuous GPS location ping",
     *     tags={"Punch & Location Tracking"},
     *     description="Receives user location pings and updates live tracking map.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="latitude", type="number"),
     *                 @OA\Property(property="longitude", type="number"),
     *                 @OA\Property(property="is_mock", type="boolean"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="X-Device-ID",
     *         in="header",
     *         required=false,
     *         description="Unique Device identifier for binding",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Location pings saved")
     * )
     */
    public function pingLocation(Request $request)
    {
        $data = $request->all();
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

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return ($miles * 1.609344);
    }
}
