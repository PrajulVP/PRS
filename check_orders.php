<?php
use App\Models\RetailerOrder;
use App\Models\User;

$orders = RetailerOrder::with(['retailer.user', 'retailer.salesManager.user', 'retailer.fieldStaff.user', 'distributor.user'])->get();
echo "Total Orders: " . $orders->count() . "\n";
foreach($orders as $order) {
    echo "ID: {$order->id}, Code: {$order->order_code}, Status: {$order->status}, Payment: {$order->payment_status}\n";
    echo "  Retailer: " . ($order->retailer?->shop_name ?? 'NULL') . " (ID: " . ($order->retailer_id ?? 'N/A') . ")\n";
    echo "  Distributor: " . ($order->distributor?->user?->name ?? 'NULL') . " (ID: " . ($order->distributor_id ?? 'N/A') . ")\n";
    echo "  Sales Manager: " . ($order->retailer?->salesManager?->user?->name ?? 'NULL') . "\n";
    echo "  Field Staff: " . ($order->retailer?->fieldStaff?->user?->name ?? 'NULL') . "\n";
    echo "---------------------------------\n";
}
