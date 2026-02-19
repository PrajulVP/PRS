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
        Schema::table('retailer_orders', function (Blueprint $table) {
            $table->string('status', 50)->default('pending')->change();
            $table->string('payment_status', 50)->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retailer_orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'accepted', 'shipped', 'dispatched', 'delivered', 'cancelled', 'cancellation_requested'])->default('pending')->change();
            $table->enum('payment_status', ['unpaid', 'paid', 'partially_paid'])->default('unpaid')->change();
        });
    }
};
