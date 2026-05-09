<?php

use App\Models\AttendanceLog;
use App\Models\User;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking ALL Attendance Logs...\n";

$logs = AttendanceLog::with('user')->orderBy('timestamp', 'desc')->take(20)->get();
echo "Total logs found (last 20): " . $logs->count() . "\n";

foreach ($logs as $log) {
    echo "User: " . ($log->user->name ?? 'Unknown') . " (ID: {$log->user_id}), Type: {$log->type}, Timestamp: {$log->timestamp}\n";
}

echo "\nChecking Users with role fieldstaff...\n";
$fieldStaffUsers = User::role('fieldstaff')->get();
echo "Total fieldstaff users: " . $fieldStaffUsers->count() . "\n";
foreach ($fieldStaffUsers as $u) {
    echo "ID: {$u->id}, Name: {$u->name}, Status: {$u->status}\n";
    $lastPunch = AttendanceLog::where('user_id', $u->id)->latest('timestamp')->first();
    echo "  Last Punch: " . ($lastPunch ? "{$lastPunch->type} at {$lastPunch->timestamp}" : "None") . "\n";
}
