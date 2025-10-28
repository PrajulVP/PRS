<?php


namespace App\Http\Controllers;


use App\Models\Area;
use App\Models\District;
use App\Models\Distributor;
use App\Models\FieldStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class FieldStaffController extends Controller
{
    public function index()
    {
        $fieldstaffs = FieldStaff::with('district', 'area', 'distributor')->latest()->get();
        return view('admin.fieldstaffs.index', compact('fieldstaffs'));
    }


    public function create()
    {
        $districts = District::all();
        $distributors = Distributor::all();
        return view('admin.fieldstaffs.create', compact('districts', 'distributors'));
    }


    public function store(Request $request)
    {
        $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:fieldstaffs,email',
        'password' => 'required|min:4',
        'contact_no' => 'nullable|string',
        'district_id' => 'required|exists:districts,id',
        'area_id' => 'required|exists:areas,id',
        'assigned_distributor_id' => 'required|exists:distributors,id',
        'address' => 'nullable|string',
        'status' => 'in:active,inactive',
    ]);


        $data['password'] = Hash::make($data['password']);


    FieldStaff::create($data);


        return redirect()->route('fieldstaffs.index')->with('success', 'Field staff added successfully!');
    }


    public function edit(FieldStaff $fieldstaff)
    {
        $districts = District::all();
        $distributors = Distributor::all();
        $areas = Area::where('district_id', $fieldstaff->district_id)->get();
        return view('admin.fieldstaffs.edit', compact('fieldstaff', 'districts', 'distributors', 'areas'));
    }


    public function update(Request $request, FieldStaff $fieldstaff)
    {
        $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:fieldstaffs,email,' . $fieldstaff->id,
        'password' => 'nullable|min:4',
        'contact_no' => 'nullable|string',
        'district_id' => 'required|exists:districts,id',
        'area_id' => 'required|exists:areas,id',
        'assigned_distributor_id' => 'required|exists:distributors,id',
        'address' => 'nullable|string',
        'status' => 'in:active,inactive',
    ]);


    if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
    } else {
        unset($data['password']);
    }
    }
}