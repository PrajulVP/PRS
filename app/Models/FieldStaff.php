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

    public function getCurrentMonthTarget()
    {
        $currentMonth = date('F');
        $currentYear = date('Y');

        $salesTarget = $this->salesTargets()->where('month', $currentMonth)->where('year', $currentYear)->first();

        if (!$salesTarget) {
            // Get the most recent target created for this user
            $latestTarget = $this->salesTargets()->latest('id')->first();
            $amount = $latestTarget ? $latestTarget->amount : 0;

            $salesTarget = $this->salesTargets()->create([
                'month' => $currentMonth, 
                'year' => $currentYear,
                'amount' => $amount, 
                'achieved_amount' => 0
            ]);
        }

        return $salesTarget;
    }

    public function getCurrentMonthAchieved()
    {
        return $this->getAchievedAmountForMonth(now()->month, now()->year);
    }

    public function getAchievedAmountForMonth($month, $year)
    {
        return \App\Models\RetailerOrder::join('retailer_order_items', 'retailer_orders.id', '=', 'retailer_order_items.retailer_order_id')
            ->where(function ($q) {
                $q->where('retailer_orders.fieldstaff_id', $this->id)
                    ->orWhereHas('retailer', function ($qr) {
                        $qr->where('field_staff_id', $this->id);
                    });
            })
            ->where('retailer_orders.status', \App\Models\RetailerOrder::STATUS_DELIVERED)
            ->whereMonth('retailer_orders.delivered_at', $month)
            ->whereYear('retailer_orders.delivered_at', $year)
            ->sum(\Illuminate\Support\Facades\DB::raw('retailer_order_items.unit_price * retailer_order_items.quantity'));
    }
}
