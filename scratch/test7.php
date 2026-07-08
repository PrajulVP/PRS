<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = \App\Models\RetailerOrder::orderBy('id', 'desc')->first();
echo "Order: {$order->id}, Dist: {$order->distributor_id}\n";
foreach ($order->items as $item) {
    echo "Item: {$item->product->product_name} (Side: {$item->side}, Size: {$item->size}, Free Size: {$item->free_size})\n";
    
    // Check inventory for this item
    $invs = \App\Models\Inventory::where('distributor_id', $order->distributor_id)
        ->where('product_id', $item->product_id)
        ->get();
    foreach ($invs as $inv) {
        echo "  - Inv ID: {$inv->id}, Batch: {$inv->batch_no}, Stock: {$inv->stock}, Side: '{$inv->side}', Size: '{$inv->size}'\n";
    }
}
