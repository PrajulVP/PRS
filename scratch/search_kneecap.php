<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\RetailerOrder;
use App\Models\RetailerOrderItem;

$items = RetailerOrderItem::where('product_name', 'like', '%Knee cap%')
    ->orderBy('retailer_order_id', 'desc')
    ->get();

foreach ($items->groupBy('retailer_order_id') as $orderId => $orderItems) {
    $order = RetailerOrder::find($orderId);
    echo "Order: " . ($order->order_code ?? 'ID:'.$orderId) . "\n";
    foreach ($orderItems as $i) {
        echo "  " . $i->id . ' | ' . $i->product_id . ' | ' . ($i->side ?: 'NULL') . ' | ' . ($i->size ?: 'NULL') . ' | ' . $i->quantity . " | " . $i->product_name . "\n";
    }
}
