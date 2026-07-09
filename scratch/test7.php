<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$invs = \App\Models\Inventory::whereHas('product', function($q) {
    $q->where('product_name', 'like', '%Ankle binder%');
})->get();

$sums = [];
foreach($invs as $inv) {
    $dist = $inv->distributor_id;
    if(!isset($sums[$dist])) $sums[$dist] = ['total' => 0, 'variants' => []];
    $sums[$dist]['total'] += $inv->stock;
    
    $v = "S:" . $inv->side . " Z:" . $inv->size;
    if(!isset($sums[$dist]['variants'][$v])) $sums[$dist]['variants'][$v] = 0;
    $sums[$dist]['variants'][$v] += $inv->stock;
}

print_r($sums);
