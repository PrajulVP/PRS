<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Distributor;
use App\Models\SalesManager;
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
            $managers = SalesManager::whereHas('user', function ($query) {
                $query->where('status', 'inactive');
            })->with('user')->get();
            $data = $distributors->concat($managers);
            $type = 'superadmin';
        } elseif ($user->hasRole('admin')) {
            $data = FieldStaff::whereHas('user', function ($query) {
                $query->where('status', 'inactive');
            })->with('user')->get();
            $type = 'admin';
        } elseif ($user->hasRole('salesmanager')) {
            if ($user->salesManager) {
                $sales_manager_id = $user->salesManager->id;
                $fieldStaffIds = FieldStaff::where('sales_manager_id', $sales_manager_id)->pluck('id');
                
                $query = Retailer::where(function ($query) use ($sales_manager_id, $fieldStaffIds) {
                    $query->where('sales_manager_id', $sales_manager_id)
                          ->orWhereIn('field_staff_id', $fieldStaffIds);
                })->whereHas('user', function ($q) {
                    $q->where('status', 'inactive');
                })->with('user');

                $data = $query->get();
            }
            $type = 'salesmanager';
        }

        return view('admin.pending_approvals.index', compact('data', 'type'));
    }
}
