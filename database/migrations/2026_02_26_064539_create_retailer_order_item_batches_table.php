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
        Schema::create('retailer_order_item_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retailer_order_item_id')->constrained('retailer_order_items')->onDelete('cascade');
            $table->string('batch_no');
            $table->date('expiry_date')->nullable();
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retailer_order_item_batches');
    }
};
