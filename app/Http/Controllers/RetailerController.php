<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Distributor;
use App\Models\Retailer;
use App\Models\SalesManager;
use App\Models\FieldStaff;
use Illuminate\Support\Facades\Auth;
use DataTables;

class RetailerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
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

            return DataTables::of($query)
                ->addIndexColumn()
                ->make(true);
        }

        $distributors = Distributor::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();

        $salesManagers = SalesManager::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();

        $fieldStaffs = FieldStaff::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();

        return view('admin.retailers.index', compact('distributors', 'salesManagers', 'fieldStaffs'));
    }

    public function store(Request $request)
    {
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4|confirmed',
        ]);

        $retailerData = $request->validate([
            'pincode' => 'required',
            'gst' => 'required|unique:retailers',
            'distributor_id' => 'nullable|exists:distributors,id',
            'field_staff_id' => 'nullable|exists:fieldstaffs,id',
            'sales_manager_id' => 'nullable|exists:sales_managers,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_no' => 'required',
            'address' => 'required',
        ]);

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'role' => 'retailer',
            'status' => 'inactive',
        ]);
        $user->assignRole('retailer');

        $retailer = new Retailer(array_merge($retailerData, ['pincode' => $request->pincode]));
        $retailer->user_id = $user->id;

        // Auto-assign logic preserved if applicable, but form sends IDs so we likely use them unless logic overrides?
        // Original logic:
        if (Auth::user()->hasRole('salesmanager')) {
            $retailer->sales_manager_id = Auth::user()->salesManager->id;
        } elseif (Auth::user()->hasRole('fieldstaff')) {
            $retailer->field_staff_id = Auth::user()->fieldStaff->id;
            $retailer->sales_manager_id = Auth::user()->fieldStaff->salesManager->id;
        }

        $retailer->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Retailer added successfully!'
            ]);
        }

        return redirect()->route('admin.retailers.index')->with('success', 'Retailer added successfully!');
    }

    public function update(Request $request, Retailer $retailer)
    {
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $retailer->user->id,
            'password' => 'nullable|min:4|confirmed',
        ]);

        $retailerData = $request->validate([
            'pincode' => 'required',
            'gst' => 'required|unique:retailers,gst,' . $retailer->id,
            'distributor_id' => 'nullable|exists:distributors,id',
            'field_staff_id' => 'nullable|exists:fieldstaffs,id',
            'sales_manager_id' => 'nullable|exists:sales_managers,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_no' => 'required',
            'address' => 'required',
        ]);

        $userUpdateData = [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'retailer',
        ];

        if ($request->filled('password')) {
            $userUpdateData['password'] = Hash::make($request->password);
        }

        $retailer->user->update($userUpdateData);

        $retailer->update(array_merge($retailerData, ['pincode' => $request->pincode]));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Retailer updated successfully!'
            ]);
        }

        return redirect()->route('admin.retailers.index')->with('success', 'Retailer updated successfully!');
    }

    public function destroy(Retailer $retailer)
    {
        $retailer->delete();
        return redirect()->route('admin.retailers.index')->with('success', 'Retailer deleted successfully!');
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
