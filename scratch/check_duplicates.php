<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\RetailerOrder;

$o = RetailerOrder::where('order_code', 'RO-BCPEUL')->with('items')->first();
if ($o) {
    echo "Order ID: " . $o->id . "\n";
    foreach ($o->items as $i) {
        echo $i->id . ' | ' . $i->product_id . ' | ' . ($i->side ?: 'NULL') . ' | ' . ($i->size ?: 'NULL') . ' | ' . $i->quantity . "\n";
    }
} else {
    echo "Not found";
}
