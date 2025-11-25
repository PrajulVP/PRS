<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SalesManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SalesManagerController extends Controller
{
    public function index()
    {
        $salesManagers = SalesManager::with('user')->latest()->paginate(10);
        return view('admin.salesmanagers.index', compact('salesManagers'));
    }

    public function show(SalesManager $salesManager)
    {
        $salesManager->load('user');
        return view('admin.salesmanagers.show', compact('salesManager'));
    }

    public function create()
    {
        return view('admin.salesmanagers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
            'contact_no' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'salesmanager',
            'status' => 'inactive',
        ]);

        $role = Role::firstOrCreate(['name' => 'salesmanager', 'guard_name' => 'web']);
        $user->assignRole($role);

        SalesManager::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'contact_no' => $request->contact_no,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.salesmanagers.index')->with('success', 'Sales Manager added successfully!');
    }

    public function edit(SalesManager $salesManager)
    {
        $salesManager->load('user');
        return view('admin.salesmanagers.edit', compact('salesManager'));
    }

    public function update(Request $request, SalesManager $salesManager)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $salesManager->user->id,
            'password' => 'nullable|string|min:4|confirmed',
            'contact_no' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $salesManager->user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $salesManager->user->password,
        ]);

        $salesManager->update([
            'name' => $request->name,
            'email' => $request->email,
            'contact_no' => $request->contact_no,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.salesmanagers.index')->with('success', 'Sales Manager updated successfully!');
    }

    public function destroy(SalesManager $salesManager)
    {
        $salesManager->user->delete();
        return redirect()->route('admin.salesmanagers.index')->with('success', 'Sales Manager deleted successfully!');
    }
}