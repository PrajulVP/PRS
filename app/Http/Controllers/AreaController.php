<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\District;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Area::with('district');

            // Apply search filter
            if ($request->has('search') && !empty($request->input('search')['value'])) {
                $searchValue = $request->input('search')['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('name', 'like', "%{$searchValue}%")
                        ->orWhereHas('district', function ($subQuery) use ($searchValue) {
                            $subQuery->where('name', 'like', "%{$searchValue}%");
                        });
                });
            }

            $totalFiltered = $query->count();
            $totalData = Area::count();

            // Apply order (sorting)
            if ($request->has('order') && !empty($request->input('order'))) {
                $columnIndex = $request->input('order')[0]['column'];
                $columnName = $request->input('columns')[$columnIndex]['data'];
                $sortDirection = $request->input('order')[0]['dir'];

                if (!empty($columnName)) {
                    if ($columnName == 'district_name') {
                        $query->join('districts', 'areas.district_id', '=', 'districts.id')
                            ->orderBy('districts.name', $sortDirection)
                            ->select('areas.*');
                    } else {
                        $query->orderBy($columnName, $sortDirection);
                    }
                }
            } else {
                $query->orderBy('id', 'desc'); // Default sort
            }

            // Apply pagination
            $start = $request->input('start');
            $length = $request->input('length');

            if ($length != -1 && $start !== null && $length !== null) {
                $query->offset($start)->limit($length);
            }

            $areas = $query->get();

            $formattedAreas = $areas->map(function ($area) {
                return [
                    'id' => $area->id,
                    'name' => $area->name,
                    'district_name' => $area->district->name ?? 'N/A',
                    'district_id' => $area->district_id,
                    'actions' => null, // Actions column will be rendered by DataTables
                ];
            });

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalData,
                'recordsFiltered' => $totalFiltered,
                'data' => $formattedAreas,
            ]);
        }

        $districts = District::all(); // Fetch districts for the form
        $totalAreas = Area::count();
        $totalDistricts = $districts->count();
        return view('admin.areas.index', compact('districts', 'totalAreas', 'totalDistricts'));
    }

    public function create()
    {
        $districts = District::all();
        return view('areas.index', compact('districts'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate(['district_id' => 'required|exists:districts,id', 'name' => 'required|string']);
        $exists = Area::where('district_id', $validated['district_id'])->where('name', $validated['name'])->exists();
        if ($exists) {
            if ($request->ajax()) {
                return response()->json(['status' => false, 'message' => 'This area already exists for the selected district.', 'data' => null], 422);
            }
            return redirect()->back()->withErrors(['name' => 'This area already exists for the selected district.']);
        }
        $area = Area::create($validated);

        if ($request->ajax()) {
            return response()->json(['status' => true, 'message' => 'Area created successfully', 'data' => $area], 201);
        }
        return redirect()->route('areas.index')->with('success', 'Area created successfully');
    }


    public function show(Request $request, Area $area)
    {
        $area->load('district');

        if ($request->ajax()) {
            return response()->json(['status' => true, 'message' => 'Area fetched', 'data' => $area]);
        }
        return view('areas.index', compact('area'));
    }

    public function edit(Area $area)
    {
        $districts = District::all();
        return view('areas.edit', compact('area', 'districts'));
    }


    public function update(Request $request, Area $area)
    {
        $validated = $request->validate(['district_id' => 'required|exists:districts,id', 'name' => 'required|string']);
        $exists = Area::where('district_id', $validated['district_id'])->where('name', $validated['name'])->where('id', '<>', $area->id)->exists();
        if ($exists) {
            if ($request->ajax()) {
                return response()->json(['status' => false, 'message' => 'This area already exists for the selected district.', 'data' => null], 422);
            }
            return redirect()->back()->withErrors(['name' => 'This area already exists for the selected district.']);
        }
        try {
            $area->update($validated);
        } catch (QueryException $e) {
            if ($request->ajax()) {
                return response()->json(['status' => false, 'message' => 'Update failed - duplicate', 'data' => null], 422);
            }
            return redirect()->back()->withErrors(['name' => 'Update failed - duplicate']);
        }

        if ($request->ajax()) {
            return response()->json(['status' => true, 'message' => 'Area updated successfully', 'data' => $area]);
        }
        return redirect()->route('areas.index')->with('success', 'Area updated successfully');
    }


    public function destroy(Request $request, Area $area)
    {
        try {
            $area->delete();
        } catch (QueryException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Area could not be deleted due to related records.',
                    'data' => null
                ], 422);
            }
            return redirect()->back()->withErrors(['error' => 'Area could not be deleted due to related records.']);
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Area deleted',
                'data' => null
            ]);
        }
        return redirect()->route('areas.index')->with('success', 'Area deleted successfully');
    }
}
