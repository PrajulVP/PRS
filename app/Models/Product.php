<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_code',
        'product_name',
        'brand',
        'generic_name',
        'pack',
        'strip_size',
        'box_size',
        'carton_size',
        'hsn_code',
        'mrp',
        'ptr',
        'pts',
        'taxable_value',
        'gst',
        'net_amount',
        'loyalty_point_percentage',
        'units_per_strip',
        'strips_per_box',
        'boxes_per_carton',
        'has_variants',
        'variant_options',
        'is_returnable',
    ];

    protected $casts = [
        'mrp' => 'decimal:2',
        'ptr' => 'decimal:2',
        'pts' => 'decimal:2',
        'taxable_value' => 'decimal:2',
        'gst' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'loyalty_point_percentage' => 'decimal:2',
        'variant_options' => 'array',
        'is_returnable' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::saving(function ($product) {
            $code = strtoupper($product->product_code ?? '');
            $name = strtoupper($product->product_name ?? '');

            // Rule 1: SN- prefix -> Sudhneelgiri (Wellness/Herbal)
            if (str_starts_with($code, 'SN-')) {
                $product->brand = 'Sudhneelgiri';
            }
            // Rule 2: AS- prefix -> Atomshield (Orthopedic/Travel)
            elseif (str_starts_with($code, 'AS-')) {
                $product->brand = 'Atomshield';
            }
            // Rule 3: Medicine brand rules (Atomets)
            // We also re-categorize from legacy names like 'Atomlife' or 'Generic' if patterns match
            elseif (empty($product->brand) || in_array($product->brand, ['Atomlife', 'Generic', 'Other', 'NULL'])) {
                $searchable = $code . ' ' . $name;
                $medicinePatterns = ['ATOM', 'TOM', 'TENE', 'TELM', 'PANT', 'GLIM', 'MET', 'ACE', 'FEN', 'DICL', 'OME', 'LEVO'];
                
                foreach ($medicinePatterns as $pattern) {
                    if (str_contains($searchable, $pattern)) {
                        $product->brand = 'Atomets';
                        break;
                    }
                }
            }
        });
    }

    // Relation with distributors via inventories
    public function distributors()
    {
        return $this->belongsToMany(Distributor::class, 'inventories', 'product_id', 'distributor_id')
            ->withPivot('stock')
            ->withTimestamps();
    }

    /**
     * Enforce standard approach: Returnable status depends on both the individual product toggle
     * and whether the brand is currently enabled in settings under 'returnable_brands'.
     */
    public function getIsReturnableAttribute($value)
    {
        if (!$value) {
            return false;
        }

        $returnableBrandsRaw = \App\Models\Setting::getValue('returnable_brands', '');
        $returnableBrands = array_map('trim', explode(',', $returnableBrandsRaw));
        return in_array($this->brand, $returnableBrands);
    }

    public function getBrandAttribute($value)
    {
        if (!$value) {
            return $value;
        }

        $masterBrands = \App\Models\Brand::pluck('name')->toArray();

        foreach ($masterBrands as $masterBrand) {
            if (strcasecmp($masterBrand, $value) === 0) {
                return $masterBrand;
            }
        }

        return $value;
    }
}

