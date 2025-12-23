<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run()
    {
        Setting::updateOrCreate(
            ['slug' => 'loyalty_point_inr'],
            ['name' => 'Loyalty point INR', 'description' => 'INR value of 1 loyalty point', 'value' => '0']
        );
    }
}
