<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SystemController extends Controller
{
    public function swaggerGenerate()
    {
        try {
            Artisan::call('l5-swagger:generate');
            return response()->json([
                'status' => 'success',
                'message' => 'Swagger documentation generated successfully.',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function migrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            return response()->json([
                'status' => 'success',
                'message' => 'Database migrated successfully.',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function migrateFresh()
    {
        try {
            Artisan::call('migrate:fresh', ['--force' => true]);
            return response()->json([
                'status' => 'success',
                'message' => 'Database dropped and migrated fresh successfully.',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function migrateFreshSeed()
    {
        try {
            Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
            return response()->json([
                'status' => 'success',
                'message' => 'Database dropped, migrated fresh and seeded successfully.',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function optimize()
    {
        try {
            Artisan::call('optimize');
            return response()->json([
                'status' => 'success',
                'message' => 'Application optimized successfully.',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/system/ocr-logs",
     *     summary="View external OCR API communication logs",
     *     tags={"Internal System"},
     *     @OA\Response(response=200, description="Log lines")
     * )
     */
    public function getOcrLogs()
    {
        try {
            $logPath = storage_path('logs/laravel.log');
            if (!file_exists($logPath)) {
                return response()->json(['message' => 'No logs found'], 200);
            }

            $lines = file($logPath);
            $ocrLogs = array_filter($lines, function ($line) {
                return str_contains($line, 'OCR API');
            });

            return response()->json(array_values($ocrLogs));
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
