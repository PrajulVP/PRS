<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HospitalClinic;

class HospitalClinicApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/hospitals-clinics",
     *     summary="Get list of Hospitals / Clinics",
     *     tags={"Hospitals & Clinics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="district_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="area_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of Hospitals and Clinics",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="City General Hospital"),
     *                 @OA\Property(property="address", type="string", example="Main Road, Sector 4"),
     *                 @OA\Property(property="district_id", type="integer", nullable=true),
     *                 @OA\Property(property="area_id", type="integer", nullable=true),
     *                 @OA\Property(property="latitude", type="number", nullable=true),
     *                 @OA\Property(property="longitude", type="number", nullable=true),
     *                 @OA\Property(property="location_locked", type="boolean", example=false)
     *             ))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = HospitalClinic::with(['district', 'area'])->where('status', 'active');

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $hospitals = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $hospitals
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/hospitals-clinics/{id}",
     *     summary="Get single Hospital / Clinic details",
     *     tags={"Hospitals & Clinics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hospital/Clinic detail"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     )
     * )
     */
    public function show($id)
    {
        $hospital = HospitalClinic::with(['district', 'area'])->find($id);

        if (!$hospital) {
            return response()->json([
                'success' => false,
                'message' => 'Hospital/Clinic record not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $hospital
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/hospitals-clinics",
     *     summary="Create a new Hospital / Clinic",
     *     tags={"Hospitals & Clinics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Apollo Clinic"),
     *             @OA\Property(property="address", type="string", example="123 Hospital Road"),
     *             @OA\Property(property="district_id", type="integer", nullable=true),
     *             @OA\Property(property="area_id", type="integer", nullable=true),
     *             @OA\Property(property="latitude", type="number", nullable=true),
     *             @OA\Property(property="longitude", type="number", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Hospital/Clinic created successfully"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'district_id' => 'nullable|exists:districts,id',
            'area_id' => 'nullable|exists:areas,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            $data['location_locked'] = true;
            $data['location_updated_at'] = now();
        }

        $hospital = HospitalClinic::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Hospital/Clinic created successfully!',
            'data' => $hospital
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/hospitals-clinics/{id}",
     *     summary="Update a Hospital / Clinic",
     *     description="Edit details. If location was already locked, coordinate changes are rejected with 403 Forbidden.",
     *     tags={"Hospitals & Clinics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Apollo Clinic Updated"),
     *             @OA\Property(property="address", type="string", example="456 New Road"),
     *             @OA\Property(property="district_id", type="integer", nullable=true),
     *             @OA\Property(property="area_id", type="integer", nullable=true),
     *             @OA\Property(property="latitude", type="number", nullable=true),
     *             @OA\Property(property="longitude", type="number", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Location locked error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $hospital = HospitalClinic::find($id);

        if (!$hospital) {
            return response()->json([
                'success' => false,
                'message' => 'Hospital/Clinic record not found'
            ], 404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'district_id' => 'nullable|exists:districts,id',
            'area_id' => 'nullable|exists:areas,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Location Lock Verification
        if ($hospital->location_locked && ($request->filled('latitude') || $request->filled('longitude'))) {
            if ($hospital->latitude != $request->latitude || $hospital->longitude != $request->longitude) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location is locked. Contact Admin to reset.'
                ], 403);
            }
        }

        if (is_null($hospital->latitude) && is_null($hospital->longitude) && !empty($data['latitude']) && !empty($data['longitude'])) {
            $data['location_locked'] = true;
            $data['location_updated_at'] = now();
        }

        $hospital->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Hospital/Clinic updated successfully!',
            'data' => $hospital
        ]);
    }
}
