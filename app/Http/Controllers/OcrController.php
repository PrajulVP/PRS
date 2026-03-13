<?php

namespace App\Http\Controllers;

use App\Services\OcrService;
use Illuminate\Http\Request;

class OcrController extends Controller
{
    protected $ocrService;

    public function __construct(OcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    /**
     * Handle the incoming invoice and send to external API.
     */
    public function process(Request $request)
    {
        $request->validate([
            'invoice' => 'required|file|mimes:pdf,jpg,jpeg,png',
        ]);

        $file = $request->file('invoice');
        $type = $request->get('type', 'admin');

        $extractedData = $this->ocrService->processInvoice($file, $type);

        if ($extractedData) {
            return response()->json([
                'success' => true,
                'data' => $extractedData
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to extract data from the invoice. The external OCR API did not return a valid response.'
        ], 500);
    }
}
