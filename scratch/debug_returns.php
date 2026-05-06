<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Distributor;
use App\Models\ReturnRequest;

$distributors = Distributor::with('user')->get()->map(function($d) {
    return ['id' => $d->id, 'name' => $d->user->name];
});

echo "Distributors:\n";
print_r($distributors->toArray());

echo "\nReturn Requests Count: " . ReturnRequest::count() . "\n";
echo "Return Requests details:\n";
print_r(ReturnRequest::all(['id', 'return_code', 'distributor_id', 'field_staff_id', 'order_id'])->toArray());

echo "\nRetailer Orders details (for return order IDs):\n";
$orderIds = ReturnRequest::where('order_type', 'retailer')->pluck('order_id')->toArray();
print_r(App\Models\RetailerOrder::whereIn('id', $orderIds)->with('retailer')->get(['id', 'order_code', 'distributor_id', 'retailer_id'])->toArray());
