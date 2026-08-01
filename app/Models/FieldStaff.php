<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class FieldStaff extends Model
{
    use HasFactory;
    protected $table = 'fieldstaffs';
    protected $fillable = [
        'user_id',
        'sales_manager_id',
        'pincode',
        'latitude',
        'longitude',
        'contact_no',
        'address',
        'status',
        'monthly_target',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salesManager(): BelongsTo
    {
        return $this->belongsTo(SalesManager::class, 'sales_manager_id');
    }

    public function distributorOrders(): HasMany
    {
        return $this->hasMany(DistributorOrder::class);
    }

    public function retailers(): HasMany
    {
        return $this->hasMany(Retailer::class);
    }

    public function salesTargets(): HasMany
    {
        return $this->hasMany(SalesTarget::class);
    }

    public function retailerOrders(): HasMany
    {
        return $this->hasMany(RetailerOrder::class, 'fieldstaff_id');
    }

    public function orders(): HasMany
    {
        return $this->retailerOrders();
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'user_id', 'user_id');
    }

    public function visitLogs(): HasMany
    {
        return $this->hasMany(VisitLog::class, 'user_id', 'user_id');
    }

    public function locationLogs(): HasMany
    {
        return $this->hasMany(LocationLog::class, 'user_id', 'user_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'field_staff_id');
    }
}
