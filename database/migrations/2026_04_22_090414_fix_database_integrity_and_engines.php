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
        // 1. Convert Engines to InnoDB (Ensures Foreign Key support)
        $tables = ['products', 'distributor_orders', 'distributor_order_items', 'distributors', 'retailer_orders', 'retailer_order_items', 'retailers'];
        foreach ($tables as $table) {
            try {
                DB::statement("ALTER TABLE `{$table}` ENGINE=InnoDB");
            } catch (\Exception $e) {
                // Ignore if already InnoDB or table missing
            }
        }

        // 2. Drop and Recreate Foreign Keys using Raw SQL for resilience
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Drop existing keys if they exist (Product ID)
        try {
            DB::statement("ALTER TABLE distributor_order_items DROP FOREIGN KEY `distributor_order_items_product_id_foreign`");
        } catch (\Exception $e) {
            try {
                DB::statement("ALTER TABLE distributor_order_items DROP INDEX `distributor_order_items_product_id_foreign`");
            } catch (\Exception $ex) {}
        }

        // Drop existing keys if they exist (Order ID)
        try {
            DB::statement("ALTER TABLE distributor_order_items DROP FOREIGN KEY `distributor_order_items_distributor_order_id_foreign`");
        } catch (\Exception $e) {
            try {
                DB::statement("ALTER TABLE distributor_order_items DROP INDEX `distributor_order_items_distributor_order_id_foreign`");
            } catch (\Exception $ex) {}
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Now recreate them using the Schema builder (which is clean now)
        Schema::table('distributor_order_items', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('distributor_order_id')->references('id')->on('distributor_orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('distributor_order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['distributor_order_id']);
        });
    }
};
