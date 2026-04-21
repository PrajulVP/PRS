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
     *     summary="Submit a rating for the assigned Field Staff",
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

        $rating = Rating::create([
            'retailer_id' => $retailer->id,
            'field_staff_id' => $retailer->field_staff_id,
            'rating' => $request->rating,
            'category' => $request->category ?? 'General',
            'comments' => $request->comments,
        ]);

        return response()->json([
            'message' => 'Thank you for your feedback!',
            'rating' => $rating
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/retailer/my-ratings",
     *     summary="Get history of ratings submitted by the retailer",
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
}
