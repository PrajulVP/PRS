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
        $basePath = env('OCR_API_URL', 'http://13.200.4.44:8001');
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

            $errorMsg = "The OCR service returned an error (Status: " . $response->status() . ")";
            if ($response->status() === 504 || $response->status() === 502) {
                $errorMsg = "The OCR server is taking too long to respond or is temporarily unavailable (Status: " . $response->status() . ")";
            }

            Log::error('OCR API Error Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            throw new \Exception($errorMsg);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('OCR API Connection Error', ['message' => $e->getMessage()]);
            throw new \Exception("The OCR server at {$basePath} is unreachable. Please ensure the server is running and accessible.");
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('OCR API Request Error', ['status' => $e->getCode(), 'message' => $e->getMessage()]);
            throw new \Exception("The OCR service returned an error. Please try again later.");
        } catch (\Exception $e) {
            Log::error('OCR API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
