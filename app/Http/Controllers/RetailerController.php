<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\District;
use App\Models\Area;
use App\Models\Distributor;

class RetailerController extends Controller
{
    public function index()
    {
        $retailers = User::where('role', 'retailer')->with('district','area','distributor')->latest()->get();
        return view('admin.retailers.index', compact('retailers'));
    }

    public function create()
    {
        $Retailer = null;  // NEW — since this is create, there is no existing retailer
        $districts = District::all();
        $areas = Area::all();   // NEW — you forgot this
        $distributors = Distributor::all();

        return view('admin.retailers.create', compact('Retailer', 'districts', 'areas', 'distributors'));
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'gst' => 'required|unique:users',
            'contact_no' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4',
            'district_id' => 'required',
            'area_id' => 'required',
            'distributor_id' => 'required',
            'route' => 'nullable|string',
            'address' => 'required',
            'pincode' => 'required'
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'retailer';
        User::create($data);

        return redirect()->route('retailers.index')->with('success', 'Retailer added successfully!');
    }


public function edit(User $Retailer)
    {
        $districts = District::all();
        $distributors = Distributor::all();
        $areas = Area::where('district_id', $Retailer->district_id)->get();
        return view('admin.retailers.edit', compact('Retailer','districts','areas','distributors'));
    }

    public function update(Request $request, User $Retailer)
    {
        $data = $request->validate([
            'name'=>'required',
            'gst'=>'required|unique:users,gst,'.$Retailer->id,
            'contact_no'=>'required',
            'email'=>'required|email|unique:users,email,'.$Retailer->id,
            'password'=>'nullable|min:4',
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

        $Retailer->update($data);

        return redirect()->route('retailers.index')->with('success','Retailer updated successfully!');
    }

    public function destroy(User $Retailer)
    {
        $Retailer->delete();
        return redirect()->route('retailers.index')->with('success','Retailer deleted successfully!');
    }

    // AJAX to get areas for selected district
    public function getAreas(District $district)
    {
        return response()->json($district->areas);
    }
}
