<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['products', 'distributor_orders', 'distributor_order_items'];
foreach ($tables as $table) {
    $res = DB::select("SHOW TABLE STATUS LIKE '{$table}'");
    echo "Table: {$table} | Engine: " . $res[0]->Engine . "\n";
}
