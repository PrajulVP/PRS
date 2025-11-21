<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\FieldStaff;
use App\Models\Manager;
use App\Models\Retailer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $users_to_approve = collect();
        $user_type = '';

        if ($user->hasRole('superadmin')) {
            $distributors = Distributor::whereHas('user', function ($query) {
                $query->where('status', 'inactive');
            })->with('user')->get();

            $managers = Manager::whereHas('user', function ($query) {
                $query->where('status', 'inactive');
            })->with('user')->get();
            $users_to_approve = $distributors->concat($managers);
            $user_type = 'Distributors & Managers';
        } elseif ($user->hasRole('admin')) {
            $users_to_approve = FieldStaff::whereHas('user', function ($query) {
                $query->where('status', 'inactive');
            })->with('user')->get();
            $user_type = 'Field Staff';
        } elseif ($user->hasRole('manager')) {
            $users_to_approve = Retailer::whereHas('user', function ($query) {
                $query->where('status', 'inactive');
            })->with('user')->get();
            $user_type = 'Retailers';
        } else {
            // Fallback for users with other roles, to ensure the page doesn't break
            $users_to_approve = collect();
            $user_type = 'No approvals for your role';
        }

        return view('approvals.pending', [
            'users_to_approve' => $users_to_approve,
            'user_type' => $user_type
        ]);
    }

    public function approve(User $user)
    {
        $user->status = 'active';
        $user->save();

        return redirect()->route('admin.users.pending_approval')->with('success', 'User approved successfully.');
    }
}
