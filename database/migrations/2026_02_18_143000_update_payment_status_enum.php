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
        // 1. Expand Enum to include new statuses
        DB::statement("ALTER TABLE `distributor_orders` CHANGE `payment_status` `payment_status` ENUM('unpaid', 'paid', 'partially_paid', 'pending', 'failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending';");

        // 2. Migrate old statuses to new ones
        DB::table('distributor_orders')->where('payment_status', 'unpaid')->update(['payment_status' => 'pending']);
        DB::table('distributor_orders')->where('payment_status', 'partially_paid')->update(['payment_status' => 'pending']);

        // 3. Restrict Enum to desired statuses
        DB::statement("ALTER TABLE `distributor_orders` CHANGE `payment_status` `payment_status` ENUM('pending', 'paid', 'failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Expand Enum back to include old statuses (and keep new ones temporarily)
        DB::statement("ALTER TABLE `distributor_orders` CHANGE `payment_status` `payment_status` ENUM('unpaid', 'paid', 'partially_paid', 'pending', 'failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid';");

        // 2. Revert 'pending' to 'unpaid'
        DB::table('distributor_orders')->where('payment_status', 'pending')->update(['payment_status' => 'unpaid']);

        // 3. Restrict Enum back to original
        DB::statement("ALTER TABLE `distributor_orders` CHANGE `payment_status` `payment_status` ENUM('unpaid', 'paid', 'partially_paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid';");
    }
};
