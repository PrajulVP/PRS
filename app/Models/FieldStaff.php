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
        'status',
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
        return $this->hasMany(distributorOrder::class);
    }

    public function retailers(): HasMany
    {
        return $this->hasMany(Retailer::class);
    }

    public function salesTargets(): HasMany
    {
        return $this->hasMany(SalesTarget::class);
    }
}