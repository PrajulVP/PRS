<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\District;
use App\Models\Area;
use App\Models\Distributor;
use App\Models\Retailer; // Added
use Illuminate\Support\Facades\Auth;

class RetailerController extends Controller
{
    public function index()
    {
        $query = Retailer::with('user', 'distributor.user');

        if (Auth::user()->hasRole('distributor')) {
            $distributor = Auth::user()->distributor; // Assuming a distributor relationship on User model
            if ($distributor) {
                $query->whereHas('user', function ($q) use ($distributor) {
                    $q->where('district_id', $distributor->district_id)
                      ->where('area_id', $distributor->area_id);
                });
            }
        }

        $retailers = $query->latest()->paginate(10);
        return view('admin.retailers.index', compact('retailers'));
    }

    public function create()
    {
        $retailer = null;  // Changed variable name
        $districts = District::all();
        $areas = Area::all();
        $distributors = Distributor::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();
        $managers = \App\Models\Manager::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();

        return view('admin.retailers.create', compact('retailer', 'districts', 'areas', 'distributors', 'managers'));
    }


    public function store(Request $request)
    {
        // Separate validation for User and Retailer fields
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4',
            'contact_no' => 'required',
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
            'route' => 'nullable|string',
            'address' => 'required',
            'pincode' => 'required',
        ]);

        $retailerData = $request->validate([
            'gst' => 'required|unique:retailers',
            'distributor_id' => 'required|exists:distributors,id',
            'sales_manager_id' => 'required|exists:managers,id',
        ]);
        $retailerData['district_id'] = $userData['district_id']; // Add district_id
        $retailerData['area_id'] = $userData['area_id'];     // Add area_id

        // Create User
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'role' => 'retailer', // Keep for consistency if other parts rely on it
            'contact_no' => $userData['contact_no'],
            'district_id' => $userData['district_id'],
            'area_id' => $userData['area_id'],
            'route' => $userData['route'],
            'address' => $userData['address'],
            'pincode' => $userData['pincode'],
        ]);
        $user->assignRole('retailer');

        // Create Retailer profile
        $retailer = new Retailer($retailerData);
        $retailer->district_id = $userData['district_id']; // ADDED
        $retailer->area_id = $userData['area_id'];     // ADDED
        $retailer->user_id = $user->id;
        $retailer->status = 'inactive';

        if (Auth::user()->hasRole('fieldstaff')) {
            $fieldstaff = Auth::user()->fieldstaff;
            $retailer->field_staff_id = $fieldstaff->id;
            $retailer->sales_manager_id = $fieldstaff->sales_manager_id;
        } else {
            $retailer->sales_manager_id = $request->sales_manager_id;
        }

        $retailer->save();

        return redirect()->route('retailers.index')->with('success', 'Retailer added successfully!');
    }


    public function edit(Retailer $retailer) // Changed type-hint and variable name
    {
        $districts = District::all();
        $distributors = Distributor::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();
        $areas = Area::where('district_id', $retailer->user->district_id)->get(); // Changed
        return view('admin.retailers.edit', compact('retailer','districts','areas','distributors'));
    }

    public function update(Request $request, Retailer $retailer) // Changed type-hint and variable name
    {
        // Separate validation for User and Retailer fields
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $retailer->user->id,
            'password' => 'nullable|min:4',
            'contact_no' => 'required',
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
            'route' => 'nullable|string',
            'address' => 'required',
            'pincode' => 'required',
        ]);

        $retailerData = $request->validate([
            'gst' => 'required|unique:retailers,gst,' . $retailer->id,
            'distributor_id' => 'required|exists:distributors,id',
        ]);

        // Update User
        $userUpdateData = [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'retailer', // Keep for consistency
            'contact_no' => $userData['contact_no'],
            'district_id' => $userData['district_id'],
            'area_id' => $userData['area_id'],
            'route' => $userData['route'],
            'address' => $userData['address'],
            'pincode' => $userData['pincode'],
        ];
        if (!empty($userData['password'])) {
            $userUpdateData['password'] = Hash::make($userData['password']);
        }
        $retailer->user->update($userUpdateData);

        // Update Retailer profile
        $retailerData['district_id'] = $userData['district_id']; // ADDED
        $retailerData['area_id'] = $userData['area_id'];     // ADDED
        $retailer->update($retailerData);

        return redirect()->route('retailers.index')->with('success','Retailer updated successfully!');
    }

    public function destroy(Retailer $retailer) // Changed type-hint and variable name
    {
        $retailer->delete(); // This will cascade delete the User due to foreign key constraint
        return redirect()->route('retailers.index')->with('success','Retailer deleted successfully!');
    }

    // AJAX to get areas for selected district
    public function getAreas(District $district)
    {
        return response()->json($district->areas->unique('name')->values()->all());
    }

    // AJAX: Get distributors for selected district and area
    public function getDistributorsByDistrictAndArea(District $district, Area $area)
    {
        $distributors = Distributor::where('district_id', $district->id)
                                   ->where('area_id', $area->id)
                                   ->get();
        return response()->json($distributors);
    }

    public function activate(Retailer $retailer)
    {
        if (!Auth::user()->hasRole('manager')) {
            return redirect()->back()->with('error', 'You are not authorized to activate a retailer.');
        }

        $retailer->update(['status' => 'active']);

        return redirect()->back()->with('success', 'Retailer activated successfully!');
    }
}