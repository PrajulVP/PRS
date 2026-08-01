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
            } else {
                if ($request->filled('sales_manager_id')) {
                    $query->where('sales_manager_id', $request->sales_manager_id);
                }
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
                ->addColumn('punch_status', function ($row) {
                    $lastPunch = \App\Models\AttendanceLog::where('user_id', $row->user_id)
                        ->orderBy('timestamp', 'desc')
                        ->first();
                        
                    $status = 'punched_out';
                    if ($lastPunch && $lastPunch->type === 'punch_in' && \Carbon\Carbon::parse($lastPunch->timestamp)->isToday()) {
                        $status = 'punched_in';
                    }
                    return $status;
                })
                ->addColumn('has_punch_today', function ($row) {
                    return \App\Models\AttendanceLog::where('user_id', $row->user_id)
                        ->whereDate('timestamp', \Carbon\Carbon::today())
                        ->exists();
                })
                ->addColumn('clock_in_permission', function ($row) {
                    return $row->user->clock_in_permission;
                })
                ->make(true);
        }

        $salesManagers = SalesManager::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();

        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        $statsQuery = FieldStaff::query();
        if ($currentUser->hasRole('salesmanager')) {
            $statsQuery->where('sales_manager_id', $currentUser->salesManager->id);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->whereHas('user', fn($q) => $q->where('status', 'active'))->count(),
            'inactive' => (clone $statsQuery)->whereHas('user', fn($q) => $q->where('status', 'inactive'))->count(),
        ];

        return view('admin.fieldstaffs.index', compact('salesManagers', 'stats'));
    }

    public function show(FieldStaff $field_staff)
    {
        $field_staff->load([
            'user',
            'user.leaveBalances.leaveType',
            'user.leaveRequests' => function ($q) {
                $q->orderBy('created_at', 'desc')->take(5);
            },
            'user.expenses' => function ($q) {
                $q->orderBy('created_at', 'desc')->take(5);
            },
            'salesManager.user',
            'retailers.user'
        ]);
        
        $latestLocation = \App\Models\LocationLog::where('user_id', $field_staff->user_id)->orderBy('timestamp', 'desc')->first();
        $todaysDistance = \App\Models\LocationLog::calculateDailyDistance($field_staff->user_id, date('Y-m-d'));
        
        $achievedTarget = \App\Models\RetailerOrder::where('fieldstaff_id', $field_staff->id)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_amount');
        
        // Append additional details to the field staff object
        $field_staff->setAttribute('latest_location', $latestLocation);
        $field_staff->setAttribute('todays_distance_km', round($todaysDistance, 2));
        $field_staff->setAttribute('achieved_target', round($achievedTarget, 2));

        return response()->json([
            'success' => true,
            'data' => $field_staff
        ]);
    }

    public function grantClockInPermission($id)
    {
        $fieldStaff = FieldStaff::findOrFail($id);
        $user = $fieldStaff->user;
        
        $user->clock_in_permission = true;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Clock-in permission granted successfully.'
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
            'monthly_target' => 'nullable|numeric|min:0',
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

    public function update(Request $request, FieldStaff $field_staff)
    {
        \Log::info('FieldStaff Update Request:', $request->all());

        // Smart Repair: If user relationship is missing, check if a user with this email already exists
        if (!$field_staff->user && $request->filled('email')) {
            $foundUser = User::where('email', $request->email)->first();
            if ($foundUser) {
                $field_staff->user_id = $foundUser->id;
                $field_staff->save();
                $field_staff->load('user');
            }
        }

        $userId = $field_staff->user ? $field_staff->user->id : null;

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
            'password.required' => 'A password is required to create a new account for this staff.',
        ]);

        $fieldstaffData = $request->validate([
            'pincode' => ['required', 'digits:6'],
            'sales_manager_id' => 'nullable|exists:sales_managers,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'monthly_target' => 'nullable|numeric|min:0',
            'contact_no' => ['required', 'digits:10', 'regex:/^[1-9][0-9]{9}$/'],
            'address' => ['required', 'string'],
        ], [
            'contact_no.regex' => 'The contact number must not start with zero.',
            'pincode.digits' => 'The pincode must be exactly 6 digits.',
        ]);

        if (!$userId) {
            // Re-create the missing user record (FoundUser logic above failed to find one)
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
            
            $field_staff->user_id = $user->id;
            $field_staff->save();
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

            if ($request->filled('status') && Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                $userUpdateData['status'] = $request->status;
            }

            $field_staff->user->update($userUpdateData);
        }

        $field_staff->update($fieldstaffData);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $userId ? 'Field staff updated successfully!' : 'Field staff record repaired and updated successfully!'
            ]);
        }

        return redirect()->route('admin.field-staffs.index')->with('success', 'Field staff updated successfully!');
    }

    public function destroy(FieldStaff $field_staff)
    {
        try {
            if ($field_staff->user) {
                $field_staff->user->delete(); 
            }

            $field_staff->delete();
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

    public function activate(FieldStaff $field_staff)
    {
        // Resilient model loading if Route Model Binding fails or relationship is broken
        if (!$field_staff->exists) {
            $id = request()->route('field_staff');
            $field_staff = FieldStaff::find($id);
            if (!$field_staff) {
                return response()->json(['success' => false, 'message' => 'Field Staff record not found.'], 404);
            }
        }

        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        
        if ($currentUser->hasAnyRole(['superadmin', 'admin'])) {
            // 1. Repair by user_id if relationship is null but ID exists
            if (!$field_staff->user && $field_staff->user_id) {
                $u = User::find($field_staff->user_id);
                if ($u) {
                    $field_staff->setRelation('user', $u);
                }
            }

            // 2. Smart Repair: Search by Contact No or User with matching email (if FS record had email, but it doesn't)
            if (!$field_staff->user) {
                $foundUser = User::where('contact_no', $field_staff->contact_no)->where('role', 'fieldstaff')->first();
                // FieldStaff doesn't have email column, but maybe we can find by name if we had it.
                
                if ($foundUser) {
                    $field_staff->user_id = $foundUser->id;
                    $field_staff->save();
                    $field_staff->load('user');
                }
            }

            if (!$field_staff->user) {
                $msg = 'Cannot activate: User account missing for this record. Please edit and save the staff to repair the account.';
                return request()->ajax() ? response()->json(['success' => false, 'message' => $msg], 422) : redirect()->back()->with('error', $msg);
            }
            $field_staff->user->status = 'active';
            $field_staff->user->save();

            $this->clearUserNotifications($field_staff->user->id);

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

    public function deactivate(FieldStaff $field_staff)
    {
        // Resilient model loading if Route Model Binding fails or relationship is broken
        if (!$field_staff->exists) {
            $id = request()->route('field_staff');
            $field_staff = FieldStaff::find($id);
            if (!$field_staff) {
                return response()->json(['success' => false, 'message' => 'Field Staff record not found.'], 404);
            }
        }

        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if ($currentUser->hasAnyRole(['superadmin', 'admin'])) {
            // Repair by user_id if relationship is null but ID exists
            if (!$field_staff->user && $field_staff->user_id) {
                $u = User::find($field_staff->user_id);
                if ($u) {
                    $field_staff->setRelation('user', $u);
                }
            }

            if (!$field_staff->user) {
                $msg = 'Cannot deactivate: User account missing for this record.';
                return request()->ajax() ? response()->json(['success' => false, 'message' => $msg], 422) : redirect()->back()->with('error', $msg);
            }
            $field_staff->user->status = 'inactive';
            $field_staff->user->save();
            
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
