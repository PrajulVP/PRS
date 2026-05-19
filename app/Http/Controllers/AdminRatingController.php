<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Distributor;
use App\Models\FieldStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRatingController extends Controller
{
    /**
     * Display a listing of distributor staff ratings.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Base query for ratings that were submitted by a distributor (where distributor_id is not null)
        $query = Rating::with(['distributor.user', 'fieldStaff.user'])
            ->whereNotNull('distributor_id');

        // Fetch distributors and field staff for the filter dropdown based on role
        if ($user->hasRole('salesmanager')) {
            $managerId = $user->sales_manager_id ?? ($user->salesManager?->id ?? null);
            
            $distributors = Distributor::with('user')
                ->where('sales_manager_id', $managerId)
                ->get();

            $fieldStaff = FieldStaff::with('user')
                ->where('sales_manager_id', $managerId)
                ->get();
                
            // Restrict ratings in query to only those distributors assigned to this sales manager
            $query->whereHas('distributor', function($q) use ($managerId) {
                $q->where('sales_manager_id', $managerId);
            });
        } else {
            // Admin or Superadmin
            $distributors = Distributor::with('user')->get();
            $fieldStaff = FieldStaff::with('user')->get();
        }

        // Apply Distributor Filter
        if ($request->filled('distributor_id')) {
            $query->where('distributor_id', $request->distributor_id);
        }

        // Apply Field Staff Filter
        if ($request->filled('field_staff_id')) {
            $query->where('field_staff_id', $request->field_staff_id);
        }

        // Handle AJAX requests for DataTables
        if ($request->ajax()) {
            return response()->json([
                'data' => $query->latest()->get()
            ]);
        }

        return view('admin.ratings.index', compact('distributors', 'fieldStaff'));
    }
}
