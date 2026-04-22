<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['products', 'distributor_order_items', 'distributor_orders'];
$output = "";

foreach ($tables as $table) {
    $output .= "--- $table ---\n";
    $res = DB::select("SHOW CREATE TABLE $table");
    $output .= print_r($res, true) . "\n\n";
}

file_put_contents('scratch/comprehensive_schema.txt', $output);
echo "Done";
