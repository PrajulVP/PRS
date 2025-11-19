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
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->foreignId('field_staff_id')->nullable()->constrained('fieldstaffs')->onDelete('set null')->after('distributor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->dropForeign(['field_staff_id']);
            $table->dropColumn('field_staff_id');
        });
    }
};
