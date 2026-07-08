<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orderItems = \App\Models\RetailerOrderItem::where('retailer_order_id', 111)->get();
foreach ($orderItems as $item) {
    echo "Item ID: {$item->id}, Product: {$item->product_name}, Qty: {$item->quantity}, Free: {$item->free_quantity}, Unit: {$item->unit}\n";
    $batches = \App\Models\RetailerOrderItemBatch::where('retailer_order_item_id', $item->id)->get();
    foreach ($batches as $b) {
        echo "  - Batch: {$b->batch_no}, Qty: {$b->quantity}\n";
    }
}
