<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->string('icon')->default('fa-tag');
            $table->string('layout_type')->default('general'); // medical, ortho, general
            $table->timestamps();
        });

        // Seed with existing values from settings database
        try {
            $medicalTitle = \DB::table('settings')->where('slug', 'type_medical_title')->value('value') ?: 'ATOMEDS';
            $medicalDesc = \DB::table('settings')->where('slug', 'type_medical_desc')->value('value') ?: 'Medicines';
            
            $orthoTitle = \DB::table('settings')->where('slug', 'type_ortho_title')->value('value') ?: 'ATOMSHIELD';
            $orthoDesc = \DB::table('settings')->where('slug', 'type_ortho_desc')->value('value') ?: 'Surgical and Orthopedic Supports';
            
            $generalTitle = \DB::table('settings')->where('slug', 'type_general_title')->value('value') ?: 'SUDHNEELGIRI';
            $generalDesc = \DB::table('settings')->where('slug', 'type_general_desc')->value('value') ?: 'Herbals';

            $brandsRaw = \DB::table('settings')->where('slug', 'product_brands')->value('value') ?: 'Atomets,Atomshield,Sudhneelgiri';
            $existingBrands = array_filter(array_map('trim', explode(',', $brandsRaw)));

            $initials = [
                ['name' => $medicalTitle, 'description' => $medicalDesc, 'icon' => 'fa-flask', 'layout_type' => 'medical'],
                ['name' => $orthoTitle, 'description' => $orthoDesc, 'icon' => 'fa-universal-access', 'layout_type' => 'ortho'],
                ['name' => $generalTitle, 'description' => $generalDesc, 'icon' => 'fa-leaf', 'layout_type' => 'general'],
            ];

            foreach ($initials as $init) {
                \DB::table('brands')->insert([
                    'name' => $init['name'],
                    'description' => $init['description'],
                    'icon' => $init['icon'],
                    'layout_type' => $init['layout_type'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $initialNames = array_map('strtolower', [$medicalTitle, $orthoTitle, $generalTitle]);
            // Also map fallbacks
            $initialNames[] = 'atomets';
            $initialNames[] = 'atomshield';
            $initialNames[] = 'sudhneelgiri';

            foreach ($existingBrands as $brandName) {
                if (!in_array(strtolower($brandName), $initialNames)) {
                    \DB::table('brands')->insert([
                        'name' => $brandName,
                        'description' => 'General Brand',
                        'icon' => 'fa-tag',
                        'layout_type' => 'general',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silence exceptions in case DB tables do not exist yet during testing
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
