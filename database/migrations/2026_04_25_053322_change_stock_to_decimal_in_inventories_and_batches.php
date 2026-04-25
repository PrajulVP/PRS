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
        Schema::table('inventories', function (Blueprint $table) {
            $table->decimal('stock', 15, 4)->default(0)->change();
        });

        Schema::table('retailer_order_item_batches', function (Blueprint $table) {
            $table->decimal('quantity', 15, 4)->change();
        });

        Schema::table('distributor_order_item_batches', function (Blueprint $table) {
            $table->decimal('quantity', 15, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->integer('stock')->default(0)->change();
        });

        Schema::table('retailer_order_item_batches', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('distributor_order_item_batches', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });
    }
};
