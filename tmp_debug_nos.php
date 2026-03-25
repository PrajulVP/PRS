<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Inventory;

echo "--- ALL Products ---\n";
$products = Product::all();
foreach ($products as $p) {
    echo "ID: {$p->id} | Name: {$p->product_name} | Pack: '{$p->pack}' | BoxSize: '{$p->box_size}' | Units/Str: {$p->units_per_strip}\n";
}

echo "\n--- ALL Inventories ---\n";
$inventories = Inventory::all();
foreach ($inventories as $inv) {
    echo "Inv ID: {$inv->id} | Product Name: {$inv->product_name} | Stock: {$inv->stock} | Batch: {$inv->batch_no} | Expiry: {$inv->expiry_date}\n";
}
