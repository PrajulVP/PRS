<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return view('admin.products.index', compact('products'));
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
            'pack_quantity' => 'required|integer|min:1',
            'expiry' => 'required|date',
            'strip_size' => 'nullable|integer|min:0',
            'box_size' => 'nullable|integer|min:0',
            'carton_size' => 'nullable|integer|min:0',
            'hsn_code' => 'nullable|string|max:255',
            'batch_no' => 'required|string|unique:products|max:255',
            'mrp' => 'required|numeric|min:0',
            'ptr' => 'required|numeric|min:0',
            'taxable_value' => 'required|numeric|min:0',
            'gst' => 'required|numeric|min:0',
            'offer' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'net_amount' => 'required|numeric|min:0',
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
            'pack_quantity' => 'required|integer|min:1',
            'expiry' => 'required|date',
            'strip_size' => 'nullable|integer|min:0',
            'box_size' => 'nullable|integer|min:0',
            'carton_size' => 'nullable|integer|min:0',
            'hsn_code' => 'nullable|string|max:255',
            'batch_no' => 'required|string|max:255|unique:products,batch_no,' . $product->id,
            'mrp' => 'required|numeric|min:0',
            'ptr' => 'required|numeric|min:0',
            'taxable_value' => 'required|numeric|min:0',
            'gst' => 'required|numeric|min:0',
            'offer' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'net_amount' => 'required|numeric|min:0',
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
}
