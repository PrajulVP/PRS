<?php


namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Area;
use App\Models\District;
use App\Models\Distributor;
use App\Models\FieldStaff; // Added
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class FieldStaffController extends Controller
{
    public function index()
    {
        $fieldstaffs = FieldStaff::with('user', 'distributor.user')->latest()->get(); // Changed
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
        // Separate validation for User and FieldStaff fields
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4',
            'contact_no' => 'nullable|string',
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
            'address' => 'nullable|string',
        ]);

        $fieldstaffData = $request->validate([
            'assigned_distributor_id' => 'required|exists:distributors,id',
            'status' => 'in:active,inactive',
        ]);

        // Create User
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'role' => 'fieldstaff', // Keep for consistency if other parts rely on it
            'contact_no' => $userData['contact_no'],
            'district_id' => $userData['district_id'],
            'area_id' => $userData['area_id'],
            'address' => $userData['address'],
        ]);
        $user->assignRole('fieldstaff');

        // Create FieldStaff profile
        $fieldstaff = new FieldStaff($fieldstaffData);
        $fieldstaff->user_id = $user->id;
        $fieldstaff->save();


        return redirect()->route('fieldstaffs.index')->with('success', 'Field staff added successfully!');
    }


    public function edit(FieldStaff $fieldstaff) // Changed type-hint
    {
        $districts = District::all();
        $distributors = Distributor::all();
        $areas = Area::where('district_id', $fieldstaff->user->district_id)->get(); // Changed
        return view('admin.fieldstaffs.edit', compact('fieldstaff', 'districts', 'distributors', 'areas'));
    }


    public function update(Request $request, FieldStaff $fieldstaff) // Changed type-hint
    {
        // Separate validation for User and FieldStaff fields
        $userData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $fieldstaff->user->id,
            'password' => 'nullable|min:4',
            'contact_no' => 'nullable|string',
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',
            'address' => 'nullable|string',
        ]);

        $fieldstaffData = $request->validate([
            'assigned_distributor_id' => 'required|exists:distributors,id',
            'status' => 'in:active,inactive',
        ]);

        // Update User
        $userUpdateData = [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'fieldstaff', // Keep for consistency
            'contact_no' => $userData['contact_no'],
            'district_id' => $userData['district_id'],
            'area_id' => $userData['area_id'],
            'address' => $userData['address'],
        ];
        if (!empty($userData['password'])) {
            $userUpdateData['password'] = Hash::make($userData['password']);
        }
        $fieldstaff->user->update($userUpdateData);

        // Update FieldStaff profile
        $fieldstaff->update($fieldstaffData);

        return redirect()->route('fieldstaffs.index')->with('success', 'Field staff updated successfully!');
    }

    public function destroy(FieldStaff $fieldstaff) // Changed type-hint
    {
        $fieldstaff->delete(); // This will cascade delete the User due to foreign key constraint
        return redirect()->route('fieldstaffs.index')->with('success', 'Field staff deleted successfully!');
    }

    // AJAX: Get areas for selected district
    public function getAreas(District $district)
    {
        return response()->json($district->areas);
    }

    // AJAX: Get distributors for selected district
    public function getDistributors(District $district)
    {
        // Assuming a distributor has a district_id
        $distributors = Distributor::where('district_id', $district->id)->get();
        return response()->json($distributors);
    }
}
