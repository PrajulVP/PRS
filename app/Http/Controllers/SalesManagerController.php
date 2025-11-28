<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SalesManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use DataTables;

class SalesManagerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getSalesManagersData();
        }
        return view('admin.salesmanagers.index');
    }

    private function getSalesManagersData()
    {
        $data = SalesManager::with('user')->select('sales_managers.*');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $editUrl = route('admin.salesmanagers.edit', $row->id);
                $showUrl = route('admin.salesmanagers.show', $row->id);
                $deleteUrl = route('admin.salesmanagers.destroy', $row->id);
                $btn = '<div class="d-flex align-items-center gap-1">';
                $btn .= '<a href="'.$editUrl.'" class="btn btn-primary btn-sm px-3">
                            <i class="fa fa-edit"></i>
                        </a>';
                $btn .= '<a href="'.$showUrl.'" class="btn btn-info btn-sm px-3">
                            <i class="fa fa-eye"></i>
                        </a>';
                $btn .= '<form action="'.$deleteUrl.'" method="POST" onsubmit="return confirm(\'Are you sure?\')" class="m-0 p-0">
                            '.csrf_field().method_field('DELETE').'
                            <button type="submit" class="btn btn-danger btn-sm px-3">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show(SalesManager $salesManager)
    {
        $salesManager->load('user');
        return view('admin.salesmanagers.show', compact('salesManager'));
    }

    public function create()
    {
        return view('admin.salesmanagers.create');
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

        return redirect()->route('admin.salesmanagers.index')->with('success', 'Sales Manager added successfully!');
    }

    public function edit(SalesManager $salesManager)
    {
        $salesManager->load('user');
        return view('admin.salesmanagers.edit', compact('salesManager'));
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

        $salesManager->user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $salesManager->user->password,
        ]);

        $salesManager->update([
            'name' => $request->name,
            'email' => $request->email,
            'contact_no' => $request->contact_no,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.salesmanagers.index')->with('success', 'Sales Manager updated successfully!');
    }

    public function destroy(SalesManager $salesManager)
    {
        $salesManager->user->delete();
        return redirect()->route('admin.salesmanagers.index')->with('success', 'Sales Manager deleted successfully!');
    }
}