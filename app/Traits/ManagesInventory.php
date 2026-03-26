<?php

namespace App\Traits;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesInventory
{
    use CalculatesPrices;

    /**
     * Unified method to adjust inventory stock.
     * Finds or creates an inventory record based on product, batch, variant, and expiry.
     * 
     * @param int $distributorId
     * @param Product $product
     * @param array $data [quantity, unit, batch_no, expiry_date, variant, operation]
     * @return array [success, inventory, message]
     */
    public function adjustInventoryStock($distributorId, Product $product, array $data)
    {
        $quantity = $data['quantity'] ?? 0;
        $unit = $data['unit'] ?? 'Strips';
        $batchNo = $data['batch_no'] ?? null;
        $expiryDate = $data['expiry_date'] ?? null;
        $variant = $data['variant'] ?? null;
        $operation = $data['operation'] ?? 'add'; // add or subtract

        if (!$batchNo || !$expiryDate) {
            return ['success' => false, 'message' => 'Batch number and expiry date are required.'];
        }

        // 1. Convert quantity to base unit (Strips)
        $stripsToAdjust = $this->convertQuantityToStrips($product, $quantity, $unit);

        if ($operation === 'subtract' && $stripsToAdjust > 0) {
            $stripsToAdjust = -$stripsToAdjust;
        }

        DB::beginTransaction();
        try {
            // 2. Find existing record
            $inventory = Inventory::where('product_id', $product->id)
                ->where('distributor_id', $distributorId)
                ->where('batch_no', $batchNo)
                ->where('variant', $variant)
                ->where('expiry_date', $expiryDate)
                ->first();

            if (!$inventory) {
                if ($operation === 'subtract') {
                    return ['success' => false, 'message' => 'Cannot subtract stock from a non-existent batch.'];
                }

                // 3. Create initial record if it doesn't exist (for add operation)
                $pName = $product->product_name;
                if ($variant) {
                    $pName .= ' [' . $variant . ']';
                }

                $inventory = Inventory::create([
                    'distributor_product_code' => $product->product_code ?? 'NA-' . $product->id,
                    'product_id' => $product->id,
                    'product_name' => $pName,
                    'distributor_id' => $distributorId,
                    'stock' => 0, // Will be updated below
                    'batch_no' => $batchNo,
                    'variant' => $variant,
                    'expiry_date' => $expiryDate,
                ]);

                $changeType = 'initial_stock';
            } else {
                $changeType = ($operation === 'add') ? 'restock' : 'stock_reduction';
            }

            // 4. Validate enough stock for subtraction
            if ($operation === 'subtract' && ($inventory->stock + $stripsToAdjust) < 0) {
                return ['success' => false, 'message' => 'Not enough stock available in this batch.'];
            }

            // 5. Update stock
            $previousStock = $inventory->stock;
            $inventory->stock += $stripsToAdjust;
            $inventory->save();

            // 6. Record History
            StockHistory::create([
                'inventory_id' => $inventory->id,
                'user_id' => Auth::id(),
                'previous_stock' => $previousStock,
                'new_stock' => $inventory->stock,
                'quantity_change' => $stripsToAdjust,
                'change_type' => $changeType,
                'remarks' => ucfirst($operation) . ' ' . $quantity . ' ' . $unit . ' via ' . (request()->is('api/*') ? 'API' : 'Web')
            ]);

            DB::commit();
            return [
                'success' => true,
                'inventory' => $inventory,
                'message' => 'Stock updated successfully.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error adjusting stock: ' . $e->getMessage()];
        }
    }
}
