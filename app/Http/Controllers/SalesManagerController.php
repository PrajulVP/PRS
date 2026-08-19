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
                ->addColumn('can_activate', function($row) use ($currentUser) {
                    return $currentUser->hasAnyRole(['admin', 'superadmin']);
                })
                ->addColumn('pincode', function ($row) {
                    return optional($row->user)->pincode ?? 'N/A';
                })
                ->make(true);
        }

        $stats = [
            'total' => SalesManager::count(),
            'active' => SalesManager::whereHas('user', fn($q) => $q->where('status', 'active'))->count(),
            'inactive' => SalesManager::whereHas('user', fn($q) => $q->where('status', 'inactive'))->count(),
        ];

        return view('admin.salesmanagers.index', compact('stats'));
    }

    public function show(SalesManager $sales_manager)
    {
        // Load relationships needed for the view modal
        $sales_manager->load(['user', 'fieldStaffs.user', 'fieldStaffs.salesTargets', 'retailers.user']);

        $totalTarget = 0;
        $totalAchieved = 0;
        
        $brand_targets = [];
        $uniqueBrands = \App\Models\Product::select('brand')->distinct()->pluck('brand');
        foreach ($uniqueBrands as $brand) {
            $brand_targets[$brand] = ['brand' => $brand, 'target' => 0, 'achieved' => 0];
        }
        
        foreach ($sales_manager->fieldStaffs as $fs) {
            $fsTargets = $fs->getCurrentMonthTargets();
            $fsTargetSum = $fsTargets->sum('amount');
            $fs->setAttribute('current_month_target_amount', round($fsTargetSum, 2));
            $totalTarget += $fsTargetSum;
            $totalAchieved += $fs->getCurrentMonthAchieved();
            
            foreach ($uniqueBrands as $brand) {
                $bTarget = $fsTargets->where('brand', $brand)->first();
                $bTargetAmount = $bTarget ? $bTarget->amount : 0;
                $bAchieved = $fs->getCurrentMonthAchieved($brand);
                $brand_targets[$brand]['target'] += $bTargetAmount;
                $brand_targets[$brand]['achieved'] += $bAchieved;
            }
        }
        
        foreach ($brand_targets as $key => $bt) {
             $brand_targets[$key]['target'] = round($bt['target'], 2);
             $brand_targets[$key]['achieved'] = round($bt['achieved'], 2);
        }

        $sales_manager->setAttribute('monthly_target', round($totalTarget, 2));
        $sales_manager->setAttribute('achieved_target', round($totalAchieved, 2));
        $sales_manager->setAttribute('brand_targets', array_values($brand_targets));

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
        // Smart Repair: If user relationship is missing, check if a user with this email already exists
        if (!$sales_manager->user && $request->filled('email')) {
            $foundUser = User::where('email', $request->email)->first();
            if ($foundUser) {
                $sales_manager->user_id = $foundUser->id;
                $sales_manager->save();
                $sales_manager->load('user');
            }
        }

        $userId = $sales_manager->user ? $sales_manager->user->id : null;

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => [
                'required', 'string', 'email', 'max:255', 
                $userId ? 'unique:users,email,' . $userId : 'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:6', 'regex:/^\S+$/', 'confirmed'],
            'contact_no' => ['required', 'digits:10', 'regex:/^[1-9][0-9]{9}$/'],
            'address' => ['required', 'string'],
            'pincode' => ['required', 'digits:6'],
        ], [
            'name.regex' => 'The name must only contain letters and spaces.',
            'email.regex' => 'The email format is invalid or has an invalid top-level domain.',
            'password.regex' => 'The password must not contain spaces.',
            'password.required' => 'A password is required to create a new account for this Sales Manager.',
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

        if ($request->filled('status') && Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
            $userData['status'] = $request->status;
        }

        if (!$userId) {
            // Re-create the missing user record
            $user = User::create(array_merge($userData, [
                'role' => 'salesmanager',
                'status' => $request->status ?? 'inactive',
            ]));
            $user->assignRole('salesmanager');
            
            $sales_manager->user_id = $user->id;
            $sales_manager->save();
        } else {
            $sales_manager->user->update($userData);
        }

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
        // Resilient model loading if Route Model Binding fails or relationship is broken
        if (!$sales_manager->exists) {
            $id = request()->route('sales_manager');
            $sales_manager = SalesManager::find($id);
            if (!$sales_manager) {
                return response()->json(['success' => false, 'message' => 'Sales Manager record not found.'], 404);
            }
        }

        /** @var User $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser->hasRole('superadmin')) {
            $msg = 'Only Superadmin can change the status of Sales Managers.';
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->route('admin.sales-managers.index')->with('error', $msg);
        }

        // 1. Repair by user_id if relationship is null but ID exists
        if (!$sales_manager->user && $sales_manager->user_id) {
            $u = User::find($sales_manager->user_id);
            if ($u) {
                $sales_manager->setRelation('user', $u);
            }
        }

        // 2. Smart Repair: Search by Email or Contact No
        if (!$sales_manager->user) {
            $foundUser = User::where('email', $sales_manager->email)->first();
            if (!$foundUser && $sales_manager->contact_no) {
                $foundUser = User::where('contact_no', $sales_manager->contact_no)->where('role', 'salesmanager')->first();
            }

            if ($foundUser) {
                $sales_manager->user_id = $foundUser->id;
                $sales_manager->save();
                $sales_manager->load('user');
            }
        }

        if (!$sales_manager->user) {
            $msg = 'Cannot activate: User account missing for this Sales Manager. Please edit and save the record to repair it.';
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
        // Resilient model loading if Route Model Binding fails or relationship is broken
        if (!$sales_manager->exists) {
            $id = request()->route('sales_manager');
            $sales_manager = SalesManager::find($id);
            if (!$sales_manager) {
                return response()->json(['success' => false, 'message' => 'Sales Manager record not found.'], 404);
            }
        }

        /** @var User $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser->hasRole('superadmin')) {
            $msg = 'Only Superadmin can change the status of Sales Managers.';
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->route('admin.sales-managers.index')->with('error', $msg);
        }

        // Repair by user_id if relationship is null but ID exists
        if (!$sales_manager->user && $sales_manager->user_id) {
            $u = User::find($sales_manager->user_id);
            if ($u) {
                $sales_manager->setRelation('user', $u);
            }
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
