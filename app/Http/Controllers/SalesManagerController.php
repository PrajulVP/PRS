<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\District;
use App\Models\SalesManager;
use App\Traits\OneSignalNotifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class SalesManagerController extends Controller
{
    use OneSignalNotifications, \App\Traits\HandlesNotifications;
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SalesManager::with('user')->select('sales_managers.*');
            
            if ($request->filled('status') && $request->status !== 'all') {
                $data->whereHas('user', function($q) use ($request) {
                    $q->where('status', $request->status);
                });
            }

            $data->orderBy('sales_managers.id', 'desc');
            /** @var \App\Models\User $currentUser */
            $currentUser = Auth::user();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('can_edit', function($row) use ($currentUser) {
                    return $currentUser->hasAnyRole(['admin', 'superadmin']) || $currentUser->hasPermissionToCategory('sales_managers', 'edit');
                })
                ->addColumn('can_delete', function($row) use ($currentUser) {
                    return $currentUser->hasAnyRole(['admin', 'superadmin']) || $currentUser->hasPermissionToCategory('sales_managers', 'delete');
                })
                ->addColumn('pincode', function ($row) {
                    return optional($row->user)->pincode ?? 'N/A';
                })
                ->make(true);
        }
        return view('admin.salesmanagers.index');
    }

    public function show(SalesManager $sales_manager)
    {
        // Load relationships needed for the view modal
        $sales_manager->load(['user', 'fieldStaffs.user', 'retailers.user']);

        return response()->json([
            'success' => true,
            'data' => $sales_manager
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => [
                'required', 'string', 'email', 'max:255', 'unique:users',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => ['required', 'string', 'min:6', 'regex:/^\S+$/', 'confirmed'],
            'contact_no' => ['required', 'digits:10', 'regex:/^[1-9][0-9]{9}$/'],
            'address' => ['required', 'string'],
            'pincode' => ['required', 'digits:6'],
        ], [
            'name.regex' => 'The name must only contain letters and spaces.',
            'email.regex' => 'The email format is invalid or has an invalid top-level domain.',
            'password.regex' => 'The password must not contain spaces.',
            'contact_no.regex' => 'The contact number must not start with zero.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'salesmanager',
            'status' => 'inactive',
            'contact_no' => $request->contact_no,
            'address' => $request->address,
            'pincode' => $request->pincode,
        ]);

        $role = Role::firstOrCreate(['name' => 'salesmanager', 'guard_name' => 'web']);
        $user->assignRole($role);

        SalesManager::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'contact_no' => $request->contact_no,
            'address' => $request->address,
            'pincode' => $request->pincode,
        ]);

        // Notify Superadmins for approval
        $superAdmins = User::role('superadmin')->get();
        foreach ($superAdmins as $superAdmin) {
            $superAdmin->notify(new \App\Notifications\UserApprovalRequired(
                $user,
                "New Sales Manager {$user->name} has been added and requires activation.",
                route('admin.sales-managers.index')
            ));
        }

        // OneSignal Push to Super Admins
        $superAdminIds = $superAdmins->pluck('id')->toArray();
        if(!empty($superAdminIds)) {
            $this->sendOneSignalPush(
                $superAdminIds,
                "New Sales Manager {$user->name} has been added and requires activation.",
                ['user_id' => $user->id, 'type' => 'user_approval'],
                'Sales Manager Approval Required'
            );
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Sales Manager added.'
            ]);
        }

        return redirect()->route('admin.sales-managers.index')->with('success', 'Sales Manager added.');
    }

    public function update(Request $request, SalesManager $sales_manager)
    {
        if (!$sales_manager->user) {
            $msg = 'User account missing for this Sales Manager. This record may be corrupted.';
            return $request->ajax() 
                ? response()->json(['success' => false, 'message' => $msg], 422) 
                : redirect()->back()->with('error', $msg);
        }
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => [
                'required', 'string', 'email', 'max:255', 'unique:users,email,' . $sales_manager->user->id,
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => ['nullable', 'string', 'min:6', 'regex:/^\S+$/', 'confirmed'],
            'contact_no' => ['required', 'digits:10', 'regex:/^[1-9][0-9]{9}$/'],
            'address' => ['required', 'string'],
            'pincode' => ['required', 'digits:6'],
        ], [
            'name.regex' => 'The name must only contain letters and spaces.',
            'email.regex' => 'The email format is invalid or has an invalid top-level domain.',
            'password.regex' => 'The password must not contain spaces.',
            'contact_no.regex' => 'The contact number must not start with zero.',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'contact_no' => $request->contact_no,
            'address' => $request->address,
            'pincode' => $request->pincode,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        if ($request->filled('status')) {
            $userData['status'] = $request->status;
        }

        $sales_manager->user->update($userData);

        $sales_manager->update([
            'name' => $request->name,
            'email' => $request->email,
            'contact_no' => $request->contact_no,
            'address' => $request->address,
            'pincode' => $request->pincode,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Sales Manager updated.'
            ]);
        }

        return redirect()->route('admin.sales-managers.index')->with('success', 'Sales Manager updated.');
    }

    public function destroy(SalesManager $sales_manager)
    {
        try {
            $sales_manager->user->delete();
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Sales Manager deleted.']);
            }
            return redirect()->route('admin.sales-managers.index')->with('success', 'Sales Manager deleted.');
        } catch (\Illuminate\Database\QueryException $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete Sales Manager with assigned staff.'], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete Sales Manager with assigned staff.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'An error occurred while deleting the Sales Manager.'], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while deleting the Sales Manager.');
        }
    }

    public function activate(SalesManager $sales_manager)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser->hasRole('superadmin')) {
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'You do not have permission to change the status of this user.'], 403);
            }
            return redirect()->route('admin.sales-managers.index')->with('error', 'You do not have permission to change the status of this user.');
        }

        if (!$sales_manager->user) {
            $msg = 'User account missing for this Sales Manager.';
            return request()->ajax() ? response()->json(['success' => false, 'message' => $msg], 422) : redirect()->back()->with('error', $msg);
        }

        $sales_manager->user->status = 'active';
        $sales_manager->user->save();

        $this->clearUserNotifications($sales_manager->user->id);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Sales Manager activated.']);
        }
        return redirect()->route('admin.sales-managers.index')->with('success', 'Sales Manager activated.');
    }

    public function deactivate(SalesManager $sales_manager)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser->hasRole('superadmin')) {
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'You do not have permission to change the status of this user.'], 403);
            }
            return redirect()->route('admin.sales-managers.index')->with('error', 'You do not have permission to change the status of this user.');
        }

        if (!$sales_manager->user) {
            $msg = 'User account missing for this Sales Manager.';
            return request()->ajax() ? response()->json(['success' => false, 'message' => $msg], 422) : redirect()->back()->with('error', $msg);
        }

        $sales_manager->user->status = 'inactive';
        $sales_manager->user->save();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Sales Manager deactivated.']);
        }
        return redirect()->route('admin.sales-managers.index')->with('success', 'Sales Manager deactivated.');
    }
}
