<?php
use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$admin = User::where('role', 'admin')->first();
if ($admin) {
    echo "Admin User: " . $admin->name . " (ID: " . $admin->id . ")\n";
    echo "Counts:\n";
    print_r($admin->getActionCounts());
} else {
    echo "No Admin found.\n";
}
