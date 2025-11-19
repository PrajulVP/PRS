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
        Schema::create('distributor_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->foreignId('distributor_id')->constrained('distributors')->onDelete('cascade');
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'accepted', 'dispatched', 'delivered', 'cancelled'])->default('pending');
            $table->timestamp('placed_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('prescription_photo')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->foreignId('fieldstaff_id')->nullable()->constrained('fieldstaffs')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_orders');
    }
};