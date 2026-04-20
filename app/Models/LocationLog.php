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
     */
    public static function calculateDailyDistance($userId, $date)
    {
        $logs = self::where('user_id', $userId)
            ->whereDate('timestamp', $date)
            ->orderBy('timestamp', 'asc')
            ->get();

        if ($logs->count() < 2) {
            return 0;
        }

        $totalDistance = 0;
        for ($i = 0; $i < $logs->count() - 1; $i++) {
            $totalDistance += self::haversineDistance(
                (float)$logs[$i]->latitude, (float)$logs[$i]->longitude,
                (float)$logs[$i+1]->latitude, (float)$logs[$i+1]->longitude
            );
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
