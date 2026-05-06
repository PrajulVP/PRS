<?php

use App\Models\ReturnRequest;
use App\Models\RetailerOrder;
use App\Models\DistributorOrder;
use App\Models\Distributor;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$returns = ReturnRequest::whereNull('distributor_id')->get();
echo "Found " . $returns->count() . " returns with NULL distributor_id.\n";

foreach ($returns as $ret) {
    echo "Processing Return ID: {$ret->id} (Code: {$ret->return_code})\n";
    $distributorId = null;
    $fieldStaffId = null;
    $salesManagerId = null;

    if ($ret->order_type === 'retailer') {
        $order = RetailerOrder::find($ret->order_id);
        if ($order) {
            $distributorId = $order->distributor_id;
            if (!$distributorId) {
                // Fallback to retailer's distributor
                $retailer = \App\Models\Retailer::find($order->retailer_id);
                $distributorId = $retailer?->distributor_id;
            }
            $fieldStaffId = $order->fieldstaff_id ?? $order->retailer?->field_staff_id;
            if ($distributorId) {
                $salesManagerId = Distributor::find($distributorId)?->sales_manager_id;
            }
            if (!$salesManagerId) {
                $salesManagerId = $order->retailer?->sales_manager_id;
            }
        }
    } elseif ($ret->order_type === 'distributor') {
        $order = DistributorOrder::find($ret->order_id);
        if ($order) {
            $distributorId = $order->distributor_id;
            $salesManagerId = $order->sales_manager_id ?? $order->distributor?->sales_manager_id;
        }
    }

    if ($distributorId || $fieldStaffId || $salesManagerId) {
        $ret->update([
            'distributor_id' => $ret->distributor_id ?? $distributorId,
            'field_staff_id' => $ret->field_staff_id ?? $fieldStaffId,
            'sales_manager_id' => $ret->sales_manager_id ?? $salesManagerId,
        ]);
        echo "  Updated: Dist: " . ($distributorId ?? 'N/A') . ", Staff: " . ($fieldStaffId ?? 'N/A') . ", Manager: " . ($salesManagerId ?? 'N/A') . "\n";
    } else {
        echo "  Could not find IDs for this return.\n";
    }
}

echo "Backfill complete.\n";
