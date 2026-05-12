<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_code',
        'order_type',
        'order_id',
        'user_id',
        'product_id',
        'product_name',
        'side',
        'size',
        'quantity',
        'unit',
        'reason',
        'image_path',
        'image_paths',
        'distributor_id',
        'field_staff_id',
        'sales_manager_id',
        'status',
        'refund_amount',
        'verified_at',
        'verified_by',
        'distributor_approved_at',
        'distributor_approved_by',
        'admin_approved_at',
        'admin_approved_by',
        'rejection_reason',
        'rejected_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'distributor_approved_at' => 'datetime',
        'admin_approved_at' => 'datetime',
        'refund_amount' => 'decimal:2',
        'quantity' => 'decimal:2',
        'image_paths' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        if ($this->order_type === 'retailer') {
            return $this->belongsTo(RetailerOrder::class, 'order_id');
        }
        return $this->belongsTo(DistributorOrder::class, 'order_id');
    }

    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function distributorApprovedByUser()
    {
        return $this->belongsTo(User::class, 'distributor_approved_by');
    }

    public function adminApprover()
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function field_staff()
    {
        return $this->belongsTo(FieldStaff::class, 'field_staff_id');
    }

    public function sales_manager()
    {
        return $this->belongsTo(SalesManager::class, 'sales_manager_id');
    }
}
