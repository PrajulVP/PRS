<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Retailer;
use App\Models\Distributor;
use App\Models\FieldStaff;
use App\Models\SalesManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')->get();
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load('distributor', 'salesManager', 'fieldStaff', 'retailer');
        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        $roles = ['superadmin', 'admin', 'salesmanager', 'distributor', 'fieldstaff', 'retailer'];
        $distributors = Distributor::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();
        return view('admin.users.create', compact('roles', 'distributors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
            'role' => 'required|string|exists:roles,name',
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'distributor_id' => 'nullable|exists:distributors,id',
        ]);

        if (Auth::guard('web')->check() && Auth::guard('web')->user()->hasRole('admin') && $request->role === 'superadmin') {
            return back()->withInput()->withErrors(['role' => 'Admins cannot assign the Super Admin role.']);
        }

        $uniqueRoles = ['superadmin', 'admin', 'salesmanager'];
        if (in_array($request->role, $uniqueRoles)) {
            $existingUserWithRole = User::role($request->role)->first();
            if ($existingUserWithRole) {
                return back()->withInput()->withErrors(['role' => 'The ' . $request->role . ' role can only be assigned to one user.']);
            }
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'inactive', // Set status to inactive by default
        ];

        if ($request->hasFile('profile_pic')) {
            $userData['profile_pic'] = $request->file('profile_pic')->store('profile_pics', 'public');
        }

        $user = User::create($userData);
        $user->assignRole($request->role);

        if ($request->role === 'retailer') {
            $request->validate([
                'gst' => 'required|string|unique:retailers',
                'distributor_id' => 'required|exists:distributors,id',
            ]);

            Retailer::create([
                'user_id' => $user->id,
                'gst' => $request->gst,
                'distributor_id' => $request->distributor_id,
            ]);
        } elseif ($request->role === 'distributor') {
            Distributor::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
            ]);
        } elseif ($request->role === 'fieldstaff') {
            $request->validate([
                'distributor_id' => 'required|exists:distributors,id',
            ]);
            FieldStaff::create([
                'user_id' => $user->id,
                'distributor_id' => $request->distributor_id,
            ]);
        } elseif ($request->role === 'salesmanager') {
            SalesManager::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => 'active',
            ]);
        }

        return redirect()->route('admin.users')->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        $loggedInUser = Auth::guard('web')->user();

        if (! $loggedInUser) {
            abort(403, 'Unauthorized action.');
        }

        $canEdit = false;
        if ($loggedInUser->id === $user->id) {
            $canEdit = true;
        } elseif ($loggedInUser->hasAnyRole(['superadmin', 'admin'])) {
            $canEdit = true;
        }

        if (!$canEdit) {
            abort(403, 'Unauthorized action.');
        }

        $roles = ['superadmin', 'admin', 'salesmanager', 'distributor', 'fieldstaff', 'retailer'];
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $loggedInUser = Auth::guard('web')->user();

        if (! $loggedInUser) {
            abort(403, 'Unauthorized action.');
        }

        $canEdit = false;
        if ($loggedInUser->id === $user->id) {
            $canEdit = true;
        } elseif ($loggedInUser->hasAnyRole(['superadmin', 'admin'])) {
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
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if (Auth::guard('web')->user()->role === 'admin' && $request->role === 'superadmin') {
            return back()->withInput()->withErrors(['role' => 'Admins cannot assign the Super Admin role.']);
        }

        $uniqueRoles = ['superadmin', 'admin', 'salesmanager'];
        if (in_array($request->role, $uniqueRoles)) {
            $existingUserWithRole = User::where('role', $request->role)->first();
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

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $loggedInUser = Auth::guard('web')->user();

        if (! $loggedInUser) {
            abort(403, 'Unauthorized action.');
        }

        $canDelete = false;
        if ($loggedInUser->id === $user->id) {
            $canDelete = false;
        } elseif ($loggedInUser->role === 'superadmin' && $user->role !== 'superadmin') {
            $canDelete = true;
        } elseif ($loggedInUser->role === 'admin' && in_array($user->role, ['salesmanager', 'distributor', 'fieldstaff', 'retailer'])) {
            $canDelete = true;
        } elseif ($loggedInUser->role === 'salesmanager' && in_array($user->role, ['distributor', 'fieldstaff', 'retailer'])) {
            $canDelete = true;
        } elseif ($loggedInUser->role === 'distributor' && in_array($user->role, ['fieldstaff', 'retailer'])) {
            $canDelete = true;
        }

        if (!$canDelete) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->profile_pic) {
            Storage::disk('public')->delete($user->profile_pic);
        }

        if ($user->profile_pic) {
            Storage::disk('public')->delete($user->profile_pic);
        }

        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully!');
    }

    public function activateUser(User $user)
    {
        if (!Auth::guard('web')->user()->hasRole('superadmin')) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $user->status = 'active';
        $user->save();

        return redirect()->back()->with('success', 'User activated successfully!');
    }
}
