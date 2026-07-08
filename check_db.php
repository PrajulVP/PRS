<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$items = \App\Models\RetailerOrderItem::limit(5)->get();
echo json_encode($items, JSON_PRETTY_PRINT);
