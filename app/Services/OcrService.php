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
        $apiUrl = "http://192.168.1.10:5000/{$type}";

        try {
            $response = Http::timeout(60)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post($apiUrl);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('OCR API Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('OCR API Connection Failed: ' . $e->getMessage());
            return null;
        }
    }
}
