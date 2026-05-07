<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

$startDate = Carbon::now()->startOfMonth();
$endDate = Carbon::now();

$res = DB::table('retailer_order_items')
    ->join('products', 'retailer_order_items.product_id', '=', 'products.id')
    ->join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
    ->where('retailer_orders.status', 'delivered')
    ->whereBetween('retailer_orders.created_at', [$startDate, $endDate])
    ->select('products.brand', DB::raw('SUM(retailer_order_items.total_amount) as revenue'))
    ->groupBy('products.brand')
    ->get();

echo "Revenue by Brand (Delivered, This Month):\n";
foreach($res as $r) {
    echo ($r->brand ?: 'NULL') . ': ' . $r->revenue . "\n";
}

$allBrands = DB::table('products')->distinct('brand')->pluck('brand');
echo "\nAll Brands in Products:\n";
foreach($allBrands as $b) {
    echo ($b ?: 'NULL') . "\n";
}
