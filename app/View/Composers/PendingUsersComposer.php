<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\User;

class PendingUsersComposer
{
    public function compose(View $view)
    {
        $pendingUsersCount = User::where('status', 'inactive')->count();
        $view->with('pendingUsersCount', $pendingUsersCount);
    }
}
