<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orders = \App\Models\RetailerOrder::orderBy('id', 'desc')->take(5)->get();
foreach ($orders as $order) {
    echo "Order {$order->id} ({$order->order_code}):\n";
    foreach ($order->items as $item) {
        $pName = $item->product ? $item->product->product_name : $item->product_name;
        echo "  - Item ID: {$item->id}, Product: {$pName}, Qty: {$item->quantity}, Free: {$item->free_quantity}, Unit: {$item->unit}\n";
    }
}
