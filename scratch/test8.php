<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = \App\Models\RetailerOrderItem::where('product_name', 'like', '%Knee%')->orderBy('id', 'desc')->take(5)->get(); 
foreach($items as $i) {
    echo "Item ID: {$i->id}, Size: '{$i->size}', Free Size: '{$i->free_size}', Paid Qty: {$i->quantity}, Free Qty: {$i->free_quantity}\n";
}
