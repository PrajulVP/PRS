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
        Schema::table('distributor_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('distributor_order_items', 'free_quantity')) {
                $table->integer('free_quantity')->default(0)->after('price');
            }
            $table->unsignedBigInteger('free_product_id')->nullable()->after('free_quantity');
            $table->string('free_side')->nullable()->after('free_product_id');
            $table->string('free_size')->nullable()->after('free_side');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_order_items', function (Blueprint $table) {
            $table->dropColumn(['free_product_id', 'free_side', 'free_size']);
            // We'll leave free_quantity alone on down just in case it was already there
        });
    }
};
