<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\District;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class AreaController extends Controller
{
    public function index(Request $request){
    $query = Area::with('district');
    if($request->district_id) $query->where('district_id',$request->district_id);
    if($request->q) $query->where('name','like','%'.$request->q.'%');
    $areas = $query->get();

    if ($request->wantsJson()) {
        return response()->json(['status'=>true,'message'=>'Area list fetched','data'=>$areas]);
    }
    $districts = District::all(); // Fetch districts for the form
    return view('admin.areas.index', compact('areas', 'districts'));
    }

    public function create()
    {
        $districts = District::all();
        return view('admin.areas.create', compact('districts'));
    }


    public function store(Request $request){
    $validated = $request->validate(['district_id'=>'required|exists:districts,id','name'=>'required|string']);
    $exists = Area::where('district_id',$validated['district_id'])->where('name',$validated['name'])->exists();
    if($exists) {
        if ($request->wantsJson()) {
            return response()->json(['status'=>false,'message'=>'This area already exists for the selected district.','data'=>null],422);
        }
        return redirect()->back()->withErrors(['name' => 'This area already exists for the selected district.']);
    }
    $area = Area::create($validated);

    if ($request->wantsJson()) {
        return response()->json(['status'=>true,'message'=>'Area created successfully','data'=>$area],201);
    }
    return redirect()->route('areas.index')->with('success', 'Area created successfully');
    }


    public function show(Request $request, Area $area){
    $area->load('district');

    if ($request->wantsJson()) {
        return response()->json(['status'=>true,'message'=>'Area fetched','data'=>$area]);
    }
    return view('admin.areas.show', compact('area'));
    }

    public function edit(Area $area)
    {
        $districts = District::all();
        return view('admin.areas.edit', compact('area', 'districts'));
    }


    public function update(Request $request,Area $area){
    $validated = $request->validate(['district_id'=>'required|exists:districts,id','name'=>'required|string']);
    $exists = Area::where('district_id',$validated['district_id'])->where('name',$validated['name'])->where('id','<>',$area->id)->exists();
    if($exists) {
        if ($request->wantsJson()) {
            return response()->json(['status'=>false,'message'=>'This area already exists for the selected district.','data'=>null],422);
        }
        return redirect()->back()->withErrors(['name' => 'This area already exists for the selected district.']);
    }
    try{ $area->update($validated); } catch(QueryException $e){
        if ($request->wantsJson()) {
            return response()->json(['status'=>false,'message'=>'Update failed - duplicate','data'=>null],422);
        }
        return redirect()->back()->withErrors(['name' => 'Update failed - duplicate']);
    }

    if ($request->wantsJson()) {
        return response()->json(['status'=>true,'message'=>'Area updated successfully','data'=>$area]);
    }
    return redirect()->route('areas.index')->with('success','Area updated successfully');
    }


    public function destroy(Request $request, Area $area){
    try {
        $area->delete();
    } catch (QueryException $e) {
        if ($request->wantsJson()) {
            return response()->json([
                'status' => false,
                'message' => 'Area could not be deleted due to related records.',
                'data' => null
            ], 422);
        }
        return redirect()->back()->withErrors(['error' => 'Area could not be deleted due to related records.']);
    }

    if ($request->wantsJson()) {
        return response()->json([
            'status'=>true,
            'message'=>'Area deleted',
            'data'=>null
        ]);
    }
    return redirect()->route('areas.index')->with('success','Area deleted successfully');
    }
}
