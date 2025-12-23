<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Product;
use App\Models\PermissionCategory;

use App\Models\RetailerOrder;
use App\Models\distributorOrder;

use App\Models\FieldStaff;
use App\Models\Retailer;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }
}
