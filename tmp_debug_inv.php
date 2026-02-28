<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Inventory;
use App\Models\RetailerOrder;
use App\Models\Distributor;

$invId = 1;
$distId = 21;
$prodId = 2;

echo "Checking Inventory ID {$invId}...\n";
$inv = Inventory::find($invId);
if (!$inv) {
    echo "ID {$invId} NOT FOUND globally.\n";
    exit;
}

echo "Found globally. Dist: {$inv->distributor_id}, Prod: {$inv->product_id}\n";

$invWithConstraints = Inventory::where('distributor_id', $distId)
    ->where('product_id', $prodId)
    ->find($invId);

if ($invWithConstraints) {
    echo "Found with constraints (Dist: {$distId}, Prod: {$prodId}).\n";
} else {
    echo "NOT FOUND with constraints (Dist: {$distId}, Prod: {$prodId}).\n";
}

// Check Order 1
$order = RetailerOrder::find(1);
if ($order) {
    echo "Order 1 Dist ID: {$order->distributor_id}\n";
    $item = $order->items->first();
    if ($item) {
        echo "Order Item 1 Product ID: {$item->product_id}\n";
    }
}
