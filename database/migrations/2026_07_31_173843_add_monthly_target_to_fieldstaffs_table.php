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
        Schema::table('fieldstaffs', function (Blueprint $table) {
            $table->decimal('monthly_target', 12, 2)->default(0)->after('sales_manager_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fieldstaffs', function (Blueprint $table) {
            $table->dropColumn('monthly_target');
        });
    }
};
