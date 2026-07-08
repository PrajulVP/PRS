<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = \App\Models\RetailerOrder::where('order_code', 'RO-2600069')->first();
if (!$order) {
    echo "Order not found\n";
} else {
    $items = \App\Models\RetailerOrderItem::where('retailer_order_id', $order->id)->get()->toArray();
    print_r($items);
}
