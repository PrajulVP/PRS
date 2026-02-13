<?php

use App\Models\DistributorOrder;
use App\Models\Distributor;
use App\Models\User;
use App\Models\SalesManager;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- Debugging Cancelled Orders Persistence ---\n";

// 1. Find the cancelled orders
$cancelledOrders = DistributorOrder::where('status', 'cancelled')->get();
if ($cancelledOrders->isEmpty()) {
    echo "No cancelled orders found.\n";
}

foreach ($cancelledOrders as $order) {
    echo "Order [{$order->order_code}] (ID: {$order->id}) - Status: {$order->status}\n";
    echo "  Distributor ID: {$order->distributor_id}\n";

    $distributor = Distributor::find($order->distributor_id);
    if ($distributor) {
        echo "  Distributor: {$distributor->name} (ID: {$distributor->id})\n";
        echo "  Distributor's Sales Manager ID: " . ($distributor->sales_manager_id ?? 'NULL') . "\n";

        if ($distributor->sales_manager_id) {
            $sm = SalesManager::find($distributor->sales_manager_id);
            echo "  -> Sales Manager Name: " . ($sm ? $sm->name : 'Unknown') . "\n";
            echo "  -> Sales Manager User ID: " . ($sm ? $sm->user_id : 'Unknown') . "\n";
        }
    } else {
        echo "  Distributor NOT FOUND.\n";
    }
    echo "------------------------------------------------\n";
}

echo "\n--- Testing Query Logic for Sales Managers ---\n";
$salesManagers = SalesManager::all();

foreach ($salesManagers as $sm) {
    echo "Simulating View for Sales Manager: {$sm->name} (ID: {$sm->id})\n";

    // Replicate the Controller Query
    $query = DistributorOrder::query();
    $query->where(function ($q) use ($sm) {
        $q->where('sales_manager_id', $sm->id)
            ->orWhere('status', DistributorOrder::STATUS_PENDING)
            ->orWhere('status', DistributorOrder::STATUS_CANCELLATION_REQUESTED)
            ->orWhereHas('distributor', function ($q) use ($sm) {
                // This is the critical line
                $q->where('sales_manager_id', $sm->id);
            });
    });

    $count = $query->count();
    echo "  Total Visible Orders: {$count}\n";

    // Check specific cancelled orders visibility
    foreach ($cancelledOrders as $co) {
        $isVisible = $query->clone()->where('id', $co->id)->exists();
        echo "  -> Order {$co->order_code} (Cancelled) is " . ($isVisible ? "VISIBLE" : "HIDDEN") . "\n";
    }
    echo "\n";
}
