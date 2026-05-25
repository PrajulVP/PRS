<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function checkPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required'
        ]);

        if (Hash::check($request->current_password, auth()->user()->password)) {
            return response()->json(['valid' => true]);
        }

        return response()->json(['valid' => false, 'message' => 'The current password you entered is incorrect.']);
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'profile_pic' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = Auth::user();

        if ($user->profile_pic) {
            Storage::disk('public')->delete($user->profile_pic);
        }

        $path = $request->file('profile_pic')->store('profile_pics', 'public');
        $user->profile_pic = $path;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully.',
            'avatar_url' => $user->avatar_url
        ]);
    }

    public function removePhoto(Request $request)
    {
        $user = Auth::user();

        if ($user->profile_pic) {
            Storage::disk('public')->delete($user->profile_pic);
        }

        $user->profile_pic = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile picture removed successfully.',
            'avatar_url' => $user->avatar_url
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'address' => 'nullable|string|max:255',
            'pincode' => 'nullable|digits:6',
        ];

        if (!$user->hasRole('distributor')) {
            $rules['fathers_name'] = 'nullable|string|max:255';
            $rules['mothers_name'] = 'nullable|string|max:255';
        }

        // Add role-specific validation
        if ($user->hasRole('retailer')) {
            $rules['shop_name'] = 'nullable|string|max:255';
            $rules['gst'] = 'nullable|string|max:50';
            // Assuming drug_license_no might be added to retailers, but for now just shop_name/gst which are known
            $rules['drug_license_no'] = 'nullable|string|max:50|regex:/^[a-zA-Z0-9\/\-]+$/';
        } elseif ($user->hasRole('distributor')) {
            $rules['gst'] = 'nullable|string|max:50';
            $rules['drug_license_no'] = 'nullable|string|max:50|regex:/^[a-zA-Z0-9\/\-]+$/';
        }

        if ($user->hasAnyRole(['superadmin', 'admin', 'distributor'])) {
            $rules['contact_no'] = ['nullable', 'digits:10', 'regex:/^[1-9][0-9]{9}$/'];
            if ($user->hasAnyRole(['superadmin', 'admin'])) {
                $rules['email'] = [
                    'required', 'email', 'unique:users,email,' . $user->id,
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
                ];
            }
        }

        // Password change logic for ALL users
        if ($request->filled('new_password')) {
            $rules['current_password'] = ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The current password you entered is incorrect.');
                }
            }];
            $rules['new_password'] = 'required|string|min:6|confirmed';
        }

        $request->validate($rules);

        // Update User Common Fields
        $user->name = $request->name;
        $user->address = $request->address;
        $user->pincode = $request->pincode;
        $user->city = $request->city;
        if (!$user->hasRole('distributor')) {
            $user->fathers_name = $request->fathers_name;
            $user->mothers_name = $request->mothers_name;
        }

        if ($user->hasAnyRole(['superadmin', 'admin', 'distributor'])) {
            $user->contact_no = $request->contact_no;
            if ($user->hasAnyRole(['superadmin', 'admin'])) {
                $user->email = $request->email;
            }
        }

        // Update password if provided for any user
        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
        }

        if ($request->remove_profile_pic == '1') {
            if ($user->profile_pic) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_pic);
            }
            $user->profile_pic = null;
        } elseif ($request->hasFile('profile_pic')) {
            if ($user->profile_pic) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_pic);
            }
            $path = $request->file('profile_pic')->store('profile_pics', 'public');
            $user->profile_pic = $path;
        }

        $user->save();

        // Update Role Specific Fields
        if ($user->hasRole('retailer') && $user->retailer) {
            $user->retailer->update([
                'shop_name' => $request->shop_name,
                'gst' => $request->gst,
                // Update drug_license_no if the column exists (we'll ensure it does via migration)
                'drug_license_no' => $request->drug_license_no,
            ]);
        } elseif ($user->hasRole('distributor') && $user->distributor) {
            $user->distributor->update([
                'gst' => $request->gst,
                'drug_license_no' => $request->drug_license_no,
                'contact_no' => $request->contact_no,
            ]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
        }

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully.');
    }
}
