<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\Retailer;
use App\Models\FieldStaff;
use Illuminate\Support\Facades\Auth;

class RatingApiController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/retailer/rate-staff",
     *     summary="Retailer: Submit a rating for the assigned Field Staff",
     *     tags={"Retailer Feedback"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating"},
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
     *             @OA\Property(property="category", type="string", example="Service"),
     *             @OA\Property(property="comments", type="string", example="Excellent service and very responsive.")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Rating submitted")
     * )
     */
    public function rateStaff(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'category' => 'nullable|string|max:255',
            'comments' => 'nullable|string',
        ]);

        $user = Auth::user();
        if (!$user->hasRole('retailer')) {
            return response()->json(['error' => 'Only retailers can rate staff.'], 403);
        }

        $retailer = $user->retailer;
        if (!$retailer || !$retailer->field_staff_id) {
            return response()->json(['error' => 'No field staff assigned to this retailer.'], 400);
        }

        $rating = Rating::updateOrCreate(
            [
                'retailer_id' => $retailer->id,
                'field_staff_id' => $retailer->field_staff_id,
                'category' => $request->category ?? 'General',
            ],
            [
                'rating' => $request->rating,
                'comments' => $request->comments,
            ]
        );

        return response()->json([
            'message' => 'Thank you for your feedback!',
            'rating' => $rating
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/retailer/my-ratings",
     *     summary="Retailer: Get history of ratings submitted by the retailer",
     *     tags={"Retailer Feedback"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of ratings")
     * )
     */
    public function getMyRatings()
    {
        $user = Auth::user();
        if (!$user->hasRole('retailer')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $ratings = Rating::with('fieldStaff.user')
            ->where('retailer_id', $user->retailer->id)
            ->latest()
            ->get()
            ->map(function($r) {
                return [
                    'id' => $r->id,
                    'staff_name' => $r->fieldStaff->user->name ?? 'N/A',
                    'rating' => $r->rating,
                    'category' => $r->category,
                    'comments' => $r->comments,
                    'date' => $r->created_at->format('Y-m-d')
                ];
            });

        return response()->json($ratings);
    }

    /**
     * @OA\Post(
     *     path="/api/distributor/rate-staff",
     *     summary="Distributor: Submit a rating for a Field Staff member",
     *     tags={"Distributor Feedback"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"field_staff_id", "rating"},
     *             @OA\Property(property="field_staff_id", type="integer", example=1),
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
     *             @OA\Property(property="category", type="string", example="Service"),
     *             @OA\Property(property="comments", type="string", example="Very professional.")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Rating submitted")
     * )
     */
    public function distributorRateStaff(Request $request)
    {
        $request->validate([
            'field_staff_id' => 'required|exists:fieldstaffs,id',
            'rating' => 'required|integer|min:1|max:5',
            'category' => 'nullable|string|max:255',
            'comments' => 'nullable|string',
        ]);

        $user = Auth::user();
        if (!$user->hasRole('distributor')) {
            return response()->json(['error' => 'Only distributors can rate staff.'], 403);
        }

        $distributor = $user->distributor;
        if (!$distributor) {
            return response()->json(['error' => 'Distributor profile not found.'], 403);
        }

        // Check relationship
        $hasRelationship = \Illuminate\Support\Facades\DB::table('retailer_orders')
            ->where('distributor_id', $distributor->id)
            ->where('fieldstaff_id', $request->field_staff_id)
            ->exists();

        if (!$hasRelationship) {
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
                'category' => $request->category ?? 'General',
            ],
            [
                'rating' => $request->rating,
                'comments' => $request->comments,
            ]
        );

        return response()->json([
            'message' => 'Rating submitted successfully!',
            'rating' => $rating
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/distributor/rateable-staff",
     *     summary="Distributor: List field staff members in network available for rating",
     *     tags={"Distributor Feedback"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of field staff")
     * )
     */
    public function getDistributorRatingStaffList()
    {
        $user = Auth::user();
        if (!$user->hasRole('distributor')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $distributor = $user->distributor;
        if (!$distributor) {
            return response()->json(['error' => 'Distributor profile not found.'], 403);
        }

        $fieldStaff = FieldStaff::whereHas('retailerOrders', function ($query) use ($distributor) {
            $query->where('distributor_id', $distributor->id);
        })
        ->orWhereHas('retailers', function ($query) use ($distributor) {
            $query->where('distributor_id', $distributor->id);
        })
        ->with(['user', 'ratings' => function ($query) use ($distributor) {
            $query->where('distributor_id', $distributor->id);
        }])
        ->get()
        ->map(function($staff) use ($distributor) {
            $rating = $staff->ratings->first();
            return [
                'id' => $staff->id,
                'name' => $staff->user->name,
                'avatar' => $staff->user->avatar_url,
                'contact' => $staff->user->contact_no,
                'current_rating' => $rating ? $rating->rating : 0,
                'current_comments' => $rating ? $rating->comments : null,
                'is_rated' => $rating ? true : false
            ];
        });

        return response()->json($fieldStaff);
    }
}
