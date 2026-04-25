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
            'order_id' => 'nullable|integer',
            'order_type' => 'nullable|string|in:retailer,distributor'
        ]);

        $file = $request->file('invoice');
        $type = $request->get('type', 'admin'); // 'retailer' or 'admin' (external API type)
        $orderId = $request->get('order_id');
        $orderType = $request->get('order_type', 'distributor');

        try {
            $extractedData = $this->ocrService->processInvoice($file, $type);

            if ($extractedData) {
                // Perform duplicate check if order context is provided
                if ($orderId && isset($extractedData['invoice_metadata']['invoice_no'])) {
                    $invoiceNo = trim($extractedData['invoice_metadata']['invoice_no']);
                    $distributorId = null;

                    if ($orderType === 'distributor') {
                        $order = \App\Models\DistributorOrder::find($orderId);
                        $distributorId = $order?->distributor_id;
                    } else {
                        $order = \App\Models\RetailerOrder::find($orderId);
                        $distributorId = $order?->distributor_id;
                    }

                    if ($distributorId && !empty($invoiceNo)) {
                        $existsInDistOrders = \App\Models\DistributorOrder::where('distributor_id', $distributorId)
                            ->where('invoice_no', $invoiceNo)
                            ->when($orderType === 'distributor', function($q) use ($orderId) {
                                return $q->where('id', '!=', $orderId);
                            })
                            ->exists();

                        $existsInRetailOrders = \App\Models\RetailerOrder::where('distributor_id', $distributorId)
                            ->where('invoice_no', $invoiceNo)
                            ->when($orderType === 'retailer', function($q) use ($orderId) {
                                return $q->where('id', '!=', $orderId);
                            })
                            ->exists();

                        $extractedData['invoice_metadata']['is_duplicate'] = ($existsInDistOrders || $existsInRetailOrders);
                    }
                }

                return response()->json([
                    'success' => true,
                    'data' => $extractedData
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'OCR processing failed. The service returned an empty or invalid response.'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
