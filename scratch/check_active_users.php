<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AttendanceLog;
use App\Models\FieldStaff;
use App\Models\User;

$today = now()->toDateString();
echo "Checking all punch_ins for $today...\n";

$logs = AttendanceLog::where('type', 'punch_in')
    ->whereDate('timestamp', $today)
    ->get();

if ($logs->isEmpty()) {
    echo "No punch_ins found for today.\n";
}

foreach ($logs as $log) {
    $user = User::find($log->user_id);
    $fs = FieldStaff::where('user_id', $log->user_id)->first();
    
    echo "User ID: {$log->user_id}, Name: " . ($user->name ?? 'N/A') . "\n";
    if ($fs) {
        echo "  - HAS FieldStaff record (ID: {$fs->id})\n";
        echo "  - Assigned SM ID: {$fs->sales_manager_id}\n";
    } else {
        echo "  - MISSING FieldStaff record!\n";
    }
}
