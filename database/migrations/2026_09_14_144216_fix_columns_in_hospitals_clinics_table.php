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
        Schema::table('hospitals_clinics', function (Blueprint $table) {
            if (!Schema::hasColumn('hospitals_clinics', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('hospitals_clinics', 'address')) {
                $table->text('address')->nullable()->after('name');
            }
            if (!Schema::hasColumn('hospitals_clinics', 'district_id')) {
                $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null')->after('address');
            }
            if (!Schema::hasColumn('hospitals_clinics', 'area_id')) {
                $table->foreignId('area_id')->nullable()->constrained('areas')->onDelete('set null')->after('district_id');
            }
            if (!Schema::hasColumn('hospitals_clinics', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('area_id');
            }
            if (!Schema::hasColumn('hospitals_clinics', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('hospitals_clinics', 'location_locked')) {
                $table->boolean('location_locked')->default(false)->after('longitude');
            }
            if (!Schema::hasColumn('hospitals_clinics', 'location_updated_at')) {
                $table->timestamp('location_updated_at')->nullable()->after('location_locked');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hospitals_clinics', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'address', 'district_id', 'area_id', 'latitude', 'longitude', 'location_locked', 'location_updated_at'
            ]);
        });
    }
};
