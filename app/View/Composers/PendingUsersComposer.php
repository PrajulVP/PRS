<?php

namespace App\View\Composers;

use App\Models\Distributor;
use App\Models\FieldStaff;
use App\Models\SalesManager;
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
                $sales_managers_count = SalesManager::whereHas('user', function ($query) {
                    $query->where('status', 'inactive');
                })->count();
                $pendingUsersCount = $distributors_count + $sales_managers_count;
            } elseif ($user->hasRole('admin')) {
                $pendingUsersCount = FieldStaff::whereHas('user', function ($query) {
                    $query->where('status', 'inactive');
                })->count();
            } elseif ($user->hasRole('salesmanager')) {
                $pendingUsersCount = Retailer::whereHas('user', function ($query) {
                    $query->where('status', 'inactive');
                })->count();
            }
        }

        $view->with('pendingUsersCount', $pendingUsersCount);
    }
}