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
        Schema::table('distributor_orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'accepted',
                'dispatched',
                'delivered',
                'cancelled',
                'assigned_to_distributor',
                'assigned_to_fieldstaff',
                'out_for_delivery'
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'accepted',
                'dispatched',
                'delivered',
                'cancelled'
            ])->default('pending')->change();
        });
    }
};