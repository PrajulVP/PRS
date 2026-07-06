<?php
$item = \App\Models\DistributorOrderItem::find(616);
if ($item) {
    $batch = $item->batches()->create([
        'batch_no' => 'TEST_BATCH_616',
        'expiry_date' => '2025-12-31',
        'quantity' => 5,
        'mrp' => 10,
        'ptr' => 8,
        'pts' => 7,
        'taxable_value' => 35,
        'cgst' => 0,
        'sgst' => 0,
        'igst' => 0,
        'net_amount' => 35
    ]);
    echo "Created: " . ($batch ? $batch->id : 'false');
} else {
    echo "Item 616 not found";
}
