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
            if (Schema::hasColumn('products', 'pack_quantity')) {
                $table->renameColumn('pack_quantity', 'quantity');
            }
            if (Schema::hasColumn('products', 'tablets_per_strip')) {
                $table->renameColumn('tablets_per_strip', 'strip_size');
            }
            if (Schema::hasColumn('products', 'strips_per_box')) {
                $table->renameColumn('strips_per_box', 'box_size');
            }
            if (Schema::hasColumn('products', 'boxes_per_carton')) {
                $table->renameColumn('boxes_per_carton', 'carton_size');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('quantity', 'pack_quantity');
            $table->renameColumn('strip_size', 'tablets_per_strip');
            $table->renameColumn('box_size', 'strips_per_box');
            $table->renameColumn('carton_size', 'boxes_per_carton');
        });
    }
};
