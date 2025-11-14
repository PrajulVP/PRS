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
            $table->foreignId('distributor_id')->nullable()->constrained('distributors')->cascadeOnDelete();
            $table->foreignId('fieldstaff_id')->nullable()->constrained('fieldstaffs')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_orders', function (Blueprint $table) {
            $table->dropForeign(['distributor_id']);
            $table->dropColumn('distributor_id');
            $table->dropForeign(['fieldstaff_id']);
            $table->dropColumn('fieldstaff_id');
        });
    }
};