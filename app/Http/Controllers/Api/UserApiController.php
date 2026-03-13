<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserApiController extends Controller
{
    /**
     * Update the OneSignal player ID for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
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
