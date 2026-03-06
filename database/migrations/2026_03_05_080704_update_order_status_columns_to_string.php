<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('distributor_orders', function (Blueprint $table) {
            $table->string('status')->change();
        });

        Schema::table('retailer_orders', function (Blueprint $table) {
            $table->string('status')->change();
        });

        // Migrate old values to standardized ones for Distributor Orders
        DB::table('distributor_orders')
            ->where('status', 'accepted_by_sales_manager')
            ->update(['status' => 'processing']);

        DB::table('distributor_orders')
            ->where('status', 'cancellation_requested')
            ->update(['status' => 'cancelled']);

        // Migrate old values to standardized ones for Retailer Orders
        DB::table('retailer_orders')
            ->whereIn('status', ['shipped', 'dispatched', 'assigned_to_fieldstaff'])
            ->update(['status' => 'processing']);

        DB::table('retailer_orders')
            ->where('status', 'accepted_by_distributor')
            ->update(['status' => 'accepted']);

        DB::table('retailer_orders')
            ->where('status', 'cancellation_requested')
            ->update(['status' => 'cancelled']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'accepted_by_sales_manager',
                'delivered',
                'cancelled',
                'cancellation_requested'
            ])->default('pending')->change();
        });

        Schema::table('retailer_orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'accepted',
                'shipped',
                'dispatched',
                'delivered',
                'cancelled',
                'cancellation_requested'
            ])->default('pending')->change();
        });
    }
};
