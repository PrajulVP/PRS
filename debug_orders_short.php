<?php

use App\Models\DistributorOrder;
use App\Models\Distributor;
use App\Models\User;
use App\Models\SalesManager;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- Debugging Cancelled Orders Persistence (TOP 3) ---\n";

// 1. Find the cancelled orders
$cancelledOrders = DistributorOrder::where('status', 'cancelled')->take(3)->get();
if ($cancelledOrders->isEmpty()) {
    echo "No cancelled orders found.\n";
}

foreach ($cancelledOrders as $order) {
    echo "Order [{$order->order_code}] (ID: {$order->id}) - Status: {$order->status}\n";
    echo "  Distributor ID: {$order->distributor_id}\n";

    $distributor = Distributor::find($order->distributor_id);
    if ($distributor) {
        echo "  Distributor: {$distributor->name} (ID: {$distributor->id})\n";
        echo "  Distributor Sales Manager ID: " . ($distributor->sales_manager_id ?? 'NULL') . "\n";
    } else {
        echo "  Distributor NOT FOUND.\n";
    }
    echo "------------------------------------------------\n";
}
