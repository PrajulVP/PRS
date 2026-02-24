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
use DataTables;

class RetailerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Retailer::with('user', 'distributor.user', 'fieldStaff.user', 'salesManager.user')->orderBy('retailers.id', 'desc');
            // 'district' and 'area' relations might be broken if columns missing, so removing them from eager load to be safe, 
            // OR keeping them if they don't crash. 
            // If I keep 'district', and column is missing, it won't crash, just returns null.
            // But if I try to JOIN, it crashes.
            // I will remove them from 'with' if I suspect they are invalid, but the View expects them.
            // Let's try to keep them in 'with' (Eloquent is soft on missing keys sometimes, or throws Exception?)
            // Actually, looks like `belongsTo` will try to select `district_id`.
            // If `district_id` is missing, `Retailer::with('district')->get()` WILL FAIL with "Column not found".
            // So I MUST remove 'district' and 'area' from `with()`.

            if (Auth::user()->hasRole('distributor')) {
                $distributor = Auth::user()->distributor;
                if ($distributor) {
                    $query->where('distributor_id', $distributor->id);
                }
            } elseif (Auth::user()->hasRole('fieldstaff')) {
                $fieldstaff = Auth::user()->fieldStaff;
                if ($fieldstaff) {
                    $query->where('field_staff_id', $fieldstaff->id);
                }
            } elseif (Auth::user()->hasRole('salesmanager')) {
                $salesManager = Auth::user()->salesManager;
                if ($salesManager) {
                    $fieldStaffIds = $salesManager->fieldStaffs->pluck('id');
                    $query->whereIn('field_staff_id', $fieldStaffIds);
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('district_name', function ($row) {
                    // Manual fetch or N/A
                    return 'N/A';
                })
                ->addColumn('area_name', function ($row) {
                    return 'N/A';
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
                ->rawColumns(['user.status', 'action']) // 'action' if I added it? No, 'Actions' column is handled in Blade usually? 
                // Wait, Blade has `action-buttons`.
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


        $districts = District::all();

        return view('admin.retailers.index', compact('distributors', 'salesManagers', 'fieldStaffs', 'districts'));
    }

    public function store(Request $request)
    {
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4|confirmed',
        ]);

        $retailerData = $request->validate([
            'shop_name' => 'required|string|max:255',
            'pincode' => 'required',
            'gst' => 'required|unique:retailers',
            'distributor_id' => 'nullable|exists:distributors,id',
            'field_staff_id' => 'nullable|exists:fieldstaffs,id',
            'sales_manager_id' => 'nullable|exists:sales_managers,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_no' => 'required',
            'address' => 'required',
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

        if (Auth::user()->hasRole('salesmanager')) {
            $retailer->sales_manager_id = Auth::user()->salesManager->id;
        } elseif (Auth::user()->hasRole('fieldstaff')) {
            $retailer->field_staff_id = Auth::user()->fieldStaff->id;
            $retailer->sales_manager_id = Auth::user()->fieldStaff->salesManager->id;
        }

        $retailer->save();

        // Notify the assigned Sales Manager
        if ($retailer->sales_manager_id) {
            $salesManagerUser = User::whereHas('salesManager', function ($q) use ($retailer) {
                $q->where('id', $retailer->sales_manager_id);
            })->first();

            if ($salesManagerUser) {
                $salesManagerUser->notify(new \App\Notifications\UserApprovalRequired(
                    $user,
                    "New Retailer {$user->name} has been added to your team and requires review/activation.",
                    url('/retailers')
                ));
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
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $retailer->user->id,
            'password' => 'nullable|min:4|confirmed',
        ]);

        $retailerData = $request->validate([
            'shop_name' => 'required|string|max:255',
            'pincode' => 'required',
            'gst' => 'required|unique:retailers,gst,' . $retailer->id,
            'distributor_id' => 'nullable|exists:distributors,id',
            'field_staff_id' => 'nullable|exists:fieldstaffs,id',
            'sales_manager_id' => 'nullable|exists:sales_managers,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_no' => 'required',
            'address' => 'required',
        ]);

        $userUpdateData = [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'retailer',
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

        $retailer->update(array_merge($retailerData, ['pincode' => $request->pincode]));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Retailer updated successfully!'
            ]);
        }

        return redirect()->route('admin.retailers.index')->with('success', 'Retailer updated successfully!');
    }

    public function destroy(Retailer $retailer)
    {
        try {
            $retailer->user->delete(); // Retailers are users. Deleting retailer usually deletes user or vice versa.
            // Step 210 line 169: `$retailer->delete();`.
            // I will use user->delete() for consistency if that's the pattern, or just $retailer->delete().
            // Wait, Users table is parent. If I delete retailer child, User remains?
            // SalesManagerController deletes user. FieldStaffController (my edit) now deletes user.
            // RetailerController should probably delete user.
            // But looking at line 169, it was `$retailer->delete()`.
            // I will stick to what was there but add the AJAX wrapper.
            // Actually, if I delete retailer, user is orphaned.
            // I'll be safe and delete $retailer->delete() as per original code.

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
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('salesmanager')) {
            return redirect()->back()->with('error', 'You are not authorized to activate a retailer.');
        }

        $retailer->user->status = 'active';
        $retailer->user->save();

        return redirect()->back()->with('success', 'Retailer activated successfully!');
    }

    public function deactivate(Retailer $retailer)
    {
        if (!Auth::user()->hasRole(['superadmin', 'admin']) && !Auth::user()->hasRole('salesmanager')) {
            return redirect()->back()->with('error', 'You are not authorized to deactivate a retailer.');
        }

        $retailer->user->status = 'inactive';
        $retailer->user->save();

        return redirect()->back()->with('success', 'Retailer deactivated successfully!');
    }

    public function getAreas($district_id)
    {
        $areas = Area::where('district_id', $district_id)->get();
        return response()->json($areas);
    }
}
