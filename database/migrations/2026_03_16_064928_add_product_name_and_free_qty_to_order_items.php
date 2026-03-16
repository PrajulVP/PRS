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
        Schema::table('retailer_order_items', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('product_id');
            $table->integer('free_quantity')->default(0)->after('quantity');
        });

        Schema::table('distributor_order_items', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('product_id');
            $table->integer('free_quantity')->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('retailer_order_items', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'free_quantity']);
        });

        Schema::table('distributor_order_items', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'free_quantity']);
        });
    }
};
