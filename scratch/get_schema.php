<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$res = DB::select("SHOW CREATE TABLE distributor_order_items");
file_put_contents('scratch/table_structure.txt', print_r($res, true));
echo "Done";
