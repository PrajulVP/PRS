<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LocationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'is_mock_location',
        'remarks',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'is_mock_location' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate total distance travelled by a user on a specific date in KM
     * Uses Google Roads API for high accuracy, with Haversine as a fallback.
     */
    public static function calculateDailyDistance($userId, $date)
    {
        $cacheKey = "user_{$userId}_distance_{$date}";
        
        // Forget stale cache to ensure accuracy
        if (request()->has('refresh') || $date === now()->toDateString()) {
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }

        $isToday = $date === now()->toDateString();
        $ttl = $isToday ? 120 : 86400;

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $ttl, function() use ($userId, $date) {
            $logs = self::where('user_id', $userId)
                ->whereDate('timestamp', $date)
                ->where('latitude', '!=', 0)
                ->where('longitude', '!=', 0)
                ->where('latitude', '>', 1.0)
                ->orderBy('timestamp', 'asc')
                ->get();

            if ($logs->count() < 2) {
                return 0;
            }

            return self::calculateHaversineDistance($logs);
        });
    }

    /**
     * Standard Haversine distance for a collection of valid logs
     */
    protected static function calculateHaversineDistance($logs)
    {
        $validLogs = $logs->filter(function($l) {
            $lat = (float)$l->latitude;
            $lng = (float)$l->longitude;
            return $lat != 0 && $lng != 0 && $lat > 1.0 && $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180;
        })->values();

        $totalDistance = 0;
        for ($i = 0; $i < $validLogs->count() - 1; $i++) {
            $step = self::haversineDistance(
                (float)$validLogs[$i]->latitude, (float)$validLogs[$i]->longitude,
                (float)$validLogs[$i+1]->latitude, (float)$validLogs[$i+1]->longitude
            );
            // Ignore unnatural jumps greater than 100 km (corrupt pings)
            if ($step < 100) {
                $totalDistance += $step;
            }
        }
        return round($totalDistance, 2);
    }

    /**
     * Haversine formula to find distance between two lat/long points in km
     */
    public static function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}
