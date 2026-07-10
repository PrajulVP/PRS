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
use Carbon\Carbon;
use App\Models\User;

class ManagerActionApiController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/manager/punch",
     *     summary="Punch-in or Punch-out with GPS validation",
     *     tags={"Sales Manager"},
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
            'device_id' => 'manager_web',
            'is_mock_location' => $request->is_mock ?? false,
            'timestamp' => now(),
        ]);

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
     *     path="/api/manager/punch",
     *     summary="Get current punch status of the manager",
     *     tags={"Sales Manager"},
     *     security={{"bearerAuth":{}}},
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
     *     path="/api/manager/ping",
     *     summary="Log continuous GPS location ping",
     *     tags={"Sales Manager"},
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

        return response()->json([
            'message' => count($logs) . ' pings received.', 
            'logs' => $logs
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/manager/log-visit",
     *     summary="Log a visit",
     *     tags={"Sales Manager"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="customer_category", type="string"),
     *             @OA\Property(property="customer_name", type="string"),
     *             @OA\Property(property="latitude", type="number"),
     *             @OA\Property(property="longitude", type="number"),
     *             @OA\Property(property="notes", type="string"),
     *             @OA\Property(property="next_follow_up", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Visit logged")
     * )
     */
    public function logVisit(Request $request)
    {
        $request->validate([
            'customer_category' => 'required|in:Doctor,Hospital,Retailer,Distributor/Wholesaler,FieldStaff',
            'customer_name' => 'required|string',
            'customer_id' => 'nullable|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'notes' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
            'photo' => 'nullable|image|max:5120',
        ]);

        $user = auth('api')->user();
        
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
            'is_flagged' => false,
        ]);

        return response()->json([
            'message' => 'Visit logged successfully.',
            'visit' => $visit
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/manager/expenses",
     *     summary="Submit an expense claim",
     *     tags={"Sales Manager"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=201, description="Expense submitted")
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

        $type = strtoupper($request->type);
        $amount = $request->amount ?? 0;
        $distance = $request->distance_km ?? 0;
        $isOutstation = $request->is_outstation ?? false;

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
     *     path="/api/manager/leaves",
     *     summary="Request a leave or permission",
     *     tags={"Sales Manager"},
     *     security={{"bearerAuth":{}}},
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
