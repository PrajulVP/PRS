<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!class_exists('\App\Models\ReturnRequest')) return;

        $returns = \App\Models\ReturnRequest::all();
        foreach ($returns as $ret) {
            $smId = null;
            $fsId = null;
            $dId = null;

            if ($ret->order_type === 'retailer') {
                $order = \App\Models\RetailerOrder::with(['distributor'])->find($ret->order_id);
                if ($order) {
                    $dId = $order->distributor_id;
                    $fsId = $order->fieldstaff_id;
                    $smId = $order->distributor?->sales_manager_id;
                }
            } else {
                $order = \App\Models\DistributorOrder::find($ret->order_id);
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
