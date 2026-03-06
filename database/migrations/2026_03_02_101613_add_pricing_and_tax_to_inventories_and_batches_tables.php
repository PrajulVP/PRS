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
            $table->decimal('mrp', 10, 2)->nullable();
            $table->decimal('ptr', 10, 2)->nullable();
            $table->decimal('pts', 10, 2)->nullable();
            $table->decimal('taxable_value', 10, 2)->nullable();
            $table->decimal('cgst', 5, 2)->nullable();
            $table->decimal('sgst', 5, 2)->nullable();
            $table->decimal('igst', 5, 2)->nullable();
            $table->decimal('net_amount', 12, 2)->nullable();
        });

        Schema::table('distributor_order_item_batches', function (Blueprint $table) {
            $table->decimal('mrp', 10, 2)->nullable();
            $table->decimal('ptr', 10, 2)->nullable();
            $table->decimal('pts', 10, 2)->nullable();
            $table->decimal('taxable_value', 10, 2)->nullable();
            $table->decimal('cgst', 5, 2)->nullable();
            $table->decimal('sgst', 5, 2)->nullable();
            $table->decimal('igst', 5, 2)->nullable();
            $table->decimal('net_amount', 12, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn([
                'mrp',
                'ptr',
                'pts',
                'taxable_value',
                'cgst',
                'sgst',
                'igst',
                'net_amount'
            ]);
        });

        Schema::table('distributor_order_item_batches', function (Blueprint $table) {
            $table->dropColumn([
                'mrp',
                'ptr',
                'pts',
                'taxable_value',
                'cgst',
                'sgst',
                'igst',
                'net_amount'
            ]);
        });
    }
};
