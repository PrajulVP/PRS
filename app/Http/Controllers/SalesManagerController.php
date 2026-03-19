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
                ->make(true);
        }
        return view('admin.salesmanagers.index');
    }

    public function show(SalesManager $salesManager)
    {
        // Load relationships needed for the view modal
        $salesManager->load(['user', 'fieldStaffs.user', 'retailers.user']);

        return response()->json([
            'success' => true,
            'data' => $salesManager
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
            'contact_no' => 'nullable|digits:10',
            'address' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'salesmanager',
            'status' => 'inactive',
            'contact_no' => $request->contact_no,
            'address' => $request->address,
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

    public function update(Request $request, SalesManager $salesManager)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $salesManager->user->id,
            'password' => 'nullable|string|min:4|confirmed',
            'contact_no' => 'nullable|digits:10',
            'address' => 'nullable|string',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'contact_no' => $request->contact_no,
            'address' => $request->address,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        if ($request->filled('status')) {
            $userData['status'] = $request->status;
        }

        $salesManager->user->update($userData);

        $salesManager->update([
            'name' => $request->name,
            'email' => $request->email,
            'contact_no' => $request->contact_no,
            'address' => $request->address,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Sales Manager updated.'
            ]);
        }

        return redirect()->route('admin.sales-managers.index')->with('success', 'Sales Manager updated.');
    }

    public function destroy(SalesManager $salesManager)
    {
        try {
            $salesManager->user->delete();
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

    public function activate(SalesManager $salesManager)
    {
        $salesManager->user->status = 'active';
        $salesManager->user->save();

        $this->clearUserNotifications($salesManager->user->id);

        return redirect()->route('admin.sales-managers.index')->with('success', 'Sales Manager activated.');
    }

    public function deactivate(SalesManager $salesManager)
    {
        $salesManager->user->status = 'inactive';
        $salesManager->user->save();

        return redirect()->route('admin.sales-managers.index')->with('success', 'Sales Manager deactivated.');
    }
}
