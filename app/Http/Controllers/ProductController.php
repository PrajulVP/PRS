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
                    'actions' => null, // Actions column will be rendered by DataTables
                ];
            });

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalData,
                'recordsFiltered' => $totalFiltered,
                'data' => $formattedProducts,
            ]);
        }

        return view('admin.products.index');
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
        ]);

        $data = $request->all();

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
        ]);

        $data = $request->all();

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

        $row = 1;
        $successCount = 0;
        $errors = [];

        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            $header = fgetcsv($handle, 0, ","); // length 0 for no limit

            if (!$header || count($header) < 12) {
                return redirect()->route('products.index')->with('error', 'Invalid CSV format or missing columns. Expected at least 12 columns, found ' . (is_array($header) ? count($header) : 0));
            }

            // Trim headers and remove BOM
            $header = array_map(function ($h) {
                return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
            }, $header);

            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $row++;
                if (count($header) !== count($data)) {
                    $errors[] = "Row $row: Column count mismatch.";
                    continue;
                }

                $productData = array_combine($header, $data);

                // Basic validation for required fields
                if (empty($productData['product_name']) || !isset($productData['mrp'])) {
                    $errors[] = "Row $row: Missing required fields (Name/MRP).";
                    continue;
                }

                $productCode = !empty($productData['product_code']) ? $productData['product_code'] : null;

                try {
                    $matchAttributes = ['product_name' => $productData['product_name']];
                    if ($productCode) {
                        $matchAttributes['product_code'] = $productCode;
                    }

                    // Create or Update
                    Product::updateOrCreate(
                        $matchAttributes,
                        [
                            'product_code' => $productCode,
                            'product_name' => $productData['product_name'],
                            'generic_name' => $productData['generic_name'] ?? null,
                            'pack' => $productData['pack'] ?? null,
                            'strip_size' => $productData['strip_size'] ?: null,
                            'box_size' => $productData['box_size'] ?: null,
                            'carton_size' => $productData['carton_size'] ?: null,
                            'hsn_code' => $productData['hsn_code'] ?? null,
                            'mrp' => (float)($productData['mrp'] ?? 0),
                            'ptr' => (float)($productData['ptr'] ?? 0),
                            'pts' => (float)($productData['pts'] ?? 0),
                            'loyalty_point_percentage' => (float)($productData['loyalty_point_percentage'] ?? 0),
                        ]
                    );
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row $row: " . $e->getMessage();
                }
            }
            fclose($handle);
        }

        if (count($errors) > 0) {
            return redirect()->route('products.index')->with('success', "Imported $successCount products. ")->with('error', "Failed: " . implode(" | ", array_slice($errors, 0, 5)) . (count($errors) > 5 ? "..." : ""));
        }

        return redirect()->route('products.index')->with('success', "$successCount products imported successfully.");
    }
}
