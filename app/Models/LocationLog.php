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
        
        // If it's a past date, we can cache it for a long time. 
        // If it's today, we only cache for 5 minutes.
        $isToday = $date === now()->toDateString();
        $ttl = $isToday ? 300 : 86400;

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $ttl, function() use ($userId, $date) {
            $logs = self::where('user_id', $userId)
                ->whereDate('timestamp', $date)
                ->orderBy('timestamp', 'asc')
                ->get();

            if ($logs->count() < 2) {
                return 0;
            }

            $apiKey = config('services.google_maps.key');
            
            if ($apiKey) {
                try {
                    return self::calculateRoadDistance($logs, $apiKey);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Roads API distance calculation failed, falling back to Haversine: " . $e->getMessage());
                }
            }

            // Fallback to Haversine
            return self::calculateHaversineDistance($logs);
        });
    }

    /**
     * Calculate distance by snapping points to roads via Google Roads API
     */
    protected static function calculateRoadDistance($logs, $apiKey)
    {
        $points = $logs->map(fn($l) => "{$l->latitude},{$l->longitude}")->toArray();
        $chunks = array_chunk($points, 100); // Roads API limit is 100 points per request
        $totalDistance = 0;
        $lastPoint = null;

        foreach ($chunks as $chunk) {
            $path = implode('|', $chunk);
            $response = \Illuminate\Support\Facades\Http::get("https://roads.googleapis.com/v1/snapToRoads", [
                'path' => $path,
                'interpolate' => 'true',
                'key' => $apiKey
            ]);

            if ($response->successful()) {
                $snappedPoints = $response->json()['snappedPoints'] ?? [];
                
                for ($i = 0; $i < count($snappedPoints); $i++) {
                    $currentPoint = [
                        'lat' => $snappedPoints[$i]['location']['latitude'],
                        'lng' => $snappedPoints[$i]['location']['longitude']
                    ];

                    if ($lastPoint) {
                        $totalDistance += self::haversineDistance(
                            $lastPoint['lat'], $lastPoint['lng'],
                            $currentPoint['lat'], $currentPoint['lng']
                        );
                    }
                    $lastPoint = $currentPoint;
                }
            } else {
                // If one chunk fails, we use Haversine for that segment to avoid 0 distance
                // But for simplicity, let's just throw an exception to trigger the full fallback
                throw new \Exception("Roads API request failed: " . $response->body());
            }
        }

        return round($totalDistance, 2);
    }

    /**
     * Standard Haversine distance for a collection of logs
     */
    protected static function calculateHaversineDistance($logs)
    {
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
