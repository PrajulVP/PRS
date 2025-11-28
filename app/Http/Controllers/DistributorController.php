<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\District;
use App\Models\Distributor; // Added
use App\Models\SalesManager; // Added
use Yajra\DataTables\DataTables;


class DistributorController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getDistributorsData();
        }
        return view('admin.distributors.index');
    }

    private function getDistributorsData()
    {
        $data = Distributor::with('user', 'district', 'area', 'salesManager.user')->select('distributors.*');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $editUrl = route('admin.distributors.edit', $row->id);
                $showUrl = route('admin.distributors.show', $row->id);
                $deleteUrl = route('admin.distributors.destroy', $row->id);
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

    public function show(Distributor $distributor)
    {
        $distributor->load('user', 'district', 'area');
        return view('admin.distributors.show', compact('distributor'));
    }

    public function create()
    {
        $districts = District::all();
        $salesManagers = SalesManager::with('user')->get(); // Fetch sales managers
        return view('admin.distributors.create', compact('districts', 'salesManagers'));
    }

    public function store(Request $request)
    {
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4',
        ]);

        $distributorData = $request->validate([
            'name' => 'required|string|max:255',
            'gst' => 'required|unique:distributors',
            'drug_license_no' => 'nullable|string',
            'contact_no' => 'required',
            'address' => 'required',
            'pincode' => 'required',
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
            'sales_manager_id' => 'required|exists:sales_managers,id', // New validation
        ]);

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'role' => 'distributor',
            'status' => 'inactive',
        ]);
        $user->assignRole('distributor');

        $distributor = new Distributor($distributorData);
        $distributor->user_id = $user->id;
        $distributor->sales_manager_id = $distributorData['sales_manager_id']; // Assign sales manager
        $distributor->save();

        return redirect()->route('admin.distributors.index')->with('success', 'Distributor added successfully!');
    }

    public function edit(Distributor $distributor)
    {
        $districts = District::all();
        $areas = Area::where('district_id', $distributor->district_id)->get();
        $salesManagers = SalesManager::with('user')->get(); // Fetch sales managers
        return view('admin.distributors.edit', compact('distributor','districts','areas', 'salesManagers'));
    }

    public function update(Request $request, Distributor $distributor)
    {
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $distributor->user->id,
            'password' => 'nullable|min:4',
        ]);

        $distributorData = $request->validate([
            'name' => 'required|string|max:255',
            'gst' => 'required|unique:distributors,gst,' . $distributor->id,
            'drug_license_no' => 'nullable|string',
            'contact_no' => 'required',
            'address' => 'required',
            'pincode' => 'required',
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
            'sales_manager_id' => 'required|exists:sales_managers,id', // New validation
        ]);

        $userUpdateData = [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'distributor',
        ];
        if (!empty($userData['password'])) {
            $userUpdateData['password'] = Hash::make($userData['password']);
        }
        $distributor->user->update($userUpdateData);

        $distributor->update(array_merge($distributorData, ['sales_manager_id' => $distributorData['sales_manager_id']])); // Update sales manager

        return redirect()->route('admin.distributors.index')->with('success','Distributor updated successfully!');
    }

    public function destroy(Distributor $distributor) // Changed type-hint
    {
        $distributor->delete(); // This will cascade delete the User due to foreign key constraint
        return redirect()->route('admin.distributors.index')->with('success','Distributor deleted successfully!');
    }

    // AJAX: Get areas for selected district
    public function getAreas(District $district)
    {
        
        return response()->json($district->areas);
    }

}