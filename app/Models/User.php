<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // Added this line
use App\Models\PermissionCategory;
use App\Models\Role;
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

    public function district() { return $this->belongsTo(District::class); }
    public function area()     { return $this->belongsTo(Area::class); }
    // public function chemists() { return $this->hasMany(Retailer::class);}
    public function orders() { return $this->hasMany(RetailerOrder::class); }
    public function targets() { return $this->hasMany(SalesTarget::class); }

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
                $query = \DB::table('roles_permissions')
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
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
