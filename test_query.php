<?php
$batches = \DB::table('distributor_order_item_batches')
    ->whereIn('distributor_order_item_id', function($q) {
        $q->select('id')->from('distributor_order_items')->where('distributor_order_id', 207);
    })->get();
echo json_encode($batches);
