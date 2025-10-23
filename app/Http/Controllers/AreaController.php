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
    return response()->json(['status'=>true,'message'=>'Area list fetched','data'=>$areas]);
    }


    public function store(Request $request){
    $validated = $request->validate(['district_id'=>'required|exists:districts,id','name'=>'required|string']);
    $exists = Area::where('district_id',$validated['district_id'])->where('name',$validated['name'])->exists();
    if($exists) return response()->json(['status'=>false,'message'=>'This area already exists for the selected district.','data'=>null],422);
    $area = Area::create($validated);
    return response()->json(['status'=>true,'message'=>'Area created successfully','data'=>$area],201);
    }


    public function show(Area $area){
    $area->load('district');
    return response()->json(['status'=>true,'message'=>'Area fetched','data'=>$area]);
    }


    public function update(Request $request,Area $area){
    $validated = $request->validate(['district_id'=>'required|exists:districts,id','name'=>'required|string']);
    $exists = Area::where('district_id',$validated['district_id'])->where('name',$validated['name'])->where('id','<>',$area->id)->exists();
    if($exists) return response()->json(['status'=>false,'message'=>'This area already exists for the selected district.','data'=>null],422);
    try{ $area->update($validated); } catch(QueryException $e){
    return response()->json(['status'=>false,'message'=>'Update failed - duplicate','data'=>null],422);
    }
    return response()->json(['status'=>true,'message'=>'Area updated successfully','data'=>$area]);
    }


    public function destroy(Area $area){
    $area->delete();
    return response()->json(['status'=>true,'message'=>'Area deleted','data'=>null]);
    }
}
