<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SalesManager;
use App\Models\FieldStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use DataTables;

class FieldStaffController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getFieldStaffsData();
        }
        return view('admin.fieldstaffs.index');
    }

    private function getFieldStaffsData()
    {
        $query = FieldStaff::with('user', 'salesManager.user');

        if (Auth::user()->hasRole('salesmanager')) {
            $query->where('sales_manager_id', Auth::user()->salesManager->id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $editUrl = route('admin.fieldstaffs.edit', $row->id);
                $showUrl = route('admin.fieldstaffs.show', $row->id);
                $deleteUrl = route('admin.fieldstaffs.destroy', $row->id);
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

    public function show(FieldStaff $fieldstaff)
    {
        $fieldstaff->load('user', 'salesManager.user');
        return view('admin.fieldstaffs.show', compact('fieldstaff'));
    }

    public function create()
    {
        $salesManagers = SalesManager::whereHas('user', function($query) {
            $query->where('status', 'active');
        })->get();
        return view('admin.fieldstaffs.create', compact('salesManagers'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('salesmanager')) {
            return redirect()->route('admin.fieldstaffs.index')->with('error', 'You are not authorized to create a field staff.');
        }

        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4',
            'contact_no' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $fieldstaffData = $request->validate([
            'pincode' => 'required|string',
            'sales_manager_id' => 'required|exists:sales_managers,id',
        ]);

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'role' => 'fieldstaff',
            'status' => 'inactive',
            'contact_no' => $userData['contact_no'],
            'address' => $userData['address'],
        ]);
        $user->assignRole('fieldstaff');

        $fieldstaff = new FieldStaff($fieldstaffData);
        $fieldstaff->user_id = $user->id;
        if (Auth::user()->hasRole('salesmanager')) {
            $fieldstaff->sales_manager_id = Auth::user()->salesManager->id;
        }
        $fieldstaff->save();

        return redirect()->route('admin.fieldstaffs.index')->with('success', 'Field staff added successfully and is pending approval.');
    }

    public function edit(FieldStaff $fieldstaff)
    {
        $salesManagers = SalesManager::whereHas('user', function($query) {
            $query->where('status', 'active');
        })->get();
        return view('admin.fieldstaffs.edit', compact('fieldstaff', 'salesManagers'));
    }

    public function update(Request $request, FieldStaff $fieldstaff)
    {
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $fieldstaff->user->id,
            'password' => 'nullable|min:4',
            'contact_no' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $fieldstaffData = $request->validate([
            'pincode' => 'required|string',
            'sales_manager_id' => 'required|exists:sales_managers,id',
        ]);

        $userUpdateData = [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'fieldstaff',
            'contact_no' => $userData['contact_no'],
            'address' => $userData['address'],
        ];
        if (!empty($userData['password'])) {
            $userUpdateData['password'] = Hash::make($userData['password']);
        }
        $fieldstaff->user->update($userUpdateData);

        $fieldstaff->update($fieldstaffData);

        return redirect()->route('admin.fieldstaffs.index')->with('success', 'Field staff updated successfully!');
    }

    public function destroy(FieldStaff $fieldstaff)
    {
        $fieldstaff->delete();
        return redirect()->route('admin.fieldstaffs.index')->with('success', 'Field staff deleted successfully!');
    }

    public function activate(FieldStaff $fieldstaff)
    {
        if (!Auth::user()->hasRole('admin')) {
            return redirect()->route('fieldstaffs.index')->with('error', 'You are not authorized to activate a field staff.');
        }

        $fieldstaff->user->status = 'active';
        $fieldstaff->user->save();

        return redirect()->route('admin.fieldstaffs.index')->with('success', 'Field staff activated successfully!');
    }
}