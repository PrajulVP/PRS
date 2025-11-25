<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SalesManager;
use App\Models\FieldStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FieldStaffController extends Controller
{
    public function index()
    {
        $query = FieldStaff::with('user', 'salesManager.user');

        if (Auth::user()->hasRole('salesmanager')) {
            $query->where('sales_manager_id', Auth::user()->salesManager->id);
        }

        $fieldstaffs = $query->latest()->paginate(10);
        return view('admin.fieldstaffs.index', compact('fieldstaffs'));
    }

    public function show(FieldStaff $fieldstaff)
    {
        $fieldstaff->load('user', 'salesManager.user');
        return view('admin.fieldstaffs.show', compact('fieldstaff'));
    }

    public function create()
    {
        $salesManagers = SalesManager::whereHas('user', function($query) {
            $query->where('status', 'active');
        })->get();
        return view('admin.fieldstaffs.create', compact('salesManagers'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('salesmanager')) {
            return redirect()->route('admin.fieldstaffs.index')->with('error', 'You are not authorized to create a field staff.');
        }

        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4',
            'contact_no' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $fieldstaffData = $request->validate([
            'pincode' => 'required|string',
            'sales_manager_id' => 'required|exists:sales_managers,id',
        ]);

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'role' => 'fieldstaff',
            'status' => 'inactive',
            'contact_no' => $userData['contact_no'],
            'address' => $userData['address'],
        ]);
        $user->assignRole('fieldstaff');

        $fieldstaff = new FieldStaff($fieldstaffData);
        $fieldstaff->user_id = $user->id;
        if (Auth::user()->hasRole('salesmanager')) {
            $fieldstaff->sales_manager_id = Auth::user()->salesManager->id;
        }
        $fieldstaff->save();

        return redirect()->route('admin.fieldstaffs.index')->with('success', 'Field staff added successfully and is pending approval.');
    }

    public function edit(FieldStaff $fieldstaff)
    {
        $salesManagers = SalesManager::whereHas('user', function($query) {
            $query->where('status', 'active');
        })->get();
        return view('admin.fieldstaffs.edit', compact('fieldstaff', 'salesManagers'));
    }

    public function update(Request $request, FieldStaff $fieldstaff)
    {
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $fieldstaff->user->id,
            'password' => 'nullable|min:4',
            'contact_no' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $fieldstaffData = $request->validate([
            'pincode' => 'required|string',
            'sales_manager_id' => 'required|exists:sales_managers,id',
        ]);

        $userUpdateData = [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'fieldstaff',
            'contact_no' => $userData['contact_no'],
            'address' => $userData['address'],
        ];
        if (!empty($userData['password'])) {
            $userUpdateData['password'] = Hash::make($userData['password']);
        }
        $fieldstaff->user->update($userUpdateData);

        $fieldstaff->update($fieldstaffData);

        return redirect()->route('admin.fieldstaffs.index')->with('success', 'Field staff updated successfully!');
    }

    public function destroy(FieldStaff $fieldstaff)
    {
        $fieldstaff->delete();
        return redirect()->route('admin.fieldstaffs.index')->with('success', 'Field staff deleted successfully!');
    }

    public function activate(FieldStaff $fieldstaff)
    {
        if (!Auth::user()->hasRole('admin')) {
            return redirect()->route('fieldstaffs.index')->with('error', 'You are not authorized to activate a field staff.');
        }

        $fieldstaff->user->status = 'active';
        $fieldstaff->user->save();

        return redirect()->route('admin.fieldstaffs.index')->with('success', 'Field staff activated successfully!');
    }
}