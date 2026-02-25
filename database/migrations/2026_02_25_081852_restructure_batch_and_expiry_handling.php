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
        // 1. Remove batch_no from products
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'batch_no')) {
                $table->dropColumn('batch_no');
            }
        });

        // 2. Add batch_no and expiry_date to inventories
        Schema::table('inventories', function (Blueprint $table) {
            $table->string('batch_no')->nullable()->after('stock');
            $table->date('expiry_date')->nullable()->after('batch_no');
        });

        // 3. Create distributor_order_item_batches for multi-batch approval
        Schema::create('distributor_order_item_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_order_item_id')->constrained('distributor_order_items')->onDelete('cascade');
            $table->string('batch_no');
            $table->date('expiry_date');
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_order_item_batches');

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['batch_no', 'expiry_date']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('batch_no')->nullable();
        });
    }
};
