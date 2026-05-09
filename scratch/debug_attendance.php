<?php

use App\Models\AttendanceLog;
use App\Models\FieldStaff;
use App\Models\SalesManager;
use App\Models\User;
use App\Models\LocationLog;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Today's date: " . date('Y-m-d') . "\n";

$govind = User::where('name', 'like', '%Govind%')->get();
echo "Total users with name Govind: " . $govind->count() . "\n";
foreach ($govind as $g) {
    echo "User ID: {$g->id}, Name: {$g->name}, Role: {$g->role}\n";
}

foreach ($logs as $log) {
    echo "User ID: {$log->user_id}, Type: {$log->type}, Timestamp: {$log->timestamp}\n";
}

$onlinePunches = AttendanceLog::where('type', 'punch_in')
    ->whereDate('timestamp', date('Y-m-d'))
    ->get();
echo "Total punch_ins today: " . $onlinePunches->count() . "\n";

$salesManagers = SalesManager::all();
echo "Total Sales Managers: " . $salesManagers->count() . "\n";

foreach ($salesManagers as $sm) {
    echo "SM ID: {$sm->id}, Name: {$sm->name}, User ID: {$sm->user_id}\n";
    $staff = FieldStaff::where('sales_manager_id', $sm->id)->get();
    echo "  Assigned Field Staff: " . $staff->count() . "\n";
    foreach ($staff as $fs) {
        $lastLog = AttendanceLog::where('user_id', $fs->user_id)
            ->latest('timestamp')
            ->first();
        echo "    FS ID: {$fs->id}, User ID: {$fs->user_id}, Last Log: " . ($lastLog ? $lastLog->type : 'None') . " at " . ($lastLog ? $lastLog->timestamp : 'N/A') . "\n";
    }
}
