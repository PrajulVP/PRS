<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VisitPurpose;

class VisitPurposeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purposes = [
            ['name' => 'New Visit'],
            ['name' => 'Sample Distribution'],
            ['name' => 'Brochure Supply'],
            ['name' => 'Gift Distribution'],
            ['name' => 'Payment Collection'],
            ['name' => 'Complaint Handling'],
            ['name' => 'Other'],
        ];

        foreach ($purposes as $purpose) {
            VisitPurpose::firstOrCreate($purpose);
        }
    }
}
