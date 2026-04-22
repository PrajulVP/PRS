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
        Schema::table('distributors', function (Blueprint $table) {
            $table->decimal('loyalty_points', 15, 2)->default(0);
        });

        Schema::table('loyalty_slabs', function (Blueprint $table) {
            $table->string('type')->default('retailer')->after('slab_name');
        });

        Schema::table('loyalty_redemptions', function (Blueprint $table) {
            $table->foreignId('distributor_id')->nullable()->after('retailer_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loyalty_redemptions', function (Blueprint $table) {
            $table->dropForeign(['distributor_id']);
            $table->dropColumn('distributor_id');
        });

        Schema::table('loyalty_slabs', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('distributors', function (Blueprint $table) {
            $table->dropColumn('loyalty_points');
        });
    }
};
