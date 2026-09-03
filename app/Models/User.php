<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // Added this line
use App\Models\PermissionCategory;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles; // Added HasRoles here

    protected $guard_name = 'web';
    protected $appends = ['avatar_url', 'avatar'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'regulations',
        'profile_pic',
        'status',
        'contact_no',
        'address',
        'city',
        'pincode',
        'fathers_name',
        'mothers_name',
        'player_id',
        'device_uuid',
        'device_bound_at',
        'otp',
        'otp_expires_at',
        'casual_leaves_balance',
        'sick_leaves_balance',
        'paid_leaves_balance',
    ];

    public function distributor()
    {
        return $this->hasOne(Distributor::class);
    }

    public function fieldStaff()
    {
        return $this->hasOne(FieldStaff::class);
    }

    public function retailer()
    {
        return $this->hasOne(Retailer::class);
    }

    public function salesManager()
    {
        return $this->hasOne(SalesManager::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // public function district() { return $this->belongsTo(District::class); }
    // public function area()
    // {
    //     return $this->belongsTo(Area::class);
    // }
    // public function chemists() { return $this->hasMany(Retailer::class);}
    public function orders()
    {
        return $this->hasMany(RetailerOrder::class);
    }
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(UserLeaveBalance::class);
    }

    public function fieldVisits()
    {
        return $this->hasMany(FieldVisit::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function targets()
    {
        return $this->hasMany(SalesTarget::class);
    }

    // public function distributor()
    // {
    //     return $this->belongsTo(Distributor::class, 'distributor_id');
    // }

    public function hasPermissionToCategory($permissionCategoryShortCode, $action)
    {
        $permissionCategory = PermissionCategory::where('short_code', $permissionCategoryShortCode)->first();

        if (!$permissionCategory) {
            return false;
        }

        $roles = $this->getRoleNames();

        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                $query = DB::table('roles_permissions')
                    ->where('role_id', $role->id)
                    ->where('permission_category_id', $permissionCategory->id);

                switch ($action) {
                    case 'view':
                        $query->where('can_view', true);
                        break;
                    case 'add':
                        $query->where('can_add', true);
                        break;
                    case 'edit':
                        $query->where('can_edit', true);
                        break;
                    case 'delete':
                        $query->where('can_delete', true);
                        break;
                    default:
                        // Or maybe throw an exception for invalid action
                        return false;
                }

                if ($query->exists()) {
                    return true;
                }
            }
        }

        return false;
    }
    public function getActionCounts()
    {
        $counts = [
            'retailer_approvals' => 0,
            'distributor_approvals' => 0,
            'staff_expenses' => 0,
            'staff_leaves' => 0,
            'retailer_orders' => 0,
            'distributor_orders' => 0,
            'inactive_users' => 0,
            'inactive_sales_managers' => 0,
            'inactive_distributors' => 0,
            'inactive_field_staff' => 0,
            'inactive_retailers' => 0,
            'loyalty_redemptions' => 0,
        ];

        // 1. Retailer Approvals (Retailer Orders in pending states)
        if ($this->hasPermissionToCategory('retailer_approvals', 'view') || $this->hasAnyRole(['admin', 'superadmin', 'salesmanager', 'fieldstaff', 'distributor'])) {
            $query = \App\Models\RetailerOrder::query();

            if ($this->hasRole('fieldstaff') && $this->fieldStaff) {
                $query->where('fieldstaff_id', $this->fieldStaff->id)->where('status', RetailerOrder::STATUS_PENDING);
            } elseif ($this->hasRole('distributor') && $this->distributor) {
                $query->where('distributor_id', $this->distributor->id)->where('status', RetailerOrder::STATUS_PROCESSING);
            } elseif ($this->hasRole('salesmanager')) {
                // Sales Managers no longer handle retailer approvals
                $query->whereRaw('1 = 0');
            } elseif ($this->hasAnyRole(['admin', 'superadmin'])) {
                // Admin should not get notified on dot icon when distributor needs to approve (processing)
                // And we recently restricted them from pending as well in the UI.
                // So for Retailer Approvals, Admin count is 0 for the dot notification.
                $query->whereRaw('1=0');
            } else {
                $query->whereRaw('1=0');
            }
            $counts['retailer_approvals'] = $query->count();
        }

        // 2. Distributor Order Approvals (Distributor Orders to Admins)
        if ($this->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            $query = \App\Models\DistributorOrder::query();
            if ($this->hasRole('salesmanager')) {
                $query->where('status', DistributorOrder::STATUS_PENDING);
            } else {
                // Admin/Superadmin only see orders that have passed Sales Manager (Processing)
                $query->where('status', DistributorOrder::STATUS_PROCESSING);
            }
            $counts['distributor_approvals'] = $query->count();
        }

        // 3. Staff Approvals (Expenses & Leaves)
        if ($this->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            $expenseQuery = \App\Models\Expense::query();
            $leaveQuery = \App\Models\LeaveRequest::query();

            if ($this->hasRole('salesmanager') && $this->salesManager) {
                $staffIds = User::whereHas('fieldStaff', function($q) {
                    $q->where('sales_manager_id', $this->salesManager->id);
                })->pluck('id');
                
                $expenseQuery->whereIn('user_id', $staffIds)->where('status', 'pending');
                $leaveQuery->whereIn('user_id', $staffIds)->where('status', 'pending');
            } elseif ($this->hasAnyRole(['admin', 'superadmin'])) {
                // Admins primarily see manager_approved for final approval
                $expenseQuery->where('status', 'manager_approved');
                $leaveQuery->where('status', 'manager_approved');
            } else {
                $expenseQuery->whereRaw('1=0');
                $leaveQuery->whereRaw('1=0');
            }
            
            $counts['staff_expenses'] = $expenseQuery->count();
            $counts['staff_leaves'] = $leaveQuery->count();
        }

        // 4. Retailer Orders (Orders to confirm receipt)
        if ($this->hasRole('retailer') && $this->retailer) {
            $counts['retailer_orders'] = \App\Models\RetailerOrder::where('retailer_id', $this->retailer->id)
                ->where('status', RetailerOrder::STATUS_APPROVED)
                ->count();
        }

        // Distributor Orders (Orders to confirm receipt)
        if ($this->hasRole('distributor') && $this->distributor) {
            $counts['distributor_orders'] = \App\Models\DistributorOrder::where('distributor_id', $this->distributor->id)
                ->where('status', \App\Models\DistributorOrder::STATUS_APPROVED)
                ->count();
        }

        // 5. Inactive Users (for Activation)
        if ($this->hasRole('superadmin')) {
            $counts['inactive_sales_managers'] = User::where('status', 'inactive')->where('role', 'salesmanager')->count();
            $counts['inactive_distributors'] = User::where('status', 'inactive')->where('role', 'distributor')->count();
        } elseif ($this->hasRole('admin')) {
            $counts['inactive_field_staff'] = User::where('status', 'inactive')->where('role', 'fieldstaff')->count();
        } elseif ($this->hasRole('salesmanager')) {
            $counts['inactive_retailers'] = User::where('status', 'inactive')->where('role', 'retailer')->count();
        }

        $counts['inactive_users'] = $counts['inactive_sales_managers'] + $counts['inactive_distributors'] + $counts['inactive_field_staff'] + $counts['inactive_retailers'];
        
        // 6. Pending Returns
        $returnQuery = \App\Models\ReturnRequest::query();
        if ($this->hasRole('fieldstaff') && $this->fieldStaff) {
            $returnQuery->where('field_staff_id', $this->fieldStaff->id)->where('status', 'pending')->where('order_type', 'retailer');
        } elseif ($this->hasRole('distributor') && $this->distributor) {
            $returnQuery->where('distributor_id', $this->distributor->id)->where('status', 'verified')->where('order_type', 'retailer');
        } elseif ($this->hasRole('salesmanager') && $this->salesManager) {
            $returnQuery->where('sales_manager_id', $this->salesManager->id)->where('status', 'pending')->where('order_type', 'distributor');
        } elseif ($this->hasAnyRole(['admin', 'superadmin'])) {
            $returnQuery->where('status', 'verified')->where('order_type', 'distributor');
        } else {
            $returnQuery->whereRaw('1=0');
        }
        $counts['pending_returns'] = $returnQuery->count();

        // 7. Loyalty Redemptions (Admin/Superadmin/Salesmanager)
        if ($this->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            $query = \Illuminate\Support\Facades\DB::table('loyalty_redemptions')
                ->join('retailers', 'loyalty_redemptions.retailer_id', '=', 'retailers.id')
                ->where('loyalty_redemptions.status', 'pending');
                
            if ($this->hasRole('salesmanager') && $this->salesManager) {
                $query->where('retailers.sales_manager_id', $this->salesManager->id);
            }
            $counts['loyalty_redemptions'] = $query->count();
        }

        if ($this->hasRole('retailer') && $this->retailer) {
            $brandTotalsQuery = \App\Models\RetailerOrderItem::join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
                ->join('products', 'retailer_order_items.product_id', '=', 'products.id')
                ->join('brands', 'products.brand_id', '=', 'brands.id')
                ->where('retailer_orders.retailer_id', $this->retailer->id)
                ->where('retailer_orders.status', \App\Models\RetailerOrder::STATUS_DELIVERED)
                ->selectRaw('brands.name as brand, SUM(retailer_order_items.quantity * retailer_order_items.unit_price) as total_amount')
                ->groupBy('brands.name')
                ->get();

            $brandTotals = [];
            foreach ($brandTotalsQuery as $row) {
                $cleanBrand = strtoupper(trim($row->brand));
                $brandTotals[$cleanBrand] = ($brandTotals[$cleanBrand] ?? 0) + $row->total_amount;
            }

            $slabs = \App\Models\LoyaltySlab::with('brand')->orderBy('min_points')->get();
            $loyaltyRules = [];
            foreach ($slabs as $slab) {
                if (!$slab->brand) continue;
                $brand = strtoupper(trim($slab->brand->name));
                $loyaltyRules[$brand][] = [
                    'id' => $slab->id,
                    'threshold' => $slab->min_points,
                ];
            }

            $redemptions = \Illuminate\Support\Facades\DB::table('loyalty_redemptions')
                ->join('loyalty_slabs', 'loyalty_redemptions.loyalty_slab_id', '=', 'loyalty_slabs.id')
                ->join('brands', 'loyalty_slabs.brand_id', '=', 'brands.id')
                ->where('loyalty_redemptions.retailer_id', $this->retailer->id)
                ->select('loyalty_slabs.id', 'brands.name as type', 'loyalty_slabs.min_points')
                ->get();

            $claimableCount = 0;
            foreach ($loyaltyRules as $brand => $rules) {
                $brandRedemptions = $redemptions->filter(function($item) use ($brand) {
                    return strtoupper(trim($item->type)) === $brand;
                });
                $totalSpent = $brandRedemptions->sum('min_points');
                $currentTotal = ($brandTotals[$brand] ?? 0) - $totalSpent;
                if ($currentTotal < 0) $currentTotal = 0;

                usort($rules, function($a, $b) { return $a['threshold'] <=> $b['threshold']; });

                foreach ($rules as $rule) {
                    if ($currentTotal >= $rule['threshold']) {
                        $claimableCount++;
                        // We do not subtract currentTotal here because we want to mimic controller logic where user must have passed the threshold (meaning they have that total balance).
                        // Actually, in LoyaltyPointsController, it just pushes to $achievedRewards if $currentTotal >= $rule['threshold']. It does NOT subtract.
                    }
                }
            }
            $counts['retailer_claimable_rewards'] = $claimableCount;
        }

        return $counts;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->profile_pic) {
            return asset('storage/' . $this->profile_pic);
        }

        $name = urlencode($this->name);

        // Define role-based colors (Dark Backgrounds)
        $roleColors = [
            'admin' => '000000',          // Black
            'superadmin' => '000000',     // Black
            'salesmanager' => '1E3A8A',   // Dark Blue
            'distributor' => '064E3B',    // Dark Green
            'fieldstaff' => '7C2D12',     // Dark Orange/Brown
            'retailer' => '7F1D1D',        // Dark Red
        ];

        // Get first role or default
        $role = $this->getRoleNames()->first();
        // Normalize role name just in case
        $roleKey = $role ? strtolower($role) : 'default';

        $background = $roleColors[$roleKey] ?? '374151'; // Default Dark Gray
        $color = 'FFFFFF'; // White Text

        return "https://ui-avatars.com/api/?name={$name}&color={$color}&background={$background}";
    }

    public function getAvatarAttribute()
    {
        return $this->avatar_url;
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
