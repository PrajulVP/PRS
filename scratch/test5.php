<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = \App\Models\RetailerOrderItem::where('retailer_order_id', 132)->get()->toArray();
print_r($items);
