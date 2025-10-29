<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role; // Added for roles

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);

        $uniqueRoles = ['superadmin', 'admin', 'manager'];
        if (in_array($request->role, $uniqueRoles)) {
            // Check if any other user already has this role
            $existingUserWithRole = User::role($request->role, $request->role)->first();
            if ($existingUserWithRole) {
                return back()->withInput()->withErrors(['role' => 'The ' . $request->role . ' role can only be assigned to one user.']);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $role = Role::where('name', $request->role)->where('guard_name', $request->role)->first();

        if ($role) {
            $user->assignRole($role);
        } else {
            return back()->withInput()->withErrors(['role' => 'The selected role is invalid or not configured correctly.']);
        }

        return redirect()->route('admin.users')->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:4|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);

        $uniqueRoles = ['superadmin', 'admin', 'manager'];
        if (in_array($request->role, $uniqueRoles)) {
            // Check if any other user already has this role
            $existingUserWithRole = User::role($request->role)->first();
            if ($existingUserWithRole && ($user->id !== $existingUserWithRole->id)) {
                return back()->withInput()->withErrors(['role' => 'The ' . $request->role . ' role can only be assigned to one user.']);
            }
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        $user->syncRoles([$request->role]); // Sync roles using Spatie package

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully!');
    }
}