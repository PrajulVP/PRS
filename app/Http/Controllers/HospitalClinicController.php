<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HospitalClinic;
use App\Models\District;
use App\Models\Area;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class HospitalClinicController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = HospitalClinic::with(['district', 'area']);

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->filled('district_id')) {
                $query->where('district_id', $request->district_id);
            }

            if ($request->filled('area_id')) {
                $query->where('area_id', $request->area_id);
            }

            $query->orderBy('id', 'desc');

            /** @var \App\Models\User $currentUser */
            $currentUser = Auth::user();

            return DataTables::of($query)
                ->addIndexColumn()
                ->filterColumn('district_name', function($q, $keyword) {
                    $q->whereHas('district', function($sub) use ($keyword) {
                        $sub->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('area_name', function($q, $keyword) {
                    $q->whereHas('area', function($sub) use ($keyword) {
                        $sub->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('can_edit', function($row) use ($currentUser) {
                    return $currentUser->hasAnyRole(['admin', 'superadmin']) || $currentUser->hasPermissionToCategory('hospitals_clinics', 'edit');
                })
                ->addColumn('can_delete', function($row) use ($currentUser) {
                    return $currentUser->hasAnyRole(['admin', 'superadmin']) || $currentUser->hasPermissionToCategory('hospitals_clinics', 'delete');
                })
                ->addColumn('district_name', function ($row) {
                    return $row->district ? $row->district->name : 'N/A';
                })
                ->addColumn('area_name', function ($row) {
                    return $row->area ? $row->area->name : 'N/A';
                })
                ->addColumn('address', function ($row) {
                    return $row->address ?? 'N/A';
                })
                ->addColumn('latitude', function ($row) {
                    return $row->latitude ?? '';
                })
                ->addColumn('longitude', function ($row) {
                    return $row->longitude ?? '';
                })
                ->make(true);
        }

        $districts = District::orderBy('name', 'asc')->get();

        $stats = [
            'total' => HospitalClinic::count(),
            'active' => HospitalClinic::where('status', 'active')->count(),
            'inactive' => HospitalClinic::where('status', 'inactive')->count(),
        ];

        return view('admin.hospitals_clinics.index', compact('districts', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'district_id' => 'nullable|exists:districts,id',
            'area_id' => 'nullable|exists:areas,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|in:active,inactive',
        ]);

        $data['status'] = $data['status'] ?? 'active';

        $hospital = HospitalClinic::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Hospital/Clinic created successfully!',
                'data' => $hospital
            ]);
        }

        return redirect()->route('admin.hospitals-clinics.index')->with('success', 'Hospital/Clinic created successfully!');
    }

    public function show(HospitalClinic $hospitals_clinic)
    {
        $hospitals_clinic->load(['district', 'area']);
        return response()->json([
            'success' => true,
            'data' => $hospitals_clinic
        ]);
    }

    public function update(Request $request, HospitalClinic $hospitals_clinic)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'district_id' => 'nullable|exists:districts,id',
            'area_id' => 'nullable|exists:areas,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|in:active,inactive',
        ]);

        $hospitals_clinic->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Hospital/Clinic updated successfully!'
            ]);
        }

        return redirect()->route('admin.hospitals-clinics.index')->with('success', 'Hospital/Clinic updated successfully!');
    }

    public function destroy(HospitalClinic $hospitals_clinic)
    {
        $hospitals_clinic->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Hospital/Clinic deleted successfully!']);
        }

        return redirect()->route('admin.hospitals-clinics.index')->with('success', 'Hospital/Clinic deleted successfully!');
    }

    public function resetLocation(HospitalClinic $hospitals_clinic)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser->hasAnyRole(['superadmin', 'admin']) && !$currentUser->hasRole('salesmanager')) {
            $msg = 'Only Admin or Sales Manager can reset hospital/clinic location.';
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->back()->with('error', $msg);
        }

        if (empty($hospitals_clinic->latitude) && empty($hospitals_clinic->longitude)) {
            $msg = 'No location exists to reset for this hospital/clinic.';
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $hospitals_clinic->update([
            'latitude' => null,
            'longitude' => null,
            'location_locked' => false,
        ]);

        $msg = 'Hospital/Clinic location reset successfully! Field staff can now re-capture the location.';
        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }
        return redirect()->back()->with('success', $msg);
    }

    public function getAreas($district_id)
    {
        $areas = Area::where('district_id', $district_id)->orderBy('name', 'asc')->get();
        return response()->json($areas);
    }
}
