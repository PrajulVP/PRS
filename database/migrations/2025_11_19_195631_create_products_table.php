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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique();
            $table->string('product_name');
            $table->string('generic_name')->nullable();
            $table->string('pack')->nullable();
            $table->string('strip_size')->nullable();
            $table->string('box_size')->nullable();
            $table->string('carton_size')->nullable();
            $table->string('hsn_code')->nullable();
            $table->integer('batch_no')->nullable();
            $table->decimal('mrp', 10, 2)->nullable();
            $table->decimal('ptr', 10, 2)->nullable();
            $table->decimal('pts', 10, 2)->nullable();
            $table->decimal('taxable_value', 10, 2)->nullable();
            $table->decimal('gst', 5, 2)->nullable();
            $table->decimal('offer', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('net_amount', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
