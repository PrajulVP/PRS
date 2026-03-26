<?php
namespace App\Traits;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

trait CalculatesPrices
{
    /**
     * Compute detailed price breakdown for a product based on quantity and unit.
     */
    protected function computePriceResponse(Product $product, $quantity, $unit, $priceField = 'ptr', $variant = null)
    {
        $multiplier = 1;
        $normalizedUnit = strtolower($unit);
        
        // Unit conversion logic matching the existing order store methods
        if ($normalizedUnit === 'box') {
            $multiplier = (int)($product->strips_per_box ?? 1);
        } elseif ($normalizedUnit === 'carton') {
            $multiplier = (int)($product->boxes_per_carton ?? 1) * (int)($product->strips_per_box ?? 1);
        } elseif ($normalizedUnit === 'nos' || $normalizedUnit === 'no' || $normalizedUnit === 'unit') {
            $multiplier = 1 / (max(1, (int)($product->units_per_strip ?? 1)));
        }
        
        // Calculate total strips (rounded up as pharma usually sells full strips)
        $totalQtyStrips = ceil($quantity * $multiplier);
        
        $price = (float)$product->$priceField;
        $gstRate = (float)($product->gst ?? 0);
        
        $taxableSubtotal = $totalQtyStrips * $price;
        $totalWithGst = $taxableSubtotal * (1 + ($gstRate / 100));
        $gstAmount = $totalWithGst - $taxableSubtotal;

        // Fetch available variants from inventory (where stock exists)
        $availableVariants = DB::table('inventories')
            ->where('product_id', $product->id)
            ->where('stock', '>', 0)
            ->whereNotNull('variant')
            ->distinct()
            ->pluck('variant');

        return [
            'product_id' => $product->id,
            'product_name' => $product->product_name,
            'has_variants' => (bool)$product->has_variants,
            'available_variants' => $availableVariants,
            'selected_variant' => $variant,
            'input_quantity' => (float)$quantity,
            'input_unit' => $unit,
            'total_quantity_strips' => $totalQtyStrips,
            'unit_price' => round($price, 2),
            'taxable_amount' => round($taxableSubtotal, 2),
            'gst_rate' => $gstRate,
            'gst_amount' => round($gstAmount, 2),
            'total_amount' => round($totalWithGst, 2),
            'currency' => 'INR',
        ];
    }
}
