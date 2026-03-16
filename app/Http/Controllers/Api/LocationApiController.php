<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\District;
use App\Models\Area;

class LocationApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/locations/districts",
     *     summary="Get list of all districts",
     *     tags={"Locations"},
     *     @OA\Response(
     *         response=200,
     *         description="List of districts"
     *     )
     * )
     */
    public function getDistricts(Request $request)
    {
        $districts = District::orderBy('name', 'asc')->get(['id', 'name']);
        
        return response()->json([
            'districts' => $districts
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/locations/areas",
     *     summary="Get list of areas, optionally filtered by district_id",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="district_id",
     *         in="query",
     *         description="Filter areas by district ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of areas"
     *     )
     * )
     */
    public function getAreas(Request $request)
    {
        $query = Area::query();
        
        if ($request->has('district_id')) {
            $query->where('district_id', $request->district_id);
        }
        
        // Load the related district to include district_name if needed
        $areas = $query->with('district:id,name')->orderBy('name', 'asc')->get(['id', 'name', 'pincode', 'district_id']);
        
        return response()->json([
            'areas' => $areas->map(function ($area) {
                return [
                    'id' => $area->id,
                    'name' => $area->name,
                    'pincode' => $area->pincode,
                    'district_id' => $area->district_id,
                    'district_name' => $area->district->name ?? null,
                ];
            })
        ]);
    }
}
