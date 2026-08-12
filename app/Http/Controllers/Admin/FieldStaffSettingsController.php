<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\LeaveType;
use App\Models\VisitPurpose;

class FieldStaffSettingsController extends Controller
{
    /**
     * Show field staff settings page
     */
    public function index()
    {
        $geofence_radius = Setting::getValue('geofence_radius', '20');
        $ta_rate_per_km = Setting::getValue('ta_rate_per_km', '10');
        $da_hq_rate = Setting::getValue('da_hq_rate', '250');
        $da_outstation_rate = Setting::getValue('da_outstation_rate', '500');
        $hq_radius_km = Setting::getValue('hq_radius_km', '15');
        
        $leaveTypes = LeaveType::all();
        $visitPurposes = VisitPurpose::all();

        return view('admin.settings.field_staff', compact(
            'geofence_radius', 'ta_rate_per_km', 'da_hq_rate', 'da_outstation_rate',
            'hq_radius_km', 'leaveTypes', 'visitPurposes'
        ));
    }

    /**
     * Store/Update a visit purpose via AJAX
     */
    public function savePurpose(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer|exists:visit_purposes,id',
            'name' => 'required|string|max:255|unique:visit_purposes,name,' . $request->id,
        ]);

        if ($request->id) {
            $purpose = VisitPurpose::find($request->id);
            $purpose->update(['name' => $request->name]);
            $message = 'Visit purpose updated successfully.';
        } else {
            $purpose = VisitPurpose::create(['name' => $request->name]);
            $message = 'Visit purpose added successfully.';
        }

        return response()->json(['message' => $message, 'purpose' => $purpose]);
    }

    /**
     * Delete a visit purpose via AJAX
     */
    public function deletePurpose(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:visit_purposes,id',
        ]);

        $purpose = VisitPurpose::find($request->id);
        if ($purpose) {
            $purpose->delete();
        }

        return response()->json(['message' => 'Visit purpose deleted successfully.']);
    }
}
