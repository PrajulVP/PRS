<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['retailer_orders', 'retailer_order_items'];
foreach ($tables as $table) {
    $res = DB::select("SHOW CREATE TABLE $table");
    echo "--- $table ---\n";
    print_r($res);
}
