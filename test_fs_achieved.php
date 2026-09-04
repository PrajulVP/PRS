<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FieldStaff;
use App\Models\RetailerOrder;
use Carbon\Carbon;

$fs = FieldStaff::whereHas('retailers.orders')->first();
if (!$fs) {
    echo "No field staff found.\n";
    exit;
}
echo "FieldStaff: " . $fs->name . "\n";

// Get all orders for this field staff for the current month
$month = now()->month;
$year = now()->year;

$orders = RetailerOrder::where(function ($q) use ($fs) {
    $q->where('retailer_orders.fieldstaff_id', $fs->id)
        ->orWhereHas('retailer', function ($qr) use ($fs) {
            $qr->where('field_staff_id', $fs->id);
        });
})->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();

echo "Total orders this month: " . $orders->count() . "\n";
foreach($orders as $o) {
    echo "Order: {$o->id} - Status: {$o->status}\n";
}

$achieved = $fs->getCurrentMonthAchieved();
echo "Current Achieved: " . $achieved . "\n";

