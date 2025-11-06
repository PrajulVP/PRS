<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $districts = [
            ['name' => 'Thiruvananthapuram'],
            ['name' => 'Kollam'],
            ['name' => 'Pathanamthitta'],
            ['name' => 'Alappuzha'],
            ['name' => 'Kottayam'],
            ['name' => 'Idukki'],
            ['name' => 'Ernakulam'],
            ['name' => 'Thrissur'],
            ['name' => 'Palakkad'],
            ['name' => 'Malappuram'],
            ['name' => 'Kozhikode'],
            ['name' => 'Wayanad'],
            ['name' => 'Kannur'],
            ['name' => 'Kasaragod'],
        ];

        DB::table('districts')->insert($districts);
    }
}
