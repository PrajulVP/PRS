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

    public function getDistanceKmAttribute()
    {
        if (!$this->start_at || !$this->end_at) return 0;
        
        $logs = \App\Models\LocationLog::where('user_id', $this->user_id)
            ->whereBetween('created_at', [$this->start_at, $this->end_at])
            ->orderBy('created_at', 'asc')
            ->get();
            
        $distance = 0;
        for ($i = 0; $i < count($logs) - 1; $i++) {
            $distance += \App\Http\Controllers\Admin\FieldStaffVisitController::calculateHaversineDistance(
                $logs[$i]->latitude, $logs[$i]->longitude,
                $logs[$i+1]->latitude, $logs[$i+1]->longitude
            );
        }
        return round($distance, 2);
    }

    public function getIsRepeatAttribute()
    {
        if (!$this->start_at || !$this->party_id || !$this->party_type) return false;

        $previousVisits = self::where('user_id', $this->user_id)
            ->where('party_type', $this->party_type)
            ->where('party_id', $this->party_id)
            ->whereDate('start_at', $this->start_at->format('Y-m-d'))
            ->where('id', '<', $this->id)
            ->count();

        return $previousVisits > 0;
    }
}
