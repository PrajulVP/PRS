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
        Schema::table('retailers', function (Blueprint $table) {
            $table->foreignId('district_id')->after('drug_license_no')->nullable()->constrained('districts')->onDelete('set null');
            $table->foreignId('area_id')->after('district_id')->nullable()->constrained('areas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retailers', function (Blueprint $table) {
            $table->dropForeign(['district_id']);
            $table->dropForeign(['area_id']);
            $table->dropColumn(['district_id', 'area_id']);
        });
    }
};
