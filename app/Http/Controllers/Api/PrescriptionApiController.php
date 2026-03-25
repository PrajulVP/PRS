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
     *     summary="Upload a prescription and get AI matched results",
     *     description="Extracts medicines from an image/PDF and matches them with available products and distributors.",
     *     tags={"Prescription"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="prescription", type="string", format="binary"),
     *                 @OA\Property(property="retailer_id", type="integer", description="Optional user id (from users table) of the retailer for distance calculation and stock filtering"),
     *                 required={"prescription"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="AI results with multi-match options",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="matched_items", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="out_of_stock_items", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="unmatched_items", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function upload(Request $request)
    {
        $request->validate([
            'prescription' => 'required|file|mimes:jpg,jpeg,png,pdf',
            'retailer_id' => 'nullable|exists:users,id'
        ]);

        $file = $request->file('prescription');
        $retailer = $request->retailer_id ? \App\Models\Retailer::where('user_id', $request->retailer_id)->first() : null;

        $extractedData = $this->aiService->extractPrescription($file);
        
        if (!$extractedData) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to extract data from the prescription.'
            ], 500);
        }

        $aiItems = $extractedData['medicines'] ?? $extractedData['line_items'] ?? $extractedData['items'] ?? [];
        $results = $this->aiService->matchExtractedMedicines($aiItems, $retailer);

        return response()->json(array_merge(['success' => true], $results));
    }
}
