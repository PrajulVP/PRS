<?php require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$inventories = App\Models\Inventory::whereIn('product_id', [15, 17])->get(['id', 'product_id', 'batch_no', 'side', 'size', 'quantity']);
foreach($inventories as $inv) {
    echo str_pad($inv->product_id, 3) . ' | ' . str_pad($inv->batch_no, 15) . ' | ' . str_pad($inv->side ?: '-', 3) . ' | ' . str_pad($inv->size ?: '-', 3) . ' | ' . $inv->quantity . "\n";
}
