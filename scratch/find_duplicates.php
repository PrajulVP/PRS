<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\RetailerOrder;

$duplicates = DB::table('retailer_order_items')
    ->select('retailer_order_id', 'product_id', 'side', 'size', DB::raw('COUNT(*) as count'))
    ->groupBy('retailer_order_id', 'product_id', 'side', 'size')
    ->having('count', '>', 1)
    ->get();

foreach ($duplicates as $dup) {
    $order = RetailerOrder::find($dup->retailer_order_id);
    echo "Order: " . ($order->order_code ?? 'ID:'.$dup->retailer_order_id) . " | Product: " . $dup->product_id . " | Side: " . ($dup->side ?: 'NULL') . " | Size: " . ($dup->size ?: 'NULL') . " | Count: " . $dup->count . "\n";
}
