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
            $table->dropForeign(['retailer_id']);
            $table->dropColumn('retailer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_orders', function (Blueprint $table) {
            $table->foreignId('retailer_id')->constrained('retailers')->cascadeOnDelete();
        });
    }
};
