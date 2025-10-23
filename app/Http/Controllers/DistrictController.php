<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\District;
use Illuminate\Validation\Rule;

class DistrictController extends Controller
{
    // Display list (API or web)
    public function index(Request $request) {
        $districts = District::with('areas')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status'=>true,
                'message'=>'District list fetched',
                'data'=>$districts
            ]);
        }

        // For web view
        return view('admin.districts.index', compact('districts'));
    }

    // Store district
    public function store(Request $request) {
        $validated = $request->validate([
            'name'=>'required|string|unique:districts,name'
        ]);
        $district = District::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'status'=>true,
                'message'=>'District created successfully',
                'data'=>$district
            ], 201);
        }

        // Web redirect with flash
        return redirect()->route('districts.index')->with('success', 'District created successfully');
    }

    // Show single district
    public function show(Request $request, District $district) {
        $district->load('areas');

        if ($request->wantsJson()) {
            return response()->json([
                'status'=>true,
                'message'=>'District fetched',
                'data'=>$district
            ]);
        }

        return view('admin.districts.show', compact('district'));
    }

    // Update district
    public function update(Request $request, District $district) {
        $validated = $request->validate([
            'name'=>['required','string',Rule::unique('districts','name')->ignore($district->id)]
        ]);
        $district->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'status'=>true,
                'message'=>'District updated successfully',
                'data'=>$district
            ]);
        }

        return redirect()->route('districts.index')->with('success','District updated successfully');
    }

    // Delete district
    public function destroy(Request $request, District $district) {
        $district->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'status'=>true,
                'message'=>'District deleted',
                'data'=>null
            ]);
        }

        return redirect()->route('districts.index')->with('success','District deleted successfully');
    }
}
