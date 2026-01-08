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
        Schema::create('retailer_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->foreignId('retailer_id')->constrained('retailers')->onDelete('cascade');
            $table->foreignId('distributor_id')->nullable()->constrained('distributors')->onDelete('set null');
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->integer('total_items')->default(0);
            $table->integer('total_quantity')->default(0);
            $table->enum('status', ['pending', 'accepted', 'shipped', 'dispatched', 'delivered', 'cancelled', 'cancellation_requested'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'partially_paid'])->default('unpaid');
            $table->string('invoice_path')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retailer_orders');
    }
};
