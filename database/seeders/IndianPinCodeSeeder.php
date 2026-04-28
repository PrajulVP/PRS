<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\District;
use App\Models\Area;

class IndianPinCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '-1'); // Allow unlimited memory for this script

        // Path to the NEW JSON file
        $jsonPath = database_path('data/pincodes.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("File not found: $jsonPath");
            return;
        }

        $json = File::get($jsonPath);
        $data = json_decode($json, true);

        if (!$data) {
            $this->command->error("Invalid JSON data: " . json_last_error_msg());
            return;
        }

        // Sort data alphabetically by District and then Area (officeName)
        usort($data, function ($a, $b) {
            $districtCmp = strcasecmp($a['districtName'] ?? '', $b['districtName'] ?? '');
            if ($districtCmp !== 0) return $districtCmp;
            return strcasecmp($a['officeName'] ?? '', $b['officeName'] ?? '');
        });

        // Disable foreign key checks to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Area::truncate();
        District::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Truncated districts and areas tables.');

        $districtsMap = []; // To track created districts
        $areasData = [];
        $csvCount = 0;

        foreach ($data as $record) {
            $stateName = strtoupper($record['stateName'] ?? '');

            // FILTER: Only Kerala
            if ($stateName !== 'KERALA' && $stateName !== 'KERALA') {
                // Note: Sometimes casing might be "Kerala" or "KERALA" in data
                if (strcasecmp($record['stateName'], 'Kerala') !== 0) continue;
            }

            $districtName = trim($record['districtName'] ?? '');
            $areaName = trim($record['officeName'] ?? '');
            $pincode = trim($record['pincode'] ?? '');

            if (!$districtName || !$areaName) continue;

            // Get or Create District
            if (!isset($districtsMap[$districtName])) {
                $district = District::firstOrCreate(['name' => $districtName]);
                $districtsMap[$districtName] = $district->id;
            }

            $districtId = $districtsMap[$districtName];

            // Prepare Area Data
            // Concatenating Pincode to name for better clarity? Or just Office Name?
            // User Request: "officeName is the area name"
            $areasData[] = [
                'district_id' => $districtId,
                'name' => $areaName, // "Area Name"
                'pincode' => $pincode,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $csvCount++;

            // Batch insert every 1000 records to avoid memory issues
            if (count($areasData) >= 1000) {
                Area::insert($areasData);
                $areasData = [];
            }
        }

        // Insert remaining
        if (!empty($areasData)) {
            Area::insert($areasData);
        }

        $this->command->info("Seeded Districts and Areas for Kerala from new JSON. Total Areas: $csvCount");
    }
}
