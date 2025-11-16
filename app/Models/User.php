<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // Added this line
use App\Models\PermissionCategory;
use App\Models\Role;

class User extends Authenticatable
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
                $hasPermission = \DB::table('roles_permissions')
                    ->where('role_id', $role->id)
                    ->where('permission_category_id', $permissionCategory->id)
                    ->where('can_' . $action, true)
                    ->exists();

                if ($hasPermission) {
                    return true;
                }
            }
        }

        return false;
    }
}
