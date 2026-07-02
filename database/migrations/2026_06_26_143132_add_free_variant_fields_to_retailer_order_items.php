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
            $table->string('free_side')->nullable()->after('free_product_id');
            $table->string('free_size')->nullable()->after('free_side');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retailer_order_items', function (Blueprint $table) {
            $table->dropColumn(['free_side', 'free_size']);
        });
    }
};
