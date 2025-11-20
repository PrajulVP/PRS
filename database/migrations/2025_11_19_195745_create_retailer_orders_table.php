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
            $table->string('order_code'); // Not unique anymore
            $table->foreignId('distributor_id')->nullable()->constrained('distributors')->onDelete('set null');
            $table->foreignId('retailer_id')->constrained('retailers')->onDelete('cascade');
            $table->decimal('total_amount', 10, 2)->default(0.00); // Added, with default
            $table->integer('total_items')->default(0); // Added, with default
            $table->integer('total_quantity')->default(0); // Added, with default
            $table->string('status', 50)->default('pending');
            $table->timestamp('placed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('fieldstaff_id')->nullable()->constrained('fieldstaffs')->onDelete('set null');
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