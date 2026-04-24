<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "Starting product categorization cleanup...\n";

$products = Product::all();
$updatedCount = 0;

foreach ($products as $product) {
    $oldBrand = $product->brand;
    
    // Trigger the 'saving' event logic in the model
    $product->save();
    
    if ($product->brand !== $oldBrand) {
        echo "Updated Product: {$product->product_name} | Code: {$product->product_code} | New Brand: {$product->brand}\n";
        $updatedCount++;
    }
}

echo "Cleanup finished. Total products updated: $updatedCount\n";
