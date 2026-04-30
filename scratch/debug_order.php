<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$o = DB::table('retailer_orders')->where('order_code', 'like', '%BCPEUL%')->first();
if ($o) {
    print_r($o);
    $items = DB::table('retailer_order_items')->where('retailer_order_id', $o->id)->get();
    foreach ($items as $i) {
        echo "Item ID: {$i->id}, Product ID: {$i->product_id}, Name: {$i->product_name}, Side: {$i->side}, Size: {$i->size}, Qty: {$i->quantity}\n";
    }
} else {
    echo "Order not found\n";
}
