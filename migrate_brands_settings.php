<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$returnableStr = \App\Models\Setting::getValue('returnable_brands', '');
$loyaltyStr = \App\Models\Setting::getValue('loyalty_brands', '');

$returnableBrands = array_map('trim', explode(',', $returnableStr));
$loyaltyBrands = array_map('trim', explode(',', $loyaltyStr));

// Set is_returnable
if (!empty($returnableStr)) {
    \App\Models\Brand::whereIn('name', $returnableBrands)->update(['is_returnable' => true]);
}

// Set is_loyalty_enabled
if (!empty($loyaltyStr)) {
    \App\Models\Brand::whereIn('name', $loyaltyBrands)->update(['is_loyalty_enabled' => true]);
}

echo "Migration script completed.\n";
