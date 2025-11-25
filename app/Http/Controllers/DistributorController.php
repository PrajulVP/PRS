<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\District;
use App\Models\Distributor; // Added

class DistributorController extends Controller
{
    public function index()
    {
        $distributors = Distributor::with('user', 'district', 'area')->latest()->paginate(10); // Changed
        return view('admin.distributors.index', compact('distributors'));
    }

    public function show(Distributor $distributor)
    {
        $distributor->load('user', 'district', 'area');
        return view('admin.distributors.show', compact('distributor'));
    }

    public function create()
    {
        $districts = District::all();
        return view('admin.distributors.create', compact('districts'));
    }

    public function store(Request $request)
    {
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4',
        ]);

        $distributorData = $request->validate([
            'name' => 'required|string|max:255',
            'gst' => 'required|unique:distributors',
            'drug_license_no' => 'nullable|string',
            'contact_no' => 'required',
            'address' => 'required',
            'pincode' => 'required',
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
        ]);

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'role' => 'distributor',
            'status' => 'inactive',
        ]);
        $user->assignRole('distributor');

        $distributor = new Distributor($distributorData);
        $distributor->user_id = $user->id;
        $distributor->save();

        return redirect()->route(route: 'admin.distributors.index')->with('success', 'Distributor added successfully!');
    }

    public function edit(Distributor $distributor)
    {
        $districts = District::all();
        $areas = Area::where('district_id', $distributor->district_id)->get();
        return view('admin.distributors.edit', compact('distributor','districts','areas'));
    }

    public function update(Request $request, Distributor $distributor)
    {
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $distributor->user->id,
            'password' => 'nullable|min:4',
        ]);

        $distributorData = $request->validate([
            'name' => 'required|string|max:255',
            'gst' => 'required|unique:distributors,gst,' . $distributor->id,
            'drug_license_no' => 'nullable|string',
            'contact_no' => 'required',
            'address' => 'required',
            'pincode' => 'required',
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
        ]);

        $userUpdateData = [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'distributor',
        ];
        if (!empty($userData['password'])) {
            $userUpdateData['password'] = Hash::make($userData['password']);
        }
        $distributor->user->update($userUpdateData);

        $distributor->update($distributorData);

        return redirect()->route('admin.distributors.index')->with('success','Distributor updated successfully!');
    }

    public function destroy(Distributor $distributor) // Changed type-hint
    {
        $distributor->delete(); // This will cascade delete the User due to foreign key constraint
        return redirect()->route('admin.distributors.index')->with('success','Distributor deleted successfully!');
    }

    // AJAX: Get areas for selected district
    public function getAreas(District $district)
    {
        
        return response()->json($district->areas);
    }

}