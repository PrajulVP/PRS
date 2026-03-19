<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SidebarController extends Controller
{
    /**
     * Get updated action counts for the sidebar.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCounts()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $counts = $user->getActionCounts();

        return response()->json([
            'success' => true,
            'counts' => $counts
        ]);
    }
}
