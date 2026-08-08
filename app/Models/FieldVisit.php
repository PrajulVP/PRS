<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldVisit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'party_type',
        'party_id',
        'start_at',
        'end_at',
        'purpose_id',
        'remarks',
        'location_lat',
        'location_lng',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purpose()
    {
        return $this->belongsTo(VisitPurpose::class);
    }

    /**
     * Get the associated party based on party_type and party_id
     */
    public function getPartyAttribute()
    {
        if ($this->party_type === 'retailer' && $this->party_id) {
            return Retailer::find($this->party_id);
        } elseif ($this->party_type === 'distributor' && $this->party_id) {
            return Distributor::find($this->party_id);
        }
        
        return null;
    }
}
