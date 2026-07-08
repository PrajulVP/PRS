<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = \App\Models\RetailerOrder::latest()->first();
echo "Order: {$order->order_code}\n";
foreach($order->items as $i) {
    echo "Item ID: {$i->id}, Product: {$i->product->product_name}, Size: '{$i->size}', Paid Qty: {$i->quantity}, Free Qty: {$i->free_quantity}, Free Size: '{$i->free_size}'\n";
}
