<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

$controller = new DashboardController();
$startDate = Carbon::now()->startOfMonth();
$endDate = Carbon::now();

// Simulate Admin
echo "Simulating Admin (All Brands):\n";
$reflection = new ReflectionClass(DashboardController::class);
$method = $reflection->getMethod('getBrandSalesDistribution');
$method->setAccessible(true);

$result = $method->invokeArgs($controller, [$startDate, $endDate, null]);

print_r($result);

// Check if all brands from products table are present
$allBrandsInDB = \App\Models\Product::distinct('brand')->pluck('brand')->map(fn($b) => $b ?: 'Standard')->unique()->values()->toArray();
$labels = $result['labels'];

$missing = array_diff($allBrandsInDB, $labels);
if (empty($missing)) {
    echo "\nSUCCESS: All brands are present in labels.\n";
} else {
    echo "\nFAILURE: Missing brands: " . implode(', ', $missing) . "\n";
}

// Check revenue for a brand we know has sales (Sudhneelgiri)
$sudhIndex = array_search('Sudhneelgiri', $result['labels']);
if ($sudhIndex !== false) {
    echo "Sudhneelgiri Revenue: " . $result['values'][$sudhIndex] . "\n";
}
