<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'distance_km',
        'bill_path',
        'is_outstation',
        'status',
        'rejection_reason',
        'manager_id',
        'admin_id',
        'expense_date',
    ];

    protected $casts = [
        'expense_date' => 'datetime',
        'is_outstation' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
