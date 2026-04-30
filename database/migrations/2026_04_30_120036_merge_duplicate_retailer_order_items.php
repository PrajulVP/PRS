<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $duplicates = DB::table('retailer_order_items')
            ->select('retailer_order_id', 'product_id', 'side', 'size', DB::raw('COUNT(*) as count'))
            ->groupBy('retailer_order_id', 'product_id', 'side', 'size')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $items = DB::table('retailer_order_items')
                ->where('retailer_order_id', $dup->retailer_order_id)
                ->where('product_id', $dup->product_id)
                ->where('side', $dup->side)
                ->where('size', $dup->size)
                ->orderBy('id', 'asc')
                ->get();
            
            $first = $items->shift();
            $totalQty = $first->quantity;
            $totalFree = $first->free_quantity;
            $totalAmt = $first->total_amount;
            
            foreach ($items as $item) {
                $totalQty += $item->quantity;
                $totalFree += $item->free_quantity;
                $totalAmt += $item->total_amount;
                
                // Move any batches if they exist
                DB::table('retailer_order_item_batches')
                    ->where('retailer_order_item_id', $item->id)
                    ->update(['retailer_order_item_id' => $first->id]);
                    
                DB::table('retailer_order_items')->where('id', $item->id)->delete();
            }
            
            DB::table('retailer_order_items')->where('id', $first->id)->update([
                'quantity' => $totalQty,
                'free_quantity' => $totalFree,
                'total_amount' => $totalAmt
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse possible for data merging
    }
};
