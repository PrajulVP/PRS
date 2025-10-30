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
        $distributors = Distributor::with('user', 'district', 'area')->latest()->get(); // Changed
        return view('admin.distributors.index', compact('distributors'));
    }

    public function create()
    {
        $districts = District::all();
        return view('admin.distributors.create', compact('districts'));
    }

    public function store(Request $request)
    {
        // Separate validation for User and Distributor fields
        $userData = $request->validate([
            'name' => 'required|string|max:255', // Added name for User
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4',
        ]);

        $distributorData = $request->validate([
            'gst' => 'required|unique:distributors', // Unique on distributors table
            'truck_license_number' => 'nullable|string', // New field
            'contact_no' => 'required',
            'address' => 'required',
            'pincode' => 'required',
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
            'route' => 'required',
        ]);

        // Create User
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'role' => 'distributor', // Keep for consistency if other parts rely on it
        ]);
        $user->assignRole('distributor');

        // Create Distributor profile
        $distributor = new Distributor($distributorData);
        $distributor->user_id = $user->id;
        $distributor->save();

        return redirect()->route('distributors.index')->with('success', 'Distributor added successfully!');
    }

    public function edit(Distributor $distributor) // Changed type-hint
    {
        $districts = District::all();
        $areas = Area::where('district_id', $distributor->district_id)->get();
        return view('admin.distributors.edit', compact('distributor','districts','areas'));
    }

    public function update(Request $request, Distributor $distributor) // Changed type-hint
    {
        // Separate validation for User and Distributor fields
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $distributor->user->id,
            'password' => 'nullable|min:4',
        ]);

        $distributorData = $request->validate([
            'gst' => 'required|unique:distributors,gst,' . $distributor->id, // Unique on distributors table
            'truck_license_number' => 'nullable|string', // New field
            'contact_no' => 'required',
            'address' => 'required',
            'pincode' => 'required',
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
            'route' => 'required',
        ]);

        // Update User
        $userUpdateData = [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'distributor', // Keep for consistency
        ];
        if (!empty($userData['password'])) {
            $userUpdateData['password'] = Hash::make($userData['password']);
        }
        $distributor->user->update($userUpdateData);

        // Update Distributor profile
        $distributor->update($distributorData);

        return redirect()->route('distributors.index')->with('success','Distributor updated successfully!');
    }

    public function destroy(Distributor $distributor) // Changed type-hint
    {
        $distributor->delete(); // This will cascade delete the User due to foreign key constraint
        return redirect()->route('distributors.index')->with('success','Distributor deleted successfully!');
    }

    // AJAX: Get areas for selected district
    public function getAreas(District $district)
    {
        
        return response()->json($district->areas);
    }

}