<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Ifsnop\Mysqldump\Mysqldump;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display a listing of the backups.
     */
    public function index()
    {
        $backupPath = storage_path('app/backups');
        
        // Create directory if it doesn't exist
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $files = File::files($backupPath);
        
        // Sort files by modified time descending (newest first)
        usort($files, function ($a, $b) {
            return $b->getMTime() - $a->getMTime();
        });

        $backups = [];
        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'size' => $this->formatSizeUnits($file->getSize()),
                    'date' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        return view('admin.backups.index', compact('backups'));
    }

    /**
     * Create a new database backup.
     */
    public function create()
    {
        try {
            // Prevent timeout for large databases
            set_time_limit(0);

            $backupPath = storage_path('app/backups');
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }

            // Generate filename based on timestamp
            $filename = 'backup_' . date('d-M-Y_H-i') . '.sql';
            $filePath = $backupPath . '/' . $filename;

            // Get DB credentials
            $host = config('database.connections.mysql.host');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            $dumpSettings = [
                'compress' => Mysqldump::NONE,
                'no-data' => false,
                'add-drop-table' => true,
                'single-transaction' => true,
                'lock-tables' => true,
                'add-locks' => true,
                'extended-insert' => true,
                'disable-foreign-keys-check' => true,
                'skip-triggers' => false,
                'add-drop-trigger' => true,
                'databases' => true,
                'add-drop-database' => false,
                'hex-blob' => true,
            ];

            $dump = new Mysqldump("mysql:host={$host};dbname={$database}", $username, $password, $dumpSettings);
            $dump->start($filePath);

            return redirect()->route('admin.backups.index')->with('success', 'Database backup created successfully: ' . $filename);

        } catch (\Throwable $e) {
            return redirect()->route('admin.backups.index')->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Download a specific backup file.
     */
    public function download($filename)
    {
        $filePath = storage_path('app/backups/' . $filename);

        if (File::exists($filePath)) {
            return response()->download($filePath);
        }

        return redirect()->route('admin.backups.index')->with('error', 'File not found.');
    }

    /**
     * Delete a specific backup file.
     */
    public function destroy($filename)
    {
        $filePath = storage_path('app/backups/' . $filename);

        if (File::exists($filePath)) {
            File::delete($filePath);
            return redirect()->route('admin.backups.index')->with('success', 'Backup deleted successfully.');
        }

        return redirect()->route('admin.backups.index')->with('error', 'File not found.');
    }

    /**
     * Helper to format file size.
     */
    private function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}
