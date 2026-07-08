<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orderItems = \App\Models\RetailerOrderItem::where('retailer_order_id', 111)->get();
foreach ($orderItems as $item) {
    echo "Item ID: {$item->id}, Product: {$item->product_name}, Qty: {$item->quantity}, Free: {$item->free_quantity}, Unit: {$item->unit}\n";
}

$p = \App\Models\Product::where('product_name', 'like', '%knee%')->first();
echo "Knee Cap: strips_per_box=" . $p->strips_per_box . ", boxes_per_carton=" . $p->boxes_per_carton . ", units_per_strip=" . $p->units_per_strip . "\n";
