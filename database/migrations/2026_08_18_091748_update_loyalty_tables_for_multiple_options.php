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
        Schema::table('loyalty_slabs', function (Blueprint $table) {
            $table->json('reward_options')->nullable()->after('gift_name');
            $table->dropColumn('gift_image');
        });

        Schema::table('loyalty_redemptions', function (Blueprint $table) {
            $table->string('selected_reward')->nullable()->after('loyalty_slab_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loyalty_slabs', function (Blueprint $table) {
            $table->dropColumn('reward_options');
            $table->string('gift_image')->nullable()->after('gift_name');
        });

        Schema::table('loyalty_redemptions', function (Blueprint $table) {
            $table->dropColumn('selected_reward');
        });
    }
};
