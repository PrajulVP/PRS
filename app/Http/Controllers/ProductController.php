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
            if (!\Illuminate\Support\Facades\Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                abort(403, 'Unauthorized action. Only Admins can manage products.');
            }
            return $next($request);
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
                        ->orWhere('generic_name', 'like', "%{$searchValue}%")
                        ->orWhere('batch_no', 'like', "%{$searchValue}%");
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
                    'strip_size' => $product->strip_size,
                    'box_size' => $product->box_size,
                    'carton_size' => $product->carton_size,
                    'hsn_code' => $product->hsn_code,
                    'batch_no' => $product->batch_no,
                    'mrp' => number_format((float)$product->mrp, 2),
                    'ptr' => number_format((float)$product->ptr, 2),
                    'pts' => number_format((float)$product->pts, 2),
                    'net_amount' => number_format((float)$product->net_amount, 2),
                    'taxable_value' => $product->taxable_value,
                    'gst' => $product->gst,
                    'offer' => $product->offer,
                    'discount' => $product->discount,
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
            'product_code' => 'required|string|unique:products|max:255',
            'product_name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'strip_size' => 'nullable|integer|min:0',
            'box_size' => 'nullable|integer|min:0',
            'carton_size' => 'nullable|integer|min:0',
            'hsn_code' => 'nullable|string|max:255',
            // 'batch_no' => 'string|unique:products|max:255',
            'mrp' => 'required|numeric|min:0',
            'ptr' => 'required|numeric|min:0',
            'pts' => 'required|numeric|min:0',
            'taxable_value' => 'required|numeric|min:0',
            'gst' => 'required|numeric|min:0',
            'offer' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'net_amount' => '|numeric|min:0',
            'loyalty_point_percentage' => 'nullable|numeric|min:0',
        ]);

        Product::create($request->all());

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
            'product_code' => 'required|string|max:255|unique:products,product_code,' . $product->id,
            'product_name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'strip_size' => 'nullable|integer|min:0',
            'box_size' => 'nullable|integer|min:0',
            'carton_size' => 'nullable|integer|min:0',
            'hsn_code' => 'nullable|string|max:255',
            // 'batch_no' => '|string|max:255|unique:products,batch_no,' . $product->id,
            'mrp' => 'required|numeric|min:0',
            'ptr' => 'required|numeric|min:0',
            'pts' => 'required|numeric|min:0',
            'taxable_value' => 'required|numeric|min:0',
            'gst' => 'required|numeric|min:0',
            'offer' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'net_amount' => '|numeric|min:0',
            'loyalty_point_percentage' => 'nullable|numeric|min:0',
        ]);

        $product->update($request->all());

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
            'strip_size',
            'box_size',
            'carton_size',
            'hsn_code',
            'mrp',
            'ptr',
            'pts',
            'taxable_value',
            'gst',
            'offer',
            'discount',
            'net_amount',
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
            $header = fgetcsv($handle, 1000, ",");

            if (!$header || count($header) < 16) {
                return redirect()->route('products.index')->with('error', 'Invalid CSV format or missing columns.');
            }

            // Remove BOM if present
            $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                if (count($header) !== count($data)) {
                    $errors[] = "Row $row: Column count mismatch.";
                    continue;
                }

                $productData = array_combine($header, $data);

                // Basic validation for required fields
                if (empty($productData['product_code']) || empty($productData['product_name']) || !isset($productData['mrp'])) {
                    $errors[] = "Row $row: Missing required fields (Code/Name/MRP).";
                    continue;
                }

                // Create or Update
                Product::updateOrCreate(
                    ['product_code' => $productData['product_code']],
                    [
                        'product_name' => $productData['product_name'],
                        'generic_name' => $productData['generic_name'] ?? null,
                        'strip_size' => $productData['strip_size'] ?: null,
                        'box_size' => $productData['box_size'] ?: null,
                        'carton_size' => $productData['carton_size'] ?: null,
                        'hsn_code' => $productData['hsn_code'] ?? null,
                        'mrp' => (float)($productData['mrp'] ?? 0),
                        'ptr' => (float)($productData['ptr'] ?? 0),
                        'pts' => (float)($productData['pts'] ?? 0),
                        'taxable_value' => (float)($productData['taxable_value'] ?? 0),
                        'gst' => (float)($productData['gst'] ?? 0),
                        'offer' => (float)($productData['offer'] ?? 0),
                        'discount' => (float)($productData['discount'] ?? 0),
                        'net_amount' => (float)($productData['net_amount'] ?? 0),
                        'loyalty_point_percentage' => (float)($productData['loyalty_point_percentage'] ?? 0),
                    ]
                );
                $successCount++;
            }
            fclose($handle);
        }

        if (count($errors) > 0) {
            return redirect()->route('products.index')->with('success', "Imported $successCount products. ")->with('error', "Failed: " . implode(" | ", array_slice($errors, 0, 5)) . (count($errors) > 5 ? "..." : ""));
        }

        return redirect()->route('products.index')->with('success', "$successCount products imported successfully.");
    }
}
