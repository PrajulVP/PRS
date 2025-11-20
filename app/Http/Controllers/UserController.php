<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Retailer;
use App\Models\Distributor;
use App\Models\FieldStaff;
use App\Models\Manager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role; // Added for roles
use Illuminate\Support\Facades\Storage; // Added

class UserController extends Controller
{
        public function index(Request $request)
        {
            $users = User::with('roles')->get();
            return view('admin.users.index', compact('users'));
        }

    public function create()
    {
        $roles = ['superadmin', 'admin', 'manager', 'distributor', 'fieldstaff', 'retailer'];
        $distributors = Distributor::all(); // Fetch all distributors
        return view('admin.users.create', compact('roles', 'distributors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
            'role' => 'required|string|exists:roles,name',
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Added
            'distributor_id' => 'nullable|exists:distributors,id', // Added for managers, fieldstaff, retailers
        ]);

        // Prevent admin from assigning superadmin role
        if (Auth::guard('web')->check() && Auth::guard('web')->user()->hasRole('admin') && $request->role === 'superadmin') {
            return back()->withInput()->withErrors(['role' => 'Admins cannot assign the Super Admin role.']);
        }

        $uniqueRoles = ['superadmin', 'admin', 'manager'];
        if (in_array($request->role, $uniqueRoles)) {
            // Check if any other user already has this role
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
        ];

        if ($request->hasFile('profile_pic')) {
            $userData['profile_pic'] = $request->file('profile_pic')->store('profile_pics', 'public');
        }

        $user = User::create($userData);

        // Assign the role to the user using spatie/laravel-permission
        $user->assignRole($request->role);

        if ($request->role === 'retailer') {
            $request->validate([
                'gst' => 'required|string|unique:retailers',
                'distributor_id' => 'required|exists:distributors,id', // Required for retailer
                // Add other retailer fields validation if needed
            ]);

            Retailer::create([
                'user_id' => $user->id,
                'gst' => $request->gst,
                'distributor_id' => $request->distributor_id,
            ]);
        } elseif ($request->role === 'distributor') {
            // Distributor creation logic should be here, assuming it's handled elsewhere
            // or through a form that provides necessary distributor-specific fields.
            // For now, it might be just creating a shell or handled by an admin directly creating a Distributor model.
            // Based on previous contexts, distributor model has more fields.
            // This needs to be clarified or handled via a separate flow.
            // For a simple user creation, we assume a basic Distributor record is enough or tied to User model.
            // If the request implies creating a full Distributor profile here, more fields are needed.
            // For this task, assuming basic creation.
            Distributor::create([
                'user_id' => $user->id,
                'name' => $request->name, // Assuming name from user is distributor name
                'email' => $request->email, // Assuming email from user
                // Other fields for Distributor would need to be passed in the request or made nullable
                // For simplicity, make other required fields nullable in the model for now if not provided.
            ]);
        } elseif ($request->role === 'fieldstaff') {
            $request->validate([
                'distributor_id' => 'required|exists:distributors,id', // Required for fieldstaff
                // Add other fieldstaff fields validation if needed
            ]);
            FieldStaff::create([
                'user_id' => $user->id,
                'distributor_id' => $request->distributor_id,
            ]);
        } elseif ($request->role === 'manager') {
            $request->validate([
                'distributor_id' => 'nullable|exists:distributors,id', // Optional for manager
            ]);
            Manager::create([
                'user_id' => $user->id,
                'distributor_id' => $request->distributor_id, // Can be null
                'name' => $user->name, // Inherit name from user
                'email' => $user->email, // Inherit email from user
                'status' => 'active', // Default status
                // Other fields contact_no, address would need to be added to request or made nullable
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

        $roles = ['superadmin', 'admin', 'manager', 'distributor', 'fieldstaff', 'retailer'];
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
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Added
        ]);

        // Prevent admin from assigning superadmin role
        if (Auth::guard('web')->user()->role === 'admin' && $request->role === 'superadmin') {
            return back()->withInput()->withErrors(['role' => 'Admins cannot assign the Super Admin role.']);
        }

        $uniqueRoles = ['superadmin', 'admin', 'manager'];
        if (in_array($request->role, $uniqueRoles)) {
            // Check if any other user already has this role
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
        if ($loggedInUser->id === $user->id) { // Cannot delete self
            $canDelete = false;
        } elseif ($loggedInUser->role === 'superadmin' && $user->role !== 'superadmin') {
            $canDelete = true;
        } elseif ($loggedInUser->role === 'admin' && in_array($user->role, ['manager', 'distributor', 'fieldstaff', 'retailer'])) {
            $canDelete = true;
        } elseif ($loggedInUser->role === 'manager' && in_array($user->role, ['distributor', 'fieldstaff', 'retailer'])) {
            $canDelete = true;
        } elseif ($loggedInUser->role === 'distributor' && in_array($user->role, ['fieldstaff', 'retailer'])) {
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

    /**
     * Display a list of users pending approval (status = 'inactive').
     * Accessible by Superadmin.
     */
    public function pendingApproval()
    {
        // Only superadmin can access this
        if (!Auth::guard('web')->user()->hasRole('superadmin')) {
            abort(403, 'Unauthorized action.');
        }
        $users = User::where('status', 'inactive')->get();
        return view('admin.users.pending_approval', compact('users'));
    }

    /**
     * Activate a user (set status to 'active').
     * Accessible by Superadmin.
     */
    public function activateUser(User $user)
    {
        // Only superadmin can access this
        if (!Auth::guard('web')->user()->hasRole('superadmin')) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $user->status = 'active';
        $user->save();

        return response()->json(['success' => 'User activated successfully!']);
    }
}