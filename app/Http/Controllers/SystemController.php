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

    public function getOcrLogs()
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            if (!file_exists($logFile)) {
                return response()->json(['message' => 'Log file not found.'], 404);
            }

            // Read the last 1MB of the log file for performance
            $size = filesize($logFile);
            $handle = fopen($logFile, 'r');
            $readSize = min($size, 1024 * 1024); // 1MB
            fseek($handle, -$readSize, SEEK_END);
            $content = fread($handle, $readSize);
            fclose($handle);

            $lines = explode("\n", $content);
            $ocrLogs = array_filter($lines, function ($line) {
                return str_contains($line, 'OCR API');
            });

            return response()->json([
                'status' => 'success',
                'logs' => array_values(array_slice($ocrLogs, -50)) // Return last 50 OCR entries
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
