<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use Illuminate\Http\Request;

class PrescriptionApiController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * @OA\Post(
     *     path="/api/prescriptions/upload",
     *     summary="Upload a prescription and get AI results",
     *     tags={"Prescription"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="prescription",
     *                     description="The prescription image or PDF",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *                 required={"prescription"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="AI results",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to extract data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to extract data from the prescription.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function upload(Request $request)
    {
        $request->validate([
            'prescription' => 'required|file|mimes:jpg,jpeg,png,pdf',
        ]);

        $file = $request->file('prescription');
        $extractedData = $this->aiService->extractPrescription($file);

        if ($extractedData) {
            return response()->json([
                'success' => true,
                'data' => $extractedData
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to extract data from the prescription.'
        ], 500);
    }
}
