<?php
// 1. Update Product.php model
$file = 'c:\wamp64\www\prs\app\Models\Product.php';
$content = file_get_contents($file);

// Remove 'brand' from fillable
$content = str_replace("        'brand',\n", "", $content);

// Add brandModel relation and getBrandAttribute
$relation = <<<'EOD'
    public function brandModel()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function getBrandAttribute()
    {
        return $this->brandModel ? $this->brandModel->name : null;
    }
EOD;

// Replace the old getBrandAttribute
$oldGetBrandAttribute = <<<'EOD'
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
EOD;

$content = str_replace($oldGetBrandAttribute, $relation, $content);

// Fix booted method
$oldBooted = <<<'EOD'
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
EOD;

$newBooted = <<<'EOD'
            // Rule 1: SN- prefix -> Sudhneelgiri (Wellness/Herbal)
            if (str_starts_with($code, 'SN-')) {
                $product->brand_id = \App\Models\Brand::where('name', 'Sudhneelgiri')->value('id');
            }
            // Rule 2: AS- prefix -> Atomshield (Orthopedic/Travel)
            elseif (str_starts_with($code, 'AS-')) {
                $product->brand_id = \App\Models\Brand::where('name', 'Atomshield')->value('id');
            }
            // Rule 3: Medicine brand rules (Atomets)
            elseif (empty($product->brand_id)) {
                $searchable = $code . ' ' . $name;
                $medicinePatterns = ['ATOM', 'TOM', 'TENE', 'TELM', 'PANT', 'GLIM', 'MET', 'ACE', 'FEN', 'DICL', 'OME', 'LEVO'];
                
                foreach ($medicinePatterns as $pattern) {
                    if (str_contains($searchable, $pattern)) {
                        $product->brand_id = \App\Models\Brand::where('name', 'Atomets')->value('id');
                        break;
                    }
                }
            }
EOD;

$content = str_replace($oldBooted, $newBooted, $content);

// Fix getIsReturnableAttribute
$oldGetIsReturnableAttribute = <<<'EOD'
    public function getIsReturnableAttribute($value)
    {
        if (!$value) {
            return false;
        }

        $brandName = $this->brand;
        $brandModel = \App\Models\Brand::where('name', $brandName)->first();
        
        if ($brandModel) {
            return (bool) $brandModel->is_returnable;
        }

        return false;
    }
EOD;

$newGetIsReturnableAttribute = <<<'EOD'
    public function getIsReturnableAttribute($value)
    {
        if (!$value) {
            return false;
        }

        $brandModel = $this->brandModel;
        
        if ($brandModel) {
            return (bool) $brandModel->is_returnable;
        }

        return false;
    }
EOD;

$content = str_replace($oldGetIsReturnableAttribute, $newGetIsReturnableAttribute, $content);

file_put_contents($file, $content);


// 2. Update ProductController.php
$file = 'c:\wamp64\www\prs\app\Http\Controllers\ProductController.php';
$content = file_get_contents($file);

// Fix index brandStats query
$oldBrandStats = <<<'EOD'
        $brandStats = Product::select('brand', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('brand')
            ->get()
            ->map(function($item) {
                return [
                    'brand' => $item->brand ?: 'Standard',
                    'count' => $item->total
                ];
            });
EOD;

$newBrandStats = <<<'EOD'
        $brandStats = Product::select('brand_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('brand_id')
            ->with('brandModel')
            ->get()
            ->map(function($item) {
                return [
                    'brand' => $item->brandModel ? $item->brandModel->name : 'Standard',
                    'count' => $item->total
                ];
            });
EOD;

$content = str_replace($oldBrandStats, $newBrandStats, $content);

// Remove 'brand' => ... in updateOrCreate inside import()
$oldUpdateOrCreate = <<<'EOD'
                            'brand' => !empty($productData['brand']) ? trim($productData['brand']) : null,
                            'brand_id' => $brandId,
EOD;

$newUpdateOrCreate = <<<'EOD'
                            'brand_id' => $brandId,
EOD;

$content = str_replace($oldUpdateOrCreate, $newUpdateOrCreate, $content);


// Fix getByBrand query
$oldGetByBrand = <<<'EOD'
        $products = Product::where('brand', $brand)
            ->select('id', 'product_name', 'product_code', 'is_returnable', 'brand')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'product_name' => $p->product_name,
                    'product_code' => $p->product_code,
                    'is_returnable' => $p->is_returnable,
                    'brand' => $p->brand
                ];
            });
EOD;

$newGetByBrand = <<<'EOD'
        $brandModel = \App\Models\Brand::where('name', $brand)->first();
        $brandId = $brandModel ? $brandModel->id : 0;
        $products = Product::where('brand_id', $brandId)
            ->select('id', 'product_name', 'product_code', 'is_returnable', 'brand_id')
            ->with('brandModel')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'product_name' => $p->product_name,
                    'product_code' => $p->product_code,
                    'is_returnable' => $p->is_returnable,
                    'brand' => $p->brandModel ? $p->brandModel->name : null
                ];
            });
EOD;

$content = str_replace($oldGetByBrand, $newGetByBrand, $content);


// Fix updateReturnable query
$oldUpdateReturnable = <<<'EOD'
        Product::where('brand', $request->brand)->update(['is_returnable' => $request->is_returnable]);
EOD;

$newUpdateReturnable = <<<'EOD'
        $brandModel = \App\Models\Brand::where('name', $request->brand)->first();
        if ($brandModel) {
            Product::where('brand_id', $brandModel->id)->update(['is_returnable' => $request->is_returnable]);
        }
EOD;

$content = str_replace($oldUpdateReturnable, $newUpdateReturnable, $content);

file_put_contents($file, $content);

echo "Patched files to completely handle 'brand' column removal.\n";
