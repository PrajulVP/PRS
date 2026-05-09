<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AttendanceLog;
use App\Models\FieldStaff;

$today = now()->toDateString();
$fsUserId = 81;

echo "Checking logs for Field Staff User ID $fsUserId today ($today)...\n";

$logs = AttendanceLog::where('user_id', $fsUserId)
    ->whereDate('timestamp', $today)
    ->get();

if ($logs->isEmpty()) {
    echo "NO logs found for Field Staff User 81 today.\n";
} else {
    foreach ($logs as $log) {
        echo "- Type: {$log->type}, Time: {$log->timestamp}\n";
    }
}
