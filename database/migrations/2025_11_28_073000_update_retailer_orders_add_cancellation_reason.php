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
            if (!Schema::hasColumn('retailer_orders', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('delivery_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retailer_orders', function (Blueprint $table) {
            if (Schema::hasColumn('retailer_orders', 'cancellation_reason')) {
                $table->dropColumn('cancellation_reason');
            }
        });
    }
};