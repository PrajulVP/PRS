<?php

use App\Models\ReturnRequest;
use App\Models\RetailerOrder;
use App\Models\DistributorOrder;
use Illuminate\Support\Facades\Log;

try {
    $returns = ReturnRequest::whereNull('sales_manager_id')
        ->orWhereNull('distributor_id')
        ->get();

    echo "Found " . $returns->count() . " returns to update.\n";

    foreach ($returns as $ret) {
        $smId = null;
        $fsId = null;
        $dId = null;

        if ($ret->order_type === 'retailer') {
            $order = RetailerOrder::with(['distributor', 'retailer'])->find($ret->order_id);
            if ($order) {
                $dId = $order->distributor_id;
                $fsId = $order->fieldstaff_id;
                $smId = $order->distributor?->sales_manager_id;
            }
        } else {
            $order = DistributorOrder::find($ret->order_id);
            if ($order) {
                $dId = $order->distributor_id;
                $smId = $order->sales_manager_id;
            }
        }

        $ret->update([
            'distributor_id' => $ret->distributor_id ?? $dId,
            'field_staff_id' => $ret->field_staff_id ?? $fsId,
            'sales_manager_id' => $ret->sales_manager_id ?? $smId,
        ]);
    }

    echo "Successfully updated " . $returns->count() . " return requests with tracking IDs.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
