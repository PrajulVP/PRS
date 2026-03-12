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
            'retailer_orders' => 0,
            'distributor_orders' => 0,
        ];

        // 1. Retailer Approvals (Retailer Orders in pending states)
        if ($this->hasPermissionToCategory('retailer_approvals', 'view') || $this->hasAnyRole(['admin', 'superadmin', 'salesmanager', 'fieldstaff', 'distributor'])) {
            $query = \App\Models\RetailerOrder::query();

            if ($this->hasRole('fieldstaff') && $this->fieldStaff) {
                $query->where('fieldstaff_id', $this->fieldStaff->id)->where('status', RetailerOrder::STATUS_PENDING);
            } elseif ($this->hasRole('distributor') && $this->distributor) {
                $query->where('distributor_id', $this->distributor->id)->where('status', RetailerOrder::STATUS_PROCESSING);
            } elseif ($this->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
                $query->whereIn('status', [RetailerOrder::STATUS_PENDING, RetailerOrder::STATUS_PROCESSING]);
            } else {
                $query->whereRaw('1=0');
            }
            $counts['retailer_approvals'] = $query->count();
        }

        // 2. Distributor Order Approvals
        if ($this->hasAnyRole(['admin', 'superadmin', 'salesmanager'])) {
            $query = \App\Models\DistributorOrder::query();
            if ($this->hasRole('salesmanager')) {
                $query->where('status', DistributorOrder::STATUS_PENDING);
            } else {
                $query->whereIn('status', [DistributorOrder::STATUS_PENDING, DistributorOrder::STATUS_PROCESSING]);
            }
            $counts['distributor_approvals'] = $query->count();
        }

        // 3. Retailer Orders (Orders to confirm receipt)
        if ($this->hasRole('retailer') && $this->retailer) {
            $counts['retailer_orders'] = \App\Models\RetailerOrder::where('retailer_id', $this->retailer->id)
                ->where('status', RetailerOrder::STATUS_APPROVED)
                ->count();
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
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->profile_pic);
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

    public function getJWTCustomClaims()
    {
        return [];
    }
}
