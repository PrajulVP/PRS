<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `distributor_orders` CHANGE `status` `status` ENUM('pending', 'accepted_by_sales_manager', 'delivered', 'cancelled', 'cancellation_requested', 'rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_orders', function (Blueprint $table) {
            //
        });
    }
};
