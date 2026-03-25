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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('units_per_strip')->default(1)->after('strip_size');
            $table->integer('strips_per_box')->default(1)->after('box_size');
            $table->integer('boxes_per_carton')->default(1)->after('carton_size');
            $table->boolean('has_variants')->default(false)->after('hsn_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['units_per_strip', 'strips_per_box', 'boxes_per_carton', 'has_variants']);
        });
    }
};
