<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Manager;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role; // Import Role model

class ManagerController extends Controller
{
    public function index()
    {
        // Fetch Manager records with their associated User records
        $managers = Manager::with('user')->latest()->get();
        return view('admin.managers.index', compact('managers'));
    }

    public function create()
    {
        $distributors = Distributor::all(); // Fetch all distributors for the dropdown
        return view('admin.managers.create', compact('distributors'));
    }

    public function store(Request $request)
    {
        // 1. Validate User details
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
        ]);

        // 2. Validate Manager-specific details (status is removed from validation as it's set by default)
        $request->validate([
            'distributor_id' => 'nullable|exists:distributors,id',
            'contact_no' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            // 'status' => 'required|string|in:active,inactive', // Removed from validation
        ]);

        // Create User record
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'manager', // Assign default role
            'status' => 'inactive', // Set user status to inactive by default
        ]);

        // Assign 'manager' role using Spatie
        $role = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $user->assignRole($role);

        // Create Manager record
        Manager::create([
            'user_id' => $user->id,
            'distributor_id' => $request->distributor_id,
            'name' => $user->name, // Inherit name from user
            'email' => $user->email, // Inherit email from user
            'contact_no' => $request->contact_no,
            'address' => $request->address,
            'status' => 'inactive', // Set manager's profile status to inactive by default
        ]);

        return redirect()->route('managers.index')->with('success', 'Manager added successfully!');
    }

    public function edit(Manager $manager)
    {
        $distributors = Distributor::all(); // Fetch all distributors for the dropdown
        // Eager load the user for the form
        $manager->load('user');
        return view('admin.managers.edit', compact('manager', 'distributors'));
    }

    public function update(Request $request, Manager $manager)
    {
        // 1. Validate User details
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $manager->user->id,
            'password' => 'nullable|string|min:4|confirmed',
        ]);

        // 2. Validate Manager-specific details
        $request->validate([
            'distributor_id' => 'nullable|exists:distributors,id',
            'contact_no' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        // Update User record
        $manager->user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $manager->user->password,
        ]);

        // Update Manager record
        $manager->update([
            'distributor_id' => $request->distributor_id,
            'name' => $request->name, // Update manager's own name field
            'email' => $request->email, // Update manager's own email field
            'contact_no' => $request->contact_no,
            'address' => $request->address,
            'status' => $request->status,
        ]);

        return redirect()->route('managers.index')->with('success', 'Manager updated successfully!');
    }

    public function destroy(Manager $manager)
    {
        // Delete associated User first if desired, or rely on cascade from migration
        $manager->user->delete(); // This will also delete the Manager record due to cascade on user_id
        return redirect()->route('managers.index')->with('success', 'Manager deleted successfully!');
    }
}