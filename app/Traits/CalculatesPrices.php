<?php
namespace App\Traits;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

trait CalculatesPrices
{
    /**
     * Compute detailed price breakdown for a product based on quantity and unit.
     */
    protected function computePriceResponse(Product $product, $quantity, $unit, $priceField = 'ptr', $side = null, $size = null)
    {
        // Calculate total strips using the shared conversion helper
        $totalQtyStrips = $this->convertQuantityToStrips($product, $quantity, $unit);
        
        $price = (float)$product->$priceField;
        $gstRate = (float)($product->gst ?? 0);
        
        $taxableSubtotal = $totalQtyStrips * $price;
        $totalWithGst = $taxableSubtotal * (1 + ($gstRate / 100));
        $gstAmount = $totalWithGst - $taxableSubtotal;

        // Fetch available variants
        $availableVariants = $this->getAvailableVariants($product);
        if (empty($availableVariants)) {
            $availableVariants = [];
        }

        // Dynamic Unit Logic matching the web front-end
        $availableUnits = $this->getAvailableUnits($product);

        return [
            'product_id' => $product->id,
            'product_name' => $product->product_name,
            'has_variants' => (bool)$product->has_variants,
            'available_variants' => $availableVariants,
            'selected_side' => $side,
            'selected_size' => $size,
            'available_units' => $availableUnits,
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

    /**
     * Helper to determine available units for a product based on its attributes and naming conventions.
     */
    public function getAvailableUnits(Product $product)
    {
        // Enforce STRICT "Strips" only policy for all tablet-based medicines
        $unitsPerStrip = (int)($product->units_per_strip ?? 1);
        if ($unitsPerStrip > 1) {
            return ['Strips'];
        }

        $pPack = strtolower($product->pack ?? '');
        $pName = strtolower($product->product_name ?? '');
        $boxSizeStr = $product->box_size ?? '';
        
        $isCount = ($boxSizeStr === "");

        if (!$isCount) {
            // Check for keywords that imply the product is sold by individual count/nos
            $keywords = [
                'nos', 'count', 'pair', 'bottle', 'ml', 'gm', 'syp', 'syrup', 
                'drop', 'ointment', 'belt', 'cap', 'binder', 'splint', 
                'brace', 'cuff', 'walker'
            ];
            
            foreach ($keywords as $keyword) {
                if (str_contains($pPack, $keyword) || str_contains($pName, $keyword)) {
                    $isCount = true;
                    break;
                }
            }
        }

        if ($isCount) {
            return ['Nos'];
        }

        // Standard medical strips/boxes/cartons pattern (Excluding 'Nos' for Strips-based items as requested)
        return ['Strips', 'Box', 'Carton'];
    }

    /**
     * Shared helper to convert an input quantity and unit into the base 'Strips' count.
     */
    public function convertQuantityToStrips(Product $product, $quantity, $unit)
    {
        $multiplier = 1;
        $normalizedUnit = strtolower($unit);
        
        if ($normalizedUnit === 'box') {
            $multiplier = (int)($product->strips_per_box ?? 1);
        } elseif ($normalizedUnit === 'carton') {
            $multiplier = (int)($product->boxes_per_carton ?? 1) * (int)($product->strips_per_box ?? 1);
        } elseif ($normalizedUnit === 'nos' || $normalizedUnit === 'no' || $normalizedUnit === 'unit') {
            $multiplier = 1 / (max(1, (int)($product->units_per_strip ?? 1)));
        }
        
        return $quantity * $multiplier;
    }

    /**
     * Helper to extract available variants from the product name string (e.g., "(S/M/L)").
     */
    public function getAvailableVariants(Product $product)
    {
        // 1. Try modern variant_options column first
        if ($product->has_variants && !empty($product->variant_options)) {
            $allValues = [];
            foreach ($product->variant_options as $category => $values) {
                if (is_array($values)) {
                    foreach ($values as $val) {
                        $allValues[] = trim($val);
                    }
                }
            }
            if (!empty($allValues)) {
                return array_values(array_unique($allValues));
            }
        }

        // 2. Fallback to legacy name extraction
        $pName = $product->product_name ?? '';
        
        // Match content between ( ) or [ ] that contains at least one /
        if (preg_match('/\(([^)]*\/[^)]*)\)/', $pName, $matches) || 
            preg_match('/\[([^\]]*\/[^\]]*)\]/', $pName, $matches)) {
            
            $variantString = $matches[1];
            $variants = array_map('trim', explode('/', $variantString));
            return array_values(array_filter($variants));
        }

        return [];
    }

}
