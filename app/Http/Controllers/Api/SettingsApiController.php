<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/settings/google-maps-api-key",
     *     summary="Get Google Maps API Key",
     *     tags={"Settings"},
     *     @OA\Response(
     *         response=200,
     *         description="Google Maps API Key",
     *         @OA\JsonContent(
     *             @OA\Property(property="google_maps_api_key", type="string")
     *         )
     *     )
     * )
     */
    public function getGoogleMapsApiKey(Request $request)
    {
        return response()->json([
            'google_maps_api_key' => config('services.google_maps.key')
        ]);
    }
}
