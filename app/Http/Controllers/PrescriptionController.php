<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrescriptionController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Extract medicines from a prescription and match them with system products.
     */
    public function extract(Request $request)
    {
        $request->validate([
            'prescription' => 'required|file|mimes:jpg,jpeg,png,pdf',
            'retailer_id' => 'required|exists:retailers,id'
        ]);

        $file = $request->file('prescription');
        $retailer = Retailer::find($request->retailer_id);

        $extractedData = $this->aiService->extractPrescription($file);

        // Flexible key check
        $aiItems = $extractedData['medicines'] ?? $extractedData['line_items'] ?? $extractedData['items'] ?? null;

        if (!$extractedData || is_null($aiItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to extract medicines from the prescription.'
            ], 500);
        }

        $results = $this->aiService->matchExtractedMedicines($aiItems, $retailer);

        return response()->json(array_merge(['success' => true], $results));
    }
}
