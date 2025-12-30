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
        Schema::table('distributor_orders', function (Blueprint $table) {
            $table->string('status', 50)->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('distributor_orders', function (Blueprint $table) {
            // Revert is risky if data exists, but we can try setting back to presumed enum or shorter string
            // Assuming previous was enum or short string. Let's keep it as string for safety in down.
            $table->string('status', 255)->change();
        });
    }
};
