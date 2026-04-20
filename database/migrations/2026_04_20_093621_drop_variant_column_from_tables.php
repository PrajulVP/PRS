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
            $table->dropColumn('variant');
        });

        Schema::table('retailer_order_items', function (Blueprint $table) {
            $table->dropColumn('variant');
        });

        Schema::table('distributor_order_items', function (Blueprint $table) {
            $table->dropColumn('variant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->string('variant')->nullable()->after('batch_no');
        });

        Schema::table('retailer_order_items', function (Blueprint $table) {
            $table->string('variant')->nullable()->after('total_amount');
        });

        Schema::table('distributor_order_items', function (Blueprint $table) {
            $table->string('variant')->nullable()->after('subtotal');
        });
    }
};
