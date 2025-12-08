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
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($request->ajax()) {
            $data = [];
            $roleType = '';

            if ($user->hasRole('superadmin')) {
                $roleType = 'superadmin';
                $distributors = Distributor::whereHas('user', function ($query) {
                    $query->where('status', 'inactive');
                })->with('user')->get()->map(function ($d) {
                    $d->role_display = 'Distributor';
                    return $d;
                });

                $managers = SalesManager::whereHas('user', function ($query) {
                    $query->where('status', 'inactive');
                })->with('user')->get()->map(function ($m) {
                    $m->role_display = 'Sales Manager';
                    return $m;
                });

                $data = $distributors->concat($managers);
            } elseif ($user->hasRole('admin')) {
                $roleType = 'admin';
                $data = FieldStaff::whereHas('user', function ($query) {
                    $query->where('status', 'inactive');
                })->with(['user', 'salesManager.user'])->get();
            } elseif ($user->hasRole('salesmanager')) {
                $roleType = 'salesmanager';
                if ($user->salesManager) {
                    $sales_manager_id = $user->salesManager->id;
                    $fieldStaffIds = FieldStaff::where('sales_manager_id', $sales_manager_id)->pluck('id');

                    $data = Retailer::where(function ($query) use ($sales_manager_id, $fieldStaffIds) {
                        $query->where('sales_manager_id', $sales_manager_id)
                            ->orWhereIn('field_staff_id', $fieldStaffIds);
                    })->whereHas('user', function ($q) {
                        $q->where('status', 'inactive');
                    })->with(['user', 'fieldStaff.user'])->get();
                }
            }

            // Format Data for DataTable
            $formatted = collect($data)->map(function ($item) use ($roleType) {
                $res = [
                    'id' => $item->id,
                    'name' => $item->user->name,
                    'email' => $item->user->email,
                    'role_type' => $roleType, // context
                ];

                if ($roleType === 'superadmin') {
                    $res['role'] = $item->role_display;
                    $res['activate_url'] = route('admin.users.activate', $item->user->id);
                } elseif ($roleType === 'admin') {
                    $res['linked_manager'] = $item->salesManager->user->name ?? 'N/A';
                    $res['activate_url'] = route('admin.field-staffs.activate', $item->id);
                } elseif ($roleType === 'salesmanager') {
                    $res['added_by'] = $item->fieldStaff->user->name ?? 'N/A';
                    $res['activate_url'] = route('admin.retailers.activate', $item->id);
                }

                return $res;
            });

            return response()->json(['data' => $formatted]);
        }

        // Determine view type to render correct headers
        $type = '';
        if ($user->hasRole('superadmin')) $type = 'superadmin';
        elseif ($user->hasRole('admin')) $type = 'admin';
        elseif ($user->hasRole('salesmanager')) $type = 'salesmanager';

        return view('admin.pending_approvals.index', compact('type'));
    }
}
