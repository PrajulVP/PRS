<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserApiController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/user/player-id",
     *     summary="Update OneSignal Player ID",
     *     description="Update the OneSignal player ID for the authenticated user to enable push notifications.",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"player_id"},
     *             @OA\Property(property="player_id", type="string", example="50482058-4177-4527-a2f9-1b8a7715f171", description="OneSignal Player ID / Subscription ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Player ID updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Player ID updated successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="player_id", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updatePlayerId(Request $request)
    {
        $request->validate([
            'player_id' => 'required|string',
        ]);

        /** @var User $user */
        $user = Auth::user();
        
        $user->update([
            'player_id' => $request->player_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Player ID updated successfully.',
            'data' => [
                'player_id' => $user->player_id
            ]
        ]);
    }
}
