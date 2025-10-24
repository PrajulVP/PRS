<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Chemist;
use App\Models\District;
use App\Models\Area;
use App\Models\Distributor;

class ChemistController extends Controller
{
    public function index()
    {
        $chemists = Chemist::with('district','area','distributor')->latest()->get();
        return view('admin.chemists.index', compact('chemists'));
    }

    public function create()
    {
        $districts = District::all();
        $distributors = Distributor::all();
        return view('admin.chemists.create', compact('districts','distributors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>'required',
            'gst'=>'required|unique:chemists',
            'contact_no'=>'required',
            'email'=>'required|email|unique:chemists',
            'password'=>'required|min:6',
            'district_id'=>'required',
            'area_id'=>'required',
            'distributor_id'=>'required',
            'route'=>'nullable|string',
            'address'=>'required',
            'pincode'=>'required'
        ]);

        $data['password'] = Hash::make($data['password']);
        Chemist::create($data);

        return redirect()->route('chemists.index')->with('success','Chemist added successfully!');
    }

    public function edit(Chemist $chemist)
    {
        $districts = District::all();
        $distributors = Distributor::all();
        $areas = Area::where('district_id', $chemist->district_id)->get();
        return view('admin.chemists.edit', compact('chemist','districts','areas','distributors'));
    }

    public function update(Request $request, Chemist $chemist)
    {
        $data = $request->validate([
            'name'=>'required',
            'gst'=>'required|unique:chemists,gst,'.$chemist->id,
            'contact_no'=>'required',
            'email'=>'required|email|unique:chemists,email,'.$chemist->id,
            'password'=>'nullable|min:6',
            'district_id'=>'required',
            'area_id'=>'required',
            'distributor_id'=>'required',
            'route'=>'nullable|string',
            'address'=>'required',
            'pincode'=>'required'
        ]);

        if(!empty($data['password'])){
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $chemist->update($data);

        return redirect()->route('chemists.index')->with('success','Chemist updated successfully!');
    }

    public function destroy(Chemist $chemist)
    {
        $chemist->delete();
        return redirect()->route('chemists.index')->with('success','Chemist deleted successfully!');
    }

    // AJAX to get areas for selected district
    public function getAreas(District $district)
    {
        return response()->json($district->areas);
    }
}
