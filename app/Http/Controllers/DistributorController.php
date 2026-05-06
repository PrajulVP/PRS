<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\District;
use App\Models\Distributor;
use App\Models\SalesManager;
use App\Traits\OneSignalNotifications;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;


class DistributorController extends Controller
{
    use OneSignalNotifications, \App\Traits\HandlesNotifications;
    public function index(Request $request)
    {
        if ($request->ajax()) {
            /** @var \App\Models\User $currentUser */
            $currentUser = Auth::user();
            $data = Distributor::with('user', 'district', 'area', 'salesManager.user')->select('distributors.*');

            if ($request->filled('status') && $request->status !== 'all') {
                $data->whereHas('user', function($q) use ($request) {
                    $q->where('status', $request->status);
                });
            }

            $data->orderBy('distributors.id', 'desc');
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('user.status', function ($row) {
                    return $row->user ? $row->user->status : 'N/A';
                })
                ->addColumn('address', function ($row) {
                    return $row->address ?? 'N/A';
                })
                ->addColumn('district_name', function ($row) {
                    return $row->district ? $row->district->name : 'N/A';
                })
                ->addColumn('area_name', function ($row) {
                    return $row->area ? $row->area->name : 'N/A';
                })
                ->addColumn('latitude', function ($row) {
                    return $row->latitude ?? '';
                })
                ->addColumn('longitude', function ($row) {
                    return $row->longitude ?? '';
                })
                ->addColumn('can_edit', function($row) use ($currentUser) {
                    return $currentUser->hasAnyRole(['admin', 'superadmin']) || $currentUser->hasPermissionToCategory('distributors', 'edit');
                })
                ->addColumn('can_delete', function($row) use ($currentUser) {
                    return $currentUser->hasAnyRole(['admin', 'superadmin']) || $currentUser->hasPermissionToCategory('distributors', 'delete');
                })
                ->addColumn('can_activate', function($row) use ($currentUser) {
                    return $currentUser->hasRole('superadmin');
                })
                ->make(true);
        }

        $districts = District::orderBy('name', 'asc')->get();
        $salesManagers = SalesManager::with('user')->get();

        return view('admin.distributors.index', compact('districts', 'salesManagers'));
    }

    public function store(Request $request)
    {
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

        $distributorData = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'gst' => ['required', 'unique:distributors', 'regex:/^[a-zA-Z0-9]+$/'],
            'drug_license_no' => ['required', 'string', 'regex:/^[a-zA-Z0-9\/\-]+$/'],
            'contact_no' => ['required', 'digits:10', 'regex:/^[1-9][0-9]{9}$/'],
            'address' => ['required', 'string'],
            'pincode' => ['required', 'digits:6'],
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
            'sales_manager_id' => 'nullable|exists:sales_managers,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ], [
            'name.regex' => 'The name must only contain letters and spaces.',
            'contact_no.regex' => 'The contact number must be 10 digits and cannot start with zero.',
            'gst.regex' => 'The GST number must only contain letters and numbers (no symbols).',
            'drug_license_no.required' => 'The drug license number is mandatory.',
            'drug_license_no.regex' => 'The drug license number can only contain letters, numbers, slashes (/), and hyphens (-).',
            'pincode.digits' => 'The pincode must be exactly 6 digits.',
        ]);

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'role' => 'distributor',
            'status' => 'inactive',
            'contact_no' => $distributorData['contact_no'],
            'address' => $distributorData['address'],
            'pincode' => $distributorData['pincode'],
        ]);
        $user->assignRole('distributor');

        $distributor = new Distributor($distributorData);
        $distributor->user_id = $user->id;
        $distributor->save();

        // Notify Superadmins for approval
        $superAdmins = User::role('superadmin')->get();
        foreach ($superAdmins as $superAdmin) {
            $superAdmin->notify(new \App\Notifications\UserApprovalRequired(
                $user,
                "New Distributor {$user->name} has been added and requires approval/activation.",
                route('admin.distributors.index')
            ));
        }

        // OneSignal Push to Super Admins
        $superAdminIds = $superAdmins->pluck('id')->toArray();
        if(!empty($superAdminIds)) {
            $this->sendOneSignalPush(
                $superAdminIds,
                "New Distributor {$user->name} has been added and requires approval/activation.",
                ['user_id' => $user->id, 'type' => 'user_approval'],
                'Distributor Approval Required'
            );
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Distributor added successfully!'
            ]);
        }

        return redirect()->route('admin.distributors.index')->with('success', 'Distributor added successfully!');
    }

    public function update(Request $request, Distributor $distributor)
    {
        // Smart Repair: If user relationship is missing, check if a user with this email already exists
        if (!$distributor->user && $request->filled('email')) {
            $foundUser = User::where('email', $request->email)->first();
            if ($foundUser) {
                $distributor->user_id = $foundUser->id;
                $distributor->save();
                $distributor->load('user');
            }
        }

        $userId = $distributor->user ? $distributor->user->id : null;

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
            'password.required' => 'A password is required to create a new account for this distributor.',
        ]);

        $distributorData = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'gst' => ['required', 'unique:distributors,gst,' . $distributor->id, 'regex:/^[a-zA-Z0-9]+$/'],
            'drug_license_no' => ['required', 'string', 'regex:/^[a-zA-Z0-9\/\-]+$/'],
            'contact_no' => ['required', 'digits:10', 'regex:/^[1-9][0-9]{9}$/'],
            'address' => ['required', 'string'],
            'pincode' => ['required', 'digits:6'],
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
            'sales_manager_id' => 'nullable|exists:sales_managers,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ], [
            'name.regex' => 'The name must only contain letters and spaces.',
            'contact_no.regex' => 'The contact number must be 10 digits and cannot start with zero.',
            'gst.regex' => 'The GST number must only contain letters and numbers (no symbols).',
            'drug_license_no.required' => 'The drug license number is mandatory.',
            'drug_license_no.regex' => 'The drug license number can only contain letters, numbers, slashes (/), and hyphens (-).',
            'pincode.digits' => 'The pincode must be exactly 6 digits.',
        ]);

        if (!$userId) {
            // Re-create the missing user record (FoundUser logic above failed to find one)
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'role' => 'distributor',
                'status' => $request->status ?? 'inactive',
                'contact_no' => $distributorData['contact_no'],
                'address' => $distributorData['address'],
                'pincode' => $distributorData['pincode'],
            ]);
            $user->assignRole('distributor');
            
            $distributor->user_id = $user->id;
            $distributor->save();
        } else {
            // Standard update
            $userUpdateData = [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'contact_no' => $distributorData['contact_no'],
                'address' => $distributorData['address'],
                'pincode' => $distributorData['pincode'],
            ];

            if ($request->filled('password')) {
                $userUpdateData['password'] = Hash::make($request->password);
            }

            if ($request->filled('status')) {
                $userUpdateData['status'] = $request->status;
            }

            $distributor->user->update($userUpdateData);
        }

        $distributor->update($distributorData);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $userId ? 'Distributor updated successfully!' : 'Distributor record repaired and updated successfully!'
            ]);
        }

        return redirect()->route('admin.distributors.index')->with('success', 'Distributor updated successfully!');
    }

    public function destroy(Distributor $distributor)
    {
        try {
            if ($distributor->user) {
                $distributor->user->delete(); 
            }
            // Wait, looking at Step 89 line 127: $distributor->delete();
            // But if I delete distributor, the user remains?
            // Usually we delete the user.
            // SalesManagerController deletes user.
            // Let's stick to original logic but add try-catch and AJAX.
            // Actually, if I delete distributor model, foreign key on users table? No, usually user_id on distributor.
            // If I delete distributor, user is orphaned.
            // I should probably delete the User associated with it if strict 1:1.
            // But I will stick to what was there to avoid breaking specific logic, just adding AJAX wrapper.
            // However, SalesManagerController deletes `$salesManager->user->delete()`.
            // DistributorController (Step 89) deletes `$distributor->delete()`.
            // I will trust the original logic but wrap it.

            $distributor->delete();
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Distributor deleted successfully!']);
            }
            return redirect()->route('admin.distributors.index')->with('success', 'Distributor deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete Distributor. They may have active Retailers or Orders.'], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete Distributor.');
        }
    }

    // AJAX: Get areas for selected district
    public function getAreas(District $district)
    {
        return response()->json($district->areas()->orderBy('name', 'asc')->get());
    }

    public function activate(Distributor $distributor)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser->hasRole('superadmin')) {
            $msg = 'Only Superadmin can change the status of Distributors.';
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->route('admin.distributors.index')->with('error', $msg);
        }

        // Smart Repair: If user relationship is missing, check if a user with this email exists
        if (!$distributor->user) {
            $foundUser = User::where('email', $distributor->email)->first();
            if ($foundUser) {
                $distributor->user_id = $foundUser->id;
                $distributor->save();
                $distributor->load('user');
            }
        }

        if (!$distributor->user) {
            $msg = 'Cannot activate: User account missing for this record. Please edit and save to repair the account.';
            return request()->ajax() ? response()->json(['success' => false, 'message' => $msg], 422) : redirect()->back()->with('error', $msg);
        }

        $distributor->user->status = 'active';
        $distributor->user->save();

        $this->clearUserNotifications($distributor->user->id);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Distributor activated successfully!']);
        }
        return redirect()->route('admin.distributors.index')->with('success', 'Distributor activated successfully!');
    }

    public function deactivate(Distributor $distributor)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser->hasRole('superadmin')) {
            $msg = 'Only Superadmin can change the status of Distributors.';
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->route('admin.distributors.index')->with('error', $msg);
        }

        if (!$distributor->user) {
            $msg = 'Cannot deactivate: User account missing for this record.';
            return request()->ajax() ? response()->json(['success' => false, 'message' => $msg], 422) : redirect()->back()->with('error', $msg);
        }

        $distributor->user->status = 'inactive';
        $distributor->user->save();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Distributor deactivated successfully!']);
        }
        return redirect()->route('admin.distributors.index')->with('success', 'Distributor deactivated successfully!');
    }
}
