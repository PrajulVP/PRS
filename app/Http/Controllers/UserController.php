<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role; // Added for roles
use Illuminate\Support\Facades\Storage; // Added

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->role($request->role);
        }

        $users = $query->get();
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
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Added
        ]);

        // Prevent admin from assigning superadmin role
        if (Auth::user()->hasRole('admin') && $request->role === 'superadmin') {
            return back()->withInput()->withErrors(['role' => 'Admins cannot assign the Super Admin role.']);
        }

        $uniqueRoles = ['superadmin', 'admin', 'manager'];
        if (in_array($request->role, $uniqueRoles)) {
            // Check if any other user already has this role
            $existingUserWithRole = User::role($request->role, $request->role)->first();
            if ($existingUserWithRole) {
                return back()->withInput()->withErrors(['role' => 'The ' . $request->role . ' role can only be assigned to one user.']);
            }
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ];

        if ($request->hasFile('profile_pic')) {
            $userData['profile_pic'] = $request->file('profile_pic')->store('profile_pics', 'public');
        }

        $user = User::create($userData);

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
        $loggedInUser = Auth::user();

        $canEdit = false;
        if ($loggedInUser->id === $user->id) {
            $canEdit = true;
        } elseif ($loggedInUser->hasRole('superadmin') && !$user->hasRole('superadmin')) {
            $canEdit = true;
        } elseif ($loggedInUser->hasRole('admin') && $user->hasAnyRole(['manager', 'distributor', 'fieldstaff', 'retailer'])) {
            $canEdit = true;
        } elseif ($loggedInUser->hasRole('manager') && $user->hasAnyRole(['distributor', 'fieldstaff', 'retailer'])) {
            $canEdit = true;
        } elseif ($loggedInUser->hasRole('distributor') && $user->hasAnyRole(['fieldstaff', 'retailer'])) {
            $canEdit = true;
        }

        if (!$canEdit) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $loggedInUser = Auth::user();

        $canEdit = false;
        if ($loggedInUser->id === $user->id) {
            $canEdit = true;
        } elseif ($loggedInUser->hasRole('superadmin') && !$user->hasRole('superadmin')) {
            $canEdit = true;
        } elseif ($loggedInUser->hasRole('admin') && $user->hasAnyRole(['manager', 'distributor', 'fieldstaff', 'retailer'])) {
            $canEdit = true;
        } elseif ($loggedInUser->hasRole('manager') && $user->hasAnyRole(['distributor', 'fieldstaff', 'retailer'])) {
            $canEdit = true;
        } elseif ($loggedInUser->hasRole('distributor') && $user->hasAnyRole(['fieldstaff', 'retailer'])) {
            $canEdit = true;
        }

        if (!$canEdit) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:4|confirmed',
            'role' => 'required|string|exists:roles,name',
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Added
        ]);

        // Prevent admin from assigning superadmin role
        if (Auth::user()->hasRole('admin') && $request->role === 'superadmin') {
            return back()->withInput()->withErrors(['role' => 'Admins cannot assign the Super Admin role.']);
        }

        $uniqueRoles = ['superadmin', 'admin', 'manager'];
        if (in_array($request->role, $uniqueRoles)) {
            // Check if any other user already has this role
            $existingUserWithRole = User::role($request->role, $request->role)->first();
            if ($existingUserWithRole && ($user->id !== $existingUserWithRole->id)) {
                return back()->withInput()->withErrors(['role' => 'The ' . $request->role . ' role can only be assigned to one user.']);
            }
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->hasFile('profile_pic')) {
            if ($user->profile_pic) {
                Storage::disk('public')->delete($user->profile_pic);
            }
            $userData['profile_pic'] = $request->file('profile_pic')->store('profile_pics', 'public');
        }

        if ($request->password) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        $user->syncRoles([$request->role]); // Sync roles using Spatie package

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $loggedInUser = Auth::user();

        $canDelete = false;
        if ($loggedInUser->id === $user->id) { // Cannot delete self
            $canDelete = false;
        } elseif ($loggedInUser->hasRole('superadmin') && !$user->hasRole('superadmin')) {
            $canDelete = true;
        } elseif ($loggedInUser->hasRole('admin') && $user->hasAnyRole(['manager', 'distributor', 'fieldstaff', 'retailer'])) {
            $canDelete = true;
        } elseif ($loggedInUser->hasRole('manager') && $user->hasAnyRole(['distributor', 'fieldstaff', 'retailer'])) {
            $canDelete = true;
        } elseif ($loggedInUser->hasRole('distributor') && $user->hasAnyRole(['fieldstaff', 'retailer'])) {
            $canDelete = true;
        }

        if (!$canDelete) {
            abort(403, 'Unauthorized action.');
        }

        // Delete profile picture if it exists
        if ($user->profile_pic) {
            Storage::disk('public')->delete($user->profile_pic);
        }

        // Delete profile picture if it exists
        if ($user->profile_pic) {
            Storage::disk('public')->delete($user->profile_pic);
        }

        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully!');
    }
}