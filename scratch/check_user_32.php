<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FieldStaff;
use App\Models\User;

$userId = 32;
$fs = FieldStaff::where('user_id', $userId)->first();
$user = User::find($userId);

if ($fs) {
    echo "FieldStaff exists for user $userId (FS ID: {$fs->id})\n";
    echo "Assigned SM ID: {$fs->sales_manager_id}\n";
} else {
    echo "NO FieldStaff record found for user $userId\n";
}

if ($user) {
    echo "User Name: {$user->name}, Roles: " . implode(', ', $user->getRoleNames()->toArray()) . "\n";
}
