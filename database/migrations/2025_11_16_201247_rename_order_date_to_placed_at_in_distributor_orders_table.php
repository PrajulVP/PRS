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
            if (Schema::hasColumn('distributor_orders', 'order_date') && !Schema::hasColumn('distributor_orders', 'placed_at')) {
                $table->renameColumn('order_date', 'placed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_orders', function (Blueprint $table) {
            if (Schema::hasColumn('distributor_orders', 'placed_at') && !Schema::hasColumn('distributor_orders', 'order_date')) {
                $table->renameColumn('placed_at', 'order_date');
            }
        });
    }
};
