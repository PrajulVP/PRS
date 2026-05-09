<?php

use App\Models\FieldStaff;
use App\Models\SalesManager;
use App\Models\User;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Sales Managers and their Field Staff...\n";

$salesManagers = SalesManager::with('user')->get();
foreach ($salesManagers as $sm) {
    echo "SM Model ID: {$sm->id}, User ID: {$sm->user_id}, Name: " . ($sm->user->name ?? 'N/A') . "\n";
    
    $staffByModelId = FieldStaff::where('sales_manager_id', $sm->id)->get();
    echo "  Staff by Model ID ({$sm->id}): " . $staffByModelId->count() . "\n";
    
    $staffByUserId = FieldStaff::where('sales_manager_id', $sm->user_id)->get();
    echo "  Staff by User ID ({$sm->user_id}): " . $staffByUserId->count() . "\n";
}

echo "\nChecking ALL Field Staff and their assigned SM IDs...\n";
$allStaff = FieldStaff::all();
foreach ($allStaff as $fs) {
    echo "FS ID: {$fs->id}, User ID: {$fs->user_id}, Assigned SM ID: {$fs->sales_manager_id}\n";
}
