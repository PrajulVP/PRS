<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\District;
use App\Models\Area;
use App\Models\Distributor;
use App\Models\Retailer;
use App\Models\SalesManager;
use App\Models\FieldStaff;
use Illuminate\Support\Facades\Auth;

class RetailerController extends Controller
{
    public function index()
    {
        $query = Retailer::with('user', 'distributor.user', 'fieldStaff.user', 'salesManager.user');

        if (Auth::user()->hasRole('distributor')) {
            $distributor = Auth::user()->distributor;
            if ($distributor) {
                $query->where('distributor_id', $distributor->id);
            }
        } elseif (Auth::user()->hasRole('fieldstaff')) {
            $fieldstaff = Auth::user()->fieldStaff;
            if ($fieldstaff) {
                $query->where('field_staff_id', $fieldstaff->id);
            }
        } elseif (Auth::user()->hasRole('salesmanager')) {
            $salesManager = Auth::user()->salesManager;
            if ($salesManager) {
                $fieldStaffIds = $salesManager->fieldStaffs->pluck('id');
                $query->whereIn('field_staff_id', $fieldStaffIds);
            }
        }

        $retailers = $query->latest()->paginate(10);
        return view('admin.retailers.index', compact('retailers'));
    }

    public function show(Retailer $retailer)
    {
        $retailer->load('user', 'distributor.user', 'fieldStaff.user', 'salesManager.user');
        return view('admin.retailers.show', compact('retailer'));
    }

    public function create()
    {
        $districts = District::all();
        $areas = Area::all();
        $distributors = Distributor::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();
        $salesManagers = SalesManager::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();
        $fieldStaffs = FieldStaff::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();

        return view('admin.retailers.create', compact('districts', 'areas', 'distributors', 'salesManagers', 'fieldStaffs'));
    }

    public function store(Request $request)
    {
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4',
            'contact_no' => 'required',
            'address' => 'required',
        ]);

        $retailerData = $request->validate([
            'pincode' => 'required',
            'gst' => 'required|unique:retailers',
            'distributor_id' => 'required|exists:distributors,id',
            'field_staff_id' => 'required|exists:fieldstaffs,id',
            'sales_manager_id' => 'required|exists:sales_managers,id',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'role' => 'retailer',
            'status' => 'inactive',
            'contact_no' => $userData['contact_no'],
            'address' => $userData['address'],
        ]);
        $user->assignRole('retailer');

        $retailer = new Retailer(array_merge($retailerData, ['pincode' => $request->pincode]));
        $retailer->user_id = $user->id;

        // Auto-assign sales_manager_id and field_staff_id based on who is creating the retailer
        if (Auth::user()->hasRole('salesmanager')) {
            $retailer->sales_manager_id = Auth::user()->salesManager->id;
        } elseif (Auth::user()->hasRole('fieldstaff')) {
            $retailer->field_staff_id = Auth::user()->fieldStaff->id;
            $retailer->sales_manager_id = Auth::user()->fieldStaff->salesManager->id;
        }
        $retailer->save();

        return redirect()->route('admin.retailers.index')->with('success', 'Retailer added successfully!');
    }

    public function edit(Retailer $retailer)
    {
        $districts = District::all();
        $areas = Area::all();
        $distributors = Distributor::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();
        $salesManagers = SalesManager::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();
        $fieldStaffs = FieldStaff::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();

        return view('admin.retailers.edit', compact('retailer', 'districts', 'areas', 'distributors', 'salesManagers', 'fieldStaffs'));
    }

    public function update(Request $request, Retailer $retailer)
    {
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $retailer->user->id,
            'password' => 'nullable|min:4',
            'contact_no' => 'required',
            'address' => 'required',
        ]);

        $retailerData = $request->validate([
            'pincode' => 'required',
            'gst' => 'required|unique:retailers,gst,' . $retailer->id,
            'distributor_id' => 'required|exists:distributors,id',
            'field_staff_id' => 'required|exists:fieldstaffs,id',
            'sales_manager_id' => 'required|exists:sales_managers,id',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $userUpdateData = [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'retailer',
            'contact_no' => $userData['contact_no'],
            'address' => $userData['address'],
        ];
        if (!empty($userData['password'])) {
            $userUpdateData['password'] = Hash::make($userData['password']);
        }
        $retailer->user->update($userUpdateData);

        $retailer->update(array_merge($retailerData, ['pincode' => $request->pincode]));

        return redirect()->route('admin.retailers.index')->with('success','Retailer updated successfully!');
    }

    public function destroy(Retailer $retailer)
    {
        $retailer->delete();
        return redirect()->route('retailers.index')->with('success','Retailer deleted successfully!');
    }

    public function getAreas(District $district)
    {
        return response()->json($district->areas->unique('name')->values()->all());
    }

    public function getDistributorsByDistrictAndArea(District $district, Area $area)
    {
        $distributors = Distributor::where('district_id', $district->id)
                                   ->where('area_id', $area->id)
                                   ->get();
        return response()->json($distributors);
    }

    public function activate(Retailer $retailer)
    {
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('salesmanager')) {
            return redirect()->back()->with('error', 'You are not authorized to activate a retailer.');
        }

        $retailer->user->status = 'active';
        $retailer->user->save();

        return redirect()->back()->with('success', 'Retailer activated successfully!');
    }
}
