<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SalesManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class SalesManagerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SalesManager::with('user')->select('sales_managers.*')->orderBy('sales_managers.id', 'desc');
            return DataTables::of($data)
                ->addIndexColumn()
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
            'contact_no' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'salesmanager',
            'status' => 'inactive',
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
                url('/sales-managers')
            ));
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Sales Manager added successfully!'
            ]);
        }

        return redirect()->route('admin.sales-managers.index')->with('success', 'Sales Manager added successfully!');
    }

    public function update(Request $request, SalesManager $salesManager)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $salesManager->user->id,
            'password' => 'nullable|string|min:4|confirmed',
            'contact_no' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
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
                'message' => 'Sales Manager updated successfully!'
            ]);
        }

        return redirect()->route('admin.sales-managers.index')->with('success', 'Sales Manager updated successfully!');
    }

    public function destroy(SalesManager $salesManager)
    {
        try {
            $salesManager->user->delete();
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Sales Manager deleted successfully!']);
            }
            return redirect()->route('admin.sales-managers.index')->with('success', 'Sales Manager deleted successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete Sales Manager. They likely have assigned Field Staff or Retailers.'], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete Sales Manager. They likely have assigned Field Staff or Retailers.');
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

        return redirect()->route('admin.sales-managers.index')->with('success', 'Sales Manager activated successfully!');
    }

    public function deactivate(SalesManager $salesManager)
    {
        $salesManager->user->status = 'inactive';
        $salesManager->user->save();

        return redirect()->route('admin.sales-managers.index')->with('success', 'Sales Manager deactivated successfully!');
    }
}
