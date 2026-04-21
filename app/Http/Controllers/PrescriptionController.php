<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\Distributor;
use App\Models\PrescriptionLog;
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

        // --- Log for Prescription Analysis ---
        $log = null;
        try {
            $log = PrescriptionLog::create([
                'retailer_id' => $retailer->id,
                'raw_text' => $extractedData['raw_text'] ?? null,
                'extracted_data' => $extractedData,
            ]);
        } catch (\Exception $e) {
            Log::error('Prescription logging failed: ' . $e->getMessage());
        }

        $results = $this->aiService->matchExtractedMedicines($aiItems, $retailer);

        // --- Patient Database Integration ---
        $patientName = $extractedData['patient_name'] ?? $extractedData['name'] ?? null;
        $patientContact = $extractedData['patient_contact'] ?? $extractedData['contact'] ?? null;

        if ($patientName) {
            $patient = \App\Models\Patient::updateOrCreate(
                ['retailer_id' => $retailer->id, 'name' => $patientName],
                [
                    'contact' => $patientContact,
                    'category' => $results['is_chronic_prescription'] ? 'chronic' : 'acute',
                    'medication_history' => json_encode($aiItems)
                ]
            );

            // Reorder alerts for chronic patients (setting to 28 days for safety)
            if ($patient->category === 'chronic') {
                $patient->next_reorder_date = now()->addDays(28);
                $patient->save();
            }
            
            $results['patient'] = $patient;
        }

        return response()->json(array_merge(['success' => true], $results));
    }
}
