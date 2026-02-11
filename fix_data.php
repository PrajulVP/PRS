<?php

use App\Models\Distributor;
use App\Models\SalesManager;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- Fixing Distributor Assignment ---\n";

$distributor = Distributor::find(22);
if ($distributor) {
    echo "Found Distributor: " . $distributor->name . "\n";
    if (!$distributor->sales_manager_id) {
        $sm = SalesManager::first();
        if ($sm) {
            $distributor->sales_manager_id = $sm->id;
            $distributor->save();
            echo "Assigned to Sales Manager: " . $sm->name . " (ID: " . $sm->id . ")\n";
        } else {
            echo "No Sales Managers found in database!\n";
        }
    } else {
        echo "Distributor already assigned to SM ID: " . $distributor->sales_manager_id . "\n";
    }
} else {
    echo "Distributor 22 not found.\n";
}
