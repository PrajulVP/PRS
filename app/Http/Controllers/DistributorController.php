<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\District;

class DistributorController extends Controller
{
    public function index()
    {
        $distributors = User::where('role', 'distributor')->with('district', 'area')->latest()->get();
        return view('admin.distributors.index', compact('distributors'));
    }

    public function create()
    {
        $districts = District::all();
        return view('admin.distributors.create', compact('districts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required',
            'gst' => 'required|unique:users',
            'contact_no' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4',
            'district_id' => 'required',
            'area_id' => 'required',
            'route' => 'required',
            'address' => 'required',
            'pincode' => 'required',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'distributor';
        User::create($data);

        return redirect()->route('distributors.index')->with('success', 'Distributor added successfully!');
    }

    public function edit(User $distributor)
    {
        $districts = District::all();
        $areas = Area::where('district_id', $distributor->district_id)->get();
        return view('admin.distributors.edit', compact('distributor','districts','areas'));
    }

    public function update(Request $request, User $distributor)
    {
        $data = $request->validate([
            'company_name' => 'required',
            'gst' => 'required|unique:users,gst,'.$distributor->id,
            'contact_no' => 'required',
            'email' => 'required|email|unique:users,email,'.$distributor->id,
            'password' => 'nullable|min:4',
            'district_id' => 'required',
            'area_id' => 'required',
            'route' => 'required',
            'address' => 'required',
            'pincode' => 'required',
        ]);

        if(!empty($data['password'])){
            $data['password'] = Hash::make($data['password']);
        }else{
            unset($data['password']);
        }

        $distributor->update($data);
        return redirect()->route('distributors.index')->with('success','Distributor updated successfully!');
    }

    public function destroy(User $distributor)
    {
        $distributor->delete();
        return redirect()->route('distributors.index')->with('success','Distributor deleted successfully!');
    }

    // AJAX: Get areas for selected district
    public function getAreas(District $district)
    {
        return response()->json($district->areas);
    }

}
