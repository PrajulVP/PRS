<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = \App\Models\Product::where('product_name', 'like', '%Ankle Binder%')->get();
foreach($products as $p) {
    echo "Product ID: {$p->id}, Name: {$p->product_name}, Size: {$p->size}\n";
    $invs = \App\Models\Inventory::where('product_id', $p->id)->get();
    foreach($invs as $inv) {
        echo "  Inv ID: {$inv->id}, Side: '{$inv->side}', Size: '{$inv->size}', Stock: {$inv->stock}\n";
    }
}
