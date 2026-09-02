<?php
$file = 'c:\wamp64\www\prs\app\Http\Controllers\ProductController.php';
$content = file_get_contents($file);

// 1. Update store()
$storeOld = <<<'EOD'
        $isBrandReturnable = 0;
        if (!empty($request->brand)) {
            $brandModel = \App\Models\Brand::where('name', $request->brand)->first();
            if ($brandModel && $brandModel->is_returnable) {
                $isBrandReturnable = 1;
            }
        }
EOD;

$storeNew = <<<'EOD'
        $isBrandReturnable = 0;
        if (!empty($request->brand)) {
            $brandModel = \App\Models\Brand::where('name', $request->brand)->first();
            if ($brandModel) {
                $data['brand_id'] = $brandModel->id;
                if ($brandModel->is_returnable) {
                    $isBrandReturnable = 1;
                }
            }
        }
EOD;

$content = str_replace($storeOld, $storeNew, $content);

// 2. Update update()
$updateOld = <<<'EOD'
        $data = $request->all();
        
        if (!isset($data['loyalty_point_percentage']) || $data['loyalty_point_percentage'] === null) {
EOD;

$updateNew = <<<'EOD'
        $data = $request->all();
        
        if (!empty($request->brand)) {
            $brandModel = \App\Models\Brand::where('name', $request->brand)->first();
            if ($brandModel) {
                $data['brand_id'] = $brandModel->id;
            }
        }
        
        if (!isset($data['loyalty_point_percentage']) || $data['loyalty_point_percentage'] === null) {
EOD;

$content = str_replace($updateOld, $updateNew, $content);

// 3. Update import() logic
// We need to move auto-register up, but wait - there's a simpler way. Just resolve brand_id right before updateOrCreate.
// Old code has:
/*
                try {
                    // Smarter Matching Strategy:
*/
$importMatchOld = <<<'EOD'
                try {
                    // Smarter Matching Strategy:
EOD;

$importMatchNew = <<<'EOD'
                try {
                    // Auto-register or find imported brand in DB
                    $importedBrand = !empty($productData['brand']) ? trim($productData['brand']) : null;
                    $brandId = null;
                    if ($importedBrand) {
                        $brandModel = \App\Models\Brand::where('name', $importedBrand)->first();
                        if (!$brandModel) {
                            $brandModel = \App\Models\Brand::create([
                                'name' => $importedBrand,
                                'description' => 'Imported Brand',
                                'icon' => 'fa-tag',
                                'layout_type' => 'general'
                            ]);
                            
                            // Sync legacy product_brands setting
                            $names = \App\Models\Brand::pluck('name')->implode(',');
                            \App\Models\Setting::setValue('product_brands', $names);
                        }
                        $brandId = $brandModel->id;
                    }

                    // Smarter Matching Strategy:
EOD;

$content = str_replace($importMatchOld, $importMatchNew, $content);

// Then in updateOrCreate:
$updateOrCreateOld = <<<'EOD'
                            'brand' => !empty($productData['brand']) ? trim($productData['brand']) : null,
                            'pack' => !empty($productData['pack']) ? trim($productData['pack']) : null,
EOD;

$updateOrCreateNew = <<<'EOD'
                            'brand' => !empty($productData['brand']) ? trim($productData['brand']) : null,
                            'brand_id' => $brandId,
                            'pack' => !empty($productData['pack']) ? trim($productData['pack']) : null,
EOD;

$content = str_replace($updateOrCreateOld, $updateOrCreateNew, $content);

// Remove the old auto-register block
$autoRegisterOld = <<<'EOD'
                    // Auto-register imported brand in DB if it is new
                    $importedBrand = !empty($productData['brand']) ? trim($productData['brand']) : null;
                    if ($importedBrand) {
                        $exists = \App\Models\Brand::where('name', $importedBrand)->exists();
                        if (!$exists) {
                            \App\Models\Brand::create([
                                'name' => $importedBrand,
                                'description' => 'Imported Brand',
                                'icon' => 'fa-tag',
                                'layout_type' => 'general'
                            ]);
                            
                            // Sync legacy product_brands setting
                            $names = \App\Models\Brand::pluck('name')->implode(',');
                            \App\Models\Setting::setValue('product_brands', $names);
                        }
                    }
EOD;

$content = str_replace($autoRegisterOld, '', $content);

file_put_contents($file, $content);
echo "Patched ProductController.php\n";
