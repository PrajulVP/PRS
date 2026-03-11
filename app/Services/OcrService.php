<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrService
{
    /**
     * Send an invoice file to the external Python OCR API and return the extracted data.
     *
     * @param UploadedFile $file
     * @param string $type The type of order ('retailer' or 'admin')
     * @return array|null
     */
    public function processInvoice(UploadedFile $file, string $type = 'admin')
    {
        $basePath = env('OCR_API_URL', 'http://13.204.159.20:5050');
        $apiUrl = rtrim($basePath, '/') . "/{$type}";
        Log::info('OCR API Request', ['url' => $apiUrl, 'type' => $type]);

        try {
            $response = Http::timeout(60)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post($apiUrl);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('OCR API Success Response', ['response' => $data]);
                return $data;
            }

            Log::error('OCR API Error Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('OCR API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Send a prescription file to the external Python OCR API and return the extracted data.
     *
     * @param UploadedFile $file
     * @return array|null
     */
    public function extractPrescription(UploadedFile $file)
    {
        $basePath = env('OCR_API_URL', 'http://13.204.159.20:5050');
        $apiUrl = rtrim($basePath, '/') . "/extract-prescription";
        Log::info('Prescription OCR API Request', ['url' => $apiUrl]);

        try {
            $response = Http::timeout(60)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post($apiUrl);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Prescription OCR API Success Response', ['response' => $data]);
                return $data;
            }

            Log::error('Prescription OCR API Error Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Prescription OCR API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
