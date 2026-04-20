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

        $log = AttendanceLog::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'device_id' => $request->header('X-Device-ID'),
            'is_mock_location' => $request->is_mock ?? false,
            'timestamp' => now(),
        ]);

        return response()->json([
            'message' => ucfirst(str_replace('_', ' ', $request->type)) . ' successful.',
            'log' => $log
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/field-staff/ping",
     *     summary="Log continuous GPS location ping",
     *     tags={"Field Staff"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="latitude", type="number"),
     *             @OA\Property(property="longitude", type="number"),
     *             @OA\Property(property="is_mock", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Location ping saved")
     * )
     */
    public function pingLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'is_mock' => 'boolean',
        ]);

        $user = auth('api')->user();

        $log = LocationLog::create([
            'user_id' => $user->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_mock_location' => $request->is_mock ?? false,
            'timestamp' => now(),
        ]);

        return response()->json(['message' => 'Ping received.', 'id' => $log->id]);
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
     *     @OA\Response(response=200, description="Visit logged")
     * )
     */
    public function logVisit(Request $request)
    {
        $request->validate([
            'customer_category' => 'required|in:Doctor,Hospital,Retailer,Distributor/Wholesaler',
            'customer_name' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'notes' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
            'photo' => 'nullable|image|max:5120', // 5MB
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
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'check_in_at' => now(), // Assuming immediate log
            'notes' => $request->notes,
            'next_follow_up_date' => $request->next_follow_up,
            'photo_path' => $photoPath,
        ]);

        return response()->json([
            'message' => 'Visit logged successfully.',
            'visit' => $visit
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/field-staff/expenses",
     *     summary="Submit an expense claim",
     *     tags={"Field Staff"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=201, description="Expense submitted")
     * )
     */
    public function submitExpense(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'distance_km' => 'nullable|numeric|min:0',
            'is_outstation' => 'boolean',
            'expense_date' => 'required|date',
            'bill' => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
        ]);

        $user = auth('api')->user();
        $billPath = null;

        if ($request->hasFile('bill')) {
            $billPath = $request->file('bill')->store('expenses/' . $user->id, 'public');
        }

        $expense = Expense::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'amount' => $request->amount,
            'distance_km' => $request->distance_km,
            'bill_path' => $billPath,
            'is_outstation' => $request->is_outstation ?? false,
            'status' => 'pending',
            'expense_date' => $request->expense_date,
        ]);

        return response()->json([
            'message' => 'Expense submitted for approval.',
            'expense' => $expense
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/field-staff/leaves",
     *     summary="Request a leave or permission",
     *     tags={"Field Staff"},
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
