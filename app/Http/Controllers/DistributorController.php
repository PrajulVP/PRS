<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\District;
use App\Models\Distributor;
use App\Models\SalesManager;
use Yajra\DataTables\DataTables;


class DistributorController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Distributor::with('user', 'district', 'area', 'salesManager.user')->select('distributors.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        $districts = District::all();
        $salesManagers = SalesManager::with('user')->get();

        return view('admin.distributors.index', compact('districts', 'salesManagers'));
    }

    public function store(Request $request)
    {
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4|confirmed',
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
            'sales_manager_id' => 'required|exists:sales_managers,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
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
        $distributor->sales_manager_id = $distributorData['sales_manager_id'];
        $distributor->save();

        return redirect()->route('admin.distributors.index')->with('success', 'Distributor added successfully!');
    }

    public function update(Request $request, Distributor $distributor)
    {
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $distributor->user->id,
            'password' => 'nullable|min:4|confirmed',
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
            'sales_manager_id' => 'required|exists:sales_managers,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
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

        $distributor->update(array_merge($distributorData, ['sales_manager_id' => $distributorData['sales_manager_id']]));

        return redirect()->route('admin.distributors.index')->with('success', 'Distributor updated successfully!');
    }

    public function destroy(Distributor $distributor)
    {
        $distributor->delete();
        return redirect()->route('admin.distributors.index')->with('success', 'Distributor deleted successfully!');
    }

    // AJAX: Get areas for selected district
    public function getAreas(District $district)
    {
        return response()->json($district->areas);
    }
}
