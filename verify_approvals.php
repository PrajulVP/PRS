<?php

use App\Models\DistributorOrder;
use App\Models\Distributor;
use App\Models\SalesManager;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- Verifying Approvals Logic ---\n";

// 1. Get a Sales Manager
$sm = SalesManager::first();
if (!$sm) {
    echo "No Sales Manager found.\n";
    exit;
}
echo "Testing visibility for Sales Manager: {$sm->name} (ID: {$sm->id})\n";

// 2. Get Cancelled Orders
$cancelledOrders = DistributorOrder::where('status', 'cancelled')->get();
echo "Found " . $cancelledOrders->count() . " cancelled orders.\n";

$visibleCount = 0;
foreach ($cancelledOrders as $order) {
    // Logic from PendingApprovalController
    $isVisible = false;

    // Check direct assignment
    if ($order->sales_manager_id == $sm->id) {
        $isVisible = true;
    }
    // Check distributor assignment (The new logic)
    elseif ($order->distributor && $order->distributor->sales_manager_id == $sm->id) {
        $isVisible = true;
    }

    if ($isVisible) {
        echo "[VISIBLE] Order {$order->order_code} (Distributor: " . ($order->distributor->name ?? 'None') . ")\n";
        $visibleCount++;
    } else {
        // Explain WHY Hidden
        $reason = [];
        if ($order->sales_manager_id != $sm->id) $reason[] = "Order SM ID (" . ($order->sales_manager_id ?? 'NULL') . ") != " . $sm->id;
        if (!$order->distributor) $reason[] = "No Distributor";
        elseif ($order->distributor->sales_manager_id != $sm->id) $reason[] = "Distributor SM ID (" . ($order->distributor->sales_manager_id ?? 'NULL') . ") != " . $sm->id;

        echo "[HIDDEN] Order {$order->order_code} (Distributor: " . ($order->distributor->name ?? 'None') . ") - Reason: " . implode(', ', $reason) . "\n";
    }
}

echo "\nTotal Visible Cancelled Orders: {$visibleCount}\n";
