<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\Inventory::where('id', 8)->update([
    'product_id' => 37,
    'distributor_id' => 21
]);
echo "Fixed corrupted DB inventory row.\n";
