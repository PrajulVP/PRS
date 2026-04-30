<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\RetailerOrder;

foreach (RetailerOrder::all() as $o) {
    echo $o->order_code . "\n";
}
