<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $settings = [
            [
                'name' => 'Geo-fencing Radius',
                'slug' => 'geofence_radius',
                'value' => '20',
                'description' => 'Maximum allowed radius from customer location for punching logs (in meters).',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'TA Rate per KM',
                'slug' => 'ta_rate_per_km',
                'value' => '10',
                'description' => 'Travel Allowance rate per kilometer travelled.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'DA HQ Rate',
                'slug' => 'da_hq_rate',
                'value' => '250',
                'description' => 'Daily Allowance rate for HQ visits.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'DA Outstation Rate',
                'slug' => 'da_outstation_rate',
                'value' => '500',
                'description' => 'Daily Allowance rate for Outstation visits.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(['slug' => $setting['slug']], $setting);
        }
    }

    public function down()
    {
        DB::table('settings')->whereIn('slug', ['geofence_radius', 'ta_rate_per_km', 'da_hq_rate', 'da_outstation_rate'])->delete();
    }
};
