<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$products = Product::select('product_code', 'product_name', 'brand')->limit(20)->get();
foreach ($products as $p) {
    echo "Code: {$p->product_code} | Brand: " . ($p->brand ?? 'NULL') . " | Name: {$p->product_name}\n";
}
