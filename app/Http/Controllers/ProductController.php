<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            /** @var \App\Models\User $user */
            $user = \Illuminate\Support\Facades\Auth::user();
            if (!$user) return redirect()->route('login');

            if ($user->hasAnyRole(['admin', 'superadmin'])) {
                return $next($request);
            }

            $routeName = $request->route()->getName();

            // Broad check for index/show
            if (in_array($routeName, ['products.index', 'products.show'])) {
                if ($user->hasPermissionToCategory('products', 'view')) {
                    return $next($request);
                }
            }

            // check for creation/import
            if (in_array($routeName, ['products.create', 'products.store', 'products.import', 'products.download-template'])) {
                if ($user->hasPermissionToCategory('products', 'add')) {
                    return $next($request);
                }
            }

            // check for editing
            if (in_array($routeName, ['products.edit', 'products.update'])) {
                if ($user->hasPermissionToCategory('products', 'edit')) {
                    return $next($request);
                }
            }

            // check for deletion
            if ($routeName === 'products.destroy') {
                if ($user->hasPermissionToCategory('products', 'delete')) {
                    return $next($request);
                }
            }

            abort(403, 'Unauthorized action. You do not have permission to manage products.');
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $totalData = Product::count();

            $query = Product::query();

            // Apply search filter
            if ($request->has('search') && !empty($request->input('search')['value'])) {
                $searchValue = $request->input('search')['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('product_code', 'like', "%{$searchValue}%")
                        ->orWhere('product_name', 'like', "%{$searchValue}%")
                        ->orWhere('generic_name', 'like', "%{$searchValue}%");
                });
            }

            $totalFiltered = $query->count();

            // Apply order (sorting)
            if ($request->has('order') && !empty($request->input('order'))) {
                $columnIndex = $request->input('order')[0]['column'];
                $columnName = $request->input('columns')[$columnIndex]['data'];
                $sortDirection = $request->input('order')[0]['dir'];

                $query->orderBy($columnName, $sortDirection);
            } else {
                $query->orderBy('id', 'desc'); // Default sort
            }

            // Apply pagination
            $start = $request->input('start');
            $length = $request->input('length');
            $products = $query->offset($start)->limit($length)->get();

            $formattedProducts = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->product_name,
                    'generic_name' => $product->generic_name,
                    'pack' => $product->pack,
                    'strip_size' => $product->strip_size,
                    'box_size' => $product->box_size,
                    'carton_size' => $product->carton_size,
                    'hsn_code' => $product->hsn_code,
                    'mrp' => number_format((float)$product->mrp, 2),
                    'ptr' => number_format((float)$product->ptr, 2),
                    'pts' => number_format((float)$product->pts, 2),
                    'loyalty_point_percentage' => $product->loyalty_point_percentage,
                    'units_per_strip' => $product->units_per_strip ?? 1,
                    'strips_per_box' => $product->strips_per_box ?? 1,
                    'boxes_per_carton' => $product->boxes_per_carton ?? 1,
                    'has_variants' => $product->has_variants,
                    'variant_options' => $product->variant_options,
                    'variant_options' => $product->variant_options,
                    'brand' => $product->brand,
                    'is_returnable' => $product->is_returnable,
                    'is_free_eligible' => $product->is_free_eligible,
                    'free_qty_buy' => $product->free_qty_buy,
                    'free_qty_get' => $product->free_qty_get,
                    'actions' => null,
                ];
            });

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalData,
                'recordsFiltered' => $totalFiltered,
                'data' => $formattedProducts,
            ]);
        }

        $brands = \App\Models\Brand::orderBy('id')->get();
        $availableBrands = $brands->pluck('name')->toArray();

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

        $totalProducts = Product::count();

        $type_medical_title = \App\Models\Setting::getValue('type_medical_title', 'ATOMEDS');
        $type_medical_desc = \App\Models\Setting::getValue('type_medical_desc', 'Medicines');
        $type_ortho_title = \App\Models\Setting::getValue('type_ortho_title', 'ATOMSHIELD');
        $type_ortho_desc = \App\Models\Setting::getValue('type_ortho_desc', 'Surgical and Orthopedic Supports');
        $type_general_title = \App\Models\Setting::getValue('type_general_title', 'SUDHNEELGIRI');
        $type_general_desc = \App\Models\Setting::getValue('type_general_desc', 'Herbals');

        return view('admin.products.index', compact(
            'availableBrands', 'brandStats', 'totalProducts',
            'type_medical_title', 'type_medical_desc',
            'type_ortho_title', 'type_ortho_desc',
            'type_general_title', 'type_general_desc',
            'brands'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_code' => 'nullable|string|max:255',
            'product_name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'pack' => 'nullable|string|max:255',
            'strip_size' => 'nullable|string|max:255',
            'box_size' => 'nullable|string|max:255',
            'carton_size' => 'nullable|string|max:255',
            'hsn_code' => 'nullable|string|max:255',
            'mrp' => 'required|numeric|min:0',
            'ptr' => 'required|numeric|min:0',
            'pts' => 'required|numeric|min:0',
            'loyalty_point_percentage' => 'nullable|numeric|min:0',
            'brand' => 'nullable|string|max:255',
            'is_returnable' => 'nullable|boolean',
            'is_free_eligible' => 'nullable|boolean',
            'free_qty_buy' => 'nullable|integer|min:0',
            'free_qty_get' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        
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
        
        if (!isset($data['is_returnable'])) {
            $data['is_returnable'] = $isBrandReturnable;
        }

        if (!isset($data['loyalty_point_percentage']) || $data['loyalty_point_percentage'] === null) {
            $data['loyalty_point_percentage'] = 0;
        }

        $data['is_free_eligible'] = $request->has('is_free_eligible');
        if (!$data['is_free_eligible']) {
            $data['free_qty_buy'] = null;
            $data['free_qty_get'] = null;
        }


        // Sync numeric fields
        $data['units_per_strip'] = $this->parseNumber($request->strip_size);
        $data['strips_per_box'] = $this->parseNumber($request->box_size);
        $data['boxes_per_carton'] = $this->parseNumber($request->carton_size);

        // Process variant options — Side and Size are independent; either can exist without the other
        $variantOptions = [];
        if ($request->filled('variant_name_1') && $request->filled('variant_values_1')) {
            $v1 = $request->variant_values_1;
            $vals = is_array($v1) ? array_map('trim', $v1) : array_map('trim', explode(',', $v1));
            $vals = array_filter($vals);
            if (!empty($vals)) {
                $variantOptions[trim($request->variant_name_1)] = array_values($vals);
            }
        }
        if ($request->filled('variant_name_2') && $request->filled('variant_values_2')) {
            $v2 = $request->variant_values_2;
            $vals2 = is_array($v2) ? array_map('trim', $v2) : array_map('trim', explode(',', $v2));
            $vals2 = array_filter($vals2);
            if (!empty($vals2)) {
                $variantOptions[trim($request->variant_name_2)] = array_values($vals2);
            }
        }
        $data['has_variants'] = !empty($variantOptions);
        $data['variant_options'] = !empty($variantOptions) ? $variantOptions : null;

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'product_code' => 'nullable|string|max:255',
            'product_name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'pack' => 'nullable|string|max:255',
            'strip_size' => 'nullable|string|max:255',
            'box_size' => 'nullable|string|max:255',
            'carton_size' => 'nullable|string|max:255',
            'hsn_code' => 'nullable|string|max:255',
            'mrp' => 'required|numeric|min:0',
            'ptr' => 'required|numeric|min:0',
            'pts' => 'required|numeric|min:0',
            'loyalty_point_percentage' => 'nullable|numeric|min:0',
            'brand' => 'nullable|string|max:255',
            'is_returnable' => 'nullable|boolean',
            'is_free_eligible' => 'nullable|boolean',
            'free_qty_buy' => 'nullable|integer|min:0',
            'free_qty_get' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        
        if (!empty($request->brand)) {
            $brandModel = \App\Models\Brand::where('name', $request->brand)->first();
            if ($brandModel) {
                $data['brand_id'] = $brandModel->id;
            }
        }
        
        if (!isset($data['loyalty_point_percentage']) || $data['loyalty_point_percentage'] === null) {
            $data['loyalty_point_percentage'] = 0;
        }

        $data['is_free_eligible'] = $request->has('is_free_eligible');
        if (!$data['is_free_eligible']) {
            $data['free_qty_buy'] = null;
            $data['free_qty_get'] = null;
        }


        // Sync numeric fields
        $data['units_per_strip'] = $this->parseNumber($request->strip_size);
        $data['strips_per_box'] = $this->parseNumber($request->box_size);
        $data['boxes_per_carton'] = $this->parseNumber($request->carton_size);

        // Process variant options — Side and Size are independent; either can exist without the other
        $variantOptions = [];
        if ($request->filled('variant_name_1') && $request->filled('variant_values_1')) {
            $v1 = $request->variant_values_1;
            $vals = is_array($v1) ? array_map('trim', $v1) : array_map('trim', explode(',', $v1));
            $vals = array_filter($vals);
            if (!empty($vals)) {
                $variantOptions[trim($request->variant_name_1)] = array_values($vals);
            }
        }
        if ($request->filled('variant_name_2') && $request->filled('variant_values_2')) {
            $v2 = $request->variant_values_2;
            $vals2 = is_array($v2) ? array_map('trim', $v2) : array_map('trim', explode(',', $v2));
            $vals2 = array_filter($vals2);
            if (!empty($vals2)) {
                $variantOptions[trim($request->variant_name_2)] = array_values($vals2);
            }
        }
        $data['has_variants'] = !empty($variantOptions);
        $data['variant_options'] = !empty($variantOptions) ? $variantOptions : null;

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products_import_template.csv"',
        ];

        $columns = [
            'product_code',
            'product_name',
            'generic_name',
            'pack',
            'sides',
            'sizes',
            'brand',
            'hsn_code',
            'strip_size',
            'box_size',
            'carton_size',
            'mrp',
            'ptr',
            'pts',
            'loyalty_point_percentage'
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:csv,txt|max:10240', // 10MB max
        ]);

        $file = $request->file('import_file');

        $rowCount = 1;
        $successCount = 0;
        $errors = [];

        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            $header = fgetcsv($handle, 0, ","); // length 0 for no limit

            if (!$header) {
                return redirect()->route('products.index')->with('error', 'Empty CSV file.');
            }

            // Normalizing headers and mapping aliases
            $mapping = [
                'product_c' => 'product_code',
                'product_code' => 'product_code',
                'product_n' => 'product_name',
                'product_name' => 'product_name',
                'generic_n' => 'generic_name',
                'generic_name' => 'generic_name',
                'carton_siz' => 'carton_size',
                'carton_size' => 'carton_size',
                'loyalty_po' => 'loyalty_point_percentage',
                'loyalty_point_percentage' => 'loyalty_point_percentage',
                'pts' => 'pts',
                'ptr' => 'ptr',
                'mrp' => 'mrp',
                'brand' => 'brand',
                'hsn_code' => 'hsn_code',
                'strip_size' => 'strip_size',
                'box_size' => 'box_size',
                'pack' => 'pack',
                'sides' => 'sides',
                'side' => 'sides',
                'sizes' => 'sizes',
                'size' => 'sizes'
            ];

            $header = array_map(function ($h) use ($mapping) {
                $clean = strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)));
                $clean = str_replace(' ', '_', $clean);
                return $mapping[$clean] ?? $clean;
            }, $header);

            // Essential columns check
            $required = ['product_name', 'mrp'];
            $missing = array_diff($required, $header);
            if (!empty($missing)) {
                return redirect()->route('products.index')->with('error', 'Missing required columns: ' . implode(', ', $missing) . '. Please check your CSV headers.');
            }

            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $rowCount++;
                
                // Settings check removed as it's handled via Brand model directly                
                // Handle trailing empty columns in data or header
                if (count($header) > count($data)) {
                    $data = array_pad($data, count($header), null);
                } elseif (count($header) < count($data)) {
                    $data = array_slice($data, 0, count($header));
                }

                $productData = array_combine($header, $data);

                // Basic validation for required fields
                if (empty($productData['product_name'])) {
                    $errors[] = "Row $rowCount: Product Name is empty.";
                    continue;
                }

                if (!isset($productData['mrp']) || trim($productData['mrp']) === '') {
                    $errors[] = "Row $rowCount: MRP is missing for '{$productData['product_name']}'.";
                    continue;
                }

                $productCode = !empty($productData['product_code']) ? trim($productData['product_code']) : null;

                // Parsing logic
                $unitsPerStrip = 1;
                if (!empty($productData['strip_size'])) {
                    if (preg_match('/(\d+)/', $productData['strip_size'], $m)) {
                        $unitsPerStrip = (int)$m[1];
                    }
                }

                $stripsPerBox = 1;
                if (!empty($productData['box_size'])) {
                    $boxSizeParts = [];
                    if (preg_match_all('/(\d+)/', $productData['box_size'], $m)) {
                        $boxSizeParts = array_map('intval', $m[1]);
                    }
                    
                    if (count($boxSizeParts) > 1) {
                        // If the last number matches unitsPerStrip, it's likely (Strips x Packing x Units)
                        // e.g., 10x3x10 for 30 strips of 10.
                        if (end($boxSizeParts) === $unitsPerStrip) {
                            $stripsPerBox = 1;
                            for ($i = 0; $i < count($boxSizeParts) - 1; $i++) {
                                $stripsPerBox *= $boxSizeParts[$i];
                            }
                        } else {
                            // Otherwise just multiply everything
                            $stripsPerBox = array_product($boxSizeParts);
                        }
                    } elseif (count($boxSizeParts) === 1) {
                        $stripsPerBox = $boxSizeParts[0];
                    }
                }

                $boxesPerCarton = 1;
                if (!empty($productData['carton_size'])) {
                    if (preg_match('/(\d+)/', $productData['carton_size'], $m)) {
                        $boxesPerCarton = (int)$m[1];
                    }
                }

                // has_variants detection (S/M/L patterns)
                $hasVariants = false;
                $variantOptions = [];

                // Process Sides
                if (!empty($productData['sides'])) {
                    $hasVariants = true;
                    $vals = array_map('trim', preg_split('/[,\/]/', $productData['sides']));
                    $variantOptions['Side'] = array_filter($vals);
                }

                // Process Sizes
                if (!empty($productData['sizes'])) {
                    $hasVariants = true;
                    $vals2 = array_map('trim', preg_split('/[,\/]/', $productData['sizes']));
                    $variantOptions['Size'] = array_filter($vals2);
                }

                // Fallback: name-based detection if structured data is missing
                if (!$hasVariants) {
                    if (preg_match('/\([SML\/]+\)/i', $productData['product_name']) || preg_match('/(S|M|L|XL|XXL|XXXL)/i', $productData['product_name'])) {
                        if (str_contains($productData['product_name'], 'Knee cap') || str_contains($productData['product_name'], 'Ankle') || str_contains($productData['product_name'], 'Belt')) {
                            $hasVariants = true;
                        }
                    }
                }

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
                    // 1. If code exists, match by code.
                    // 2. If no code, match by Name + Generic Name + Pack to distinguish different products.
                    $matchCriteria = [];
                    if (!empty($productCode)) {
                        $matchCriteria = ['product_code' => $productCode];
                    } else {
                        $matchCriteria = [
                            'product_name' => trim($productData['product_name']),
                            'generic_name' => !empty($productData['generic_name']) ? trim($productData['generic_name']) : null,
                            'pack' => !empty($productData['pack']) ? trim($productData['pack']) : null
                        ];
                        
                        // If there are different sizes/sides for the exact same name, don't overwrite them
                        if (!empty($variantOptions)) {
                            $matchCriteria['variant_options'] = json_encode($variantOptions);
                        }
                    }

                    // Create or Update
                    $p = Product::updateOrCreate(
                        $matchCriteria,
                        [
                            'product_code' => $productCode,
                            'product_name' => trim($productData['product_name']),
                            'generic_name' => !empty($productData['generic_name']) ? trim($productData['generic_name']) : null,
                            'brand_id' => $brandId,
                            'pack' => !empty($productData['pack']) ? trim($productData['pack']) : null,
                            'strip_size' => !empty($productData['strip_size']) ? trim($productData['strip_size']) : null,
                            'units_per_strip' => $unitsPerStrip,
                            'box_size' => !empty($productData['box_size']) ? trim($productData['box_size']) : null,
                            'strips_per_box' => $stripsPerBox,
                            'carton_size' => !empty($productData['carton_size']) ? trim($productData['carton_size']) : null,
                            'boxes_per_carton' => $boxesPerCarton,
                            'hsn_code' => !empty($productData['hsn_code']) ? trim($productData['hsn_code']) : null,
                            'has_variants' => $hasVariants ? 1 : 0,
                            'variant_options' => !empty($variantOptions) ? $variantOptions : null,
                            'mrp' => (float)$productData['mrp'],
                            'ptr' => !empty($productData['ptr']) ? (float)$productData['ptr'] : 0,
                            'pts' => !empty($productData['pts']) ? (float)$productData['pts'] : 0,
                            'loyalty_point_percentage' => !empty($productData['loyalty_point_percentage']) ? (float)$productData['loyalty_point_percentage'] : 0,
                        ]
                    );

                    // If it was newly created, set its returnable status based on the brand
                    if ($p->wasRecentlyCreated) {
                        $isBrandReturnable = 0;
                        if (!empty($productData['brand'])) {
                            $brandModel = \App\Models\Brand::where('name', trim($productData['brand']))->first();
                            if ($brandModel && $brandModel->is_returnable) {
                                $isBrandReturnable = 1;
                            }
                        }
                        $p->is_returnable = $isBrandReturnable;
                        $p->save();
                    }
                    $successCount++;


                } catch (\Exception $e) {
                    $errors[] = "Row $rowCount: " . $e->getMessage();
                }
            }
            fclose($handle);
        }

        if (count($errors) > 0) {
            return redirect()->route('products.index')->with('success', "Imported $successCount products. ")->with('error', "Failed: " . implode(" | ", array_slice($errors, 0, 5)) . (count($errors) > 5 ? "..." : ""));
        }

        return redirect()->route('products.index')->with('success', "$successCount products imported successfully.");
    }

    public function getByBrand($brand)
    {
        $brandModel = \App\Models\Brand::where('name', $brand)->first();
        $brandId = $brandModel ? $brandModel->id : 0;
        $products = Product::where('brand_id', $brandId)
            ->select('id', 'product_name', 'product_code', 'is_returnable', 'brand_id')
            ->with('brandModel')
            ->orderBy('product_name')
            ->get();
        return response()->json($products);
    }

    public function toggleReturnable(Product $product)
    {
        $product->is_returnable = !$product->is_returnable;
        $product->save();
        return response()->json(['success' => true, 'is_returnable' => $product->is_returnable]);
    }

    public function bulkBrandReturnable(Request $request)
    {
        $request->validate([
            'brand' => 'required|string',
            'is_returnable' => 'required|boolean'
        ]);

        $brandModel = \App\Models\Brand::where('name', $request->brand)->first();
        if ($brandModel) {
            Product::where('brand_id', $brandModel->id)->update(['is_returnable' => $request->is_returnable]);
        }

        return response()->json(['success' => 'Products for brand ' . $request->brand . ' updated successfully.']);
    }

    private function parseNumber($string)
    {
        if (empty($string)) return 1;
        if (preg_match_all('/(\d+)/', $string, $m)) {
            $parts = array_map('intval', $m[1]);
            return count($parts) > 1 ? array_product($parts) : $parts[0];
        }
        return 1;
    }
}
