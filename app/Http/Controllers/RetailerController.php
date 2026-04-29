<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Distributor;
use App\Models\Retailer;
use App\Models\SalesManager;
use App\Models\FieldStaff;
use App\Models\District;
use App\Models\Area;
use Illuminate\Support\Facades\Auth;
use App\Traits\HandlesNotifications;
use App\Traits\OneSignalNotifications;
use Yajra\DataTables\Facades\DataTables;

class RetailerController extends Controller
{
    use OneSignalNotifications, HandlesNotifications;
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Retailer::with(['user', 'distributor.user', 'fieldStaff.user', 'salesManager.user', 'district', 'area']);

            if ($request->filled('status') && $request->status !== 'all') {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('status', $request->status);
                });
            }

            /** @var \App\Models\User $currentUser */
            $currentUser = Auth::user();

            if ($currentUser->hasRole('distributor')) {
                $distributor = $currentUser->distributor;
                if ($distributor) {
                    $query->whereHas('retailerOrders', function ($orderQuery) use ($distributor) {
                        $orderQuery->where('distributor_id', $distributor->id);
                    });
                }
            } elseif ($currentUser->hasRole('fieldstaff')) {
                $fieldstaff = $currentUser->fieldStaff;
                if ($fieldstaff) {
                    $query->where('field_staff_id', $fieldstaff->id);
                }
            } elseif ($currentUser->hasRole('salesmanager')) {
                $salesManager = $currentUser->salesManager;
                if ($salesManager) {
                    $fieldStaffIds = $salesManager->fieldStaffs->pluck('id');
                    $query->whereIn('field_staff_id', $fieldStaffIds);
                }
            }

            $query->orderBy('retailers.id', 'desc');

            /** @var \App\Models\User $currentUser */
            $currentUser = Auth::user();
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('can_edit', function($row) use ($currentUser) {
                    return $currentUser->hasAnyRole(['admin', 'superadmin']) || $currentUser->hasPermissionToCategory('retailers', 'edit');
                })
                ->addColumn('can_delete', function($row) use ($currentUser) {
                    return $currentUser->hasAnyRole(['admin', 'superadmin']) || $currentUser->hasPermissionToCategory('retailers', 'delete');
                })
                ->addColumn('district_name', function ($row) {
                    return $row->district ? $row->district->name : 'N/A';
                })
                ->addColumn('area_name', function ($row) {
                    return $row->area ? $row->area->name : 'N/A';
                })
                ->addColumn('user_name', function ($row) {
                    return $row->user ? $row->user->name : 'N/A';
                })
                ->editColumn('user.email', function ($row) {
                    return $row->user ? $row->user->email : 'N/A';
                })
                ->editColumn('user.status', function ($row) {
                    return $row->user ? $row->user->status : 'N/A';
                })
                ->addColumn('gst', function ($row) {
                    return $row->gst ?? 'N/A';
                })
                ->addColumn('drug_license_no', function ($row) {
                    return $row->drug_license_no ?? 'N/A';
                })
                ->addColumn('address', function ($row) {
                    return $row->address ?? 'N/A';
                })
                ->addColumn('credit_limit', function ($row) {
                    return $row->credit_limit ?? 0;
                })
                ->addColumn('loyalty_points', function ($row) {
                    return $row->loyalty_points ?? 0;
                })
                ->addColumn('latitude', function ($row) {
                    return $row->latitude ?? '';
                })
                ->addColumn('longitude', function ($row) {
                    return $row->longitude ?? '';
                })
                ->addColumn('distributor_name', function ($row) {
                    return $row->distributor && $row->distributor->user ? $row->distributor->user->name : 'N/A';
                })
                ->addColumn('field_staff_name', function ($row) {
                    return $row->fieldStaff && $row->fieldStaff->user ? $row->fieldStaff->user->name : 'N/A';
                })
                ->addColumn('sales_manager_name', function ($row) {
                    return $row->salesManager && $row->salesManager->user ? $row->salesManager->user->name : 'N/A';
                })
                ->rawColumns(['user.status', 'action'])
                ->make(true);
        }

        $distributors = Distributor::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();

        $salesManagers = SalesManager::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();

        $fieldStaffs = FieldStaff::whereHas('user', function ($query) {
            $query->where('status', 'active');
        })->get();


        $districts = District::orderBy('name', 'asc')->get();

        return view('admin.retailers.index', compact('distributors', 'salesManagers', 'fieldStaffs', 'districts'));
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

        $retailerData = $request->validate([
            'shop_name' => 'required|string|max:255',
            'pincode' => ['required', 'digits:6'],
            'gst' => ['required', 'unique:retailers', 'regex:/^[a-zA-Z0-9]+$/'],
            'drug_license_no' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\/\-]+$/'],
            'distributor_id' => 'nullable|exists:distributors,id',
            'field_staff_id' => 'nullable|exists:fieldstaffs,id',
            'sales_manager_id' => 'nullable|exists:sales_managers,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_no' => ['required', 'digits:10', 'regex:/^[1-9][0-9]{9}$/'],
            'address' => ['required', 'string'],
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
        ], [
            'contact_no.regex' => 'The contact number must not start with zero.',
            'gst.regex' => 'The GST number must only contain letters and numbers (no symbols).',
            'drug_license_no.required' => 'The drug license number is mandatory.',
            'drug_license_no.regex' => 'The drug license number can only contain letters, numbers, slashes (/), and hyphens (-).',
            'pincode.digits' => 'The pincode must be exactly 6 digits.',
        ]);

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'role' => 'retailer',
            'status' => 'inactive',
            'contact_no' => $retailerData['contact_no'],
            'address' => $retailerData['address'],
            'pincode' => $retailerData['pincode'],
        ]);
        $user->assignRole('retailer');

        $retailer = new Retailer(array_merge($retailerData, ['pincode' => $request->pincode]));
        $retailer->user_id = $user->id;

        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        if ($currentUser->hasRole('salesmanager')) {
            $retailer->sales_manager_id = $currentUser->salesManager->id;
        } elseif ($currentUser->hasRole('fieldstaff')) {
            $retailer->field_staff_id = $currentUser->fieldStaff->id;
            $retailer->sales_manager_id = $currentUser->fieldStaff->salesManager->id;
        }

        $retailer->save();

        // Notify the assigned Sales Manager
        if ($retailer->sales_manager_id) {
            /** @var \App\Models\User $salesManagerUser */
            $salesManagerUser = User::whereHas('salesManager', function ($q) use ($retailer) {
                $q->where('id', $retailer->sales_manager_id);
            })->first();

            if ($salesManagerUser) {
                $salesManagerUser->notify(new \App\Notifications\UserApprovalRequired(
                    $user,
                    "New Retailer {$user->name} has been added to your team and requires review/activation.",
                    route('admin.retailers.index')
                ));

                // OneSignal Push
                $this->sendOneSignalPush(
                    [$salesManagerUser->id],
                    "New Retailer {$user->name} has been added to your team and requires review/activation.",
                    ['user_id' => $user->id, 'type' => 'user_approval'],
                    'Retailer Approval Required'
                );
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Retailer added successfully!'
            ]);
        }

        return redirect()->route('admin.retailers.index')->with('success', 'Retailer added successfully!');
    }

    public function update(Request $request, Retailer $retailer)
    {
        // Smart Repair: If user relationship is missing, check if a user with this email already exists
        if (!$retailer->user && $request->filled('email')) {
            $foundUser = User::where('email', $request->email)->first();
            if ($foundUser) {
                $retailer->user_id = $foundUser->id;
                $retailer->save();
                $retailer->load('user');
            }
        }

        $userId = $retailer->user ? $retailer->user->id : null;

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
            'password.required' => 'A password is required to create a new account for this retailer.',
        ]);

        $retailerData = $request->validate([
            'shop_name' => 'required|string|max:255',
            'pincode' => ['required', 'digits:6'],
            'gst' => ['required', 'unique:retailers,gst,' . $retailer->id, 'regex:/^[a-zA-Z0-9]+$/'],
            'drug_license_no' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\/\-]+$/'],
            'distributor_id' => 'nullable|exists:distributors,id',
            'field_staff_id' => 'nullable|exists:fieldstaffs,id',
            'sales_manager_id' => 'nullable|exists:sales_managers,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_no' => ['required', 'digits:10', 'regex:/^[1-9][0-9]{9}$/'],
            'address' => ['required', 'string'],
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
        ], [
            'contact_no.regex' => 'The contact number must not start with zero.',
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
                'role' => 'retailer',
                'status' => $request->status ?? 'inactive',
                'contact_no' => $retailerData['contact_no'],
                'address' => $retailerData['address'],
                'pincode' => $retailerData['pincode'],
            ]);
            $user->assignRole('retailer');
            
            $retailer->user_id = $user->id;
            $retailer->save();
        } else {
            // Standard update
            $userUpdateData = [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'contact_no' => $retailerData['contact_no'],
                'address' => $retailerData['address'],
                'pincode' => $retailerData['pincode'],
            ];

            if ($request->filled('password')) {
                $userUpdateData['password'] = Hash::make($request->password);
            }

            if ($request->filled('status')) {
                $userUpdateData['status'] = $request->status;
            }

            $retailer->user->update($userUpdateData);
        }

        $retailer->update(array_merge($retailerData, ['pincode' => $request->pincode]));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $userId ? 'Retailer updated successfully!' : 'Retailer record repaired and updated successfully!'
            ]);
        }

        return redirect()->route('admin.retailers.index')->with('success', 'Retailer updated successfully!');
    }

    public function destroy(Retailer $retailer)
    {
        try {
            if ($retailer->user) {
                $retailer->user->delete(); 
            }

            $retailer->delete();
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Retailer deleted successfully!']);
            }
            return redirect()->route('admin.retailers.index')->with('success', 'Retailer deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete Retailer. They may have active Orders.'], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete Retailer.');
        }
    }

    public function activate(Retailer $retailer)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        if ($currentUser->hasAnyRole(['superadmin', 'admin']) || $currentUser->hasRole('salesmanager')) {
            if (!$retailer->user) {
                $msg = 'Cannot activate: User account missing for this record. Please edit and save to repair the account.';
                return request()->ajax() ? response()->json(['success' => false, 'message' => $msg], 422) : redirect()->back()->with('error', $msg);
            }
            $retailer->user->status = 'active';
            $retailer->user->save();

            $this->clearUserNotifications($retailer->user->id);

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Retailer activated successfully!']);
            }
            return redirect()->back()->with('success', 'Retailer activated successfully!');
        }

        if (request()->ajax()) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to change the status of this user.'], 403);
        }
        return redirect()->back()->with('error', 'You do not have permission to change the status of this user.');
    }

    public function deactivate(Retailer $retailer)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        if ($currentUser->hasAnyRole(['superadmin', 'admin']) || $currentUser->hasRole('salesmanager')) {
            if (!$retailer->user) {
                $msg = 'Cannot deactivate: User account missing for this record.';
                return request()->ajax() ? response()->json(['success' => false, 'message' => $msg], 422) : redirect()->back()->with('error', $msg);
            }
            $retailer->user->status = 'inactive';
            $retailer->user->save();

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Retailer deactivated successfully!']);
            }
            return redirect()->back()->with('success', 'Retailer deactivated successfully!');
        }

        if (request()->ajax()) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to change the status of this user.'], 403);
        }
        return redirect()->back()->with('error', 'You do not have permission to change the status of this user.');
    }

    public function getAreas($district_id)
    {
        $areas = Area::where('district_id', $district_id)->orderBy('name', 'asc')->get();
        return response()->json($areas);
    }
}
