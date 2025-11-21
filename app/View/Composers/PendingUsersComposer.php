<?php

namespace App\View\Composers;

use App\Models\Distributor;
use App\Models\FieldStaff;
use App\Models\Manager;
use App\Models\Retailer;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PendingUsersComposer
{
    public function compose(View $view)
    {
        $user = Auth::user();
        $pendingUsersCount = 0;

        if ($user) {
            if ($user->hasRole('superadmin')) {
                $distributors_count = Distributor::whereHas('user', function ($query) {
                    $query->where('status', 'inactive');
                })->count();
                $managers_count = Manager::whereHas('user', function ($query) {
                    $query->where('status', 'inactive');
                })->count();
                $pendingUsersCount = $distributors_count + $managers_count;
            } elseif ($user->hasRole('admin')) {
                $pendingUsersCount = FieldStaff::whereHas('user', function ($query) {
                    $query->where('status', 'inactive');
                })->count();
            } elseif ($user->hasRole('manager')) {
                $pendingUsersCount = Retailer::whereHas('user', function ($query) {
                    $query->where('status', 'inactive');
                })->count();
            }
        }

        $view->with('pendingUsersCount', $pendingUsersCount);
    }
}