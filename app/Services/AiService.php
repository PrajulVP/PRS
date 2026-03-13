<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    /**
     * Send a prescription file to the external Python AI API and return the extracted data.
     *
     * @param UploadedFile $file
     * @return array|null
     */
    public function extractPrescription(UploadedFile $file)
    {
        $basePath = env('AI_API_URL', 'http://13.204.159.20');
        $apiUrl = rtrim($basePath, '/') . "/extract-prescription";
        Log::info('Prescription AI API Request', ['url' => $apiUrl]);

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
                Log::info('Prescription AI API Success Response', ['response' => $data]);
                return $data;
            }

            Log::error('Prescription AI API Error Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Prescription AI API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
