<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orders = \App\Models\DistributorOrder::all();
foreach ($orders as $order) {
    if ($order->total_amount == 0 && $order->items()->count() > 0) {
        $total = 0;
        $totalItems = 0;
        $totalQuantity = 0;
        foreach ($order->items as $itm) {
            $total += $itm->subtotal;
            $totalItems++;
            $totalQuantity += $itm->quantity;
        }
        $order->update([
            'total_amount' => $total,
            'total_items' => $totalItems,
            'total_quantity' => $totalQuantity
        ]);
        echo "Fixed order " . $order->id . " - Total: " . $total . "\n";
    }
}
echo "Done.";
