<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Distributor;
use App\Models\District;

class DistributorController extends Controller
{
    public function index()
    {
        // // Distributor\DashboardController@index
        // $distributor = Auth::user(); // distributor guard
        // $month = now()->month;
        // $year = now()->year;

        // // target
        // $target = $distributor->targets()->where('year',$year)->where('month',$month)->first();
        // $targetValue = $target->target_value ?? 0;

        // // achievement — sum of delivered orders in month
        // $achievement = $distributor->orders()
        //     ->where('status','delivered')
        //     ->whereYear('delivered_at', $year)
        //     ->whereMonth('delivered_at', $month)
        //     ->sum('total_value');

        $distributors = Distributor::with('district', 'area')->latest()->get();
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
            'gst' => 'required|unique:distributors',
            'contact_no' => 'required',
            'email' => 'required|email|unique:distributors',
            'password' => 'required|min:4',
            'district_id' => 'required',
            'area_id' => 'required',
            'route' => 'required',
            'address' => 'required',
            'pincode' => 'required',
        ]);

        $data['password'] = Hash::make($data['password']);
        Distributor::create($data);

        return redirect()->route('distributors.index')->with('success', 'Distributor added successfully!');
    }

    public function edit(Distributor $distributor)
    {
        $districts = District::all();
        $areas = Area::where('district_id', $distributor->district_id)->get();
        return view('admin.distributors.edit', compact('distributor','districts','areas'));
    }

    public function update(Request $request, Distributor $distributor)
    {
        $data = $request->validate([
            'company_name' => 'required',
            'gst' => 'required|unique:distributors,gst,'.$distributor->id,
            'contact_no' => 'required',
            'email' => 'required|email|unique:distributors,email,'.$distributor->id,
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

    public function destroy(Distributor $distributor)
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
