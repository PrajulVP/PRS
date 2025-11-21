<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Distributor;
use App\Models\Manager;
use App\Models\FieldStaff;
use App\Models\Retailer;

class PendingApprovalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];
        $type = '';

        if ($user->hasRole('superadmin')) {
            $distributors = Distributor::whereHas('user', function ($query) {
                $query->where('status', 'inactive');
            })->with('user')->get();
            $managers = Manager::whereHas('user', function ($query) {
                $query->where('status', 'inactive');
            })->with('user')->get();
            $data = $distributors->concat($managers);
            $type = 'superadmin';
        } elseif ($user->hasRole('admin')) {
            $data = FieldStaff::where('status', 'inactive')->with('user')->get();
            $type = 'admin';
        } elseif ($user->hasRole('manager')) {
            if ($user->manager) {
                $manager_id = $user->manager->id;
                // Get the fieldstaff associated with the manager
                $fieldStaffIds = FieldStaff::where('sales_manager_id', $manager_id)->pluck('id');
                // Get the retailers associated with the fieldstaff
                $data = Retailer::whereIn('field_staff_id', $fieldStaffIds)->where('status', 'inactive')->with('user')->get();
            }
            $type = 'manager';
        }

        return view('admin.pending_approvals.index', compact('data', 'type'));
    }
}
