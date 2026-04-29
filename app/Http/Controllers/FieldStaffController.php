<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SalesManager;
use App\Models\FieldStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\OneSignalNotifications;
use Yajra\DataTables\Facades\DataTables;

class FieldStaffController extends Controller
{
    use OneSignalNotifications, \App\Traits\HandlesNotifications;
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = FieldStaff::with('user', 'salesManager.user');

            if ($request->filled('status') && $request->status !== 'all') {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('status', $request->status);
                });
            }

            /** @var \App\Models\User $currentUser */
            $currentUser = Auth::user();

            if ($currentUser->hasRole('salesmanager')) {
                $query->where('sales_manager_id', $currentUser->salesManager->id);
            }

            $query->orderBy('fieldstaffs.id', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('can_edit', function($row) use ($currentUser) {
                    return $currentUser->hasAnyRole(['admin', 'superadmin']) || $currentUser->hasPermissionToCategory('field_staff', 'edit');
                })
                ->addColumn('can_delete', function($row) use ($currentUser) {
                    return $currentUser->hasAnyRole(['admin', 'superadmin']) || $currentUser->hasPermissionToCategory('field_staff', 'delete');
                })
                ->addColumn('address', function ($row) {
                    return $row->address ?? 'N/A';
                })
                ->addColumn('latitude', function ($row) {
                    return $row->latitude ?? '';
                })
                ->addColumn('longitude', function ($row) {
                    return $row->longitude ?? '';
                })
                ->make(true);
        }

        $salesManagers = SalesManager::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();

        return view('admin.fieldstaffs.index', compact('salesManagers'));
    }

    public function show(FieldStaff $fieldStaff)
    {
        $fieldStaff->load(['user', 'salesManager.user', 'retailers.user']);
        return response()->json([
            'success' => true,
            'data' => $fieldStaff
        ]);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser->hasRole('salesmanager') && !$currentUser->hasRole('admin')) {
        }

        $userData = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => [
                'required', 'email', 'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => ['required', 'min:6', 'confirmed', 'regex:/^\S+$/'],
        ], [
            'name.regex' => 'The name must only contain letters and spaces.',
            'email.regex' => 'The email format is invalid or has an invalid top-level domain.',
            'password.min' => 'The password must be at least 6 characters.',
            'password.regex' => 'The password must not contain spaces.',
        ]);

        $fieldstaffData = $request->validate([
            'pincode' => ['required', 'digits:6'],
            'sales_manager_id' => 'nullable|exists:sales_managers,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'contact_no' => ['required', 'digits:10', 'regex:/^[1-9][0-9]{9}$/'],
            'address' => ['required', 'string'],
        ], [
            'contact_no.regex' => 'The contact number must not start with zero.',
            'pincode.digits' => 'The pincode must be exactly 6 digits.',
        ]);

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'role' => 'fieldstaff',
            'status' => 'inactive',
            'contact_no' => $fieldstaffData['contact_no'],
            'address' => $fieldstaffData['address'],
            'pincode' => $fieldstaffData['pincode'],
        ]);
        $user->assignRole('fieldstaff');

        $fieldstaff = new FieldStaff($fieldstaffData);
        $fieldstaff->user_id = $user->id;
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        if ($currentUser->hasRole('salesmanager')) {
            $fieldstaff->sales_manager_id = $currentUser->salesManager->id;
        }
        $fieldstaff->save();

        /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $admins */
        $admins = User::role(['admin', 'superadmin'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\UserApprovalRequired(
                $user,
                "New Field Staff {$user->name} has been added and requires activation.",
                route('admin.field-staffs.index')
            ));
        }

        $adminIds = $admins->pluck('id')->toArray();
        if (!empty($adminIds)) {
            $this->sendOneSignalPush(
                $adminIds,
                "New Field Staff {$user->name} has been added and requires activation.",
                ['user_id' => $user->id, 'type' => 'user_approval'],
                'Field Staff Approval Required'
            );
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Field staff added successfully and is pending approval.'
            ]);
        }

        return redirect()->route('admin.field-staffs.index')->with('success', 'Field staff added successfully and is pending approval.');
    }

    public function update(Request $request, FieldStaff $fieldstaff)
    {
        $userId = $fieldstaff->user ? $fieldstaff->user->id : null;

        $userData = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => [
                'required', 'email', 
                $userId ? 'unique:users,email,' . $userId : 'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => [$userId ? 'nullable' : 'required', 'min:6', 'confirmed', 'regex:/^\S+$/'],
        ], [
            'name.regex' => 'The name must only contain letters and spaces.',
            'email.regex' => 'The email format is invalid or has an invalid top-level domain.',
            'password.min' => 'The password must be at least 6 characters.',
            'password.regex' => 'The password must not contain spaces.',
            'password.required' => 'Password is required to repair the missing User account.',
        ]);

        $fieldstaffData = $request->validate([
            'pincode' => ['required', 'digits:6'],
            'sales_manager_id' => 'nullable|exists:sales_managers,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'contact_no' => ['required', 'digits:10', 'regex:/^[1-9][0-9]{9}$/'],
            'address' => ['required', 'string'],
        ], [
            'contact_no.regex' => 'The contact number must not start with zero.',
            'pincode.digits' => 'The pincode must be exactly 6 digits.',
        ]);

        if (!$userId) {
            // Repair: Re-create the missing user record
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'role' => 'fieldstaff',
                'status' => $request->status ?? 'inactive',
                'contact_no' => $fieldstaffData['contact_no'],
                'address' => $fieldstaffData['address'],
                'pincode' => $fieldstaffData['pincode'],
            ]);
            $user->assignRole('fieldstaff');
            
            $fieldstaff->user_id = $user->id;
            $fieldstaff->save();
        } else {
            // Standard update
            $userUpdateData = [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'contact_no' => $fieldstaffData['contact_no'],
                'address' => $fieldstaffData['address'],
                'pincode' => $fieldstaffData['pincode'],
            ];

            if ($request->has('device_uuid')) {
                $userUpdateData['device_uuid'] = $request->device_uuid;
                if (empty($request->device_uuid)) {
                    $userUpdateData['device_bound_at'] = null;
                }
            }

            if ($request->filled('password')) {
                $userUpdateData['password'] = Hash::make($request->password);
            }

            if ($request->filled('status')) {
                $userUpdateData['status'] = $request->status;
            }

            $fieldstaff->user->update($userUpdateData);
        }

        $fieldstaff->update($fieldstaffData);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $userId ? 'Field staff updated successfully!' : 'Field staff record repaired and updated successfully!'
            ]);
        }

        return redirect()->route('admin.field-staffs.index')->with('success', 'Field staff updated successfully!');
    }

    public function destroy(FieldStaff $fieldstaff)
    {
        try {
            if ($fieldstaff->user) {
                $fieldstaff->user->delete(); 
            }

            $fieldstaff->delete();
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Field staff deleted successfully!']);
            }
            return redirect()->route('admin.field-staffs.index')->with('success', 'Field staff deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete Field Staff. They may have active Retailers.'], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete Field Staff.');
        }
    }

    public function activate(FieldStaff $fieldstaff)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        
        if ($currentUser->hasAnyRole(['superadmin', 'admin'])) {
            if (!$fieldstaff->user) {
                $msg = 'Cannot activate: User account missing for this record. Please edit and save the staff to repair the account.';
                return request()->ajax() ? response()->json(['success' => false, 'message' => $msg], 422) : redirect()->back()->with('error', $msg);
            }
            $fieldstaff->user->status = 'active';
            $fieldstaff->user->save();

            $this->clearUserNotifications($fieldstaff->user->id);

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Field staff activated successfully!']);
            }
            return redirect()->route('admin.field-staffs.index')->with('success', 'Field staff activated successfully!');
        }

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to change the status of this user.'], 403);
        }
        return redirect()->route('admin.field-staffs.index')->with('error', 'You do not have permission to change the status of this user.');
    }

    public function deactivate(FieldStaff $fieldstaff)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if ($currentUser->hasAnyRole(['superadmin', 'admin'])) {
            if (!$fieldstaff->user) {
                $msg = 'Cannot deactivate: User account missing for this record.';
                return request()->ajax() ? response()->json(['success' => false, 'message' => $msg], 422) : redirect()->back()->with('error', $msg);
            }
            $fieldstaff->user->status = 'inactive';
            $fieldstaff->user->save();
            
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Field staff deactivated successfully!']);
            }
            return redirect()->route('admin.field-staffs.index')->with('success', 'Field staff deactivated successfully!');
        }

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to change the status of this user.'], 403);
        }
        return redirect()->route('admin.field-staffs.index')->with('error', 'You do not have permission to change the status of this user.');
    }
}
